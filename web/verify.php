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
  <title>Student Verification · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: #f0f2f5;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      color: #333;
      min-height: 100vh;
      padding: 1rem;
      -webkit-font-smoothing: antialiased;
    }

    .verify-wrapper {
      max-width: 720px;
      margin: 2rem auto;
    }

    .verify-card {
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      border: 1px solid #e0e0e0;
    }

    /* ============ HEADER HIJAU ============ */
    .verify-header {
      background: #1a7f4e;
      color: white;
      padding: 1.5rem 1.5rem;
      text-align: center;
    }
    .verify-header .title {
      font-size: 1.15rem;
      font-weight: 700;
      margin-bottom: 0.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    .verify-header .subtitle {
      font-size: 0.75rem;
      opacity: 0.9;
    }
    .verify-header.error {
      background: #dc3545;
    }

    /* ============ BODY ============ */
    .verify-body {
      padding: 2rem 1.5rem;
    }

    /* ============ SEAL BULAT ============ */
    .verify-seal-wrap {
      text-align: center;
      margin-bottom: 1.25rem;
    }
    .verify-seal {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: #1a7f4e;
      color: white;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      box-shadow: 0 4px 12px rgba(26, 127, 78, 0.3);
    }
    .verify-seal.error {
      background: #dc3545;
      box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    /* ============ STUDENT INFO ============ */
    .student-block {
      text-align: center;
      margin-bottom: 2rem;
    }
    .student-block h2 {
      font-size: 1.35rem;
      font-weight: 600;
      color: #1a1a1a;
      margin-bottom: 0.35rem;
    }
    .student-block .meta {
      font-size: 0.85rem;
      color: #6b7280;
      line-height: 1.6;
    }
    .student-block .meta strong {
      color: #1a7f4e;
      font-weight: 700;
    }

    /* ============ SUBJECTS LABEL ============ */
    .section-title {
      font-size: 0.8rem;
      font-weight: 700;
      color: #1a7f4e;
      margin-bottom: 0.75rem;
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }

    /* ============ TABLE ============ */
    .verify-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      overflow: hidden;
      margin-bottom: 1.5rem;
    }
    .verify-table thead {
      background: #f8f9fa;
    }
    .verify-table thead th {
      padding: 0.7rem 0.9rem;
      font-size: 0.75rem;
      font-weight: 700;
      color: #1a1a1a;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }
    .verify-table tbody td {
      padding: 0.9rem 0.9rem;
      border-bottom: 1px solid #f0f0f0;
      font-size: 0.85rem;
      color: #333;
      vertical-align: top;
    }
    .verify-table tbody tr:last-child td { border-bottom: none; }
    .verify-table .course-code {
      font-weight: 700;
      color: #1a1a1a;
      font-size: 0.88rem;
    }
    .verify-table .course-title {
      font-size: 0.72rem;
      color: #9ca3af;
      margin-top: 0.15rem;
      line-height: 1.4;
    }
    .verify-table .subject-name {
      font-weight: 500;
      color: #333;
      font-size: 0.85rem;
    }

    /* ============ SUCCESS ALERT ============ */
    .verify-alert {
      padding: 0.9rem 1rem;
      background: #d4edda;
      border: 1px solid #c3e6cb;
      border-radius: 6px;
      color: #155724;
      font-size: 0.82rem;
      display: flex;
      align-items: flex-start;
      gap: 0.5rem;
      line-height: 1.5;
    }
    .verify-alert.success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
    .verify-alert.danger  { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .verify-alert .icon { flex-shrink: 0; font-weight: 700; }

    /* ============ FOOTER ============ */
    .verify-footer {
      padding: 1rem 1.5rem;
      background: #f8f9fa;
      border-top: 1px solid #e0e0e0;
      text-align: center;
      font-size: 0.72rem;
      color: #9ca3af;
    }

    @media (max-width: 640px) {
      .verify-wrapper { margin: 1rem auto; }
      .verify-body { padding: 1.5rem 1rem; }
      .verify-table .course-title { display: none; }
    }
  </style>
</head>
<body>
<div class="verify-wrapper">
  <div class="verify-card">

    <!-- ============ HEADER ============ -->
    <div class="verify-header <?= $error ? 'error' : '' ?>">
      <div class="title">
        <span>🎓</span> Exam Verification
      </div>
      <div class="subtitle">Scan result — Official Student Record</div>
    </div>

    <!-- ============ BODY ============ -->
    <div class="verify-body">

      <!-- Seal -->
      <div class="verify-seal-wrap">
        <div class="verify-seal <?= $error ? 'error' : '' ?>">
          <?= $error ? '✕' : '✓' ?>
        </div>
      </div>

      <?php if ($error): ?>

        <!-- ERROR STATE -->
        <div class="student-block">
          <h2 style="color:#dc3545;">❌ Verification Failed</h2>
        </div>
        <div class="verify-alert danger">
          <span class="icon">⚠</span>
          <div><?= htmlspecialchars($error) ?></div>
        </div>

      <?php else: ?>

        <!-- STUDENT INFO -->
        <div class="student-block">
          <h2><?= htmlspecialchars($student['full_name']) ?></h2>
          <div class="meta">
            Matric No: <strong><?= htmlspecialchars($student['matric_no'] ?? '—') ?></strong><br>
            User ID: <?= htmlspecialchars($student['user_id']) ?>
          </div>
        </div>

        <!-- SUBJECTS -->
        <div class="section-title">
          <span>📚</span> Registered Subjects · <?= count($subjects) ?>
        </div>

        <?php if (empty($subjects)): ?>
          <div class="verify-alert danger">
            <span class="icon">⚠</span>
            <div>No subjects registered.</div>
          </div>
        <?php else: ?>
          <table class="verify-table">
            <thead>
              <tr>
                <th style="width:45%;">Course</th>
                <th>Subject</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($subjects as $s): ?>
              <tr>
                <td>
                  <div class="course-code"><?= htmlspecialchars($s['course_code']) ?></div>
                  <div class="course-title"><?= htmlspecialchars($s['course_title']) ?></div>
                </td>
                <td>
                  <div class="subject-name"><?= htmlspecialchars($s['subject_name']) ?></div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <!-- SUCCESS ALERT -->
        <div class="verify-alert success">
          <span class="icon">✓</span>
          <div>
            <strong>Verified.</strong> This student is officially registered in
            <?= count($subjects) ?> subject(s) for the upcoming examination.
          </div>
        </div>

      <?php endif; ?>

    </div>

    <!-- ============ FOOTER ============ -->
    <div class="verify-footer">
      ExamSys © <?= date('Y') ?> — Verified at <?= date('Y-m-d H:i:s') ?>
    </div>

  </div>
</div>
</body>
</html>