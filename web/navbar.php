<?php 
$role = $_SESSION['role'] ?? ''; 
$current = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar navbar-expand-lg navbar-modern">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="index.php">
      <span class="brand-icon">📚</span>
      ExamSys
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link <?= $current === 'index' ? 'active' : '' ?>" href="index.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $current === 'courses' ? 'active' : '' ?>" href="courses.php"><i class="bi bi-book"></i> Courses</a></li>
        <li class="nav-item"><a class="nav-link <?= $current === 'exams' ? 'active' : '' ?>" href="exams.php"><i class="bi bi-calendar-event"></i> Exams</a></li>
        <?php if ($role === 'student'): ?>
          <li class="nav-item"><a class="nav-link <?= $current === 'my_subjects' ? 'active' : '' ?>" href="my_subjects.php"><i class="bi bi-journal-text"></i> My Subjects</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link <?= $current === 'results' ? 'active' : '' ?>" href="results.php"><i class="bi bi-bar-chart"></i> Results</a></li>
        <li class="nav-item"><a class="nav-link <?= $current === 'notifications' ? 'active' : '' ?>" href="notifications.php"><i class="bi bi-bell"></i> Notifications</a></li>
        <?php if ($role === 'admin'): ?>
          <li class="nav-item"><a class="nav-link <?= $current === 'users' ? 'active' : '' ?>" href="users.php"><i class="bi bi-people"></i> Users</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>