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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verification · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #0f0f1e;
      font-family: 'Inter', -apple-system, sans-serif;
      color: white;
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
      padding: 2rem 1rem;
    }
    .vf-card {
      width: 100%; max-width: 640px;
      background: linear-gradient(180deg, #1a1a2e 0%, #16162a 100%);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 30px 80px rgba(0,0,0,0.5);
    }
    /* HERO */
    .vf-hero {
      padding: 2.5rem 2rem 1.75rem;
      text-align: center;
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
      position: relative;
      overflow: hidden;
    }
    .vf-hero::before {
      content: '';
      position: absolute; top: -50%; right: -20%;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(255,255,255,0.2), transparent 65%);
      border-radius: 50%;
    }
    .vf-hero.error { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); }
    .vf-seal {
      width: 80px; height: 80px;
      border-radius: 50%;
      background: rgba(255,255,255,0.2);
      border: 3px solid rgba(255,255,255,0.5);
      display: flex; align-items: center; justify-content: center;
      font-size: 2.2rem;
      margin: 0 auto 1rem;
      position: relative; z-index: 1;
      backdrop-filter: blur(10px);
    }
    .vf-hero h1 {
      font-size: 1.6rem;
      font-weight: 800;
      position: relative; z-index: 1;
      margin-bottom: 0.25rem;
    }
    .vf-hero p {
      font-size: 0.85rem;
      opacity: 0.85;
      position: relative; z-index: 1;
    }
    /* BODY */
    .vf-body { padding: 2rem; }
    .vf-student {
      text-align: center;
      padding: 1.5rem;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 16px;
      margin-bottom: 1.5rem;
    }
    .vf-student .name {
      font-size: 1.4rem;
      font-weight: 800;
      margin-bottom: 0.5rem;
      background: linear-gradient(135deg, #a5b4fc, #f0abfc);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .vf-student .meta {
      display: flex; justify-content: center; gap: 1.5rem; flex-wrap: wrap;
      font-size: 0.82rem;
      color: rgba(255,255,255,0.6);
    }
    .vf-student .meta strong { color: #a5b4fc; font-family: 'SF Mono', monospace; }
    .vf-section {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.15em;
      color: #a5b4fc;
      font-weight: 700;
      margin-bottom: 0.85rem;
      display: flex; align-items: center; gap: 0.5rem;
    }
    .vf-list { display: flex; flex-direction: column; gap: 0.6rem; margin-bottom: 1.5rem; }
    .vf-item {
      display: flex; align-items: center; gap: 1rem;
      padding: 1rem 1.15rem;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      transition: all .25s;
    }
    .vf-item:hover {
      background: rgba(255,255,255,0.08);
      border-color: rgba(99,102,241,0.4);
      transform: translateX(4px);
    }
    .vf-item .num {
      width: 34px; height: 34px;
      border-radius: 50%;
      background: linear-gradient(135deg, #6366f1, #a855f7);
      display: flex; align-items: center; justify-content: center;
      font-weight: 800;
      font-size: 0.8rem;
      flex-shrink: 0;
    }
    .vf-item .info { flex: 1; min-width: 0; }
    .vf-item .subj { font-weight: 700; font-size: 0.92rem; margin-bottom: 0.15rem; }
    .vf-item .code {
      font-family: 'SF Mono', monospace;
      font-size: 0.72rem;
      color: #a5b4fc;
    }
    .vf-alert {
      padding: 1rem 1.15rem;
      border-radius: 12px;
      font-size: 0.85rem;
      display: flex; align-items: flex-start; gap: 0.65rem;
      background: rgba(16, 185, 129, 0.15);
      border: 1px solid rgba(16, 185, 129, 0.3);
      color: #6ee7b7;
      line-height: 1.5;
    }
    .vf-alert.danger {
      background: rgba(239, 68, 68, 0.15);
      border-color: rgba(239, 68, 68, 0.3);
      color: #fca5a5;
    }
    .vf-foot {
      padding: 1rem 2rem;
      background: rgba(0,0,0,0.3);
      border-top: 1px solid rgba(255,255,255,0.06);
      text-align: center;
      font-size: 0.72rem;
      color: rgba(255,255,255,0.4);
      font-family: 'SF Mono', monospace;
    }
  </style>
</head>
<body>
<div class="vf-card">

  <div class="vf-hero <?= $error ? 'error' : '' ?>">
    <div class="vf-seal"><?= $error ? '✕' : '✓' ?></div>
    <h1><?= $error ? 'Verification Failed' : 'Verified Student' ?></h1>
    <p><?= $error ? htmlspecialchars($error) : 'Official examination registration confirmed' ?></p>
  </div>

  <?php if (!$error): ?>
  <div class="vf-body">

    <div class="vf-student">
      <div class="name"><?= htmlspecialchars($student['full_name']) ?></div>
      <div class="meta">
        <span>Matric · <strong><?= htmlspecialchars($student['matric_no'] ?? '—') ?></strong></span>
        <span>ID · <strong>#<?= htmlspecialchars($student['user_id']) ?></strong></span>
      </div>
    </div>

    <div class="vf-section">
      <i class="bi bi-bookmark-star-fill"></i> Registered Subjects · <?= count($subjects) ?>
    </div>

    <?php if (empty($subjects)): ?>
      <div class="vf-alert danger"><i class="bi bi-exclamation-triangle-fill"></i><div>No subjects registered.</div></div>
    <?php else: ?>
      <div class="vf-list">
        <?php foreach ($subjects as $i => $s): ?>
          <div class="vf-item">
            <div class="num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></div>
            <div class="info">
              <div class="subj"><?= htmlspecialchars($s['subject_name']) ?></div>
              <div class="code"><?= htmlspecialchars($s['course_code']) ?> · <?= htmlspecialchars($s['course_title']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="vf-alert">
      <i class="bi bi-patch-check-fill"></i>
      <div><strong>Verified.</strong> This student is officially registered in <?= count($subjects) ?> subject(s) for the upcoming examination.</div>
    </div>

  </div>
  <?php else: ?>
  <div class="vf-body">
    <div class="vf-alert danger">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <div><?= htmlspecialchars($error) ?></div>
    </div>
  </div>
  <?php endif; ?>

  <div class="vf-foot">
    ExamSys · Verified at <?= date('Y-m-d H:i:s') ?>
  </div>

</div>
</body>
</html>