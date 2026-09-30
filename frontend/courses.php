<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];
$response = apiRequest('/courses', 'GET', null, $_SESSION['token']);
$courses  = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];
$error    = ($response['status_code'] !== 200) ? ($response['body']['message'] ?? 'Failed') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Courses - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h3>Courses</h3>
  <p class="text-muted">
    <?php
      if ($role === 'admin')     echo "All courses in the system.";
      elseif ($role === 'lecturer') echo "Courses you teach.";
      else                          echo "Courses related to subjects you take.";
    ?>
  </p>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php elseif (empty($courses)): ?>
    <div class="alert alert-info">No courses found.</div>
  <?php else: ?>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Code</th>
          <th>Title</th>
          <th>Credits</th>
          <th>Lecturer</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($courses as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['course_code']) ?></td>
          <td><?= htmlspecialchars($c['course_title']) ?></td>
          <td><?= htmlspecialchars($c['credits'] ?? '-') ?></td>
          <td><?= htmlspecialchars($c['lecturer_name'] ?? 'TBA') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>