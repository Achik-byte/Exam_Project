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
  <title>Manage Users - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern">

  <div class="page-header">
    <div>
      <h1>Manage Users</h1>
      <p class="subtitle">Every account on the platform.</p>
    </div>
    <a href="user_add.php" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Add User</a>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Matric No</th>
          <th>Phone</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td>#<?= $u['user_id'] ?></td>
          <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td>
            <span class="badge <?= $u['role']==='admin' ? 'bg-primary' : ($u['role']==='lecturer' ? 'bg-info' : 'bg-success') ?>">
              <?= ucfirst(htmlspecialchars($u['role'])) ?>
            </span>
          </td>
          <td><?= htmlspecialchars($u['matric_no'] ?? '-') ?></td>
          <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
          <td>
            <?php if ($u['is_active']): ?>
              <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Active</span>
            <?php else: ?>
              <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Inactive</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="user_edit.php?id=<?= $u['user_id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-fill"></i></a>
            <a href="user_delete.php?id=<?= $u['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="bi bi-trash-fill"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>