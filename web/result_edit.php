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

if (!$result) {
    die("Result not found or access denied.");
}

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
<html>
<head>
  <title>Edit Result</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-aurora" style="max-width:720px;">

  <div class="page-head">
    <div>
      <div class="eyebrow">◆ Grade Update</div>
      <h1>Edit Result</h1>
      <p class="subtitle">Adjust marks and grade for a student.</p>
    </div>
    <a href="results.php" class="btn-neo ghost"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-neo danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <div class="glass">
    <form method="POST">
      <div class="field">
        <label><i class="bi bi-person-fill"></i> Student</label>
        <input type="text" class="input-neo" value="<?= htmlspecialchars($result['full_name']) ?>" disabled>
      </div>
      <div class="field">
        <label><i class="bi bi-collection-fill"></i> Course</label>
        <input type="text" class="input-neo" value="<?= htmlspecialchars($result['course_code'].' - '.$result['course_title']) ?>" disabled>
      </div>
      <div class="grid-2">
        <div class="field">
          <label><i class="bi bi-123"></i> Marks</label>
          <input type="number" step="0.01" name="marks" class="input-neo" value="<?= htmlspecialchars($result['marks']) ?>" required>
        </div>
        <div class="field">
          <label><i class="bi bi-award-fill"></i> Grade</label>
          <select name="grade" class="select-neo" required>
            <?php foreach (['A','A-','B+','B','B-','C+','C','D','F'] as $g): ?>
              <option value="<?= $g ?>" <?= $result['grade']==$g?'selected':'' ?>><?= $g ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
        <button type="submit" class="btn-neo violet"><i class="bi bi-check-circle-fill"></i> Update Result</button>
        <a href="results.php" class="btn-neo ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>