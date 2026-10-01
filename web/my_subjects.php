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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Subjects · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-aurora">

  <div class="page-head">
    <div>
      <div class="eyebrow">◆ Enrolled Courses</div>
      <h1>My Subjects</h1>
      <p class="subtitle">Subjects you are registered for and your exam verification QR.</p>
    </div>
    <span class="chip violet"><i class="bi bi-journal-bookmark-fill"></i> <?= count($subjects) ?> subjects</span>
  </div>

  <?php if (!empty($qrError)): ?>
    <div class="alert-neo danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($qrError) ?></div></div>
  <?php endif; ?>

  <div class="qr-hero">
    <?php if (!$qrCode): ?>
      <div style="display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap;">
        <div style="width:70px;height:70px;border-radius:20px;background:var(--grad-violet);display:flex;align-items:center;justify-content:center;font-size:2rem;box-shadow:var(--glow-violet);">🎫</div>
        <div style="flex:1;min-width:220px;">
          <h3 style="margin:0 0 0.35rem;font-weight:700;">Exam Slip QR Code</h3>
          <p style="margin:0;color:var(--text-2);font-size:0.9rem;">Generate a secure QR to present at the exam hall for verification.</p>
        </div>
        <a href="my_subjects.php?generate_qr=1" class="btn-neo violet"><i class="bi bi-qr-code"></i> Generate QR</a>
      </div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:auto 1fr;gap:2rem;align-items:center;" class="qr-flex">
        <div style="text-align:center;">
          <img src="<?= htmlspecialchars($qrCode) ?>" alt="QR" class="qr-img">
        </div>
        <div>
          <h3 style="margin:0 0 0.5rem;font-weight:700;"><i class="bi bi-patch-check-fill" style="color:var(--mint)"></i> Your Verification QR</h3>
          <p style="color:var(--text-2);margin-bottom:1rem;font-size:0.92rem;">
            Show this to the invigilator. Scanning it reveals your registered subjects for official verification.
          </p>
          <div class="code-block mb-2">
            <strong style="color:var(--text-3);font-size:0.72rem;letter-spacing:0.1em;">QR ENCODES →</strong><br>
            <?= htmlspecialchars($verifyUrl) ?>
          </div>
          <div style="display:flex;gap:1.5rem;flex-wrap:wrap;font-size:0.85rem;color:var(--text-2);">
            <span><i class="bi bi-person-fill" style="color:var(--violet-2)"></i> <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            <span><i class="bi bi-cloud-fill" style="color:var(--cyan)"></i> Powered by qrserver.com</span>
          </div>
          <a href="my_subjects.php" class="btn-neo ghost sm mt-3"><i class="bi bi-x-lg"></i> Close</a>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem;"><i class="bi bi-list-stars" style="color:var(--cyan)"></i> Enrolled Subjects</h3>

  <?php if (empty($subjects)): ?>
    <div class="empty-neo">
      <div class="icon"><i class="bi bi-journal-x"></i></div>
      <h4>No enrollments</h4>
      <p>You are not enrolled in any subjects yet.</p>
    </div>
  <?php else: ?>
    <div class="subject-grid">
      <?php foreach ($subjects as $s): ?>
      <div class="subject-card">
        <div class="code"><?= htmlspecialchars($s['course_code']) ?></div>
        <div class="name"><?= htmlspecialchars($s['subject_name']) ?></div>
        <div class="meta">
          <span class="chip gray"><?= htmlspecialchars($s['course_title']) ?></span>
          <?php if (!empty($s['grade'])): ?>
            <span class="chip mint"><i class="bi bi-check-circle-fill"></i> Graded (<?= htmlspecialchars($s['grade']) ?>)</span>
          <?php else: ?>
            <span class="chip amber"><i class="bi bi-hourglass-split"></i> Pending</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<style>@media (max-width:640px){.qr-flex{grid-template-columns:1fr!important;}}</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>