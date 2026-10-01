<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];
$response = apiRequest('/courses', 'GET', null, $_SESSION['token']);
$courses  = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];
$error    = ($response['status_code'] !== 200) ? ($response['body']['message'] ?? 'Failed') : '';
$palette  = ['violet','cyan','mint','pink','amber'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Courses · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-aurora">

  <div class="page-head">
    <div>
      <div class="eyebrow">◆ Curriculum</div>
      <h1>Courses</h1>
      <p class="subtitle">
        <?php
          if ($role === 'admin')        echo "Complete course catalog across the institution.";
          elseif ($role === 'lecturer') echo "Courses you are assigned to teach.";
          else                          echo "Courses connected to your enrolled subjects.";
        ?>
      </p>
    </div>
    <span class="chip violet"><i class="bi bi-collection-fill"></i> <?= count($courses) ?> total</span>
  </div>

  <?php if ($error): ?>
    <div class="alert-neo danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php elseif (empty($courses)): ?>
    <div class="empty-neo">
      <div class="icon"><i class="bi bi-inbox"></i></div>
      <h4>No courses found</h4>
      <p>The course catalog is empty.</p>
    </div>
  <?php else: ?>
    <div class="subject-grid">
      <?php foreach ($courses as $i => $c): $tone = $palette[$i % count($palette)]; ?>
      <div class="subject-card">
        <div class="code"><?= htmlspecialchars($c['course_code']) ?></div>
        <div class="name"><?= htmlspecialchars($c['course_title']) ?></div>
        <div class="meta">
          <span class="chip <?= $tone ?>"><i class="bi bi-award-fill"></i> <?= htmlspecialchars($c['credits'] ?? '—') ?> credits</span>
          <span class="chip gray"><i class="bi bi-person-video3"></i> <?= htmlspecialchars($c['lecturer_name'] ?? 'TBA') ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>