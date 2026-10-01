<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: exams.php"); exit; }

$res  = apiRequest('/examinations/' . $id, 'GET', null, $_SESSION['token']);
$exam = $res['body']['data'] ?? null;
if (!$exam) { die("Exam not found."); }

$subjectRes = apiRequest('/subjects?course_id=' . $exam['course_id'], 'GET', null, $_SESSION['token']);
$subjects   = $subjectRes['body']['data'] ?? [];

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = apiRequest('/examinations/' . $id, 'PUT', [
        'subject_id' => $_POST['subject_id'],
        'exam_date'  => $_POST['exam_date'],
        'start_time' => $_POST['start_time'],
        'end_time'   => $_POST['end_time'],
        'venue'      => $_POST['venue'],
        'status'     => $_POST['status']
    ], $_SESSION['token']);

    if ($response['status_code'] === 200) {
        header("Location: exams.php"); exit;
    } else {
        $error = $response['body']['message'] ?? 'Failed to update';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Exam - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-lum" style="max-width:820px;">
  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ New Entry</span>
      <h1>Add <em>exam</em></h1>
      <p class="sub">Schedule a new examination session.</p>
    </div>
    <a href="exams.php" class="btn-lum ghost"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-lum danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <div class="card-lum flat">
    <form method="POST">
      <div class="grid-2">
        <div class="field-lum">
          <label><i class="bi bi-collection-fill"></i> Course</label>
          <select name="course_id" id="courseSelect" class="select-lum" required>
            <option value="">— Select Course —</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_code'].' - '.$c['course_title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-lum">
          <label><i class="bi bi-bookmark-fill"></i> Subject</label>
          <select name="subject_id" id="subjectSelect" class="select-lum" required>
            <option value="">— Select Course First —</option>
          </select>
        </div>
      </div>
      <div class="field-lum">
        <label><i class="bi bi-calendar-event-fill"></i> Date</label>
        <input type="date" name="exam_date" class="input-lum" required>
      </div>
      <div class="grid-2">
        <div class="field-lum">
          <label><i class="bi bi-clock-fill"></i> Start Time</label>
          <input type="time" name="start_time" class="input-lum" required>
        </div>
        <div class="field-lum">
          <label><i class="bi bi-clock-history"></i> End Time</label>
          <input type="time" name="end_time" class="input-lum" required>
        </div>
      </div>
      <div class="field-lum">
        <label><i class="bi bi-geo-alt-fill"></i> Venue</label>
        <input type="text" name="venue" class="input-lum" placeholder="e.g. Exam Hall A" required>
      </div>
      <div class="field-lum">
        <label><i class="bi bi-flag-fill"></i> Status</label>
        <select name="status" class="select-lum" required>
          <option value="scheduled">Scheduled</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
        <button type="submit" class="btn-lum primary"><i class="bi bi-check-circle-fill"></i> Save Exam</button>
        <a href="exams.php" class="btn-lum ghost">Cancel</a>
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
    allSubjects.filter(s => String(s.course_id) === String(courseId))
        .forEach(s => { const o=document.createElement('option'); o.value=s.subject_id; o.textContent=s.subject_name; subjectSelect.appendChild(o); });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>