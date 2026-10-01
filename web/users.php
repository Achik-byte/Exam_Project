<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';

$response = apiRequest('/users', 'GET', null, $_SESSION['token']);
$data     = $response['body']['data'] ?? [];
$users    = $data['users'] ?? $data;
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
<div class="container-aurora">

  <div class="page-head">
    <div>
      <div class="eyebrow">◆ Access Control</div>
      <h1>Users</h1>
      <p class="subtitle">Manage every account on the platform.</p>
    </div>
    <a href="user_add.php" class="btn-neo violet"><i class="bi bi-person-plus-fill"></i> Add User</a>
  </div>

  <?php if (empty($users)): ?>
    <div class="empty-neo">
      <div class="icon"><i class="bi bi-people"></i></div>
      <h4>No users yet</h4>
      <p>Start by adding your first user.</p>
    </div>
  <?php else: ?>
    <div class="subject-grid">
      <?php foreach ($users as $u):
        $roleTone = ['admin'=>'amber','lecturer'=>'cyan','student'=>'mint'][$u['role']] ?? 'gray';
      ?>
      <div class="subject-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.85rem;">
          <div style="width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:800;background:rgba(255,255,255,0.05);border:1px solid var(--border);color:var(--violet-2);">
            <?= strtoupper(substr($u['full_name'],0,1)) ?>
          </div>
          <span class="chip <?= $roleTone ?>"><?= htmlspecialchars($u['role']) ?></span>
        </div>
        <div class="name" style="margin-bottom:0.35rem;"><?= htmlspecialchars($u['full_name']) ?></div>
        <div class="meta" style="margin-bottom:1rem;">
          <span class="chip gray"><i class="bi bi-hash"></i> <?= $u['user_id'] ?></span>
          <?= $u['is_active'] ? '<span class="chip mint">Active</span>' : '<span class="chip red">Inactive</span>' ?>
        </div>
        <div style="font-size:0.82rem;color:var(--text-2);display:flex;flex-direction:column;gap:0.35rem;margin-bottom:1rem;">
          <span><i class="bi bi-envelope-fill" style="color:var(--cyan)"></i> <?= htmlspecialchars($u['email']) ?></span>
          <span><i class="bi bi-telephone-fill" style="color:var(--pink)"></i> <?= htmlspecialchars($u['phone'] ?? '—') ?></span>
          <span><i class="bi bi-person-vcard-fill" style="color:var(--amber)"></i> <?= htmlspecialchars($u['matric_no'] ?? '—') ?></span>
        </div>
        <div style="display:flex;gap:0.5rem;">
          <a href="user_edit.php?id=<?= $u['user_id'] ?>" class="btn-neo ghost sm" style="flex:1;justify-content:center;"><i class="bi bi-pencil"></i> Edit</a>
          <a href="user_delete.php?id=<?= $u['user_id'] ?>" class="btn-neo danger sm" onclick="return confirm('Delete this user?')"><i class="bi bi-trash3"></i></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>