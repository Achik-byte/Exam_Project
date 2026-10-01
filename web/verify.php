<?php
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
$student = null;
$subjects = [];
$error = "";

if (!$id) {
    $error = "No student ID provided.";
} else {
    $response = apiRequest('/verify/' . $id, 'GET');
    
    if ($response['status_code'] === 200) {
        $student  = $response['body']['data']['student'];
        $subjects = $response['body']['data']['subjects'];
    } else {
        $error = $response['body']['message'] ?? 'Verification failed';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Verification - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 800px;">
  <div class="card shadow">
    <div class="card-header bg-success text-white text-center py-3">
      <h4 class="mb-0">🎓 Exam Verification</h4>
      <small>Scan result — Official Student Record</small>
    </div>

    <div class="card-body">
      <?php if ($error): ?>
        <div class="alert alert-danger text-center">
          <h5>❌ Verification Failed</h5>
          <p class="mb-0"><?= htmlspecialchars($error) ?></p>
        </div>
      <?php else: ?>
        
        <div class="text-center mb-4">
          <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
               style="width: 80px; height: 80px; font-size: 32px;">
            ✓
          </div>
          <h3 class="mt-3"><?= htmlspecialchars($student['full_name']) ?></h3>
          <p class="text-muted mb-0">
            Matric No: <strong><?= htmlspecialchars($student['matric_no'] ?? '-') ?></strong>
          </p>
          <small class="text-muted">User ID: <?= htmlspecialchars($student['user_id']) ?></small>
        </div>

        <h5 class="border-bottom pb-2">📚 Registered Subjects</h5>
        
        <?php if (empty($subjects)): ?>
          <div class="alert alert-warning">No subjects registered.</div>
        <?php else: ?>
          <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th style="width: 50%;">Course</th>
                <th>Subject</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($subjects as $s): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($s['course_code']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($s['course_title']) ?></small>
                </td>
                <td><?= htmlspecialchars($s['subject_name']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <div class="alert alert-success mt-4 mb-0">
          <strong>✓ Verified.</strong> 
          This student is officially registered in <?= count($subjects) ?> subject(s) for the upcoming examination.
        </div>

      <?php endif; ?>
    </div>

    <div class="card-footer text-center text-muted small">
      ExamSys &copy; <?= date('Y') ?> — Verified at <?= date('Y-m-d H:i:s') ?>
    </div>
  </div>
</div>
</body>
</html>