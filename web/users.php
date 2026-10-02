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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern">
  <div class="page-header">
    <div>
      <h1>Manage Users</h1>
      <p class="subtitle">Add, edit, or remove users from the system</p>
    </div>
    <a href="user_add.php" class="btn-modern primary"><i class="bi bi-plus-circle"></i> Add New User</a>
  </div>

  <?php if (empty($users)): ?>
    <div class="empty-state">
      <div class="icon"><i class="bi bi-people"></i></div>
      <h4>No Users Found</h4>
      <p>There are no users in the system yet.</p>
    </div>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="table-modern">
        <thead>
          <tr>
            <th>ID</th><th>Full Name</th><th>Email</th><th>Role</th>
            <th>Matric No</th><th>Phone</th><th>Status</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><?= $u['user_id'] ?></td>
            <td><?= htmlspecialchars($u['full_name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <?php
                $roleClass = 'primary';
                if ($u['role'] === 'admin') $roleClass = 'warning';
                elseif ($u['role'] === 'student') $roleClass = 'success';
              ?>
              <span class="badge-modern <?= $roleClass ?>"><?= htmlspecialchars($u['role']) ?></span>
            </td>
            <td><?= htmlspecialchars($u['matric_no'] ?? '-') ?></td>
            <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
            <td>
              <?php if ($u['is_active']): ?>
                <span class="badge-modern success">Active</span>
              <?php else: ?>
                <span class="badge-modern gray">Inactive</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="user_edit.php?id=<?= $u['user_id'] ?>" class="btn-modern warning sm">Edit</a>
              <a href="user_delete.php?id=<?= $u['user_id'] ?>" class="btn-modern danger sm" onclick="return confirm('Delete?')">Delete</a>
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