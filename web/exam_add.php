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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Exam · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-lum" style="max-width:880px;">

  <!-- BREADCRUMB -->
  <div style="display:flex;align-items:center;gap:0.5rem;color:var(--ink-3);font-size:0.85rem;margin-bottom:1.25rem;">
    <a href="exams.php" style="color:var(--ink-3);text-decoration:none;"><i class="bi bi-calendar-event"></i> Exams</a>
    <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i>
    <span style="color:var(--ink);font-weight:600;">Add New</span>
  </div>

  <!-- PAGE HEADER -->
  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ New Entry</span>
      <h1>Add new <em>exam</em></h1>
      <p class="sub">Fill in the details to schedule a new examination session.</p>
    </div>
    <a href="exams.php" class="btn-lum ghost"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-lum danger">
      <i class="bi bi-exclamation-octagon-fill"></i>
      <div><?= htmlspecialchars($error) ?></div>
    </div>
  <?php endif; ?>

  <!-- FORM CARD -->
  <div class="card-lum flat">
    <form method="POST">

      <!-- SECTION 1: Course & Subject -->
      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--indigo-soft);color:var(--indigo);">
            <i class="bi bi-collection-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Course & Subject</h3>
            <p class="form-section-desc">Choose which course and subject this exam belongs to.</p>
          </div>
        </div>

        <div class="grid-2">
          <div class="field-lum">
            <label><i class="bi bi-collection-fill"></i> Course <span class="req">*</span></label>
            <select name="course_id" id="courseSelect" class="select-lum" required>
              <option value="">— Select Course —</option>
              <?php foreach ($courses as $c): ?>
                <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_code'].' · '.$c['course_title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field-lum">
            <label><i class="bi bi-bookmark-fill"></i> Subject <span class="req">*</span></label>
            <select name="subject_id" id="subjectSelect" class="select-lum" required>
              <option value="">— Select Course First —</option>
            </select>
          </div>
        </div>
      </div>

      <!-- SECTION 2: Date & Time -->
      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--amber-soft);color:#92400e;">
            <i class="bi bi-clock-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Date & Time</h3>
            <p class="form-section-desc">When will this examination take place?</p>
          </div>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-calendar-event-fill"></i> Exam Date <span class="req">*</span></label>
          <input type="date" name="exam_date" class="input-lum" required>
        </div>

        <div class="grid-2">
          <div class="field-lum">
            <label><i class="bi bi-play-circle-fill"></i> Start Time <span class="req">*</span></label>
            <input type="time" name="start_time" class="input-lum" required>
          </div>
          <div class="field-lum">
            <label><i class="bi bi-stop-circle-fill"></i> End Time <span class="req">*</span></label>
            <input type="time" name="end_time" class="input-lum" required>
          </div>
        </div>
      </div>

      <!-- SECTION 3: Venue & Status -->
      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--mint-soft);color:#047857;">
            <i class="bi bi-geo-alt-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Location & Status</h3>
            <p class="form-section-desc">Where and what's the current state of this exam?</p>
          </div>
        </div>

        <div class="grid-2">
          <div class="field-lum">
            <label><i class="bi bi-geo-alt-fill"></i> Venue <span class="req">*</span></label>
            <input type="text" name="venue" class="input-lum" placeholder="e.g. Exam Hall A" required>
          </div>
          <div class="field-lum">
            <label><i class="bi bi-flag-fill"></i> Status <span class="req">*</span></label>
            <select name="status" class="select-lum" required>
              <option value="scheduled">🕒 Scheduled</option>
              <option value="completed">✓ Completed</option>
              <option value="cancelled">✕ Cancelled</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ACTIONS -->
      <div class="form-actions">
        <a href="exams.php" class="btn-lum ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        <button type="submit" class="btn-lum primary"><i class="bi bi-check-circle-fill"></i> Save Exam</button>
      </div>

    </form>
  </div>

</div>

<script>
const allSubjects = <?= json_encode($subjects) ?>;
document.getElementById('courseSelect').addEventListener('change', function () {
    const courseId = this.value;
    const subjectSelect = document.getElementById('subjectSelect');
    subjectSelect.innerHTML = '<option value="">— Select Subject —</option>';
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
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>