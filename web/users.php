<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$response = apiRequest('/users', 'GET', null, $_SESSION['token']);
$data     = $response['body']['data'] ?? [];
$users    = $data['users'] ?? $data;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ Access Control</span>
      <h1>Manage <em>users</em></h1>
      <p class="sub">Every account on the platform.</p>
    </div>
    <a href="user_add.php" class="btn-glass primary"><i class="bi bi-person-plus-fill"></i> Add User</a>
  </div>

  <?php if (empty($users)): ?>
    <div class="empty-glass">
      <div class="icon"><i class="bi bi-people"></i></div>
      <h4>No users found</h4>
      <p>There are no users in the system yet.</p>
    </div>
  <?php else: ?>
    <div class="grid-glass">
      <?php foreach ($users as $u):
        $tone = 'blue';
        if ($u['role'] === 'admin') $tone = 'amber';
        elseif ($u['role'] === 'student') $tone = 'green';
        elseif ($u['role'] === 'lecturer') $tone = 'purple';
        $initial = strtoupper(substr($u['full_name'], 0, 1));
      ?>
      <div class="tile-glass <?= $tone ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
          <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,0.1);border:1px solid var(--glass-line);display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;color:white;">
            <?= $initial ?>
          </div>
          <span class="pill-glass <?= $tone ?>"><span class="dot"></span> <?= ucfirst(htmlspecialchars($u['role'])) ?></span>
        </div>
        <div class="name" style="margin-bottom:0.5rem;"><?= htmlspecialchars($u['full_name']) ?></div>
        <div style="display:flex;flex-direction:column;gap:0.35rem;font-size:0.82rem;color:var(--ink-3);margin-bottom:1rem;">
          <div><i class="bi bi-hash"></i> ID <?= htmlspecialchars($u['user_id']) ?></div>
          <div><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($u['email']) ?></div>
          <?php if (!empty($u['phone'])): ?>
            <div><i class="bi bi-phone-fill"></i> <?= htmlspecialchars($u['phone']) ?></div>
          <?php endif; ?>
          <?php if (!empty($u['matric_no'])): ?>
            <div><i class="bi bi-person-vcard-fill"></i> <?= htmlspecialchars($u['matric_no']) ?></div>
          <?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;padding-top:1rem;border-top:1px solid var(--glass-line);">
          <div>
            <?php if ($u['is_active']): ?>
              <span class="pill-glass green"><span class="dot"></span> Active</span>
            <?php else: ?>
              <span class="pill-glass gray"><span class="dot"></span> Inactive</span>
            <?php endif; ?>
          </div>
          <div style="display:flex;gap:0.35rem;">
            <a href="user_edit.php?id=<?= $u['user_id'] ?>" class="btn-glass sm" title="Edit"><i class="bi bi-pencil-fill"></i></a>
            <a href="user_delete.php?id=<?= $u['user_id'] ?>" class="btn-glass sm red" title="Delete" onclick="return confirm('Delete this user?')"><i class="bi bi-trash3-fill"></i></a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>