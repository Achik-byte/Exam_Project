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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Verification - ExamSys</title>
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
      backdrop-filter: blur(24px) saturate(180%);
      -webkit-backdrop-filter: blur(24px) saturate(180%);
      border-radius: var(--radius-2xl);
      max-width: 720px;
      width: 100%;
      box-shadow: var(--shadow-xl), inset 0 1px 0 rgba(255,255,255,0.08);
      border: 1px solid var(--glass-border);
      overflow: hidden;
      animation: fadeInUp 0.7s cubic-bezier(0.4,0,0.2,1);
    }
    .verify-header {
      padding: 2.75rem 2rem;
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
      background: radial-gradient(circle, rgba(255,255,255,0.25) 0%, transparent 70%);
      border-radius: 50%;
      animation: float 8s ease-in-out infinite;
    }
    .verify-header::after {
      content: '';
      position: absolute;
      bottom: -50%; left: -20%;
      width: 300px; height: 300px;
      background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
      border-radius: 50%;
      animation: float 10s ease-in-out infinite reverse;
    }
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-15px); }
    }
    .verify-icon {
      width: 90px;
      height: 90px;
      background: rgba(255,255,255,0.22);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      font-size: 2.75rem;
      border: 2px solid rgba(255,255,255,0.35);
      position: relative;
      z-index: 1;
      backdrop-filter: blur(12px);
    }
    .verify-header h2 {
      margin: 0;
      font-weight: 800;
      font-size: 1.9rem;
      position: relative;
      z-index: 1;
      letter-spacing: -0.03em;
    }
    .verify-header p {
      margin: 0.5rem 0 0 0;
      opacity: 0.95;
      position: relative;
      z-index: 1;
      font-weight: 500;
    }
    .verify-body { padding: 2.25rem; }
    .student-info {
      text-align: center;
      margin-bottom: 2rem;
      padding-bottom: 2rem;
      border-bottom: 1px solid var(--border);
    }
    .student-avatar {
      width: 72px;
      height: 72px;
      background: var(--gradient-primary);
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.75rem;
      font-weight: 800;
      color: white;
      margin: 0 auto 1rem;
      box-shadow: var(--shadow-glow-primary);
    }
    .student-info h3 {
      margin: 0.5rem 0 0.5rem 0;
      font-weight: 800;
      color: #ffffff;
      font-size: 1.65rem;
      letter-spacing: -0.02em;
    }
    .student-info .matric {
      color: var(--gray);
      font-size: 0.95rem;
    }
    .student-info .matric strong { color: var(--primary-light); }
    .verify-footer {
      padding: 1.25rem 2.25rem;
      background: rgba(0,0,0,0.25);
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
          <div class="student-avatar">
            <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
          </div>
          <h3><?= htmlspecialchars($student['full_name']) ?></h3>
          <p class="matric">
            Matric No: <strong><?= htmlspecialchars($student['matric_no'] ?? '-') ?></strong>
            &nbsp;·&nbsp;
            User ID: <strong>#<?= htmlspecialchars($student['user_id']) ?></strong>
          </p>
        </div>

        <h5 style="color:#ffffff;margin-bottom:1rem;font-weight:700;">
          <i class="bi bi-book-fill" style="color:var(--primary-light);"></i>
          Registered Subjects (<?= count($subjects) ?>)
        </h5>

        <?php if (empty($subjects)): ?>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill"></i> No subjects registered.
          </div>
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
        <i class="bi bi-shield-check" style="color:var(--primary-light);"></i>
        ExamSys &copy; <?= date('Y') ?> — Verified at <?= date('Y-m-d H:i:s') ?>
      </div>
    <?php endif; ?>

  </div>
</div>
</body>
</html>