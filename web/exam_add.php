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
    $response = apiRequest('/examinations', 'POST', [
        'course_id'  => $_POST['course_id'],
        'subject_id' => $_POST['subject_id'],
        'exam_date'  => $_POST['exam_date'],
        'start_time' => $_POST['start_time'],
        'end_time'   => $_POST['end_time'],
        'venue'      => $_POST['venue'],
        'status'     => $_POST['status']
    ], $_SESSION['token']);
    if ($response['status_code'] === 201) { header("Location: exams.php"); exit; }
    else $error = $response['body']['message'] ?? 'Failed to create exam';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Exam · ExamSys</title>
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

    /* === STEPS NAV === */
    .steps-nav {
      display: flex; gap: 0.5rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }
    .step-pill {
      display: inline-flex; align-items: center; gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 999px;
      background: var(--glass);
      border: 1px solid var(--glass-line);
      color: var(--ink-3);
      font-size: 0.78rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .step-pill .num {
      width: 20px; height: 20px;
      border-radius: 50%;
      background: rgba(255,255,255,0.1);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.7rem;
    }
    .step-pill.active {
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      border-color: transparent;
      color: white;
      box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
    }
    .step-pill.active .num { background: rgba(255,255,255,0.25); }

    /* === FORM PANEL === */
    .form-panel {
      background: var(--glass);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-lg);
      padding: 2rem;
    }
    .panel-title {
      font-family: 'Outfit', sans-serif;
      font-size: 1.1rem;
      font-weight: 700;
      color: white;
      margin-bottom: 1.25rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--glass-line);
      display: flex; align-items: center; gap: 0.6rem;
    }
    .panel-title i { color: #a5b4fc; }

    /* === PREVIEW PANEL === */
    .preview-sticky { position: sticky; top: 100px; }
    .preview-panel {
      background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(168,85,247,0.1));
      border: 1px solid var(--glass-line-2);
      border-radius: var(--r-lg);
      padding: 1.5rem;
    }
    .preview-panel .label {
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.15em;
      color: var(--ink-3);
      font-weight: 700;
      margin-bottom: 0.75rem;
    }
    .preview-panel .preview-card {
      background: var(--glass-2);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-md);
      padding: 1.25rem;
    }
    .preview-panel .preview-card .subject {
      font-family: 'Outfit', sans-serif;
      font-size: 1.05rem;
      font-weight: 700;
      color: white;
      margin-bottom: 0.5rem;
      line-height: 1.3;
      min-height: 1.35em;
    }
    .preview-panel .preview-card .meta {
      display: flex; flex-direction: column; gap: 0.5rem;
      font-size: 0.82rem;
      color: var(--ink-2);
      margin-top: 0.75rem;
      padding-top: 0.75rem;
      border-top: 1px solid var(--glass-line);
    }
    .preview-panel .preview-card .meta-row {
      display: flex; align-items: center; gap: 0.5rem;
    }
    .preview-panel .preview-card .meta-row i { color: #a5b4fc; width: 16px; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass" style="max-width:1080px;">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ New Entry</span>
      <h1>Add new <em>exam</em></h1>
      <p class="sub">Fill in the details to schedule a new examination session.</p>
    </div>
    <a href="exams.php" class="btn-glass"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-glass danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <!-- STEPS -->
  <div class="steps-nav">
    <div class="step-pill active"><span class="num">1</span> Course & Subject</div>
    <div class="step-pill"><span class="num">2</span> Date & Time</div>
    <div class="step-pill"><span class="num">3</span> Location</div>
  </div>

  <div class="wizard-wrap">

    <!-- FORM (KIRI) -->
    <div class="form-panel">
      <form method="POST" id="examForm">

        <div class="panel-title"><i class="bi bi-collection-fill"></i> Course & Subject</div>
        <div class="grid-2" style="margin-bottom:2rem;">
          <div class="field-glass">
            <label><i class="bi bi-collection-fill"></i> Course <span class="req">*</span></label>
            <select name="course_id" id="courseSelect" class="select-glass" required>
              <option value="">— Select Course —</option>
              <?php foreach ($courses as $c): ?>
                <option value="<?= $c['course_id'] ?>" data-title="<?= htmlspecialchars($c['course_title']) ?>">
                  <?= htmlspecialchars($c['course_code'].' · '.$c['course_title']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-glass">
            <label><i class="bi bi-bookmark-fill"></i> Subject <span class="req">*</span></label>
            <select name="subject_id" id="subjectSelect" class="select-glass" required>
              <option value="">— Select Course First —</option>
            </select>
          </div>
        </div>

        <div class="panel-title"><i class="bi bi-clock-fill"></i> Date & Time</div>
        <div class="field-glass">
          <label><i class="bi bi-calendar-event-fill"></i> Exam Date <span class="req">*</span></label>
          <input type="date" name="exam_date" id="examDate" class="input-glass" required>
        </div>
        <div class="grid-2" style="margin-bottom:2rem;">
          <div class="field-glass">
            <label><i class="bi bi-play-circle-fill"></i> Start Time <span class="req">*</span></label>
            <input type="time" name="start_time" id="startTime" class="input-glass" required>
          </div>
          <div class="field-glass">
            <label><i class="bi bi-stop-circle-fill"></i> End Time <span class="req">*</span></label>
            <input type="time" name="end_time" id="endTime" class="input-glass" required>
          </div>
        </div>

        <div class="panel-title"><i class="bi bi-geo-alt-fill"></i> Location & Status</div>
        <div class="grid-2">
          <div class="field-glass">
            <label><i class="bi bi-geo-alt-fill"></i> Venue <span class="req">*</span></label>
            <input type="text" name="venue" id="venue" class="input-glass" placeholder="e.g. Exam Hall A" required>
          </div>
          <div class="field-glass">
            <label><i class="bi bi-flag-fill"></i> Status <span class="req">*</span></label>
            <select name="status" id="status" class="select-glass" required>
              <option value="scheduled">🕒 Scheduled</option>
              <option value="completed">✓ Completed</option>
              <option value="cancelled">✕ Cancelled</option>
            </select>
          </div>
        </div>

        <div class="form-actions" style="margin-top:2rem;">
          <a href="exams.php" class="btn-glass"><i class="bi bi-x-lg"></i> Cancel</a>
          <button type="submit" class="btn-glass primary"><i class="bi bi-check-circle-fill"></i> Save Exam</button>
        </div>
      </form>
    </div>

    <!-- PREVIEW (KANAN) -->
    <div class="preview-sticky">
      <div class="preview-panel">
        <div class="label">◆ Live Preview</div>
        <div class="preview-card">
          <div class="subject" id="previewSubject">Subject Name</div>
          <span class="pill-glass blue" id="previewStatus" style="font-size:0.7rem;padding:0.3rem 0.65rem;">
            <span class="dot"></span> Scheduled
          </span>
          <div class="meta">
            <div class="meta-row"><i class="bi bi-collection-fill"></i> <span id="previewCourse">Course</span></div>
            <div class="meta-row"><i class="bi bi-calendar-event-fill"></i> <span id="previewDate">—</span></div>
            <div class="meta-row"><i class="bi bi-clock-fill"></i> <span id="previewTime">—</span></div>
            <div class="meta-row"><i class="bi bi-geo-alt-fill"></i> <span id="previewVenue">—</span></div>
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
const allSubjects = <?= json_encode($subjects) ?>;

// Course → Subjects
document.getElementById('courseSelect').addEventListener('change', function () {
    const courseId = this.value;
    const subjectSelect = document.getElementById('subjectSelect');
    subjectSelect.innerHTML = '<option value="">— Select Subject —</option>';
    document.getElementById('previewCourse').textContent = this.options[this.selectedIndex]?.text || 'Course';

    if (!courseId) { subjectSelect.innerHTML = '<option value="">— Select Course First —</option>'; return; }
    const filtered = allSubjects.filter(s => String(s.course_id) === String(courseId));
    if (filtered.length === 0) { subjectSelect.innerHTML = '<option value="">— No subjects available —</option>'; return; }
    filtered.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.subject_id;
        opt.textContent = s.subject_name;
        subjectSelect.appendChild(opt);
    });
});

// Live preview update
document.getElementById('subjectSelect').addEventListener('change', function() {
    document.getElementById('previewSubject').textContent = this.options[this.selectedIndex]?.text || 'Subject Name';
});
document.getElementById('examDate').addEventListener('change', function() {
    document.getElementById('previewDate').textContent = this.value || '—';
});
['startTime','endTime'].forEach(id => {
    document.getElementById(id).addEventListener('change', updateTime);
});
function updateTime() {
    const s = document.getElementById('startTime').value;
    const e = document.getElementById('endTime').value;
    document.getElementById('previewTime').textContent = (s && e) ? (s + '–' + e) : '—';
}
document.getElementById('venue').addEventListener('input', function() {
    document.getElementById('previewVenue').textContent = this.value || '—';
});
document.getElementById('status').addEventListener('change', function() {
    const status = this.value;
    const map = { scheduled:'blue', completed:'green', cancelled:'red' };
    const el = document.getElementById('previewStatus');
    el.className = 'pill-glass ' + (map[status] || 'gray');
    el.innerHTML = '<span class="dot"></span> ' + status.charAt(0).toUpperCase() + status.slice(1);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>