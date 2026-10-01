<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: users.php"); exit; }

$res  = apiRequest('/users/' . $id, 'GET', null, $_SESSION['token']);
$user = $res['body']['data'] ?? null;
if (!$user) { die("User not found."); }

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = apiRequest('/users/' . $id, 'PUT', [
        'full_name' => $_POST['full_name'],
        'email'     => $_POST['email'],
        'role'      => $_POST['role'],
        'matric_no' => $_POST['matric_no'] ?? null,
        'no_ic'     => $_POST['no_ic'],
        'phone'     => $_POST['phone'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ], $_SESSION['token']);

    if ($response['status_code'] === 200) {
        header("Location: users.php"); exit;
    } else {
        $error = $response['body']['message'] ?? 'Failed to update';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
<h3>Edit User</h3>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST">
  <div class="mb-3"><label>Full Name</label>
    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required></div>
  <div class="mb-3"><label>Email</label>
    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required></div>
  <div class="mb-3"><label>Role</label>
    <select name="role" class="form-control" required>
      <?php foreach (['admin','lecturer','student'] as $r): ?>
        <option value="<?= $r ?>" <?= $user['role']==$r?'selected':'' ?>><?= ucfirst($r) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="mb-3"><label>Matric No</label>
    <input type="text" name="matric_no" class="form-control" value="<?= htmlspecialchars($user['matric_no'] ?? '') ?>"></div>
  <div class="mb-3"><label>IC No</label>
    <input type="text" name="no_ic" class="form-control" value="<?= htmlspecialchars($user['no_ic'] ?? '') ?>" required></div>
  <div class="mb-3"><label>Phone</label>
    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
  <div class="mb-3">
    <input type="checkbox" name="is_active" <?= $user['is_active']?'checked':'' ?>> Active
  </div>
  <button type="submit" class="btn btn-primary">Update</button>
  <a href="users.php" class="btn btn-secondary">Cancel</a>
</form>
</body>
</html>