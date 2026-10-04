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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <style>
    .verify-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      position: relative;
      z-index: 1;
    }
    .verify-card {
      background: var(--card-bg);
      backdrop-filter: blur(24px);
      border-radius: var(--radius-2xl);
      max-width: 720px;
      width: 100%;
      box-shadow: var(--shadow-xl);
      border: 1px solid var(--glass-border);
      overflow: hidden;
    }
    .verify-header {
      padding: 2.5rem;
      text-align: center;
      color: white;
      position: relative;
      overflow: hidden;
    }
    .verify-header.ok { background: var(--gradient-forest); }
    .verify-header.err { background: var(--gradient-fire); }
    .verify-header::before {
      content: '';
      position: absolute;
      top: -50%; right: -20%;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
      border-radius: 50%;
    }
    .verify-icon {
      width: 90px;
      height: 90px;
      background: rgba(255,255,255,0.25);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      font-size: 2.75rem;
      border: 2px solid rgba(255,255,255,0.3);
      position: relative;
      z-index: 1;
    }
    .verify-header h2 {
      margin: 0;
      font-weight: 800;
      font-size: 1.85rem;
      position: relative;
      z-index: 1;
    }
    .verify-header p { margin: 0.5rem 0 0 0; opacity: 0.95; position: relative; z-index: 1; }
    .verify-body { padding: 2.25rem; }
    .student-info {
      text-align: center;
      margin-bottom: 2rem;
      padding-bottom: 2rem;
      border-bottom: 1px solid var(--border);
    }
    .student-info h3 {
      margin: 0.5rem 0 0.5rem 0;
      font-weight: 800;
      color: #ffffff;
      font-size: 1.65rem;
    }
    .student-info .matric {
      color: var(--gray);
      font-size: 0.95rem;
    }
    .student-info .matric strong { color: var(--primary-light); }
    .verify-footer {
      padding: 1.25rem 2.25rem;
      background: rgba(0,0,0,0.2);
      text-align: center;
      font-size: 0.82rem;
      color: var(--gray);
      border-top: 1px solid var(--border);
    }
  </style>
</head>
<body>
<div class="verify-wrapper">
  <div class="verify-card">

    <?php if ($error): ?>
      <div class="verify-header err">
        <div class="verify-icon"><i class="bi bi-x-lg"></i></div>
        <h2>Verification Failed</h2>
        <p><?= htmlspecialchars($error) ?></p>
      </div>
    <?php else: ?>
      <div class="verify-header ok">
        <div class="verify-icon"><i class="bi bi-check-lg"></i></div>
        <h2>Student Verified</h2>
        <p>Official examination registration confirmed</p>
      </div>
    <?php endif; ?>

    <?php if (!$error && $student): ?>
      <div class="verify-body">

        <div class="student-info">
          <h3><?= htmlspecialchars($student['full_name']) ?></h3>
          <p class="matric">
            Matric No: <strong><?= htmlspecialchars($student['matric_no'] ?? '-') ?></strong>
            &nbsp;·&nbsp;
            User ID: <strong>#<?= htmlspecialchars($student['user_id']) ?></strong>
          </p>
        </div>

        <h5 style="color:#ffffff;margin-bottom:1rem;">
          <i class="bi bi-book-fill"></i> Registered Subjects (<?= count($subjects) ?>)
        </h5>

        <?php if (empty($subjects)): ?>
          <div class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill"></i> No subjects registered.</div>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>Course</th>
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

        <div class="alert alert-success" style="margin-top:1.5rem;">
          <i class="bi bi-check-circle-fill"></i>
          <strong>Verified.</strong> This student is officially registered in <?= count($subjects) ?> subject(s).
        </div>
      </div>

      <div class="verify-footer">
        ExamSys &copy; <?= date('Y') ?> — Verified at <?= date('Y-m-d H:i:s') ?>
      </div>
    <?php endif; ?>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>