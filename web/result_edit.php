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

    if ($response['status_code'] === 200) {
        header("Location: results.php"); exit;
    } else {
        $error = $response['body']['message'] ?? 'Failed to update';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Result - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern" style="max-width: 700px;">

  <div class="page-header">
    <div>
      <h1>Update Result</h1>
      <p class="subtitle">Adjust the marks and grade for this student.</p>
    </div>
    <a href="results.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card-modern mb-4">
    <div style="display:flex;align-items:center;gap:1.25rem;">
      <div style="width:60px;height:60px;background:var(--gradient-primary);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;font-weight:800;box-shadow:var(--shadow-glow-primary);">
        <?= strtoupper(substr($result['full_name'], 0, 1)) ?>
      </div>
      <div>
        <h4 style="margin:0;color:#ffffff;font-weight:700;"><?= htmlspecialchars($result['full_name']) ?></h4>
        <p class="text-muted" style="margin:0.25rem 0 0 0;">
          <i class="bi bi-book-fill"></i> <?= htmlspecialchars($result['course_code'].' - '.$result['course_title']) ?>
        </p>
      </div>
    </div>
  </div>

  <div class="card-modern">
    <form method="POST">
      <div class="mb-4">
        <label class="form-label"><i class="bi bi-123"></i> Marks (0-100)</label>
        <input type="number" step="0.01" min="0" max="100" name="marks" class="form-control" value="<?= htmlspecialchars($result['marks']) ?>" required>
      </div>

      <div class="mb-4">
        <label class="form-label"><i class="bi bi-award-fill"></i> Grade</label>
        <select name="grade" class="form-select" required>
          <?php foreach (['A','A-','B+','B','B-','C+','C','D','F'] as $g): ?>
            <option value="<?= $g ?>" <?= $result['grade']==$g?'selected':'' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display:flex;gap:0.75rem;">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Result</button>
        <a href="results.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Cancel</a>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>