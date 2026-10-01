<?php
require_once __DIR__ . '/config.php';
$id = $_GET['id'] ?? null;
$student = null; $subjects = []; $error = "";
if (!$id) $error = "No student ID provided.";
else {
    $response = apiRequest('/verify/' . $id, 'GET');
    if ($response['status_code'] === 200) {
        $student  = $response['body']['data']['student'];
        $subjects = $response['body']['data']['subjects'];
    } else $error = $response['body']['message'] ?? 'Verification failed';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verification · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="verify-lum-shell">
  <div class="verify-lum">
    <div class="head <?= $error ? 'error' : '' ?>">
      <div class="seal <?= $error ? 'error' : '' ?>"><?= $error ? '✕' : '✓' ?></div>
      <?php if ($error): ?>
        <h2>Verification Failed</h2>
        <p><?= htmlspecialchars($error) ?></p>
      <?php else: ?>
        <h2>Student Verified</h2>
        <p>Official examination registration confirmed</p>
      <?php endif; ?>
    </div>

    <div class="body">
      <?php if (!$error): ?>
        <div style="text-align:center;margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:1px solid var(--line);">
          <h3 style="font-size:1.6rem;margin-bottom:0.35rem;"><?= htmlspecialchars($student['full_name']) ?></h3>
          <p style="color:var(--ink-3);margin:0;">
            Matric No <strong class="mono" style="color:var(--indigo)"><?= htmlspecialchars($student['matric_no'] ?? '—') ?></strong>
            · User ID <span class="mono">#<?= htmlspecialchars($student['user_id']) ?></span>
          </p>
        </div>

        <h4 style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--ink-3);margin-bottom:1rem;">
          <i class="bi bi-bookmark-star-fill" style="color:var(--indigo)"></i> Registered Subjects · <?= count($subjects) ?>
        </h4>

        <?php if (empty($subjects)): ?>
          <div class="alert-lum warning"><i class="bi bi-exclamation-triangle-fill"></i><div>No subjects registered.</div></div>
        <?php else: ?>
          <div class="table-lum-wrap">
            <table class="table-lum">
              <thead><tr><th>Course</th><th>Subject</th></tr></thead>
              <tbody>
                <?php foreach ($subjects as $s): ?>
                <tr>
                  <td>
                    <div class="primary mono" style="color:var(--indigo)"><?= htmlspecialchars($s['course_code']) ?></div>
                    <div class="muted"><?= htmlspecialchars($s['course_title']) ?></div>
                  </td>
                  <td><?= htmlspecialchars($s['subject_name']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <div class="alert-lum success mt-3">
          <i class="bi bi-patch-check-fill"></i>
          <div><strong>Verified.</strong> This student is officially registered in <?= count($subjects) ?> subject(s).</div>
        </div>
      <?php endif; ?>
    </div>

    <div class="foot">
      ExamSys · Verified at <?= date('Y-m-d H:i:s') ?>
    </div>
  </div>
</div>
</body>
</html>