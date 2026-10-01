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
    if ($response['status_code'] === 200) { header("Location: exams.php"); exit; }
    else $error = $response['body']['message'] ?? 'Failed to update';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Exam · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-lum" style="max-width:880px;">

  <div style="display:flex;align-items:center;gap:0.5rem;color:var(--ink-3);font-size:0.85rem;margin-bottom:1.25rem;">
    <a href="exams.php" style="color:var(--ink-3);text-decoration:none;"><i class="bi bi-calendar-event"></i> Exams</a>
    <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i>
    <span style="color:var(--ink);font-weight:600;">Edit #<?= htmlspecialchars($exam['exam_id']) ?></span>
  </div>

  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ Edit Entry</span>
      <h1>Edit <em>exam</em></h1>
      <p class="sub">Update the details of this examination.</p>
    </div>
    <a href="exams.php" class="btn-lum ghost"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-lum danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <!-- INFO BANNER -->
  <div class="info-banner">
    <div class="info-icon" style="background:var(--indigo);">
      <i class="bi bi-collection-fill"></i>
    </div>
    <div style="flex:1;">
      <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--ink-3);font-weight:700;margin-bottom:0.2rem;">Course (locked)</div>
      <div style="font-weight:700;color:var(--ink);font-size:1rem;"><?= htmlspecialchars($exam['course_code']) ?></div>
      <div style="color:var(--ink-3);font-size:0.88rem;"><?= htmlspecialchars($exam['course_title']) ?></div>
    </div>
  </div>

  <div class="card-lum flat">
    <form method="POST">

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--indigo-soft);color:var(--indigo);">
            <i class="bi bi-bookmark-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Subject</h3>
            <p class="form-section-desc">Choose the subject for this exam.</p>
          </div>
        </div>
        <div class="field-lum">
          <label><i class="bi bi-bookmark-fill"></i> Subject <span class="req">*</span></label>
          <select name="subject_id" class="select-lum" required>
            <?php foreach ($subjects as $s): ?>
              <option value="<?= $s['subject_id'] ?>" <?= $exam['subject_id']==$s['subject_id']?'selected':'' ?>>
                <?= htmlspecialchars($s['subject_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--amber-soft);color:#92400e;">
            <i class="bi bi-clock-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Date & Time</h3>
            <p class="form-section-desc">When will this exam take place?</p>
          </div>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-calendar-event-fill"></i> Exam Date <span class="req">*</span></label>
          <input type="date" name="exam_date" class="input-lum" value="<?= htmlspecialchars($exam['exam_date']) ?>" required>
        </div>

        <div class="grid-2">
          <div class="field-lum">
            <label><i class="bi bi-play-circle-fill"></i> Start Time <span class="req">*</span></label>
            <input type="time" name="start_time" class="input-lum" value="<?= htmlspecialchars($exam['start_time']) ?>" required>
          </div>
          <div class="field-lum">
            <label><i class="bi bi-stop-circle-fill"></i> End Time <span class="req">*</span></label>
            <input type="time" name="end_time" class="input-lum" value="<?= htmlspecialchars($exam['end_time']) ?>" required>
          </div>
        </div>
      </div>

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--mint-soft);color:#047857;">
            <i class="bi bi-geo-alt-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Location & Status</h3>
            <p class="form-section-desc">Where and current state.</p>
          </div>
        </div>

        <div class="grid-2">
          <div class="field-lum">
            <label><i class="bi bi-geo-alt-fill"></i> Venue <span class="req">*</span></label>
            <input type="text" name="venue" class="input-lum" value="<?= htmlspecialchars($exam['venue']) ?>" required>
          </div>
          <div class="field-lum">
            <label><i class="bi bi-flag-fill"></i> Status <span class="req">*</span></label>
            <select name="status" class="select-lum" required>
              <?php foreach (['scheduled'=>'🕒 Scheduled','completed'=>'✓ Completed','cancelled'=>'✕ Cancelled'] as $s => $lbl): ?>
                <option value="<?= $s ?>" <?= $exam['status']==$s?'selected':'' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <a href="exams.php" class="btn-lum ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        <button type="submit" class="btn-lum primary"><i class="bi bi-check-circle-fill"></i> Update Exam</button>
      </div>

    </form>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>