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
                $r = apiRequest('/results', 'POST', [
                    'student_id' => $newUserId,
                    'subject_id' => $subjectId,
                    'course_id'  => $courseId
                ], $_SESSION['token']);
                if ($r['status_code'] !== 201) $enrollErrors[] = "Subject ID $subjectId: " . ($r['body']['message'] ?? 'Unknown');
            }
            if (!empty($enrollErrors)) $error = "User created but enrollment issues:<br>" . implode('<br>', $enrollErrors);
        }
        if (empty($error)) { header("Location: users.php"); exit; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add User · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-lum" style="max-width:880px;">

  <div style="display:flex;align-items:center;gap:0.5rem;color:var(--ink-3);font-size:0.85rem;margin-bottom:1.25rem;">
    <a href="users.php" style="color:var(--ink-3);text-decoration:none;"><i class="bi bi-people-fill"></i> Users</a>
    <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i>
    <span style="color:var(--ink);font-weight:600;">Add New</span>
  </div>

  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ New Account</span>
      <h1>Add new <em>user</em></h1>
      <p class="sub">Create a new admin, lecturer, or student account.</p>
    </div>
    <a href="users.php" class="btn-lum ghost"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-lum danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= $error ?></div></div>
  <?php endif; ?>

  <div class="card-lum flat">
    <form method="POST" id="userForm">

      <!-- IDENTITY -->
      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--indigo-soft);color:var(--indigo);">
            <i class="bi bi-person-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Identity</h3>
            <p class="form-section-desc">Basic information about the user.</p>
          </div>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-person-fill"></i> Full Name <span class="req">*</span></label>
          <input type="text" name="full_name" class="input-lum" placeholder="e.g. Ahmad Faizal bin Abdullah" required>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-envelope-fill"></i> Email Address <span class="req">*</span></label>
          <input type="email" name="email" class="input-lum" placeholder="user@uni.edu" required>
        </div>
      </div>

      <!-- ROLE -->
      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--amber-soft);color:#92400e;">
            <i class="bi bi-shield-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Role & Access</h3>
            <p class="form-section-desc">Determine what this user can do in the system.</p>
          </div>
        </div>

        <div class="role-picker">
          <label class="role-card">
            <input type="radio" name="role" value="admin" required style="display:none;">
            <div class="role-icon" style="background:var(--amber-soft);color:#92400e;"><i class="bi bi-shield-fill-check"></i></div>
            <div class="role-name">Admin</div>
            <div class="role-desc">Full system access</div>
          </label>
          <label class="role-card">
            <input type="radio" name="role" value="lecturer" required style="display:none;">
            <div class="role-icon" style="background:var(--sky-soft);color:#0369a1;"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="role-name">Lecturer</div>
            <div class="role-desc">Manage results & exams</div>
          </label>
          <label class="role-card">
            <input type="radio" name="role" value="student" required style="display:none;">
            <div class="role-icon" style="background:var(--mint-soft);color:#047857;"><i class="bi bi-backpack-fill"></i></div>
            <div class="role-name">Student</div>
            <div class="role-desc">View results & exams</div>
          </label>
        </div>
      </div>

      <!-- CONTACT -->
      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--mint-soft);color:#047857;">
            <i class="bi bi-telephone-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Contact Details</h3>
            <p class="form-section-desc">Additional identification & contact info.</p>
          </div>
        </div>

        <div class="grid-2">
          <div class="field-lum">
            <label><i class="bi bi-credit-card-2-front-fill"></i> IC Number <span class="req">*</span></label>
            <input type="text" name="no_ic" class="input-lum" placeholder="900101010101" required>
          </div>
          <div class="field-lum">
            <label><i class="bi bi-phone-fill"></i> Phone Number</label>
            <input type="text" name="phone" class="input-lum" placeholder="012-3456789">
          </div>
        </div>

        <div class="field-lum" id="matricField" style="display:none;">
          <label><i class="bi bi-person-vcard-fill"></i> Matric Number <span class="req">*</span></label>
          <input type="text" name="matric_no" class="input-lum" placeholder="e.g. S2023001">
          <div class="hint"><i class="bi bi-info-circle"></i> Required for student accounts.</div>
        </div>
      </div>

      <!-- ENROLLMENT (only for student) -->
      <div id="enrollmentSection" class="form-section" style="display:none;">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--violet-soft);color:#7e22ce;">
            <i class="bi bi-journal-plus"></i>
          </div>
          <div>
            <h3 class="form-section-title">Course Enrollment</h3>
            <p class="form-section-desc">Assign the student to a course and pick their subjects (max 4).</p>
          </div>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-collection-fill"></i> Course <span class="req">*</span></label>
          <select name="course_id" id="courseSelect" class="select-lum">
            <option value="">— Select Course —</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_code'].' · '.$c['course_title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-bookmark-star-fill"></i> Subjects</label>
          <div id="subjectList" class="subject-picker">
            <em class="text-muted-lum">Select a course first to see available subjects.</em>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <a href="users.php" class="btn-lum ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        <button type="submit" class="btn-lum primary"><i class="bi bi-person-plus-fill"></i> Create User</button>
      </div>

    </form>
  </div>

</div>

<script>
var allSubjects = <?= json_encode($subjects) ?>;

// Role picker interaction
document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input').checked = true;

        const role = card.querySelector('input').value;
        document.getElementById('matricField').style.display = (role === 'student') ? 'block' : 'none';
        document.getElementById('enrollmentSection').style.display = (role === 'student') ? 'block' : 'none';
    });
});

// Subject picker
document.getElementById('courseSelect').addEventListener('change', function() {
    var courseId = this.value;
    var list = document.getElementById('subjectList');
    if (!courseId) { list.innerHTML = '<em class="text-muted-lum">Select a course first.</em>'; return; }
    var filtered = allSubjects.filter(s => String(s.course_id) === String(courseId));
    if (filtered.length === 0) { list.innerHTML = '<em class="text-muted-lum" style="color:var(--coral);">No subjects available.</em>'; return; }
    list.innerHTML = '';
    filtered.forEach(function(s) {
        var label = document.createElement('label');
        label.className = 'check-lum';
        label.innerHTML = '<input type="checkbox" name="subjects[]" value="'+s.subject_id+'" class="subject-cb"><span>'+s.subject_name+'</span>';
        list.appendChild(label);
    });
    document.querySelectorAll('.subject-cb').forEach(cb => cb.addEventListener('change', function() {
        if (document.querySelectorAll('.subject-cb:checked').length > 4) { this.checked=false; alert('Maximum 4 subjects.'); }
    }));
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>