<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';
$role = $_SESSION['role'];
$endpoint = ($role === 'student') ? '/results?graded=1' : '/results';
$response = apiRequest($endpoint, 'GET', null, $_SESSION['token']);
$results  = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];

// === SUSUN IKUT SUBJECT (A-Z), KEMUDIAN NAMA STUDENT (A-Z) ===
usort($results, function($a, $b) {
    $subjectCmp = strcasecmp($a['subject_name'] ?? '', $b['subject_name'] ?? '');
    if ($subjectCmp !== 0) return $subjectCmp;
    return strcasecmp($a['full_name'] ?? '', $b['full_name'] ?? '');
});

// === GROUP ikut subject untuk paparan ===
$grouped = [];
foreach ($results as $r) {
    $key = $r['subject_name'] ?? 'Unknown Subject';
    $grouped[$key][] = $r;
}

function gradePill($g) {
  if (!$g) return "<span class='pill-glass gray'>Pending</span>";
  $f = strtoupper($g[0]);
  $t = $f === 'A' ? 'green' : ($f === 'B' ? 'blue' : ($f === 'C' ? 'amber' : 'red'));
  return "<span class='pill-glass $t' style='font-weight:700;'>$g</span>";
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
  <style>
    .subject-group {
      margin-bottom: 1.5rem;
    }
    .subject-header {
      display: flex; align-items: center; gap: 0.75rem;
      padding: 1rem 1.25rem;
      background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(168,85,247,0.15));
      border: 1px solid var(--glass-line-2);
      border-radius: var(--r-lg) var(--r-lg) 0 0;
      backdrop-filter: blur(20px);
    }
    .subject-header .icon {
      width: 38px; height: 38px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      display: flex; align-items: center; justify-content: center;
      color: white;
      font-size: 1rem;
      flex-shrink: 0;
      box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
    }
    .subject-header .name {
      font-family: 'Outfit', sans-serif;
      font-weight: 700;
      font-size: 1.05rem;
      color: white;
      letter-spacing: -0.01em;
    }
    .subject-header .count {
      margin-left: auto;
      font-size: 0.78rem;
      color: var(--ink-3);
      font-weight: 600;
    }
    .subject-body {
      border: 1px solid var(--glass-line);
      border-top: none;
      border-radius: 0 0 var(--r-lg) var(--r-lg);
      overflow: hidden;
      background: var(--glass);
      backdrop-filter: blur(20px);
    }
    .subject-body table { width: 100%; border-collapse: collapse; }
    .subject-body table thead th {
      padding: 0.85rem 1.25rem;
      background: rgba(0,0,0,0.2);
      font-size: 0.68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--ink-3);
      text-align: left;
    }
    .subject-body table tbody td {
      padding: 0.95rem 1.25rem;
      border-top: 1px solid var(--glass-line);
      color: var(--ink-2);
      font-size: 0.9rem;
      vertical-align: middle;
    }
    .subject-body table tbody tr:hover { background: var(--glass-2); }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ Academic Records</span>
      <h1>Published <em>results</em></h1>
      <p class="sub">
        <?php
          if ($role === 'admin')        echo "Full result ledger — grouped by subject.";
          elseif ($role === 'lecturer') echo "Results grouped by subject for easy grading.";
          else                          echo "Your results, grouped by subject.";
        ?>
      </p>
    </div>
    <span class="pill-glass green" style="padding:0.6rem 1rem;font-size:0.85rem;">
      <i class="bi bi-award-fill"></i> <?= count($results) ?> records
    </span>
  </div>

  <?php if (empty($results)): ?>
    <div class="empty-glass">
      <div class="icon"><i class="bi bi-clipboard-x"></i></div>
      <h4>No results yet</h4>
      <p><?= $role === 'student' ? 'Nothing graded for you yet.' : 'No results in the system.' ?></p>
    </div>
  <?php else: ?>

    <?php foreach ($grouped as $subjectName => $items): ?>
      <div class="subject-group">
        <div class="subject-header">
          <div class="icon"><i class="bi bi-bookmark-fill"></i></div>
          <div class="name"><?= htmlspecialchars($subjectName) ?></div>
          <div class="count"><?= count($items) ?> record(s)</div>
        </div>
        <div class="subject-body">
          <table>
            <thead>
              <tr>
                <th style="width:28%;">Student</th>
                <th style="width:15%;">Course</th>
                <th style="width:15%;">Marks</th>
                <th style="width:15%;">Grade</th>
                <?php if ($role === 'lecturer'): ?><th style="width:27%;text-align:right;">Action</th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $r): ?>
              <tr>
                <td class="primary" style="font-weight:600;color:white;"><?= htmlspecialchars($r['full_name']) ?></td>
                <td><span class="mono"><?= htmlspecialchars($r['course_code']) ?></span></td>
                <td><span class="mono" style="font-weight:700;color:#a5b4fc;"><?= htmlspecialchars($r['marks'] ?? '—') ?></span></td>
                <td><?= gradePill($r['grade'] ?? null) ?></td>
                <?php if ($role === 'lecturer'): ?>
                  <td style="text-align:right;white-space:nowrap;">
                    <a href="result_edit.php?id=<?= $r['result_id'] ?>" class="btn-glass sm" title="Edit">
                      <i class="bi bi-pencil-fill"></i>
                    </a>
                    <a href="result_delete.php?id=<?= $r['result_id'] ?>" class="btn-glass sm red" title="Delete"
                       onclick="return confirm('Delete this result?')">
                      <i class="bi bi-trash3-fill"></i>
                    </a>
                  </td>
                <?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>

  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>