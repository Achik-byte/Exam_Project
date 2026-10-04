<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$response = apiRequest('/results', 'GET', null, $_SESSION['token']);
$subjects = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];

$qrCode = null;
$qrError = "";
$verifyUrl = "";

if (isset($_GET['generate_qr'])) {
    $verifyUrl = PUBLIC_BASE_URL . "/verify.php?id=" . $_SESSION['user_id'];
    $qrResponse = apiRequest('/generate-qr?data=' . urlencode($verifyUrl), 'GET', null, $_SESSION['token']);
    if ($qrResponse['status_code'] === 200) {
        $qrCode = $qrResponse['body']['data']['qr_url'] ?? null;
    } else {
        $qrError = $qrResponse['body']['message'] ?? 'Failed to generate QR';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Subjects - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern">

  <div class="page-header">
    <div>
      <h1>My Subjects</h1>
      <p class="subtitle">Subjects you are registered for, and your exam verification QR.</p>
    </div>
    <span class="badge bg-primary"><i class="bi bi-journal-text"></i> <?= count($subjects) ?> Subjects</span>
  </div>

  <?php if (!empty($qrError)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($qrError) ?></div>
  <?php endif; ?>

  <!-- QR CARD -->
  <div class="stat-card primary" style="padding:2rem;margin-bottom:2rem;">
    <div style="position:relative;z-index:3;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1.5rem;">
      <div>
        <div class="stat-icon" style="margin-bottom:1rem;">
          <i class="bi bi-qr-code"></i>
        </div>
        <h3 style="color:#ffffff;margin:0 0 0.5rem 0;font-weight:800;">Exam Slip QR Code</h3>
        <p style="color:rgba(255,255,255,0.85);margin:0;font-size:0.95rem;">
          Generate a secure QR to present at the exam hall for verification.
        </p>
      </div>
      <?php if (!$qrCode): ?>
        <a href="my_subjects.php?generate_qr=1" class="btn" style="background:white;color:var(--primary);font-weight:800;padding:0.9rem 1.75rem;">
          <i class="bi bi-qr-code"></i> Generate QR
        </a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($qrCode): ?>
    <div class="card-modern mb-4">
      <div class="row align-items-center">
        <div class="col-md-4 text-center">
          <img src="<?= htmlspecialchars($qrCode) ?>" alt="QR" class="img-fluid" style="background:white;padding:14px;border-radius:16px;max-width:240px;box-shadow:var(--shadow-md);">
        </div>
        <div class="col-md-8">
          <h4 style="color:#ffffff;"><i class="bi bi-shield-check"></i> Your Verification QR</h4>
          <p class="text-muted">Show this to the exam invigilator. Scanning it reveals your registered subjects.</p>
          <div class="alert alert-info">
            <strong>QR Contains URL:</strong><br>
            <code style="color:#67e8f9;word-break:break-all;"><?= htmlspecialchars($verifyUrl) ?></code>
          </div>
          <p style="color:#cbd5e1;">
            <strong>Name:</strong> <?= htmlspecialchars($_SESSION['full_name']) ?><br>
            <strong>Generated via:</strong> qrserver.com (Third-Party API)
          </p>
          <a href="my_subjects.php" class="btn btn-secondary btn-sm"><i class="bi bi-x-lg"></i> Close QR</a>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <h5 style="color:#ffffff;margin-bottom:1rem;"><i class="bi bi-list-check"></i> Enrolled Subjects</h5>

  <?php if (empty($subjects)): ?>
    <div class="empty-state">
      <div class="icon"><i class="bi bi-journal-x"></i></div>
      <h4>No Subjects</h4>
      <p>You are not enrolled in any subjects yet.</p>
    </div>
  <?php else: ?>
    <div class="subject-grid">
      <?php foreach ($subjects as $s): ?>
        <div class="subject-card">
          <div class="subject-code"><i class="bi bi-hash"></i> <?= htmlspecialchars($s['course_code']) ?></div>
          <div class="subject-name"><?= htmlspecialchars($s['subject_name']) ?></div>
          <p class="text-muted" style="font-size:0.85rem;margin-bottom:0.75rem;">
            <?= htmlspecialchars($s['course_title']) ?>
          </p>
          <?php if (!empty($s['grade'])): ?>
            <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Graded <?= htmlspecialchars($s['grade']) ?></span>
          <?php else: ?>
            <span class="badge bg-warning"><i class="bi bi-hourglass-split"></i> Pending</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>