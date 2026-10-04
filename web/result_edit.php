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
<body class="container mt-4">
<h3>Edit Result</h3>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST">
  <div class="mb-3"><label>Student</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($result['full_name']) ?>" disabled></div>
  <div class="mb-3"><label>Course</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($result['course_code'].' - '.$result['course_title']) ?>" disabled></div>
  <div class="mb-3"><label>Marks</label>
    <input type="number" step="0.01" name="marks" class="form-control" value="<?= htmlspecialchars($result['marks']) ?>" required></div>
  <div class="mb-3"><label>Grade</label>
    <select name="grade" class="form-control" required>
      <?php foreach (['A','A-','B+','B','B-','C+','C','D','F'] as $g): ?>
        <option value="<?= $g ?>" <?= $result['grade']==$g?'selected':'' ?>><?= $g ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button type="submit" class="btn btn-primary">Update Result</button>
  <a href="results.php" class="btn btn-secondary">Cancel</a>
</form>
</body>
</html>