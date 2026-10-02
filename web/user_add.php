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
                if ($r['status_code'] !== 201) {
                    $enrollErrors[] = "Subject ID $subjectId: " . ($r['body']['message'] ?? 'Unknown');
                }
            }
            if (!empty($enrollErrors)) {
                $error = "User created but enrollment issues:<br>" . implode('<br>', $enrollErrors);
            }
        }
        if (empty($error)) { header("Location: users.php"); exit; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
  <style>
    .wizard-wrap {
      display: grid;
      grid-template-columns: 1fr 360px;
      gap: 1.5rem;
      align-items: start;
    }
    @media (max-width: 960px) { .wizard-wrap { grid-template-columns: 1fr; } }

    .steps-nav { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .step-pill {
      display: inline-flex; align-items: center; gap: 0.5rem;
      padding: 0.5rem 1rem; border-radius: 999px;
      background: var(--glass); border: 1px solid var(--glass-line);
      color: var(--ink-3); font-size: 0.78rem; font-weight: 600;
      text-transform: uppercase; letter-spacing: 0.05em;
    }
    .step-pill .num {
      width: 20px; height: 20px; border-radius: 50%;
      background: rgba(255,255,255,0.1);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.7rem;
    }
    .step-pill.active {
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      border-color: transparent; color: white;
      box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
    }
    .step-pill.active .num { background: rgba(255,255,255,0.25); }

    .form-panel {
      background: var(--glass); backdrop-filter: blur(20px);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-lg); padding: 2rem;
    }
    .panel-title {
      font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700;
      color: white; margin-bottom: 1.25rem; padding-bottom: 1rem;
      border-bottom: 1px solid var(--glass-line);
      display: flex; align-items: center; gap: 0.6rem;
    }
    .panel-title i { color: #a5b4fc; }

    /* Role picker */
    .role-picker {
      display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;
    }
    .role-card {
      display: flex; flex-direction: column; align-items: flex-start;
      padding: 1rem; border-radius: var(--r-md);
      background: var(--glass-2);
      border: 1.5px solid var(--glass-line);
      cursor: pointer; transition: all .2s; position: relative;
    }
    .role-card:hover { border-color: var(--aurora-1); transform: translateY(-2px); }
    .role-card.selected {
      background: linear-gradient(180deg, rgba(99,102,241,0.2) 0%, var(--glass-2) 100%);
      border-color: var(--aurora-1);
      box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
    }
    .role-card.selected::before {
      content: '✓';
      position: absolute; top: 0.5rem; right: 0.6rem;
      width: 20px; height: 20px; border-radius: 50%;
      background: var(--aurora-1); color: white;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.7rem; font-weight: 700;
    }
    .role-card .role-icon {
      width: 36px; height: 36px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem; margin-bottom: 0.5rem;
    }
    .role-card .role-name { font-weight: 700; color: white; font-size: 0.9rem; margin-bottom: 0.15rem; }
    .role-card .role-desc { color: var(--ink-3); font-size: 0.72rem; line-height: 1.3; }

    /* Preview */
    .preview-sticky { position: sticky; top: 100px; }
    .preview-panel {
      background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(168,85,247,0.1));
      border: 1px solid var(--glass-line-2);
      border-radius: var(--r-lg); padding: 1.5rem;
    }
    .preview-panel .label {
      font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.15em;
      color: var(--ink-3); font-weight: 700; margin-bottom: 0.75rem;
    }
    .preview-panel .preview-card {
      background: var(--glass-2); border: 1px solid var(--glass-line);
      border-radius: var(--r-md); padding: 1.25rem;
    }
    .preview-panel .avatar {
      width: 56px; height: 56px; border-radius: 14px;
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      display: flex; align-items: center; justify-content: center;
      font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800;
      color: white; margin-bottom: 0.85rem;
      box-shadow: 0 8px 24px rgba(99,102,241,0.4);
    }
    .preview-panel .preview-card .name {
      font-family: 'Outfit', sans-serif; font-size: 1.05rem; font-weight: 700;
      color: white; margin-bottom: 0.5rem; line-height: 1.3; min-height: 1.35em;
    }
    .preview-panel .preview-card .meta {
      display: flex; flex-direction: column; gap: 0.4rem;
      font-size: 0.8rem; color: var(--ink-3);
      margin-top: 0.75rem; padding-top: 0.75rem;
      border-top: 1px solid var(--glass-line);
    }
    .preview-panel .preview-card .meta-row {
      display: flex; align-items: center; gap: 0.5rem;
    }
    .preview-panel .preview-card .meta-row i { width: 16px; color: #a5b4fc; }

    .subject-picker {
      padding: 1rem; border: 1.5px dashed var(--glass-line-2);
      border-radius: var(--r-md); background: rgba(0,0,0,0.15);
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass" style="max-width:1080px;">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ New Account</span>
      <h1>Add new <em>user</em></h1>
      <p class="sub">Create a new admin, lecturer, or student account.</p>
    </div>
    <a href="users.php" class="btn-glass"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-glass danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= $error ?></div></div>
  <?php endif; ?>

  <div class="steps-nav">
    <div class="step-pill active"><span class="num">1</span> Identity</div>
    <div class="step-pill active"><span class="num">2</span> Role</div>
    <div class="step-pill active"><span class="num">3</span> Contact</div>
  </div>

  <div class="wizard-wrap">

    <!-- FORM -->
    <div class="form-panel">
      <form method="POST" id="userForm">

        <div class="panel-title"><i class="bi bi-person-fill"></i> Identity</div>
        <div class="field-glass">
          <label><i class="bi bi-person-fill"></i> Full Name <span class="req">*</span></label>
          <input type="text" name="full_name" id="fullName" class="input-glass" placeholder="e.g. Ahmad Faizal" required>
        </div>
        <div class="field-glass" style="margin-bottom:2rem;">
          <label><i class="bi bi-envelope-fill"></i> Email Address <span class="req">*</span></label>
          <input type="email" name="email" id="email" class="input-glass" placeholder="user@uni.edu" required>
        </div>

        <div class="panel-title"><i class="bi bi-shield-fill"></i> Role & Access</div>
        <div class="role-picker" style="margin-bottom:2rem;">
          <label class="role-card" data-role="admin">
            <input type="radio" name="role" value="admin" required style="display:none;">
            <div class="role-icon" style="background:rgba(245,158,11,0.15);color:#fcd34d;"><i class="bi bi-shield-fill-check"></i></div>
            <div class="role-name">Admin</div>
            <div class="role-desc">Full system access</div>
          </label>
          <label class="role-card" data-role="lecturer">
            <input type="radio" name="role" value="lecturer" required style="display:none;">
            <div class="role-icon" style="background:rgba(99,102,241,0.15);color:#a5b4fc;"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="role-name">Lecturer</div>
            <div class="role-desc">Manage results & exams</div>
          </label>
          <label class="role-card" data-role="student">
            <input type="radio" name="role" value="student" required style="display:none;">
            <div class="role-icon" style="background:rgba(16,185,129,0.15);color:#6ee7b7;"><i class="bi bi-backpack-fill"></i></div>
            <div class="role-name">Student</div>
            <div class="role-desc">View results & exams</div>
          </label>
        </div>

        <div class="panel-title"><i class="bi bi-telephone-fill"></i> Contact Details</div>
        <div class="grid-2">
          <div class="field-glass">
            <label><i class="bi bi-credit-card-2-front-fill"></i> IC Number <span class="req">*</span></label>
            <input type="text" name="no_ic" class="input-glass" placeholder="900101010101" required>
          </div>
          <div class="field-glass">
            <label><i class="bi bi-phone-fill"></i> Phone Number</label>
            <input type="text" name="phone" id="phone" class="input-glass" placeholder="012-3456789">
          </div>
        </div>
        <div class="field-glass" id="matricField" style="display:none;">
          <label><i class="bi bi-person-vcard-fill"></i> Matric Number <span class="req">*</span></label>
          <input type="text" name="matric_no" id="matricNo" class="input-glass" placeholder="e.g. S2023001">
        </div>

        <div class="form-section" id="enrollmentSection" style="display:none; margin-top:2rem; padding-top:2rem; border-top:1px solid var(--glass-line);">
          <div class="panel-title" style="padding-bottom:0.75rem;margin-bottom:1rem;border-bottom:none;">
            <i class="bi bi-journal-plus" style="color:#d8b4fe;"></i> Course Enrollment
          </div>
          <div class="field-glass">
            <label><i class="bi bi-collection-fill"></i> Course</label>
            <select name="course_id" id="courseSelect" class="select-glass">
              <option value="">— Select Course —</option>
              <?php foreach ($courses as $c): ?>
                <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_code'].' · '.$c['course_title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-glass">
            <label><i class="bi bi-bookmark-star-fill"></i> Subjects (max 4)</label>
            <div id="subjectList" class="subject-picker">
              <em style="color:var(--ink-3);font-size:0.85rem;">Select a course first.</em>
            </div>
          </div>
        </div>

        <div class="form-actions" style="margin-top:2rem;">
          <a href="users.php" class="btn-glass"><i class="bi bi-x-lg"></i> Cancel</a>
          <button type="submit" class="btn-glass primary"><i class="bi bi-person-plus-fill"></i> Create User</button>
        </div>
      </form>
    </div>

    <!-- PREVIEW -->
    <div class="preview-sticky">
      <div class="preview-panel">
        <div class="label">◆ Live Preview</div>
        <div class="preview-card">
          <div class="avatar" id="previewAvatar">?</div>
          <div class="name" id="previewName">New User</div>
          <span class="pill-glass gray" id="previewRole" style="font-size:0.7rem;padding:0.3rem 0.65rem;">
            <span class="dot"></span> No role
          </span>
          <div class="meta">
            <div class="meta-row"><i class="bi bi-envelope-fill"></i> <span id="previewEmail">—</span></div>
            <div class="meta-row"><i class="bi bi-phone-fill"></i> <span id="previewPhone">—</span></div>
            <div class="meta-row" id="previewMatricRow" style="display:none;"><i class="bi bi-person-vcard-fill"></i> <span id="previewMatric">—</span></div>
          </div>
        </div>
        <p style="color:var(--ink-3);font-size:0.78rem;margin:1rem 0 0 0;text-align:center;">
          Preview updates as you fill the form.
        </p>
      </div>
    </div>

  </div>
</div>

<script>
var allSubjects = <?= json_encode($subjects) ?>;

// Role picker
document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input').checked = true;
        const role = card.dataset.role;

        // Preview update
        const map = { admin:'amber', lecturer:'blue', student:'green' };
        const el = document.getElementById('previewRole');
        el.className = 'pill-glass ' + map[role];
        el.innerHTML = '<span class="dot"></span> ' + role.charAt(0).toUpperCase() + role.slice(1);

        // Show/hide fields
        const isStudent = role === 'student';
        document.getElementById('matricField').style.display = isStudent ? 'block' : 'none';
        document.getElementById('enrollmentSection').style.display = isStudent ? 'block' : 'none';
        document.getElementById('previewMatricRow').style.display = isStudent ? 'flex' : 'none';
    });
});

