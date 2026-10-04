<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$courseRes  = apiRequest('/courses', 'GET', null, $_SESSION['token']);
$subjectRes = apiRequest('/subjects', 'GET', null, $_SESSION['token']);
$courses    = $courseRes['body']['data'] ?? [];
$subjects   = $subjectRes['body']['data'] ?? [];

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST['role'];
    $userResponse = apiRequest('/users', 'POST', [
        'full_name' => $_POST['full_name'],
        'email'     => $_POST['email'],
        'role'      => $role,
        'matric_no' => $_POST['matric_no'] ?? null,
        'no_ic'     => $_POST['no_ic'],
        'phone'     => $_POST['phone']
    ], $_SESSION['token']);

    if ($userResponse['status_code'] !== 201) {
        $error = $userResponse['body']['message'] ?? 'Failed to create user';
    } else {
        if ($role === 'student' && !empty($_POST['subjects'])) {
            $newUserId = $userResponse['body']['data']['user_id'];
            $courseId  = $_POST['course_id'];
            foreach ($_POST['subjects'] as $subjectId) {
                apiRequest('/results', 'POST', [
                    'student_id' => $newUserId,
                    'subject_id' => $subjectId,
                    'course_id'  => $courseId
                ], $_SESSION['token']);
            }
        }
        header("Location: users.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern" style="max-width: 900px;">

  <div class="page-header">
    <div>
      <h1>Add New User</h1>
      <p class="subtitle">Create a new account on the platform.</p>
    </div>
    <a href="users.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?></div>
  <?php endif; ?>

  <div class="card-modern">
    <form method="POST" id="userForm">
      <div class="mb-3">
        <label class="form-label"><i class="bi bi-person-fill"></i> Full Name</label>
        <input type="text" name="full_name" class="form-control" placeholder="e.g. Ahmad Faizal" required>
      </div>

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-envelope-fill"></i> Email</label>
        <input type="email" name="email" class="form-control" placeholder="name@uni.edu" required>
      </div>

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-shield-fill-check"></i> Role</label>
        <select name="role" id="roleSelect" class="form-select" required>
          <option value="">-- Select Role --</option>
          <option value="admin">Admin</option>
          <option value="lecturer">Lecturer</option>
          <option value="student">Student</option>
        </select>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label"><i class="bi bi-hash"></i> Matric No (Student only)</label>
          <input type="text" name="matric_no" class="form-control" placeholder="S2023001">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label"><i class="bi bi-person-vcard-fill"></i> IC No</label>
          <input type="text" name="no_ic" class="form-control" placeholder="900101010101" required>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label"><i class="bi bi-telephone-fill"></i> Phone</label>
        <input type="text" name="phone" class="form-control" placeholder="012-3456789">
      </div>

      <div id="enrollmentSection" style="display:none;padding:1.5rem;border:1.5px dashed var(--glass-border);border-radius:14px;margin-bottom:1.5rem;">
        <h5 style="color:#ffffff;margin-bottom:1rem;"><i class="bi bi-journal-text"></i> Course & Subjects Enrollment</h5>
        <div class="mb-3">
          <label class="form-label">Course</label>
          <select name="course_id" id="courseSelect" class="form-select">
            <option value="">-- Select Course --</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_code'].' - '.$c['course_title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Subjects (max 4)</label>
          <div id="subjectList" style="padding:1rem;background:rgba(255,255,255,0.02);border-radius:10px;min-height:60px;">
            <em class="text-muted">Select a course first.</em>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:0.75rem;">
        <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Create User</button>
        <a href="users.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Cancel</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
var allSubjects = <?= json_encode($subjects) ?>;

document.getElementById('roleSelect').addEventListener('change', function() {
    document.getElementById('enrollmentSection').style.display = (this.value === 'student') ? 'block' : 'none';
});

document.getElementById('courseSelect').addEventListener('change', function() {
    var courseId = this.value;
    var list = document.getElementById('subjectList');
    if (!courseId) {
        list.innerHTML = '<em class="text-muted">Select a course first.</em>';
        return;
    }
    var filtered = allSubjects.filter(s => String(s.course_id) === String(courseId));
    if (filtered.length === 0) {
        list.innerHTML = '<em class="text-danger">No subjects found.</em>';
        return;
    }
    list.innerHTML = '';
    filtered.forEach(function(s) {
        var div = document.createElement('div');
        div.className = 'form-check';
        div.innerHTML =
            '<input class="form-check-input subject-cb" type="checkbox" name="subjects[]" value="' + s.subject_id + '" id="sub-' + s.subject_id + '">' +
            '<label class="form-check-label" for="sub-' + s.subject_id + '" style="color:#cbd5e1;">' + s.subject_name + '</label>';
        list.appendChild(div);
    });
    document.querySelectorAll('.subject-cb').forEach(cb => {
        cb.addEventListener('change', function() {
            if (document.querySelectorAll('.subject-cb:checked').length > 4) {
                this.checked = false;
                alert('Maximum 4 subjects only.');
            }
        });
    });
});
</script>
</body>
</html>