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
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h3>Examinations</h3>
  <p class="text-muted">
    <?php
      if ($role === 'admin')        echo "All exams. You can create, edit or delete.";
      elseif ($role === 'lecturer') echo "All exams (view only).";
      else                          echo "Exams for subjects you are taking.";
    ?>
  </p>

  <!-- ⭐ HANYA ADMIN nampak butang ni -->
  <?php if ($role === 'admin'): ?>
    <a href="exam_add.php" class="btn btn-primary mb-3">Add New Exam</a>
  <?php endif; ?>

  <?php if (empty($exams)): ?>
    <div class="alert alert-info">No exams found.</div>
  <?php else: ?>
    <table class="table table-striped">
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
          <td><?= htmlspecialchars($e['course_code'] . ' - ' . $e['course_title']) ?></td>
          <td><?= htmlspecialchars($e['subject_name']) ?></td>
          <td><?= htmlspecialchars($e['exam_date']) ?></td>
          <td><?= htmlspecialchars($e['start_time'] . ' - ' . $e['end_time']) ?></td>
          <td><?= htmlspecialchars($e['venue']) ?></td>
          <td><span class="badge bg-info"><?= htmlspecialchars($e['status']) ?></span></td>
          <?php if ($role === 'admin'): ?>
            <td>
              <a href="exam_edit.php?id=<?= $e['exam_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
              <a href="exam_delete.php?id=<?= $e['exam_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this exam?')">Delete</a>
            </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>