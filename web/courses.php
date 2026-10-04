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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern">

  <div class="page-header">
    <div>
      <h1>Our Courses</h1>
      <p class="subtitle">
        <?php
          if ($role === 'admin')        echo "All courses in the system.";
          elseif ($role === 'lecturer') echo "Courses you are assigned to teach.";
          else                          echo "Courses related to subjects you take.";
        ?>
      </p>
    </div>
    <span class="badge bg-primary"><i class="bi bi-book-fill"></i> <?= count($courses) ?> Courses</span>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div>
  <?php elseif (empty($courses)): ?>
    <div class="empty-state">
      <div class="icon"><i class="bi bi-journal-x"></i></div>
      <h4>No Courses Found</h4>
      <p>There are no courses available at the moment.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Course Title</th>
            <th>Credits</th>
            <th>Lecturer</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $c): ?>
          <tr>
            <td><span class="badge bg-primary"><?= htmlspecialchars($c['course_code']) ?></span></td>
            <td><?= htmlspecialchars($c['course_title']) ?></td>
            <td><i class="bi bi-award-fill"></i> <?= htmlspecialchars($c['credits'] ?? '-') ?> credits</td>
            <td><i class="bi bi-person-circle"></i> <?= htmlspecialchars($c['lecturer_name'] ?? 'TBA') ?></td>
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