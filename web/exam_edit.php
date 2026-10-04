<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: exams.php"); exit; }

$examRes    = apiRequest('/examinations/' . $id, 'GET', null, $_SESSION['token']);
$exam       = $examRes['body']['data'] ?? null;
$courseRes  = apiRequest('/courses', 'GET', null, $_SESSION['token']);
$subjectRes = apiRequest('/subjects', 'GET', null, $_SESSION['token']);
$courses    = $courseRes['body']['data'] ?? [];
$subjects   = $subjectRes['body']['data'] ?? [];

if (!$exam) { die("Exam not found"); }
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = apiRequest('/examinations/' . $id, 'PUT', [
        'course_id'  => $_POST['course_id'],
        'subject_id' => $_POST['subject_id'],
        'exam_date'  => $_POST['exam_date'],
        'start_time' => $_POST['start_time'],
        'end_time'   => $_POST['end_time'],
        'venue'      => $_POST['venue'],
        'status'     => $_POST['status']
    ], $_SESSION['token']);

    if ($response['status_code'] === 200) {
        header("Location: exams.php"); exit;
    } else {
        $error = $response['body']['message'] ?? 'Failed to update';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Exam - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern" style="max-width: 800px;">

  <div class="page-header">
    <div>
      <h1>Edit Exam</h1>
      <p class="subtitle">Update the examination details.</p>
    </div>
    <a href="exams.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card-modern">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label"><i class="bi bi-book-fill"></i> Course</label>
        <select name="course_id" id="courseSelect" class="form-select" required>
          <option value="">-- Select Course --</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= $c['course_id'] ?>" <?= ($exam['course_id'] == $c['course_id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['course_code'].' - '.$c['course_title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-journal-text"></i> Subject</label>
        <select name="subject_id" id="subjectSelect" class="form-select" required>
          <option value="">-- Select Subject --</option>
          <?php foreach ($subjects as $s): ?>
            <?php if ($s['course_id'] == $exam['course_id']): ?>
              <option value="<?= $s['subject_id'] ?>" <?= ($exam['subject_id'] ?? '') == $s['subject_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['subject_name']) ?>
              </option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label"><i class="bi bi-calendar3"></i> Exam Date</label>
          <input type="date" name="exam_date" class="form-control" value="<?= htmlspecialchars($exam['exam_date']) ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label"><i class="bi bi-clock-fill"></i> Start Time</label>
          <input type="time" name="start_time" class="form-control" value="<?= htmlspecialchars($exam['start_time']) ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label"><i class="bi bi-clock-history"></i> End Time</label>
          <input type="time" name="end_time" class="form-control" value="<?= htmlspecialchars($exam['end_time']) ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-geo-alt-fill"></i> Venue</label>
        <input type="text" name="venue" class="form-control" value="<?= htmlspecialchars($exam['venue']) ?>" required>
      </div>

      <div class="mb-4">
        <label class="form-label"><i class="bi bi-info-circle-fill"></i> Status</label>
        <select name="status" class="form-select" required>
          <?php foreach (['scheduled','completed','cancelled'] as $st): ?>
            <option value="<?= $st ?>" <?= $exam['status']==$st?'selected':'' ?>><?= ucfirst($st) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display:flex;gap:0.75rem;">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Exam</button>
        <a href="exams.php" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Cancel</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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