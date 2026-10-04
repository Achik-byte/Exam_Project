<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$courseRes  = apiRequest('/courses', 'GET', null, $_SESSION['token']);
$subjectRes = apiRequest('/subjects', 'GET', null, $_SESSION['token']);
$courses    = $courseRes['body']['data'] ?? [];
$subjects   = $subjectRes['body']['data'] ?? [];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = apiRequest('/examinations', 'POST', [
        'course_id'  => $_POST['course_id'],
        'subject_id' => $_POST['subject_id'],
        'exam_date'  => $_POST['exam_date'],
        'start_time' => $_POST['start_time'],
        'end_time'   => $_POST['end_time'],
        'venue'      => $_POST['venue'],
        'status'     => $_POST['status']
    ], $_SESSION['token']);

    if ($response['status_code'] === 201) {
        header("Location: exams.php"); exit;
    } else {
        $error = $response['body']['message'] ?? 'Failed to create exam';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Exam</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
<h3>Add New Exam</h3>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST">
  <div class="mb-3"><label>Course</label>
    <select name="course_id" id="courseSelect" class="form-control" required>
      <option value="">-- Select Course --</option>
      <?php foreach ($courses as $c): ?>
        <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_code'].' - '.$c['course_title']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="mb-3"><label>Subject</label>
    <select name="subject_id" id="subjectSelect" class="form-control" required>
      <option value="">-- Select Course First --</option>
    </select>
  </div>
  <div class="mb-3"><label>Date</label>
    <input type="date" name="exam_date" class="form-control" required></div>
  <div class="mb-3"><label>Start Time</label>
    <input type="time" name="start_time" class="form-control" required></div>
  <div class="mb-3"><label>End Time</label>
    <input type="time" name="end_time" class="form-control" required></div>
  <div class="mb-3"><label>Venue</label>
    <input type="text" name="venue" class="form-control" required></div>
  <div class="mb-3"><label>Status</label>
    <select name="status" class="form-control" required>
      <option value="scheduled">Scheduled</option>
      <option value="completed">Completed</option>
      <option value="cancelled">Cancelled</option>
    </select>
  </div>
  <button type="submit" class="btn btn-primary">Save Exam</button>
  <a href="exams.php" class="btn btn-secondary">Cancel</a>
</form>

<script>
const allSubjects = <?= json_encode($subjects) ?>;

document.getElementById('courseSelect').addEventListener('change', function () {
    const courseId = this.value;
    const subjectSelect = document.getElementById('subjectSelect');
    subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';

    if (!courseId) {
        subjectSelect.innerHTML = '<option value="">-- Select Course First --</option>';
        return;
    }

    allSubjects
        .filter(s => String(s.course_id) === String(courseId))
        .forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.subject_id;
            opt.textContent = s.subject_name;
            subjectSelect.appendChild(opt);
        });
});
</script>
</body>
</html>