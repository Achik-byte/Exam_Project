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
<body class="container mt-4">
<h3>Add New User</h3>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form method="POST" id="userForm">
  <div class="mb-3"><label>Full Name</label>
    <input type="text" name="full_name" class="form-control" required></div>
  <div class="mb-3"><label>Email</label>
    <input type="email" name="email" class="form-control" required></div>
  <div class="mb-3"><label>Role</label>
    <select name="role" id="roleSelect" class="form-control" required>
      <option value="">-- Select Role --</option>
      <option value="admin">Admin</option>
      <option value="lecturer">Lecturer</option>
      <option value="student">Student</option>
    </select>
  </div>
  <div class="mb-3"><label>Matric No (Student only)</label>
    <input type="text" name="matric_no" class="form-control"></div>
  <div class="mb-3"><label>IC No</label>
    <input type="text" name="no_ic" class="form-control" required></div>
  <div class="mb-3"><label>Phone</label>
    <input type="text" name="phone" class="form-control"></div>

  <div id="enrollmentSection" style="display:none; border: 1px solid #ccc; padding: 15px; margin: 15px 0;">
    <h5>Course & Subjects Enrollment</h5>
    <div class="mb-3">
      <label>Course</label>
      <select name="course_id" id="courseSelect" class="form-control">
        <option value="">-- Select Course --</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_code'].' - '.$c['course_title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Subjects (maksimum 4)</label>
      <div id="subjectList" style="border: 1px dashed #999; padding: 10px; min-height: 50px;">
        <em class="text-muted">Pilih course dulu.</em>
      </div>
    </div>
  </div>

  <button type="submit" class="btn btn-primary">Save</button>
  <a href="users.php" class="btn btn-secondary">Cancel</a>
</form>

<script>
var allSubjects = <?= json_encode($subjects) ?>;
console.log('Subjects loaded:', allSubjects.length);

document.getElementById('roleSelect').addEventListener('change', function() {
    var section = document.getElementById('enrollmentSection');
    section.style.display = (this.value === 'student') ? 'block' : 'none';
});

document.getElementById('courseSelect').addEventListener('change', function() {
    var courseId = this.value;
    var list = document.getElementById('subjectList');
    
    if (!courseId) {
        list.innerHTML = '<em class="text-muted">Pilih course dulu.</em>';
        return;
    }

    var filtered = allSubjects.filter(function(s) {
        return String(s.course_id) === String(courseId);
    });

    console.log('Course', courseId, 'has', filtered.length, 'subjects');

    if (filtered.length === 0) {
        list.innerHTML = '<em class="text-danger">Tiada subject.</em>';
        return;
    }

    list.innerHTML = '';
    filtered.forEach(function(s) {
        var div = document.createElement('div');
        div.className = 'form-check';
        div.innerHTML = 
            '<input class="form-check-input subject-cb" type="checkbox" ' +
            'name="subjects[]" value="' + s.subject_id + '" id="sub-' + s.subject_id + '">' +
            '<label class="form-check-label" for="sub-' + s.subject_id + '">' + 
            s.subject_name + '</label>';
        list.appendChild(div);
    });

    document.querySelectorAll('.subject-cb').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var checked = document.querySelectorAll('.subject-cb:checked').length;
            if (checked > 4) {
                this.checked = false;
                alert('Maksimum 4 subjects sahaja.');
            }
        });
    });
});
</script>
</body>
</html>