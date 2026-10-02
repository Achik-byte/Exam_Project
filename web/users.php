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
<div class="container-lum">

  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ Administration</span>
      <h1>Manage <em>users</em></h1>
      <p class="sub">Add, edit, or remove users from the system.</p>
    </div>
    <a href="user_add.php" class="btn-lum primary">
      <i class="bi bi-person-plus-fill"></i> Add New User
    </a>
  </div>

  <div class="mb-3" style="display:flex;gap:0.5rem;flex-wrap:wrap;">
    <span class="pill indigo" style="padding:0.5rem 0.9rem;font-size:0.82rem;">
      <i class="bi bi-people-fill"></i> <?= count($users) ?> total users
    </span>
  </div>

  <?php if (empty($users)): ?>
    <div class="empty-lum">
      <div class="icon"><i class="bi bi-people"></i></div>
      <h4>No users found</h4>
      <p>There are no users in the system yet. Start by adding one.</p>
    </div>
  <?php else: ?>
    <div class="table-lum-wrap">
      <table class="table-lum">
        <thead>
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Matric No</th>
            <th>Phone</th>
            <th>Status</th>
            <th style="text-align:right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u):
            $roleTone = 'indigo';
            if ($u['role'] === 'admin') $roleTone = 'amber';
            elseif ($u['role'] === 'student') $roleTone = 'mint';
          ?>
          <tr>
            <td><span class="mono">#<?= htmlspecialchars($u['user_id']) ?></span></td>
            <td>
              <div class="primary"><?= htmlspecialchars($u['full_name']) ?></div>
            </td>
            <td><span class="mono" style="font-size:0.82rem;"><?= htmlspecialchars($u['email']) ?></span></td>
            <td>
              <span class="pill <?= $roleTone ?>">
                <span class="dot"></span> <?= ucfirst(htmlspecialchars($u['role'])) ?>
              </span>
            </td>
            <td><span class="mono"><?= htmlspecialchars($u['matric_no'] ?? '—') ?></span></td>
            <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
            <td>
              <?php if ($u['is_active']): ?>
                <span class="pill mint"><span class="dot"></span> Active</span>
              <?php else: ?>
                <span class="pill gray"><span class="dot"></span> Inactive</span>
              <?php endif; ?>
            </td>
            <td style="text-align:right;white-space:nowrap;">
              <a href="user_edit.php?id=<?= $u['user_id'] ?>" class="btn-lum ghost sm" title="Edit">
                <i class="bi bi-pencil-fill"></i>
              </a>
              <a href="user_delete.php?id=<?= $u['user_id'] ?>" class="btn-lum danger sm" title="Delete" onclick="return confirm('Delete this user?')">
                <i class="bi bi-trash3-fill"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>