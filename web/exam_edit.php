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
<div class="container-aurora" style="max-width:800px;">

  <div class="page-head">
    <div>
      <div class="eyebrow">◆ Edit Entry</div>
      <h1>Edit Exam</h1>
      <p class="subtitle">Update examination details.</p>
    </div>
    <a href="exams.php" class="btn-neo ghost"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-neo danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <div class="glass">
    <form method="POST">
      <div class="field">
        <label><i class="bi bi-collection-fill"></i> Course (locked)</label>
        <input type="text" class="input-neo" value="<?= htmlspecialchars($exam['course_code'].' - '.$exam['course_title']) ?>" disabled>
      </div>
      <div class="field">
        <label><i class="bi bi-bookmark-fill"></i> Subject</label>
        <select name="subject_id" class="select-neo" required>
          <?php foreach ($subjects as $s): ?>
            <option value="<?= $s['subject_id'] ?>" <?= $exam['subject_id']==$s['subject_id']?'selected':'' ?>>
              <?= htmlspecialchars($s['subject_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label><i class="bi bi-calendar-event-fill"></i> Date</label>
        <input type="date" name="exam_date" class="input-neo" value="<?= htmlspecialchars($exam['exam_date']) ?>" required>
      </div>
      <div class="grid-2">
        <div class="field">
          <label><i class="bi bi-clock-fill"></i> Start Time</label>
          <input type="time" name="start_time" class="input-neo" value="<?= htmlspecialchars($exam['start_time']) ?>" required>
        </div>
        <div class="field">
          <label><i class="bi bi-clock-history"></i> End Time</label>
          <input type="time" name="end_time" class="input-neo" value="<?= htmlspecialchars($exam['end_time']) ?>" required>
        </div>
      </div>
      <div class="field">
        <label><i class="bi bi-geo-alt-fill"></i> Venue</label>
        <input type="text" name="venue" class="input-neo" value="<?= htmlspecialchars($exam['venue']) ?>" required>
      </div>
      <div class="field">
        <label><i class="bi bi-flag-fill"></i> Status</label>
        <select name="status" class="select-neo" required>
          <?php foreach (['scheduled','completed','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $exam['status']==$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
        <button type="submit" class="btn-neo violet"><i class="bi bi-check-circle-fill"></i> Update Exam</button>
        <a href="exams.php" class="btn-neo ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>