<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';
$role = $_SESSION['role'];
$endpoint = ($role === 'student') ? '/results?graded=1' : '/results';
$response = apiRequest($endpoint, 'GET', null, $_SESSION['token']);
$results  = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];

function gradePill($g) {
  if (!$g) return "<span class='pill gray'>Pending</span>";
  $f = strtoupper($g[0]);
  $t = $f === 'A' ? 'mint' : ($f === 'B' ? 'sky' : ($f === 'C' ? 'amber' : 'coral'));
  return "<span class='pill $t' style='font-weight:800;'>$g</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Results · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-lum">

  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ Academic Records</span>
      <h1>Published <em>results</em></h1>
      <p class="sub">
        <?php
          if ($role === 'admin')        echo "Full result ledger — read-only.";
          elseif ($role === 'lecturer') echo "Results for students in your courses.";
          else                          echo "Your published examination results.";
        ?>
      </p>
    </div>
    <span class="pill mint" style="padding:0.5rem 1rem;font-size:0.85rem;">
      <i class="bi bi-award-fill"></i> <?= count($results) ?> records
    </span>
  </div>

  <?php if (empty($results)): ?>
    <div class="empty-lum">
      <div class="icon"><i class="bi bi-clipboard-x"></i></div>
      <h4>No results yet</h4>
      <p><?= $role === 'student' ? 'Nothing graded for you yet.' : 'No results in the system.' ?></p>
    </div>
  <?php else: ?>
    <div class="table-lum-wrap">
      <table class="table-lum">
        <thead>
          <tr>
            <th>Student</th>
            <th>Course</th>
            <th>Subject</th>
            <th>Marks</th>
            <th>Grade</th>
            <?php if ($role === 'lecturer'): ?><th style="text-align:right;">Action</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results as $r): ?>
          <tr>
            <td class="primary"><?= htmlspecialchars($r['full_name']) ?></td>
            <td><span class="mono"><?= htmlspecialchars($r['course_code']) ?></span></td>
            <td><?= htmlspecialchars($r['subject_name'] ?? '—') ?></td>
            <td><span class="mono" style="font-weight:700;color:var(--indigo);font-size:0.98rem;"><?= htmlspecialchars($r['marks'] ?? '—') ?></span></td>
            <td><?= gradePill($r['grade'] ?? null) ?></td>
            <?php if ($role === 'lecturer'): ?>
              <td style="text-align:right;">
                <a href="result_edit.php?id=<?= $r['result_id'] ?>" class="btn-lum ghost sm"><i class="bi bi-pencil"></i> Edit</a>
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