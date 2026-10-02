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
    if ($response['status_code'] === 200) { header("Location: users.php"); exit; }
    else $error = $response['body']['message'] ?? 'Failed to update';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass" style="max-width:880px;">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ Edit Account</span>
      <h1>Edit <em>user</em></h1>
      <p class="sub">Update user details and account status.</p>
    </div>
    <a href="users.php" class="btn-glass"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-glass danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <div class="card-glass">
    <form method="POST">

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="color:#a5b4fc;"><i class="bi bi-person-fill"></i></div>
          <div><h3 class="form-section-title">Identity</h3><p class="form-section-desc">Basic information.</p></div>
        </div>
        <div class="field-glass">
          <label><i class="bi bi-person-fill"></i> Full Name</label>
          <input type="text" name="full_name" class="input-glass" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>
        <div class="field-glass">
          <label><i class="bi bi-envelope-fill"></i> Email</label>
          <input type="email" name="email" class="input-glass" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
      </div>

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="color:#fcd34d;"><i class="bi bi-shield-fill"></i></div>
          <div><h3 class="form-section-title">Role</h3><p class="form-section-desc">Change access level.</p></div>
        </div>
        <div class="field-glass">
          <label><i class="bi bi-shield-fill"></i> Role</label>
          <select name="role" class="select-glass" required>
            <?php foreach (['admin','lecturer','student'] as $r): ?>
              <option value="<?= $r ?>" <?= $user['role']==$r?'selected':'' ?>><?= ucfirst($r) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="color:#6ee7b7;"><i class="bi bi-telephone-fill"></i></div>
          <div><h3 class="form-section-title">Contact Details</h3><p class="form-section-desc">Identification info.</p></div>
        </div>
        <div class="grid-2">
          <div class="field-glass">
            <label><i class="bi bi-credit-card-2-front-fill"></i> IC Number</label>
            <input type="text" name="no_ic" class="input-glass" value="<?= htmlspecialchars($user['no_ic'] ?? '') ?>" required>
          </div>
          <div class="field-glass">
            <label><i class="bi bi-phone-fill"></i> Phone</label>
            <input type="text" name="phone" class="input-glass" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
          </div>
        </div>
        <div class="field-glass">
          <label><i class="bi bi-person-vcard-fill"></i> Matric Number</label>
          <input type="text" name="matric_no" class="input-glass" value="<?= htmlspecialchars($user['matric_no'] ?? '') ?>">
        </div>
      </div>

      <div class="form-section">
        <label class="check-glass">
          <input type="checkbox" name="is_active" <?= $user['is_active']?'checked':'' ?>>
          <span><strong style="color:white;">Account is active</strong> — Inactive users cannot log in.</span>
        </label>
      </div>

      <div class="form-actions">
        <a href="users.php" class="btn-glass"><i class="bi bi-x-lg"></i> Cancel</a>
        <button type="submit" class="btn-glass primary"><i class="bi bi-check-circle-fill"></i> Save Changes</button>
      </div>

    </form>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>