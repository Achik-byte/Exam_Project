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
    /* === QR TICKET STYLE === */
    .qr-ticket {
      background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
      border-radius: var(--r-lg);
      padding: 2rem;
      position: relative;
      overflow: hidden;
      margin-bottom: 2rem;
      box-shadow: 0 20px 60px rgba(99, 102, 241, 0.4);
      display: grid;
      grid-template-columns: 180px 1fr;
      gap: 2rem;
      align-items: center;
      color: white;
    }
    .qr-ticket::before {
      content: '';
      position: absolute; top: -50%; right: -20%;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(255,255,255,0.2), transparent 65%);
      border-radius: 50%;
    }
    .qr-ticket::after {
      content: '';
      position: absolute;
      left: 220px; top: 0; bottom: 0;
      border-left: 2px dashed rgba(255,255,255,0.3);
    }
    .qr-ticket > * { position: relative; z-index: 1; }
    .qr-ticket .qr-box {
      background: white;
      padding: 10px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .qr-ticket .qr-box img { width: 160px; height: 160px; display: block; }
    .qr-ticket .info h3 {
      font-size: 1.4rem;
      color: white;
      margin-bottom: 0.5rem;
    }
    .qr-ticket .info p {
      color: rgba(255,255,255,0.85);
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }
    .qr-ticket .info .url {
      background: rgba(255,255,255,0.18);
      border: 1px solid rgba(255,255,255,0.25);
      border-radius: 8px;
      padding: 0.65rem 0.9rem;
      font-family: 'SF Mono', monospace;
      font-size: 0.72rem;
      word-break: break-all;
      margin-bottom: 1rem;
    }

    /* === QR EMPTY === */
    .qr-empty {
      background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(168,85,247,0.15));
      border: 1px dashed var(--glass-line-2);
      border-radius: var(--r-lg);
      padding: 2rem;
      display: flex;
      align-items: center;
      gap: 1.5rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }
    .qr-empty .icon {
      width: 60px; height: 60px;
      border-radius: 14px;
      background: rgba(99,102,241,0.2);
      border: 1px solid rgba(99,102,241,0.3);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem;
      flex-shrink: 0;
    }
    .qr-empty h3 { color: white; margin: 0 0 0.25rem; font-size: 1.15rem; }
    .qr-empty p { color: var(--ink-3); margin: 0; font-size: 0.85rem; }

    /* === SUBJECT ROW (Timetable sheet) === */
    .subject-row {
      display: grid;
      grid-template-columns: 80px 1fr 150px 130px;
      gap: 1.5rem;
      align-items: center;
      background: var(--glass);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-md);
      padding: 1.25rem 1.5rem;
      margin-bottom: 0.75rem;
      transition: all .25s;
      position: relative;
      overflow: hidden;
    }
    .subject-row::before {
      content: '';
      position: absolute;
      left: 0; top: 0; bottom: 0;
      width: 4px;
      background: linear-gradient(180deg, var(--aurora-1), var(--aurora-2));
    }
    .subject-row.green::before  { background: linear-gradient(180deg, #10b981, #059669); }
    .subject-row.amber::before  { background: linear-gradient(180deg, #f59e0b, #d97706); }
    .subject-row.purple::before { background: linear-gradient(180deg, #a855f7, #7e22ce); }
    .subject-row.pink::before   { background: linear-gradient(180deg, #ec4899, #be185d); }
    .subject-row:hover {
      border-color: var(--glass-line-2);
      background: var(--glass-2);
      transform: translateX(6px);
      box-shadow: 0 8px 32px rgba(99, 102, 241, 0.15);
    }
    .subject-row .num {
      font-family: 'SF Mono', monospace;
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--ink-3);
      text-align: center;
      line-height: 1;
    }
    .subject-row .details .code {
      display: inline-block;
      font-family: 'SF Mono', monospace;
      font-size: 0.72rem;
      font-weight: 700;
      padding: 0.25rem 0.6rem;
      border-radius: 6px;
      background: rgba(99,102,241,0.15);
      color: #a5b4fc;
      margin-bottom: 0.5rem;
    }
    .subject-row.green .details .code  { background: rgba(16,185,129,0.15); color: #6ee7b7; }
    .subject-row.amber .details .code  { background: rgba(245,158,11,0.15); color: #fcd34d; }
    .subject-row.purple .details .code { background: rgba(168,85,247,0.15); color: #d8b4fe; }
    .subject-row.pink .details .code   { background: rgba(236,72,153,0.15); color: #f9a8d4; }
    .subject-row .details .name {
      font-family: 'Outfit', sans-serif;
      font-size: 1.05rem;
      font-weight: 700;
      color: white;
      line-height: 1.3;
      margin-bottom: 0.25rem;
    }
    .subject-row .details .course {
      font-size: 0.78rem;
      color: var(--ink-3);
    }
    .subject-row .grade-box {
      text-align: center;
    }
    .subject-row .grade-box .lbl {
      font-size: 0.65rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--ink-3);
      margin-bottom: 0.35rem;
    }
    .subject-row .grade-box .grade {
      font-family: 'Outfit', sans-serif;
      font-size: 1.5rem;
      font-weight: 800;
      line-height: 1;
    }
    .subject-row .grade-box .grade.a { color: #6ee7b7; }
    .subject-row .grade-box .grade.b { color: #a5b4fc; }
    .subject-row .grade-box .grade.c { color: #fcd34d; }
    .subject-row .grade-box .grade.pending { color: var(--ink-3); font-size: 0.9rem; font-weight: 600; }

    @media (max-width: 720px) {
      .qr-ticket { grid-template-columns: 1fr; }
      .qr-ticket::after { display: none; }
      .subject-row { grid-template-columns: 60px 1fr; }
      .subject-row .grade-box { grid-column: span 2; text-align: left; }
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

  <!-- === QR SECTION === -->
  <?php if (!$qrCode): ?>
    <div class="qr-empty">
      <div class="icon">🎫</div>
      <div style="flex:1;min-width:200px;">
        <h3>Exam Slip QR Code</h3>
        <p>Generate a secure QR to present at the exam hall for verification.</p>
      </div>
      <a href="my_subjects.php?generate_qr=1" class="btn-glass primary">
        <i class="bi bi-qr-code"></i> Generate QR
      </a>
    </div>
  <?php else: ?>
    <div class="qr-ticket">
      <div class="qr-box">
        <img src="<?= htmlspecialchars($qrCode) ?>" alt="QR">
      </div>
      <div class="info">
        <h3><i class="bi bi-patch-check-fill"></i> Your Verification QR</h3>
        <p>Show this to the invigilator. Scanning it reveals your registered subjects.</p>
        <div class="url">
          <strong style="opacity:0.7;font-size:0.65rem;letter-spacing:0.12em;display:block;margin-bottom:0.3rem;">QR ENCODES →</strong>
          <?= htmlspecialchars($verifyUrl) ?>
        </div>
        <div style="display:flex;gap:1.25rem;flex-wrap:wrap;font-size:0.85rem;color:rgba(255,255,255,0.9);margin-bottom:1rem;">
          <span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($_SESSION['full_name']) ?></span>
          <span><i class="bi bi-cloud-fill"></i> qrserver.com</span>
        </div>
        <a href="my_subjects.php" class="btn-glass" style="background:rgba(255,255,255,0.15);color:white;border-color:rgba(255,255,255,0.3);">
          <i class="bi bi-x-lg"></i> Close
        </a>
      </div>
    </div>
  <?php endif; ?>

  <!-- === SUBJECT LIST (Timetable style) === -->
  <h3 style="font-size:1.2rem;margin:2rem 0 1rem;color:white;">
    <i class="bi bi-list-stars" style="color:#a5b4fc;"></i> Enrolled subjects · <?= count($subjects) ?>
  </h3>

  <?php if (empty($subjects)): ?>
    <div class="empty-glass">
      <div class="icon"><i class="bi bi-journal-x"></i></div>
      <h4>No enrollments</h4>
      <p>You are not enrolled in any subjects yet.</p>
    </div>
  <?php else: ?>
    <?php foreach ($subjects as $i => $s): $tone = $tones[$i % count($tones)];
      $grade = $s['grade'] ?? null;
      $gradeClass = 'pending';
      if ($grade) {
        $f = strtoupper($grade[0]);
        if ($f === 'A') $gradeClass = 'a';
        elseif ($f === 'B') $gradeClass = 'b';
        elseif ($f === 'C') $gradeClass = 'c';
        else $gradeClass = 'c';
      }
    ?>
      <div class="subject-row <?= $tone ?>">
        <div class="num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></div>
        <div class="details">
          <span class="code"><?= htmlspecialchars($s['course_code']) ?></span>
          <div class="name"><?= htmlspecialchars($s['subject_name']) ?></div>
          <div class="course"><?= htmlspecialchars($s['course_title']) ?></div>
        </div>
        <div style="text-align:center;">
          <?php if ($grade): ?>
            <span class="pill-glass green"><i class="bi bi-check-circle-fill"></i> Graded</span>
          <?php else: ?>
            <span class="pill-glass amber"><i class="bi bi-hourglass-split"></i> Pending</span>
          <?php endif; ?>
        </div>
        <div class="grade-box">
          <div class="lbl">Grade</div>
          <?php if ($grade): ?>
            <div class="grade <?= $gradeClass ?>"><?= htmlspecialchars($grade) ?></div>
          <?php else: ?>
            <div class="grade pending">—</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>