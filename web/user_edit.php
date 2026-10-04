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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern" style="max-width: 800px;">

  <div class="page-header">
    <div>
      <h1>Edit User</h1>
      <p class="subtitle">Update user details and account status.</p>
    </div>
    <a href="users.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- User Info Card -->
  <div class="card-modern mb-4">
    <div style="display:flex;align-items:center;gap:1.25rem;">
      <div style="width:60px;height:60px;background:var(--gradient-primary);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;font-weight:800;box-shadow:var(--shadow-glow-primary);">
        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
      </div>
      <div>
        <h4 style="margin:0;color:#ffffff;font-weight:700;"><?= htmlspecialchars($user['full_name']) ?></h4>
        <p class="text-muted" style="margin:0.25rem 0 0 0;">
          <i class="bi bi-person-badge"></i> <?= ucfirst($user['role']) ?> · ID #<?= $user['user_id'] ?>
        </p>
      </div>
      <?php if ($user['is_active']): ?>
        <span class="badge bg-success" style="margin-left:auto;"><i class="bi bi-check-circle-fill"></i> Active</span>
      <?php else: ?>
        <span class="badge bg-danger" style="margin-left:auto;"><i class="bi bi-x-circle-fill"></i> Inactive</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="card-modern">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label"><i class="bi bi-person-fill"></i> Full Name</label>
        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-envelope-fill"></i> Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-shield-fill-check"></i> Role</label>
        <select name="role" class="form-select" required>
          <?php foreach (['admin','lecturer','student'] as $r): ?>
            <option value="<?= $r ?>" <?= $user['role']==$r?'selected':'' ?>><?= ucfirst($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label"><i class="bi bi-hash"></i> Matric No</label>
          <input type="text" name="matric_no" class="form-control" value="<?= htmlspecialchars($user['matric_no'] ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label"><i class="bi bi-person-vcard-fill"></i> IC No</label>
          <input type="text" name="no_ic" class="form-control" value="<?= htmlspecialchars($user['no_ic'] ?? '') ?>" required>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label"><i class="bi bi-telephone-fill"></i> Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
      </div>

      <div class="form-check mb-4" style="padding-left:2.5rem;">
        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= $user['is_active']?'checked':'' ?>>
        <label class="form-check-label" for="isActive" style="color:#cbd5e1;font-weight:600;">
          Account is active (user can log in)
        </label>
      </div>

      <div style="display:flex;gap:0.75rem;">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update User</button>
        <a href="users.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Cancel</a>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>