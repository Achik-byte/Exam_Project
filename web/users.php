<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$response = apiRequest('/users', 'GET', null, $_SESSION['token']);
$data     = $response['body']['data'] ?? [];
$users    = $data['users'] ?? $data;   // handle pagination structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h3>Manage Users</h3>
  <a href="user_add.php" class="btn btn-primary mb-3">Add New User</a>
  <table class="table table-striped">
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
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td><?= htmlspecialchars($u['matric_no'] ?? '-') ?></td>
        <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
        <td><?= $u['is_active'] ? 'Active' : 'Inactive' ?></td>
        <td>
          <a href="user_edit.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
          <a href="user_delete.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>