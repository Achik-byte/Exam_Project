<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';
$role = $_SESSION['role'];
$response = apiRequest('/courses', 'GET', null, $_SESSION['token']);
$courses  = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];
$error    = ($response['status_code'] !== 200) ? ($response['body']['message'] ?? 'Failed') : '';
$tones = ['', 'green', 'amber', 'purple', 'pink'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Courses · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
  <style>
    .course-row {
      display: grid;
      grid-template-columns: 4px 120px 1fr 200px 100px;
      gap: 1.5rem;
      align-items: center;
      padding: 1.5rem 1.5rem 1.5rem 0;
      background: var(--glass);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-lg);
      margin-bottom: 1rem;
      transition: all .25s;
      overflow: hidden;
    }
    .course-row:hover {
      border-color: var(--glass-line-2);
      background: var(--glass-2);
      transform: translateX(6px);
      box-shadow: 0 8px 32px rgba(99, 102, 241, 0.15);
    }
    .course-row .stripe {
      height: 100%;
      background: linear-gradient(180deg, var(--aurora-1), var(--aurora-2));
    }
    .course-row.green .stripe  { background: linear-gradient(180deg, #10b981, #059669); }
    .course-row.amber .stripe  { background: linear-gradient(180deg, #f59e0b, #d97706); }
    .course-row.purple .stripe { background: linear-gradient(180deg, #a855f7, #7e22ce); }
    .course-row.pink .stripe   { background: linear-gradient(180deg, #ec4899, #be185d); }
    .course-row .code-badge {
      font-family: 'SF Mono', monospace;
      font-weight: 700;
      color: #a5b4fc;
      font-size: 0.95rem;
      padding: 0.5rem 0.9rem;
      background: rgba(99, 102, 241, 0.15);
      border: 1px solid rgba(99, 102, 241, 0.3);
      border-radius: var(--r-pill);
      text-align: center;
    }
    .course-row.green .code-badge { color: #6ee7b7; background: rgba(16,185,129,0.15); border-color: rgba(16,185,129,0.3); }
    .course-row.amber .code-badge { color: #fcd34d; background: rgba(245,158,11,0.15); border-color: rgba(245,158,11,0.3); }
    .course-row.purple .code-badge { color: #d8b4fe; background: rgba(168,85,247,0.15); border-color: rgba(168,85,247,0.3); }
    .course-row.pink .code-badge  { color: #f9a8d4; background: rgba(236,72,153,0.15); border-color: rgba(236,72,153,0.3); }
    .course-row .title {
      font-family: 'Outfit', sans-serif;
      font-size: 1.15rem;
      font-weight: 700;
      color: white;
      line-height: 1.3;
    }
    .course-row .meta-item {
      font-size: 0.85rem;
      color: var(--ink-3);
      display: flex; align-items: center; gap: 0.4rem;
      margin-bottom: 0.35rem;
    }
    @media (max-width: 900px) {
      .course-row { grid-template-columns: 4px 1fr; gap: 1rem; }
      .course-row > *:nth-child(n+2) { grid-column: 2; }
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass">

  <div class="head-glass">
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
    <span class="pill-glass blue" style="padding:0.6rem 1rem;font-size:0.85rem;">
      <i class="bi bi-collection-fill"></i> <?= count($courses) ?> courses
    </span>
  </div>

  <?php if ($error): ?>
    <div class="alert-glass danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php elseif (empty($courses)): ?>
    <div class="empty-glass">
      <div class="icon"><i class="bi bi-inbox"></i></div>
      <h4>No courses yet</h4>
      <p>The course catalog is currently empty.</p>
    </div>
  <?php else: ?>
    <?php foreach ($courses as $i => $c): $tone = $tones[$i % count($tones)]; ?>
      <div class="course-row <?= $tone ?>">
        <div class="stripe"></div>
        <div class="code-badge"><?= htmlspecialchars($c['course_code']) ?></div>
        <div class="title"><?= htmlspecialchars($c['course_title']) ?></div>
        <div>
          <div class="meta-item"><i class="bi bi-award-fill" style="color:#fcd34d;"></i> <?= htmlspecialchars($c['credits'] ?? '—') ?> Credits</div>
          <div class="meta-item"><i class="bi bi-person-video3" style="color:#6ee7b7;"></i> <?= htmlspecialchars($c['lecturer_name'] ?? 'TBA') ?></div>
        </div>
        <div style="text-align:right;">
          <span class="pill-glass gray">Course #<?= $i+1 ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>