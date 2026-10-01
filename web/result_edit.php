<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: results.php"); exit; }

$res    = apiRequest('/results/' . $id, 'GET', null, $_SESSION['token']);
$result = $res['body']['data'] ?? null;
if (!$result) { die("Result not found or access denied."); }

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = apiRequest('/results/' . $id, 'PUT', [
        'marks' => $_POST['marks'],
        'grade' => $_POST['grade']
    ], $_SESSION['token']);
    if ($response['status_code'] === 200) { header("Location: results.php"); exit; }
    else $error = $response['body']['message'] ?? 'Failed to update';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Result · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-lum" style="max-width:760px;">

  <div style="display:flex;align-items:center;gap:0.5rem;color:var(--ink-3);font-size:0.85rem;margin-bottom:1.25rem;">
    <a href="results.php" style="color:var(--ink-3);text-decoration:none;"><i class="bi bi-bar-chart-fill"></i> Results</a>
    <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i>
    <span style="color:var(--ink);font-weight:600;">Update Grade</span>
  </div>

  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ Grade Update</span>
      <h1>Update <em>result</em></h1>
      <p class="sub">Adjust the marks and grade for this student.</p>
    </div>
    <a href="results.php" class="btn-lum ghost"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-lum danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <!-- STUDENT INFO CARD -->
  <div class="student-card">
    <div class="student-avatar"><?= strtoupper(substr($result['full_name'],0,1)) ?></div>
    <div style="flex:1;">
      <div style="font-weight:700;color:var(--ink);font-size:1.05rem;"><?= htmlspecialchars($result['full_name']) ?></div>
      <div style="color:var(--ink-3);font-size:0.85rem;margin-top:0.15rem;">
        <i class="bi bi-collection-fill"></i> <?= htmlspecialchars($result['course_code']) ?> · <?= htmlspecialchars($result['course_title']) ?>
      </div>
    </div>
    <span class="pill indigo" style="padding:0.5rem 0.9rem;font-size:0.8rem;">
      <i class="bi bi-hash"></i> ID <?= htmlspecialchars($result['result_id']) ?>
    </span>
  </div>

  <div class="card-lum flat">
    <form method="POST">

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--violet-soft);color:#7e22ce;">
            <i class="bi bi-award-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Grading</h3>
            <p class="form-section-desc">Enter the student's marks and assign a grade.</p>
          </div>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-123"></i> Marks (0–100) <span class="req">*</span></label>
          <input type="number" step="0.01" min="0" max="100" name="marks" class="input-lum" value="<?= htmlspecialchars($result['marks']) ?>" required style="font-size:1.15rem;font-weight:700;">
          <div class="hint"><i class="bi bi-info-circle"></i> Marks akan affect grade point automatically.</div>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-trophy-fill"></i> Grade <span class="req">*</span></label>
          <div class="grade-picker">
            <?php foreach (['A','A-','B+','B','B-','C+','C','D','F'] as $g):
              $sel = $result['grade'] == $g;
            ?>
              <label class="grade-chip <?= $sel ? 'selected' : '' ?>">
                <input type="radio" name="grade" value="<?= $g ?>" <?= $sel ? 'checked' : '' ?> required style="display:none;">
                <span><?= $g ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <a href="results.php" class="btn-lum ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        <button type="submit" class="btn-lum primary"><i class="bi bi-check-circle-fill"></i> Update Result</button>
      </div>

    </form>
  </div>

</div>

<script>
document.querySelectorAll('.grade-chip').forEach(chip => {
    chip.addEventListener('click', () => {
        document.querySelectorAll('.grade-chip').forEach(c => c.classList.remove('selected'));
        chip.classList.add('selected');
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>