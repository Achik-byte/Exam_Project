<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { session_destroy(); header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];
$response = apiRequest('/profile', 'GET', null, $_SESSION['token']);
if ($response['status_code'] === 401) { session_destroy(); header("Location: login.php"); exit; }
$userData = $response['body']['data'] ?? [];

$fullName = $_SESSION['full_name'];
$greeting = 'Hi';

// Ambil stats
$statsData = ['users' => 0, 'exams' => 0, 'notifications' => 0];
if ($role === 'admin') {
    $u = apiRequest('/users', 'GET', null, $_SESSION['token']);
    $statsData['users'] = count($u['body']['data']['users'] ?? $u['body']['data'] ?? []);
}
$e = apiRequest('/examinations', 'GET', null, $_SESSION['token']);
$statsData['exams'] = count($e['body']['data'] ?? []);
$n = apiRequest('/notifications', 'GET', null, $_SESSION['token']);
$statsData['notifications'] = count($n['body']['data'] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
  <style>
    .dash-wrap {
      display: grid;
      grid-template-columns: 240px 1fr;
      gap: 1.5rem;
      padding: 1.5rem 1.5rem 3rem;
      max-width: 1400px;
      margin: 0 auto;
    }
    .sidebar-glass {
      background: rgba(15, 15, 30, 0.55);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-lg);
      padding: 1.5rem 1rem;
      height: fit-content;
      position: sticky;
      top: 100px;
    }
    .side-label {
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.15em;
      color: var(--ink-3);
      font-weight: 700;
      margin-bottom: 0.75rem;
      padding-left: 0.75rem;
    }
    .side-link {
      display: flex; align-items: center; gap: 0.75rem;
      padding: 0.75rem 0.9rem;
      border-radius: var(--r-md);
      color: var(--ink-2);
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      margin-bottom: 0.25rem;
      transition: all .2s;
    }
    .side-link:hover { background: var(--glass-2); color: white; }
    .side-link.active {
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      color: white;
      box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
    }
    .side-link i { font-size: 1.05rem; }

    @media (max-width: 900px) {
      .dash-wrap { grid-template-columns: 1fr; }
      .sidebar-glass { position: static; }
    }

    .hero-glass {
      background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(168,85,247,0.2), rgba(236,72,153,0.15));
      border: 1px solid var(--glass-line-2);
      border-radius: var(--r-lg);
      padding: 2rem;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(20px);
      margin-bottom: 1.5rem;
    }
    .hero-glass::before {
      content: '';
      position: absolute; top: -50%; right: -10%;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(168,85,247,0.3), transparent 65%);
      border-radius: 50%;
    }
    .hero-glass .hero-content { position: relative; z-index: 1; }
    .hero-glass h1 {
      font-size: clamp(1.5rem, 3vw, 2.4rem);
      margin-bottom: 0.5rem;
      color: white;
      word-break: break-word;
      line-height: 1.15;
    }
    .hero-glass h1 em {
      font-style: italic;
      background: linear-gradient(135deg,#a5b4fc,#f0abfc);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .hero-glass .sub {
      color: var(--ink-2);
      margin: 0;
      font-size: 0.95rem;
    }

    .grid-stats-3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.15rem;
      margin-bottom: 1.5rem;
    }
    @media (max-width: 640px) { .grid-stats-3 { grid-template-columns: 1fr; } }

    .stat-glass {
      background: var(--glass);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-lg);
      padding: 1.5rem;
      position: relative;
      overflow: hidden;
      transition: all .3s;
    }
    .stat-glass:hover { transform: translateY(-4px); border-color: var(--glass-line-2); }
    .stat-glass .icon {
      width: 48px; height: 48px;
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem;
      margin-bottom: 1rem;
    }
    .stat-glass.blue .icon   { background: rgba(99,102,241,0.15); color: #a5b4fc; }
    .stat-glass.green .icon  { background: rgba(16,185,129,0.15); color: #6ee7b7; }
    .stat-glass.amber .icon  { background: rgba(245,158,11,0.15); color: #fcd34d; }
    .stat-glass.purple .icon { background: rgba(168,85,247,0.15); color: #d8b4fe; }
    .stat-glass .lbl {
      font-size: 0.72rem; text-transform: uppercase;
      letter-spacing: 0.1em; font-weight: 600;
      color: var(--ink-3); margin-bottom: 0.35rem;
    }
    .stat-glass .val {
      font-family: 'Outfit', sans-serif;
      font-size: 1.5rem; font-weight: 700; color: white;
      letter-spacing: -0.02em; word-break: break-word; line-height: 1.2;
    }
    .stat-glass .val-sm { font-size: 1rem; font-weight: 600; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="dash-wrap">

  <!-- SIDEBAR -->
  <aside class="sidebar-glass">
    <div class="side-label">Menu</div>
    <a href="index.php" class="side-link active"><i class="bi bi-house-door-fill"></i> Dashboard</a>
    <a href="courses.php" class="side-link"><i class="bi bi-book-fill"></i> Courses</a>
    <a href="exams.php" class="side-link"><i class="bi bi-calendar-event-fill"></i> Exams</a>
    <?php if ($role === 'student'): ?>
      <a href="my_subjects.php" class="side-link"><i class="bi bi-journal-bookmark-fill"></i> Subjects</a>
    <?php endif; ?>
    <a href="results.php" class="side-link"><i class="bi bi-bar-chart-fill"></i> Results</a>
    <a href="notifications.php" class="side-link"><i class="bi bi-bell-fill"></i> Inbox</a>
    <?php if ($role === 'admin'): ?>
      <a href="users.php" class="side-link"><i class="bi bi-people-fill"></i> Users</a>
    <?php endif; ?>
    <div class="side-label" style="margin-top:1.5rem;">Session</div>
    <a href="logout.php" class="side-link"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
  </aside>

  <!-- MAIN -->
  <main>

    <!-- HERO -->
    <div class="hero-glass">
      <div class="hero-content" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
        <div style="flex:1;min-width:200px;">
          <span class="eyebrow" style="display:inline-flex;align-items:center;gap:0.5rem;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.15em;color:#a5b4fc;font-weight:600;padding:0.4rem 0.85rem;background:rgba(255,255,255,0.06);border:1px solid var(--glass-line);border-radius:999px;margin-bottom:0.85rem;">◆ Dashboard</span>
          <h1><?= $greeting ?>, <em><?= htmlspecialchars($fullName) ?></em></h1>
          <p class="sub">Welcome back. Here's your activity snapshot.</p>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.5rem;">
          <span class="pill-glass blue" style="padding:0.6rem 1rem;font-size:0.8rem;text-transform:uppercase;">
            <i class="bi bi-shield-fill-check"></i> <?= htmlspecialchars($role) ?>
          </span>
          <span class="pill-glass purple" style="padding:0.5rem 0.9rem;font-size:0.75rem;">
            <i class="bi bi-calendar3"></i> <?= date('d M Y') ?>
          </span>
        </div>
      </div>
    </div>

    <!-- STATS -->
    <div class="grid-stats-3">
      <div class="stat-glass blue">
        <div class="icon"><i class="bi bi-person-badge-fill"></i></div>
        <div class="lbl">Account</div>
        <div class="val val-sm"><?= htmlspecialchars($userData['full_name'] ?? $_SESSION['full_name']) ?></div>
      </div>
      <div class="stat-glass green">
        <div class="icon"><i class="bi bi-shield-fill-check"></i></div>
        <div class="lbl">Access Level</div>
        <div class="val" style="text-transform:capitalize;"><?= htmlspecialchars($role) ?></div>
      </div>
      <div class="stat-glass purple">
        <div class="icon"><i class="bi bi-envelope-fill"></i></div>
        <div class="lbl">Email</div>
        <div class="val val-sm"><?= htmlspecialchars($userData['email'] ?? '—') ?></div>
      </div>
    </div>

    <!-- STATS BIG -->
    <div class="grid-stats-3" style="grid-template-columns:1fr 1fr;">
      <div class="stat-glass amber">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div class="icon"><i class="bi bi-calendar-event-fill"></i></div>
          <i class="bi bi-arrow-up-right" style="color:var(--ink-3);"></i>
        </div>
        <div class="lbl">Total Exams</div>
        <div class="val" style="font-size:2.5rem;"><?= $statsData['exams'] ?></div>
        <p style="color:var(--ink-3);font-size:0.82rem;margin-top:0.5rem;">Scheduled examinations in the system</p>
      </div>
      <div class="stat-glass <?= $role === 'admin' ? 'blue' : 'purple' ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div class="icon"><i class="bi <?= $role === 'admin' ? 'bi-people-fill' : 'bi-bell-fill' ?>"></i></div>
          <i class="bi bi-arrow-up-right" style="color:var(--ink-3);"></i>
        </div>
        <div class="lbl"><?= $role === 'admin' ? 'Total Users' : 'Messages' ?></div>
        <div class="val" style="font-size:2.5rem;"><?= $role === 'admin' ? $statsData['users'] : $statsData['notifications'] ?></div>
        <p style="color:var(--ink-3);font-size:0.82rem;margin-top:0.5rem;"><?= $role === 'admin' ? 'Registered accounts on the platform' : 'Notifications in your inbox' ?></p>
      </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="card-glass">
      <div style="margin-bottom:1.5rem;">
        <h3 style="margin:0;color:white;">Quick actions</h3>
        <p style="color:var(--ink-3);font-size:0.9rem;margin:0.35rem 0 0 0;">Jump straight to what matters most.</p>
      </div>

      <div class="grid-glass">
        <?php if ($role === 'admin'): ?>
          <a href="users.php" class="tile-glass" style="text-decoration:none;">
            <span class="code">USERS</span>
            <div class="name">Manage Users</div>
            <div class="meta"><span class="pill-glass gray"><i class="bi bi-people-fill"></i> Add · Edit · Remove</span></div>
          </a>
          <a href="exams.php" class="tile-glass green" style="text-decoration:none;">
            <span class="code">EXAMS</span>
            <div class="name">Manage Exams</div>
            <div class="meta"><span class="pill-glass gray"><i class="bi bi-calendar-plus-fill"></i> Schedule & Update</span></div>
          </a>
          <a href="notifications.php" class="tile-glass amber" style="text-decoration:none;">
            <span class="code">INBOX</span>
            <div class="name">Send Notification</div>
            <div class="meta"><span class="pill-glass gray"><i class="bi bi-megaphone-fill"></i> Broadcast</span></div>
          </a>
        <?php elseif ($role === 'lecturer'): ?>
          <a href="results.php" class="tile-glass" style="text-decoration:none;">
            <span class="code">GRADING</span>
            <div class="name">Update Results</div>
            <div class="meta"><span class="pill-glass gray"><i class="bi bi-pencil-square"></i> Grade Students</span></div>
          </a>
          <a href="exams.php" class="tile-glass green" style="text-decoration:none;">
            <span class="code">EXAMS</span>
            <div class="name">View Exams</div>
            <div class="meta"><span class="pill-glass gray"><i class="bi bi-calendar-week-fill"></i> Timetable</span></div>
          </a>
          <a href="notifications.php" class="tile-glass amber" style="text-decoration:none;">
            <span class="code">INBOX</span>
            <div class="name">Notify Students</div>
            <div class="meta"><span class="pill-glass gray"><i class="bi bi-send-fill"></i> Send Updates</span></div>
          </a>
        <?php else: ?>
          <a href="my_subjects.php" class="tile-glass" style="text-decoration:none;">
            <span class="code">SUBJECTS</span>
            <div class="name">My Subjects</div>
            <div class="meta"><span class="pill-glass gray"><i class="bi bi-journal-bookmark-fill"></i> Enrollments</span></div>
          </a>
          <a href="results.php" class="tile-glass green" style="text-decoration:none;">
            <span class="code">RESULTS</span>
            <div class="name">View Results</div>
            <div class="meta"><span class="pill-glass gray"><i class="bi bi-bar-chart-fill"></i> Marks & Grades</span></div>
          </a>
          <a href="exams.php" class="tile-glass amber" style="text-decoration:none;">
            <span class="code">EXAMS</span>
            <div class="name">Exam Timetable</div>
            <div class="meta"><span class="pill-glass gray"><i class="bi bi-calendar-week-fill"></i> Upcoming</span></div>
          </a>
        <?php endif; ?>
      </div>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>