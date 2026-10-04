<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];
$endpoint = ($role === 'student') ? '/results?graded=1' : '/results';
$response = apiRequest($endpoint, 'GET', null, $_SESSION['token']);
$results  = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Results - ExamSys</title>
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
      <h1>Published Results</h1>
      <p class="subtitle">
        <?php
          if ($role === 'admin')        echo "All results — read only.";
          elseif ($role === 'lecturer') echo "Results for students in your courses.";
          else                          echo "Your published examination results.";
        ?>
      </p>
    </div>
    <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> <?= count($results) ?> Records</span>
  </div>

  <?php if (empty($results)): ?>
    <div class="empty-state">
      <div class="icon"><i class="bi bi-bar-chart"></i></div>
      <h4>No Results Found</h4>
      <p>
        <?php if ($role === 'student'): ?>
          You have no graded results yet.
        <?php else: ?>
          No results are available at the moment.
        <?php endif; ?>
      </p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Student</th>
            <th>Course</th>
            <th>Subject</th>
            <th>Marks</th>
            <th>Grade</th>
            <?php if ($role === 'lecturer'): ?><th>Action</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results as $r): ?>
          <tr>
            <td><i class="bi bi-person-circle"></i> <?= htmlspecialchars($r['full_name']) ?></td>
            <td><span class="badge bg-primary"><?= htmlspecialchars($r['course_code']) ?></span></td>
            <td><?= htmlspecialchars($r['subject_name'] ?? '-') ?></td>
            <td><strong><?= htmlspecialchars($r['marks'] ?? '-') ?></strong></td>
            <td>
              <?php if (!empty($r['grade'])): ?>
                <span class="badge bg-info"><?= htmlspecialchars($r['grade']) ?></span>
              <?php else: ?>
                <span class="badge bg-secondary">Pending</span>
              <?php endif; ?>
            </td>
            <?php if ($role === 'lecturer'): ?>
              <td>
                <a href="result_edit.php?id=<?= $r['result_id'] ?>" class="btn btn-warning btn-sm">
                  <i class="bi bi-pencil-fill"></i> Edit
                </a>
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