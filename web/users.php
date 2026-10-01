<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';
$response = apiRequest('/users', 'GET', null, $_SESSION['token']);
$data     = $response['body']['data'] ?? [];
$users    = $data['users'] ?? $data;
$roleTone = ['admin'=>'amber','lecturer'=>'sky','student'=>'mint'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Users · ExamFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-lum">

  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ Access Control</span>
      <h1>Manage <em>users</em></h1>
      <p class="sub">Every account on the platform.</p>
    </div>
    <a href="user_add.php" class="btn-lum primary"><i class="bi bi-person-plus-fill"></i> Add User</a>
  </div>

  <?php if (empty($users)): ?>
    <div class="empty-lum">
      <div class="icon"><i class="bi bi-people"></i></div>
      <h4>No users yet</h4>
      <p>Start by adding your first user.</p>
    </div>
  <?php else: ?>
    <div class="grid-lum">
      <?php foreach ($users as $u): $t = $roleTone[$u['role']] ?? 'gray'; ?>
      <div class="tile-lum <?= $t === 'amber' ? 'amber' : ($t === 'sky' ? 'sky' : 'mint') ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.85rem;">
          <div style="width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:800;background:var(--paper);box-shadow:var(--shadow-xs);color:var(--ink);">
            <?= strtoupper(substr($u['full_name'],0,1)) ?>
          </div>
          <span class="pill <?= $t ?>"><?= htmlspecialchars($u['role']) ?></span>
        </div>
        <div class="name" style="margin-bottom:0.6rem;"><?= htmlspecialchars($u['full_name']) ?></div>
        <div style="display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:0.9rem;">
          <span class="pill gray"><i class="bi bi-hash"></i> <?= $u['user_id'] ?></span>
          <?= $u['is_active'] ? '<span class="pill mint"><span class="dot"></span> Active</span>' : '<span class="pill coral"><span class="dot"></span> Inactive</span>' ?>
        </div>
        <div style="font-size:0.82rem;color:var(--ink-2);display:flex;flex-direction:column;gap:0.4rem;margin-bottom:1.1rem;line-height:1.5;">
          <span><i class="bi bi-envelope-fill" style="color:var(--indigo)"></i> <?= htmlspecialchars($u['email']) ?></span>
          <span><i class="bi bi-telephone-fill" style="color:var(--mint)"></i> <?= htmlspecialchars($u['phone'] ?? '—') ?></span>
          <span><i class="bi bi-person-vcard-fill" style="color:var(--amber)"></i> <?= htmlspecialchars($u['matric_no'] ?? '—') ?></span>
        </div>
        <div style="display:flex;gap:0.5rem;">
          <a href="user_edit.php?id=<?= $u['user_id'] ?>" class="btn-lum ghost sm" style="flex:1;justify-content:center;"><i class="bi bi-pencil"></i> Edit</a>
          <a href="user_delete.php?id=<?= $u['user_id'] ?>" class="btn-lum danger sm" onclick="return confirm('Delete this user?')"><i class="bi bi-trash3"></i></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>