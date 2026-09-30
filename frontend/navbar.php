<?php $role = $_SESSION['role'] ?? ''; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">ExamSys</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
        <li class="nav-item"><a class="nav-link" href="exams.php">Exams</a></li>
        <?php if ($role === 'student'): ?>
          <li class="nav-item"><a class="nav-link" href="my_subjects.php">My Subjects</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="results.php">Results</a></li>
        <li class="nav-item"><a class="nav-link" href="notifications.php">Notifications</a></li>
        <?php if ($role === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>