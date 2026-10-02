<?php 
$role = $_SESSION['role'] ?? ''; 
$current = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar navbar-expand-lg navbar-nb">
  <div class="container-fluid px-4">
    <a class="navbar-brand brand-nb" href="index.php">
      <span class="mark">E</span>
      Exam<span class="amp">Sys</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-nb <?= $current==='index'?'active':'' ?>" href="index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-nb <?= $current==='courses'?'active':'' ?>" href="courses.php"><i class="bi bi-book-fill"></i> Courses</a></li>
        <li class="nav-item"><a class="nav-nb <?= $current==='exams'?'active':'' ?>" href="exams.php"><i class="bi bi-calendar-event-fill"></i> Exams</a></li>
        <?php if ($role === 'student'): ?>
          <li class="nav-item"><a class="nav-nb <?= $current==='my_subjects'?'active':'' ?>" href="my_subjects.php"><i class="bi bi-journal-bookmark-fill"></i> Subjects</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-nb <?= $current==='results'?'active':'' ?>" href="results.php"><i class="bi bi-bar-chart-fill"></i> Results</a></li>
        <li class="nav-item"><a class="nav-nb <?= $current==='notifications'?'active':'' ?>" href="notifications.php"><i class="bi bi-bell-fill"></i> Inbox</a></li>
        <?php if ($role === 'admin'): ?>
          <li class="nav-item"><a class="nav-nb <?= $current==='users'?'active':'' ?>" href="users.php"><i class="bi bi-people-fill"></i> Users</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>