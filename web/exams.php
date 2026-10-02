<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';
$role = $_SESSION['role'];
$response = apiRequest('/examinations', 'GET', null, $_SESSION['token']);
$exams    = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];

// === SUSUN IKUT SUBJECT (A-Z) ===
usort($exams, function($a, $b) {
    return strcasecmp($a['subject_name'] ?? '', $b['subject_name'] ?? '');
});

function statusPill($s) {
  $map = ['scheduled'=>'blue', 'completed'=>'green', 'cancelled'=>'red'];
  $t = $map[strtolower($s)] ?? 'gray';
  return "<span class='pill-glass $t'><span class='dot'></span> ".htmlspecialchars($s)."</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Exams · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ Schedule</span>
      <h1>Exam <em>timetable</em></h1>
      <p class="sub">
        <?php
          if ($role === 'admin')        echo "Full exam control — sorted by subject.";
          elseif ($role === 'lecturer') echo "View only. Sorted by subject.";
          else                          echo "Exams for subjects you are enrolled in.";
        ?>
      </p>
    </div>
    <?php if ($role === 'admin'): ?>
      <a href="exam_add.php" class="btn-glass primary"><i class="bi bi-plus-lg"></i> New Exam</a>
    <?php endif; ?>
  </div>

  <?php if (empty($exams)): ?>
    <div class="empty-glass">
      <div class="icon"><i class="bi bi-calendar-x"></i></div>
      <h4>No exams scheduled</h4>
      <p>No exams have been added yet.</p>
    </div>
  <?php else: ?>
    <div class="table-glass-wrap">
      <table class="table-glass">
        <thead>
          <tr>
            <th style="width:28%;">Subject</th>
            <th style="width:28%;">Course</th>
            <th style="width:10%;">Date</th>
            <th style="width:12%;">Time</th>
            <th style="width:12%;">Venue</th>
            <th style="width:10%;">Status</th>
            <?php if ($role === 'admin'): ?><th style="width:10%;text-align:right;">Action</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($exams as $e): ?>
          <tr>
            <td class="primary"><?= htmlspecialchars($e['subject_name'] ?? '—') ?></td>
            <td>
              <div style="font-weight:600;color:white;font-size:0.9rem;"><?= htmlspecialchars($e['course_code']) ?></div>
              <div style="font-size:0.75rem;color:var(--ink-3);margin-top:0.15rem;line-height:1.3;"><?= htmlspecialchars($e['course_title']) ?></div>
            </td>
            <td><span class="mono"><?= htmlspecialchars($e['exam_date']) ?></span></td>
            <td><span class="mono"><?= htmlspecialchars(substr($e['start_time'],0,5)) ?>–<?= htmlspecialchars(substr($e['end_time'],0,5)) ?></span></td>
            <td><i class="bi bi-geo-alt-fill" style="color:#f9a8d4;"></i> <?= htmlspecialchars($e['venue']) ?></td>
            <td><?= statusPill($e['status']) ?></td>
            <?php if ($role === 'admin'): ?>
              <td style="text-align:right;white-space:nowrap;">
                <a href="exam_edit.php?id=<?= $e['exam_id'] ?>" class="btn-glass sm" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                <a href="exam_delete.php?id=<?= $e['exam_id'] ?>" class="btn-glass sm red" title="Delete" onclick="return confirm('Delete this exam?')"><i class="bi bi-trash3-fill"></i></a>
              </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>