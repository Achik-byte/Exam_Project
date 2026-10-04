<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$response = apiRequest('/users', 'GET', null, $_SESSION['token']);
$data     = $response['body']['data'] ?? [];
$users    = $data['users'] ?? $data;

// Filter ikut role
$filterRole = $_GET['role'] ?? 'all';
if ($filterRole !== 'all') {
    $users = array_filter($users, fn($u) => $u['role'] === $filterRole);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
  <style>
    .filter-bar {
      display: flex; gap: 0.5rem; flex-wrap: wrap;
      margin-bottom: 1.5rem;
    }
    .filter-chip {
      padding: 0.5rem 1rem;
      border-radius: 999px;
      background: var(--glass);
      border: 1px solid var(--glass-line);
      color: var(--ink-2);
      text-decoration: none;
      font-size: 0.82rem;
      font-weight: 600;
      transition: all .2s;
    }
    .filter-chip:hover { background: var(--glass-2); color: white; }
    .filter-chip.active {
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      color: white;
      border-color: transparent;
      box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
    }
    .user-row {
      display: grid;
      grid-template-columns: 50px 1fr 200px 150px 130px 100px;
      gap: 1rem;
      align-items: center;
      padding: 1rem 1.25rem;
      background: var(--glass);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-md);
      margin-bottom: 0.65rem;
      transition: all .2s;
    }
    .user-row:hover {
      border-color: var(--glass-line-2);
      background: var(--glass-2);
      transform: translateX(4px);
    }
    .user-row .avatar {
      width: 44px; height: 44px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; color: white; font-size: 1.1rem;
    }
    .user-row .name { font-weight: 700; color: white; font-size: 0.95rem; }
    .user-row .email { color: var(--ink-3); font-size: 0.82rem; font-family: 'SF Mono', monospace; }
    @media (max-width: 900px) {
      .user-row { grid-template-columns: 50px 1fr; }
      .user-row > *:nth-child(n+3) { grid-column: span 2; }
    }
  </style>
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

  <div class="filter-bar">
    <a href="?role=all" class="filter-chip <?= $filterRole==='all'?'active':'' ?>">
      <i class="bi bi-people-fill"></i> All (<?= count($data['users'] ?? $data) ?>)
    </a>
    <a href="?role=admin" class="filter-chip <?= $filterRole==='admin'?'active':'' ?>">
      <i class="bi bi-shield-fill"></i> Admin
    </a>
    <a href="?role=lecturer" class="filter-chip <?= $filterRole==='lecturer'?'active':'' ?>">
      <i class="bi bi-mortarboard-fill"></i> Lecturer
    </a>
    <a href="?role=student" class="filter-chip <?= $filterRole==='student'?'active':'' ?>">
      <i class="bi bi-backpack-fill"></i> Student
    </a>
  </div>

  <?php if (empty($users)): ?>
    <div class="empty-glass">
      <div class="icon"><i class="bi bi-people"></i></div>
      <h4>No users found</h4>
      <p>There are no users in this category.</p>
    </div>
  <?php else: ?>
    <?php foreach ($users as $u):
      $tone = 'blue';
      if ($u['role'] === 'admin') $tone = 'amber';
      elseif ($u['role'] === 'student') $tone = 'green';
      elseif ($u['role'] === 'lecturer') $tone = 'purple';
      $initial = strtoupper(substr($u['full_name'], 0, 1));
    ?>
      <div class="user-row">
        <div class="avatar"><?= $initial ?></div>
        <div>
          <div class="name"><?= htmlspecialchars($u['full_name']) ?></div>
          <div class="email"><?= htmlspecialchars($u['email']) ?></div>
        </div>
        <div>
          <span class="pill-glass <?= $tone ?>"><span class="dot"></span> <?= ucfirst(htmlspecialchars($u['role'])) ?></span>
        </div>
        <div style="color:var(--ink-3);font-size:0.82rem;">
          <?php if (!empty($u['matric_no'])): ?>
            <div><i class="bi bi-person-vcard-fill"></i> <?= htmlspecialchars($u['matric_no']) ?></div>
          <?php endif; ?>
          <?php if (!empty($u['phone'])): ?>
            <div><i class="bi bi-phone-fill"></i> <?= htmlspecialchars($u['phone']) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <?php if ($u['is_active']): ?>
            <span class="pill-glass green"><span class="dot"></span> Active</span>
          <?php else: ?>
            <span class="pill-glass gray"><span class="dot"></span> Inactive</span>
          <?php endif; ?>
        </div>
        <div style="text-align:right;white-space:nowrap;">
          <a href="user_edit.php?id=<?= $u['user_id'] ?>" class="btn-glass sm" title="Edit"><i class="bi bi-pencil-fill"></i></a>
          <a href="user_delete.php?id=<?= $u['user_id'] ?>" class="btn-glass sm red" title="Delete" onclick="return confirm('Delete this user?')"><i class="bi bi-trash3-fill"></i></a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>