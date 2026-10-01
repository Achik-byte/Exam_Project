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
            $enrollErrors = [];

            foreach ($_POST['subjects'] as $subjectId) {
                $resultResponse = apiRequest('/results', 'POST', [
                    'student_id' => $newUserId,
                    'subject_id' => $subjectId,
                    'course_id'  => $courseId
                ], $_SESSION['token']);

                if ($resultResponse['status_code'] !== 201) {
                    $enrollErrors[] = "Subject ID $subjectId: " . ($resultResponse['body']['message'] ?? 'Unknown');
                }
            }

            if (!empty($enrollErrors)) {
                $error = "User created but enrollment issues:<br>" . implode('<br>', $enrollErrors);
            }
        }

        if (empty($error)) {
            header("Location: users.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-aurora" style="max-width:820px;">

  <div class="page-head">
    <div>
      <div class="eyebrow">◆ New Account</div>
      <h1>Add User</h1>
      <p class="subtitle">Create a new admin, lecturer, or student account.</p>
    </div>
    <a href="users.php" class="btn-neo ghost"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-neo danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= $error ?></div></div>
  <?php endif; ?>

  <div class="glass">
    <form method="POST" id="userForm">
      <div class="grid-2">
        <div class="field">
          <label><i class="bi bi-person-fill"></i> Full Name</label>
          <input type="text" name="full_name" class="input-neo" required>
        </div>
        <div class="field">
          <label><i class="bi bi-envelope-fill"></i> Email</label>
          <input type="email" name="email" class="input-neo" required>
        </div>
      </div>
      <div class="grid-2">
        <div class="field">
          <label><i class="bi bi-shield-fill"></i> Role</label>
          <select name="role" id="roleSelect" class="select-neo" required>
            <option value="">— Select Role —</option>
            <option value="admin">Admin</option>
            <option value="lecturer">Lecturer</option>
            <option value="student">Student</option>
          </select>
        </div>
        <div class="field">
          <label><i class="bi bi-person-vcard-fill"></i> Matric No (Student)</label>
          <input type="text" name="matric_no" class="input-neo">
        </div>
      </div>
      <div class="grid-2">
        <div class="field">
          <label><i class="bi bi-credit-card-2-front-fill"></i> IC Number</label>
          <input type="text" name="no_ic" class="input-neo" required>
        </div>
        <div class="field">
          <label><i class="bi bi-telephone-fill"></i> Phone</label>
          <input type="text" name="phone" class="input-neo">
        </div>
      </div>

      <div id="enrollmentSection" style="display:none;padding:1.25rem;border:1px dashed var(--border-hi);border-radius:14px;margin:1rem 0;">
        <h5 style="font-weight:700;margin:0 0 1rem;color:var(--violet-2);"><i class="bi bi-journal-plus"></i> Course & Subject Enrollment</h5>
        <div class="field">
          <label>Course</label>
          <select name="course_id" id="courseSelect" class="select-neo">
            <option value="">— Select Course —</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_code'].' - '.$c['course_title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Subjects (max 4)</label>
          <div id="subjectList" style="min-height:50px;"><em style="color:var(--text-3);">Select a course first.</em></div>
        </div>
      </div>

      <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
        <button type="submit" class="btn-neo violet"><i class="bi bi-check-circle-fill"></i> Save User</button>
        <a href="users.php" class="btn-neo ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
var allSubjects = <?= json_encode($subjects) ?>;
document.getElementById('roleSelect').addEventListener('change', function() {
    document.getElementById('enrollmentSection').style.display = (this.value === 'student') ? 'block' : 'none';
});
document.getElementById('courseSelect').addEventListener('change', function() {
    var courseId = this.value;
    var list = document.getElementById('subjectList');
    if (!courseId) { list.innerHTML = '<em style="color:var(--text-3);">Select a course first.</em>'; return; }
    var filtered = allSubjects.filter(s => String(s.course_id) === String(courseId));
    if (filtered.length === 0) { list.innerHTML = '<em style="color:var(--red);">No subjects available.</em>'; return; }
    list.innerHTML = '';
    filtered.forEach(function(s) {
        var div = document.createElement('label');
        div.className = 'check-neo';
        div.style.marginBottom = '0.5rem';
        div.innerHTML = '<input type="checkbox" name="subjects[]" value="'+s.subject_id+'" class="subject-cb"><span>'+s.subject_name+'</span>';
        list.appendChild(div);
    });
    document.querySelectorAll('.subject-cb').forEach(cb => cb.addEventListener('change', function() {
        if (document.querySelectorAll('.subject-cb:checked').length > 4) { this.checked=false; alert('Maximum 4 subjects.'); }
    }));
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>