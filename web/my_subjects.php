<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'student') { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';
$response = apiRequest('/results', 'GET', null, $_SESSION['token']);
$subjects = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];
$qrCode = null; $qrError = ""; $verifyUrl = "";

if (isset($_GET['generate_qr'])) {
    $verifyUrl = PUBLIC_BASE_URL . "/verify.php?id=" . $_SESSION['user_id'];
    $qrResponse = apiRequest('/generate-qr?data=' . urlencode($verifyUrl), 'GET', null, $_SESSION['token']);
    if ($qrResponse['status_code'] === 200) $qrCode = $qrResponse['body']['data']['qr_url'] ?? null;
    else $qrError = $qrResponse['body']['message'] ?? 'Failed to generate QR';
}

$tones = ['', 'green', 'amber', 'purple', 'pink'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Subjects · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
  <style>
    .qr-banner {
      display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap;
      padding: 2rem;
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2), var(--aurora-3));
      border-radius: var(--r-lg);
      box-shadow: 0 20px 60px rgba(99, 102, 241, 0.4);
      position: relative;
      overflow: hidden;
      margin-bottom: 1.75rem;
    }
    .qr-banner::before {
      content: '';
      position: absolute; top: -50%; right: -20%;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(255,255,255,0.2), transparent 65%);
      border-radius: 50%;
    }
    .qr-banner > * { position: relative; z-index: 1; }
    .qr-banner .icon-badge {
      width: 70px; height: 70px;
      border-radius: 18px;
      background: rgba(255, 255, 255, 0.18);
      border: 1px solid rgba(255, 255, 255, 0.25);
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem;
      backdrop-filter: blur(10px);
      flex-shrink: 0;
    }
    .qr-banner h3 {
      color: white;
      margin: 0 0 0.35rem;
      font-size: 1.5rem;
    }
    .qr-banner p {
      color: rgba(255, 255, 255, 0.9);
      margin: 0;
      font-size: 0.9rem;
    }
    .qr-banner .btn-gen {
      background: white;
      color: var(--aurora-1);
      font-weight: 700;
      padding: 0.85rem 1.5rem;
      border-radius: var(--r-pill);
      border: none;
      text-decoration: none;
      display: inline-flex; align-items: center; gap: 0.5rem;
      transition: all .25s;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }
    .qr-banner .btn-gen:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
      color: var(--aurora-1);
    }

    .qr-display {
      display: grid;
      grid-template-columns: auto 1fr;
      gap: 2rem;
      align-items: center;
      padding: 2rem;
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2), var(--aurora-3));
      border-radius: var(--r-lg);
      box-shadow: 0 20px 60px rgba(99, 102, 241, 0.4);
      position: relative;
      overflow: hidden;
      margin-bottom: 1.75rem;
      color: white;
    }
    .qr-display::before {
      content: '';
      position: absolute; top: -50%; right: -20%;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 65%);
      border-radius: 50%;
    }
    .qr-display > * { position: relative; z-index: 1; }
    .qr-display .qr-image {
      background: white;
      padding: 14px;
      border-radius: var(--r-md);
      box-shadow: 0 20px 50px -20px rgba(0,0,0,0.5);
      max-width: 220px;
    }
    .qr-display h3 {
      color: white;
      margin-bottom: 0.5rem;
      font-size: 1.4rem;
    }
    .qr-display .desc {
      color: rgba(255,255,255,0.9);
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }
    .qr-display .code-box {
      background: rgba(255, 255, 255, 0.18);
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: var(--r-sm);
      padding: 0.75rem 1rem;
      font-family: 'SF Mono', monospace;
      font-size: 0.78rem;
      color: white;
      word-break: break-all;
      margin-bottom: 1rem;
    }
    .qr-display .info-row {
      display: flex; gap: 1.25rem; flex-wrap: wrap;
      font-size: 0.85rem;
      color: rgba(255,255,255,0.9);
      margin-bottom: 1rem;
    }

    .subject-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.15rem;
    }
    .subject-tile {
      background: var(--glass);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-lg);
      padding: 1.5rem;
      transition: all .3s;
      position: relative;
      overflow: hidden;
    }
    .subject-tile::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 4px; height: 100%;
      background: linear-gradient(180deg, var(--aurora-1), var(--aurora-2));
    }
    .subject-tile.green::before  { background: linear-gradient(180deg, #10b981, #059669); }
    .subject-tile.amber::before  { background: linear-gradient(180deg, #f59e0b, #d97706); }
    .subject-tile.purple::before { background: linear-gradient(180deg, #a855f7, #7e22ce); }
    .subject-tile.pink::before   { background: linear-gradient(180deg, #ec4899, #be185d); }
    .subject-tile:hover {
      transform: translateY(-4px);
      border-color: var(--glass-line-2);
      box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2);
    }
    .subject-tile .code {
      display: inline-block;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      padding: 0.35rem 0.75rem;
      border-radius: var(--r-pill);
      background: rgba(99, 102, 241, 0.15);
      color: #a5b4fc;
      border: 1px solid rgba(99, 102, 241, 0.3);
      margin-bottom: 0.85rem;
    }
    .subject-tile.green .code  { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; border-color: rgba(16, 185, 129, 0.3); }
    .subject-tile.amber .code  { background: rgba(245, 158, 11, 0.15); color: #fcd34d; border-color: rgba(245, 158, 11, 0.3); }
    .subject-tile.purple .code { background: rgba(168, 85, 247, 0.15); color: #d8b4fe; border-color: rgba(168, 85, 247, 0.3); }
    .subject-tile.pink .code   { background: rgba(236, 72, 153, 0.15); color: #f9a8d4; border-color: rgba(236, 72, 153, 0.3); }
    .subject-tile .name {
      font-family: 'Outfit', sans-serif;
      font-size: 1.1rem;
      font-weight: 700;
      color: white;
      margin-bottom: 0.75rem;
      line-height: 1.3;
    }
    .subject-tile .course-line {
      font-size: 0.82rem;
      color: var(--ink-3);
      margin-bottom: 1rem;
    }
    .subject-tile .meta { display: flex; gap: 0.5rem; flex-wrap: wrap; }

    @media (max-width: 640px) {
      .qr-display { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ Enrollments</span>
      <h1>My <em>subjects</em></h1>
      <p class="sub">Subjects you are registered for, and your exam verification QR.</p>
    </div>
    <span class="pill-glass blue" style="padding:0.6rem 1rem;font-size:0.85rem;">
      <i class="bi bi-journal-bookmark-fill"></i> <?= count($subjects) ?> subjects
    </span>
  </div>

  <?php if (!empty($qrError)): ?>
    <div class="alert-glass danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($qrError) ?></div></div>
  <?php endif; ?>

  <!-- QR BANNER -->
  <?php if (!$qrCode): ?>
    <div class="qr-banner">
      <div class="icon-badge">🎫</div>
      <div style="flex:1;min-width:220px;">
        <h3>Exam Slip QR Code</h3>
        <p>Generate a secure QR to present at the exam hall for verification.</p>
      </div>
      <a href="my_subjects.php?generate_qr=1" class="btn-gen">
        <i class="bi bi-qr-code"></i> Generate QR
      </a>
    </div>
  <?php else: ?>
    <div class="qr-display">
      <div>
        <img src="<?= htmlspecialchars($qrCode) ?>" alt="QR" class="qr-image">
      </div>
      <div>
        <h3><i class="bi bi-patch-check-fill"></i> Your Verification QR</h3>
        <p class="desc">Show this to the invigilator. Scanning it reveals your registered subjects for official verification.</p>
        <div class="code-box">
          <strong style="opacity:0.7;font-size:0.68rem;letter-spacing:0.12em;display:block;margin-bottom:0.35rem;">QR ENCODES →</strong>
          <?= htmlspecialchars($verifyUrl) ?>
        </div>
        <div class="info-row">
          <span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($_SESSION['full_name']) ?></span>
          <span><i class="bi bi-cloud-fill"></i> Powered by qrserver.com</span>
        </div>
        <a href="my_subjects.php" class="btn-glass" style="background:rgba(255,255,255,0.15);color:white;border-color:rgba(255,255,255,0.3);">
          <i class="bi bi-x-lg"></i> Close
        </a>
      </div>
    </div>
  <?php endif; ?>

  <!-- SUBJECT LIST -->
  <h3 style="font-size:1.3rem;margin:2rem 0 1rem;color:white;">
    <i class="bi bi-list-stars" style="color:#a5b4fc;"></i> Enrolled subjects
  </h3>

  <?php if (empty($subjects)): ?>
    <div class="empty-glass">
      <div class="icon"><i class="bi bi-journal-x"></i></div>
      <h4>No enrollments</h4>
      <p>You are not enrolled in any subjects yet.</p>
    </div>
  <?php else: ?>
    <div class="subject-grid">
      <?php foreach ($subjects as $i => $s): $tone = $tones[$i % count($tones)]; ?>
      <div class="subject-tile <?= $tone ?>">
        <span class="code"><?= htmlspecialchars($s['course_code']) ?></span>
        <div class="name"><?= htmlspecialchars($s['subject_name']) ?></div>
        <div class="course-line"><?= htmlspecialchars($s['course_title']) ?></div>
        <div class="meta">
          <?php if (!empty($s['grade'])): ?>
            <span class="pill-glass green"><i class="bi bi-check-circle-fill"></i> Graded <?= htmlspecialchars($s['grade']) ?></span>
          <?php else: ?>
            <span class="pill-glass amber"><i class="bi bi-hourglass-split"></i> Pending</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>