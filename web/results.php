<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];
$endpoint = ($role === 'student') ? '/results?graded=1' : '/results';
$response = apiRequest($endpoint, 'GET', null, $_SESSION['token']);
$results  = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];

function gradeTone($g) {
  if (!$g) return 'gray';
  $first = $g[0];
  if ($first === 'A') return 'mint';
  if ($first === 'B') return 'cyan';
  if ($first === 'C') return 'amber';
  return 'red';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Results · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-aurora">

  <div class="page-head">
    <div>
      <div class="eyebrow">◆ Academic Performance</div>
      <h1>Results</h1>
      <p class="subtitle">
        <?php
          if ($role === 'admin')        echo "Full result ledger — read-only.";
          elseif ($role === 'lecturer') echo "Results for students in your courses.";
          else                          echo "Your published examination results.";
        ?>
      </p>
    </div>
    <span class="chip mint"><i class="bi bi-bar-chart-fill"></i> <?= count($results) ?> records</span>
  </div>

  <?php if (empty($results)): ?>
    <div class="empty-neo">
      <div class="icon"><i class="bi bi-clipboard-x"></i></div>
      <h4>No results available</h4>
      <p><?= $role === 'student' ? 'Nothing graded for you yet.' : 'No results in the system.' ?></p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table-neo">
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
            <td class="cell-primary"><?= htmlspecialchars($r['full_name']) ?></td>
            <td><span class="mono" style="color:var(--cyan)"><?= htmlspecialchars($r['course_code']) ?></span></td>
            <td><?= htmlspecialchars($r['subject_name'] ?? '—') ?></td>
            <td><span class="mono" style="font-size:1rem;color:var(--violet-2);font-weight:700;"><?= htmlspecialchars($r['marks'] ?? '—') ?></span></td>
            <td>
              <?php if (!empty($r['grade'])): ?>
                <span class="chip <?= gradeTone($r['grade']) ?>"><?= htmlspecialchars($r['grade']) ?></span>
              <?php else: ?>
                <span class="chip gray">Pending</span>
              <?php endif; ?>
            </td>
            <?php if ($role === 'lecturer'): ?>
              <td style="text-align:right;">
                <a href="result_edit.php?id=<?= $r['result_id'] ?>" class="btn-neo ghost sm"><i class="bi bi-pencil"></i> Edit</a>
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