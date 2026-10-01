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
<body>
<?php include 'navbar.php'; ?>
<div class="container-aurora" style="max-width:820px;">
  <div class="page-head">
    <div>
      <div class="eyebrow">◆ Edit Account</div>
      <h1>Edit User</h1>
      <p class="subtitle">Update user details and status.</p>
    </div>
    <a href="users.php" class="btn-neo ghost"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
  <?php if ($error): ?><div class="alert-neo danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div><?php endif; ?>
  <div class="glass">
    <form method="POST">
      <div class="grid-2">
        <div class="field"><label><i class="bi bi-person-fill"></i> Full Name</label>
          <input type="text" name="full_name" class="input-neo" value="<?= htmlspecialchars($user['full_name']) ?>" required></div>
        <div class="field"><label><i class="bi bi-envelope-fill"></i> Email</label>
          <input type="email" name="email" class="input-neo" value="<?= htmlspecialchars($user['email']) ?>" required></div>
      </div>
      <div class="grid-2">
        <div class="field"><label><i class="bi bi-shield-fill"></i> Role</label>
          <select name="role" class="select-neo" required>
            <?php foreach (['admin','lecturer','student'] as $r): ?>
              <option value="<?= $r ?>" <?= $user['role']==$r?'selected':'' ?>><?= ucfirst($r) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="field"><label><i class="bi bi-person-vcard-fill"></i> Matric No</label>
          <input type="text" name="matric_no" class="input-neo" value="<?= htmlspecialchars($user['matric_no'] ?? '') ?>"></div>
      </div>
      <div class="grid-2">
        <div class="field"><label><i class="bi bi-credit-card-2-front-fill"></i> IC Number</label>
          <input type="text" name="no_ic" class="input-neo" value="<?= htmlspecialchars($user['no_ic'] ?? '') ?>" required></div>
        <div class="field"><label><i class="bi bi-telephone-fill"></i> Phone</label>
          <input type="text" name="phone" class="input-neo" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
      </div>
      <label class="check-neo" style="display:inline-flex;margin-bottom:1rem;">
        <input type="checkbox" name="is_active" <?= $user['is_active']?'checked':'' ?>>
        <span>Account is active</span>
      </label>
      <div style="display:flex;gap:0.75rem;">
        <button type="submit" class="btn-neo violet"><i class="bi bi-check-circle-fill"></i> Update User</button>
        <a href="users.php" class="btn-neo ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>