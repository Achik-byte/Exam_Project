<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';
$role = $_SESSION['role'];
$response = apiRequest('/courses', 'GET', null, $_SESSION['token']);
$courses  = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];
$error    = ($response['status_code'] !== 200) ? ($response['body']['message'] ?? 'Failed') : '';
$tones = ['indigo','mint','amber','coral','sky','violet'];
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
<div class="container-lum">

  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ Curriculum</span>
      <h1>Our <em>courses</em></h1>
      <p class="sub">
        <?php
          if ($role === 'admin')        echo "Complete course catalog across the institution.";
          elseif ($role === 'lecturer') echo "Courses you are assigned to teach.";
          else                          echo "Courses connected to your enrolled subjects.";
        ?>
      </p>
    </div>
    <span class="pill indigo" style="padding:0.5rem 1rem;font-size:0.85rem;">
      <i class="bi bi-collection-fill"></i> <?= count($courses) ?> courses
    </span>
  </div>

  <?php if ($error): ?>
    <div class="alert-lum danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php elseif (empty($courses)): ?>
    <div class="empty-lum">
      <div class="icon"><i class="bi bi-inbox"></i></div>
      <h4>No courses yet</h4>
      <p>The course catalog is currently empty.</p>
    </div>
  <?php else: ?>
    <div class="grid-lum">
      <?php foreach ($courses as $i => $c): $tone = $tones[$i % count($tones)]; ?>
      <div class="tile-lum <?= $tone ?>">
        <span class="code"><?= htmlspecialchars($c['course_code']) ?></span>
        <div class="name"><?= htmlspecialchars($c['course_title']) ?></div>
        <div class="meta">
          <span class="pill gray"><i class="bi bi-award-fill"></i> <?= htmlspecialchars($c['credits'] ?? '—') ?> credits</span>
          <span class="pill gray"><i class="bi bi-person-video3"></i> <?= htmlspecialchars($c['lecturer_name'] ?? 'TBA') ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>