// Live preview — text fields
document.getElementById('fullName').addEventListener('input', function() {
    const v = this.value.trim();
    document.getElementById('previewName').textContent = v || 'New User';
    document.getElementById('previewAvatar').textContent = v ? v.charAt(0).toUpperCase() : '?';
});
document.getElementById('email').addEventListener('input', function() {
    document.getElementById('previewEmail').textContent = this.value || '—';
});
document.getElementById('phone').addEventListener('input', function() {
    document.getElementById('previewPhone').textContent = this.value || '—';
});
document.getElementById('matricNo').addEventListener('input', function() {
    document.getElementById('previewMatric').textContent = this.value || '—';
});

// Course → Subjects
document.getElementById('courseSelect').addEventListener('change', function() {
    var courseId = this.value;
    var list = document.getElementById('subjectList');
    if (!courseId) { list.innerHTML = '<em style="color:var(--ink-3);font-size:0.85rem;">Select a course first.</em>'; return; }
    var filtered = allSubjects.filter(s => String(s.course_id) === String(courseId));
    if (filtered.length === 0) { list.innerHTML = '<em style="color:#fca5a5;font-size:0.85rem;">No subjects available.</em>'; return; }
    list.innerHTML = '';
    filtered.forEach(function(s) {
        var label = document.createElement('label');
        label.className = 'check-glass';
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