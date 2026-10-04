<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];
$response = apiRequest('/examinations', 'GET', null, $_SESSION['token']);
$exams    = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Exams - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern">

  <div class="page-header">
    <div>
      <h1>Exam Timetable</h1>
      <p class="subtitle">
        <?php
          if ($role === 'admin')        echo "Full exam control — create, update, or remove.";
          elseif ($role === 'lecturer') echo "View only. All scheduled exams.";
          else                          echo "Exams for subjects you are taking.";
        ?>
      </p>
    </div>
    <?php if ($role === 'admin'): ?>
      <a href="exam_add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Exam</a>
    <?php else: ?>
      <span class="badge bg-info"><i class="bi bi-calendar-event-fill"></i> <?= count($exams) ?> Exams</span>
    <?php endif; ?>
  </div>

  <?php if (empty($exams)): ?>
    <div class="empty-state">
      <div class="icon"><i class="bi bi-calendar-x"></i></div>
      <h4>No Exams Found</h4>
      <p>There are no exams scheduled at the moment.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Course</th>
            <th>Subject</th>
            <th>Date</th>
            <th>Time</th>
            <th>Venue</th>
            <th>Status</th>
            <?php if ($role === 'admin'): ?><th>Action</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($exams as $e): ?>
          <tr>
            <td><strong><?= htmlspecialchars($e['course_code']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($e['course_title']) ?></small></td>
            <td><?= htmlspecialchars($e['subject_name']) ?></td>
            <td><i class="bi bi-calendar3"></i> <?= htmlspecialchars($e['exam_date']) ?></td>
            <td><i class="bi bi-clock-fill"></i> <?= htmlspecialchars($e['start_time'] . ' - ' . $e['end_time']) ?></td>
            <td><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($e['venue']) ?></td>
            <td><span class="badge bg-info"><?= htmlspecialchars($e['status']) ?></span></td>
            <?php if ($role === 'admin'): ?>
              <td>
                <a href="exam_edit.php?id=<?= $e['exam_id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-fill"></i></a>
                <a href="exam_delete.php?id=<?= $e['exam_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this exam?')"><i class="bi bi-trash-fill"></i></a>
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