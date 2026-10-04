<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$response = apiRequest('/results', 'GET', null, $_SESSION['token']);
$subjects = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];

// QR code generation
$qrCode = null;
$qrError = "";
$verifyUrl = "";

if (isset($_GET['generate_qr'])) {
    // URL untuk pengawas exam buka (public)
   $verifyUrl = PUBLIC_BASE_URL . "/verify.php?id=" . $_SESSION['user_id'];
    
    // Hantar URL ni sebagai data untuk QR code
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
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h3>My Enrolled Subjects</h3>
  <p class="text-muted">Subjects you are registered for. Marks will appear once lecturer grades them.</p>

  <?php if (!empty($qrError)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($qrError) ?></div>
  <?php endif; ?>

  <!-- ===== QR CODE CARD ===== -->
  <div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
      <strong>🎫 Exam Slip QR Code</strong>
    </div>
    <div class="card-body">
      <?php if (!$qrCode): ?>
        <p>Click the button below to generate a QR code for exam verification.</p>
        <a href="my_subjects.php?generate_qr=1" class="btn btn-primary">
          📱 Generate My QR Code
        </a>
      <?php else: ?>
        <div class="row align-items-center">
          <div class="col-md-4 text-center">
            <img src="<?= htmlspecialchars($qrCode) ?>" 
                 alt="Student QR Code" 
                 class="img-fluid border p-2 bg-white"
                 style="max-width: 250px;">
          </div>
          <div class="col-md-8">
            <h5>Your Exam Verification QR</h5>
            <p class="text-muted mb-2">
              Show this to the exam invigilator. They will scan it to verify your registered subjects.
            </p>
            <div class="alert alert-info small">
              <strong>QR contains URL:</strong><br>
              <code class="text-break"><?= htmlspecialchars($verifyUrl) ?></code>
            </div>
            <p>
              <strong>Name:</strong> <?= htmlspecialchars($_SESSION['full_name']) ?><br>
              <strong>Generated via:</strong> qrserver.com (Third-Party API)
            </p>
            <a href="my_subjects.php" class="btn btn-secondary btn-sm">Close QR</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ===== SUBJECT LIST ===== -->
  <h5>Subject List</h5>
  <?php if (empty($subjects)): ?>
    <div class="alert alert-info">You are not enrolled in any subjects yet.</div>
  <?php else: ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Course</th>
          <th>Subject</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($subjects as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['course_code']) ?> - <?= htmlspecialchars($s['course_title']) ?></td>
          <td><?= htmlspecialchars($s['subject_name']) ?></td>
          <td>
            <?php if (!empty($s['grade'])): ?>
              <span class="badge bg-success">Graded (<?= htmlspecialchars($s['grade']) ?>)</span>
            <?php else: ?>
              <span class="badge bg-warning text-dark">Pending</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>