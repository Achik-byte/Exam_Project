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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit User · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-lum" style="max-width:880px;">

  <div style="display:flex;align-items:center;gap:0.5rem;color:var(--ink-3);font-size:0.85rem;margin-bottom:1.25rem;">
    <a href="users.php" style="color:var(--ink-3);text-decoration:none;"><i class="bi bi-people-fill"></i> Users</a>
    <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i>
    <span style="color:var(--ink);font-weight:600;"><?= htmlspecialchars($user['full_name']) ?></span>
  </div>

  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ Edit Account</span>
      <h1>Edit <em>user</em></h1>
      <p class="sub">Update user details and account status.</p>
    </div>
    <a href="users.php" class="btn-lum ghost"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-lum danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <div class="student-card">
    <div class="student-avatar" style="background:var(--indigo);color:white;"><?= strtoupper(substr($user['full_name'],0,1)) ?></div>
    <div style="flex:1;">
      <div style="font-weight:700;color:var(--ink);font-size:1.05rem;"><?= htmlspecialchars($user['full_name']) ?></div>
      <div style="color:var(--ink-3);font-size:0.85rem;margin-top:0.15rem;">
        <i class="bi bi-shield-fill"></i> <?= ucfirst(htmlspecialchars($user['role'])) ?>
        · <i class="bi bi-hash"></i> <?= htmlspecialchars($user['user_id']) ?>
      </div>
    </div>
    <?= $user['is_active']
        ? '<span class="pill mint" style="padding:0.5rem 0.9rem;font-size:0.8rem;"><span class="dot"></span> Active</span>'
        : '<span class="pill coral" style="padding:0.5rem 0.9rem;font-size:0.8rem;"><span class="dot"></span> Inactive</span>' ?>
  </div>

  <div class="card-lum flat">
    <form method="POST">

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--indigo-soft);color:var(--indigo);">
            <i class="bi bi-person-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Identity</h3>
            <p class="form-section-desc">Basic information about the user.</p>
          </div>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-person-fill"></i> Full Name <span class="req">*</span></label>
          <input type="text" name="full_name" class="input-lum" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-envelope-fill"></i> Email Address <span class="req">*</span></label>
          <input type="email" name="email" class="input-lum" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
      </div>

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--amber-soft);color:#92400e;">
            <i class="bi bi-shield-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Role</h3>
            <p class="form-section-desc">Change the user's access level.</p>
          </div>
        </div>

        <div class="role-picker">
          <?php
            $roleMeta = [
              'admin'    => ['icon'=>'bi-shield-fill-check', 'bg'=>'var(--amber-soft)', 'color'=>'#92400e', 'desc'=>'Full system access'],
              'lecturer' => ['icon'=>'bi-mortarboard-fill',  'bg'=>'var(--sky-soft)',   'color'=>'#0369a1', 'desc'=>'Manage results & exams'],
              'student'  => ['icon'=>'bi-backpack-fill',     'bg'=>'var(--mint-soft)',  'color'=>'#047857', 'desc'=>'View results & exams'],
            ];
            foreach ($roleMeta as $r => $m):
              $sel = $user['role'] === $r;
          ?>
            <label class="role-card <?= $sel ? 'selected' : '' ?>">
              <input type="radio" name="role" value="<?= $r ?>" <?= $sel ? 'checked' : '' ?> required style="display:none;">
              <div class="role-icon" style="background:<?= $m['bg'] ?>;color:<?= $m['color'] ?>;"><i class="bi <?= $m['icon'] ?>"></i></div>
              <div class="role-name"><?= ucfirst($r) ?></div>
              <div class="role-desc"><?= $m['desc'] ?></div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="background:var(--mint-soft);color:#047857;">
            <i class="bi bi-telephone-fill"></i>
          </div>
          <div>
            <h3 class="form-section-title">Contact Details</h3>
            <p class="form-section-desc">Identification and contact information.</p>
          </div>
        </div>

        <div class="grid-2">
          <div class="field-lum">
            <label><i class="bi bi-credit-card-2-front-fill"></i> IC Number <span class="req">*</span></label>
            <input type="text" name="no_ic" class="input-lum" value="<?= htmlspecialchars($user['no_ic'] ?? '') ?>" required>
          </div>
          <div class="field-lum">
            <label><i class="bi bi-phone-fill"></i> Phone Number</label>
            <input type="text" name="phone" class="input-lum" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
          </div>
        </div>

        <div class="field-lum">
          <label><i class="bi bi-person-vcard-fill"></i> Matric Number</label>
          <input type="text" name="matric_no" class="input-lum" value="<?= htmlspecialchars($user['matric_no'] ?? '') ?>">
        </div>
      </div>

      <div class="form-section" style="background:var(--paper-2);border-radius:var(--r-md);padding:1.15rem;">
        <label class="toggle-lum">
          <input type="checkbox" name="is_active" <?= $user['is_active']?'checked':'' ?>>
          <div class="toggle-track"><div class="toggle-thumb"></div></div>
          <div>
            <div style="font-weight:700;color:var(--ink);font-size:0.92rem;">Account is active</div>
            <div style="color:var(--ink-3);font-size:0.82rem;">Inactive users cannot log in to the system.</div>
          </div>
        </label>
      </div>

      <div class="form-actions">
        <a href="users.php" class="btn-lum ghost"><i class="bi bi-x-lg"></i> Cancel</a>
        <button type="submit" class="btn-lum primary"><i class="bi bi-check-circle-fill"></i> Save Changes</button>
      </div>

    </form>
  </div>

</div>

<script>
document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input').checked = true;
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>