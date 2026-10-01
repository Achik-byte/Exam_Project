<?php 
$role = $_SESSION['role'] ?? ''; 
$current = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar navbar-expand-lg navbar-lum">
  <div class="container-fluid px-4">
    <a class="navbar-brand brand-lum" href="index.php">
      <span class="brand-mark">◈</span>
      Exam<span class="amp">Flow</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto gap-1">
        <li class="nav-item"><a class="nav-lum <?= $current==='index'?'active':'' ?>" href="index.php"><i class="bi bi-house-door-fill"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-lum <?= $current==='courses'?'active':'' ?>" href="courses.php"><i class="bi bi-book-fill"></i> Courses</a></li>
        <li class="nav-item"><a class="nav-lum <?= $current==='exams'?'active':'' ?>" href="exams.php"><i class="bi bi-calendar-event-fill"></i> Exams</a></li>
        <?php if ($role === 'student'): ?>
          <li class="nav-item"><a class="nav-lum <?= $current==='my_subjects'?'active':'' ?>" href="my_subjects.php"><i class="bi bi-journal-bookmark-fill"></i> Subjects</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-lum <?= $current==='results'?'active':'' ?>" href="results.php"><i class="bi bi-graph-up-arrow"></i> Results</a></li>
        <li class="nav-item"><a class="nav-lum <?= $current==='notifications'?'active':'' ?>" href="notifications.php"><i class="bi bi-bell-fill"></i> Inbox</a></li>
        <?php if ($role === 'admin'): ?>
          <li class="nav-item"><a class="nav-lum <?= $current==='users'?'active':'' ?>" href="users.php"><i class="bi bi-people-fill"></i> Users</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>