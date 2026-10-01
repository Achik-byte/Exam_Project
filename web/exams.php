<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];
$response = apiRequest('/examinations', 'GET', null, $_SESSION['token']);
$exams    = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];

function statusChip($s) {
  $map = ['scheduled' => 'cyan', 'completed' => 'mint', 'cancelled' => 'red'];
  $tone = $map[strtolower($s)] ?? 'gray';
  return "<span class='chip $tone'><i class='bi bi-circle-fill' style='font-size:.5rem;'></i> ".htmlspecialchars($s)."</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Exams · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-aurora">

  <div class="page-head">
    <div>
      <div class="eyebrow">◆ Schedule</div>
      <h1>Examinations</h1>
      <p class="subtitle">
        <?php
          if ($role === 'admin')        echo "Full exam control — create, update, or remove.";
          elseif ($role === 'lecturer') echo "View only. All scheduled exams.";
          else                          echo "Exams for subjects you are enrolled in.";
        ?>
      </p>
    </div>
    <?php if ($role === 'admin'): ?>
      <a href="exam_add.php" class="btn-neo violet"><i class="bi bi-plus-lg"></i> New Exam</a>
    <?php endif; ?>
  </div>

  <?php if (empty($exams)): ?>
    <div class="empty-neo">
      <div class="icon"><i class="bi bi-calendar-x"></i></div>
      <h4>No exams scheduled</h4>
      <p>No exams have been added to the system yet.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table-neo">
        <thead>
          <tr>
            <th>Course</th>
            <th>Subject</th>
            <th>Date</th>
            <th>Time</th>
            <th>Venue</th>
            <th>Status</th>
            <?php if ($role === 'admin'): ?><th style="text-align:right;">Action</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($exams as $e): ?>
          <tr>
            <td>
              <div class="cell-primary"><?= htmlspecialchars($e['course_code']) ?></div>
              <div class="cell-muted"><?= htmlspecialchars($e['course_title']) ?></div>
            </td>
            <td><?= htmlspecialchars($e['subject_name']) ?></td>
            <td><span class="mono"><?= htmlspecialchars($e['exam_date']) ?></span></td>
            <td><span class="mono"><?= htmlspecialchars(substr($e['start_time'],0,5)) ?> – <?= htmlspecialchars(substr($e['end_time'],0,5)) ?></span></td>
            <td><i class="bi bi-geo-alt-fill" style="color:var(--pink)"></i> <?= htmlspecialchars($e['venue']) ?></td>
            <td><?= statusChip($e['status']) ?></td>
            <?php if ($role === 'admin'): ?>
              <td style="text-align:right;">
                <a href="exam_edit.php?id=<?= $e['exam_id'] ?>" class="btn-neo ghost sm"><i class="bi bi-pencil"></i></a>
                <a href="exam_delete.php?id=<?= $e['exam_id'] ?>" class="btn-neo danger sm" onclick="return confirm('Delete this exam?')"><i class="bi bi-trash3"></i></a>
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