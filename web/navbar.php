<?php 
$role = $_SESSION['role'] ?? ''; 
$current = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar navbar-expand-lg navbar-modern">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
      <span class="brand-icon">📚</span>
      ExamSys
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?= $current === 'index' ? 'active' : '' ?>" href="index.php">
            <i class="bi bi-house-door-fill"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current === 'courses' ? 'active' : '' ?>" href="courses.php">
            <i class="bi bi-book-fill"></i> Courses
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current === 'exams' || $current === 'exam_add' || $current === 'exam_edit' ? 'active' : '' ?>" href="exams.php">
            <i class="bi bi-calendar-event-fill"></i> Exams
          </a>
        </li>
        <?php if ($role === 'student'): ?>
          <li class="nav-item">
            <a class="nav-link <?= $current === 'my_subjects' ? 'active' : '' ?>" href="my_subjects.php">
              <i class="bi bi-journal-text"></i> My Subjects
            </a>
          </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link <?= $current === 'results' || $current === 'result_edit' ? 'active' : '' ?>" href="results.php">
            <i class="bi bi-bar-chart-fill"></i> Results
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current === 'notifications' ? 'active' : '' ?>" href="notifications.php">
            <i class="bi bi-bell-fill"></i> Notifications
          </a>
        </li>
        <?php if ($role === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link <?= $current === 'users' || $current === 'user_add' || $current === 'user_edit' ? 'active' : '' ?>" href="users.php">
              <i class="bi bi-people-fill"></i> Users
            </a>
          </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link" href="logout.php" style="color: #fca5a5 !important;">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>