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
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h3>Results</h3>
  <p class="text-muted">
    <?php
      if ($role === 'admin')        echo "All results (view only).";
      elseif ($role === 'lecturer') echo "Results of students in your courses (you can edit).";
      else                          echo "Your graded results.";
    ?>
  </p>

  <?php if (empty($results)): ?>
    <div class="alert alert-info">
      <?php if ($role === 'student'): ?>
        You have no graded results yet.
      <?php else: ?>
        No results found.
      <?php endif; ?>
    </div>
  <?php else: ?>
    <table class="table table-striped">
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
          <td><?= htmlspecialchars($r['full_name']) ?></td>
          <td><?= htmlspecialchars($r['course_code']) ?></td>
          <td><?= htmlspecialchars($r['subject_name'] ?? '-') ?></td>
          <td><?= htmlspecialchars($r['marks'] ?? '-') ?></td>
          <td>
            <?php if (!empty($r['grade'])): ?>
              <span class="badge bg-info"><?= htmlspecialchars($r['grade']) ?></span>
            <?php else: ?>
              <span class="badge bg-secondary">Not graded</span>
            <?php endif; ?>
          </td>
          <?php if ($role === 'lecturer'): ?>
            <td>
              <a href="result_edit.php?id=<?= $r['result_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
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