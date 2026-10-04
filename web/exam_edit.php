<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: exams.php"); exit; }

$res  = apiRequest('/examinations/' . $id, 'GET', null, $_SESSION['token']);
$exam = $res['body']['data'] ?? null;
if (!$exam) { die("Exam not found."); }

$subjectRes = apiRequest('/subjects?course_id=' . $exam['course_id'], 'GET', null, $_SESSION['token']);
$subjects   = $subjectRes['body']['data'] ?? [];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = apiRequest('/examinations/' . $id, 'PUT', [
        'subject_id' => $_POST['subject_id'],
        'exam_date'  => $_POST['exam_date'],
        'start_time' => $_POST['start_time'],
        'end_time'   => $_POST['end_time'],
        'venue'      => $_POST['venue'],
        'status'     => $_POST['status']
    ], $_SESSION['token']);
    if ($response['status_code'] === 200) { header("Location: exams.php"); exit; }
    else $error = $response['body']['message'] ?? 'Failed to update';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Exam · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
  <style>
    .wizard-wrap {
      display: grid;
      grid-template-columns: 1fr 360px;
      gap: 1.5rem;
      align-items: start;
    }
    @media (max-width: 960px) { .wizard-wrap { grid-template-columns: 1fr; } }
    .steps-nav { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .step-pill {
      display: inline-flex; align-items: center; gap: 0.5rem;
      padding: 0.5rem 1rem; border-radius: 999px;
      background: var(--glass); border: 1px solid var(--glass-line);
      color: var(--ink-3); font-size: 0.78rem; font-weight: 600;
      text-transform: uppercase; letter-spacing: 0.05em;
    }
    .step-pill .num {
      width: 20px; height: 20px; border-radius: 50%;
      background: rgba(255,255,255,0.1);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.7rem;
    }
    .step-pill.active {
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      border-color: transparent; color: white;
      box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
    }
    .step-pill.active .num { background: rgba(255,255,255,0.25); }
    .form-panel {
      background: var(--glass); backdrop-filter: blur(20px);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-lg); padding: 2rem;
    }
    .panel-title {
      font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700;
      color: white; margin-bottom: 1.25rem; padding-bottom: 1rem;
      border-bottom: 1px solid var(--glass-line);
      display: flex; align-items: center; gap: 0.6rem;
    }
    .panel-title i { color: #a5b4fc; }
    .preview-sticky { position: sticky; top: 100px; }
    .preview-panel {
      background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(168,85,247,0.1));
      border: 1px solid var(--glass-line-2);
      border-radius: var(--r-lg); padding: 1.5rem;
    }
    .preview-panel .label {
      font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.15em;
      color: var(--ink-3); font-weight: 700; margin-bottom: 0.75rem;
    }
    .preview-panel .preview-card {
      background: var(--glass-2);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-md); padding: 1.25rem;
    }
    .preview-panel .preview-card .subject {
      font-family: 'Outfit', sans-serif; font-size: 1.05rem; font-weight: 700;
      color: white; margin-bottom: 0.5rem; line-height: 1.3; min-height: 1.35em;
    }
    .preview-panel .preview-card .meta {
      display: flex; flex-direction: column; gap: 0.5rem;
      font-size: 0.82rem; color: var(--ink-2);
      margin-top: 0.75rem; padding-top: 0.75rem;
      border-top: 1px solid var(--glass-line);
    }
    .preview-panel .preview-card .meta-row {
      display: flex; align-items: center; gap: 0.5rem;
    }
    .preview-panel .preview-card .meta-row i { color: #a5b4fc; width: 16px; }
    .locked-banner {
      display: flex; align-items: center; gap: 1rem;
      padding: 1rem 1.25rem;
      background: rgba(245,158,11,0.12);
      border: 1px solid rgba(245,158,11,0.3);
      border-radius: var(--r-md);
      margin-bottom: 1.5rem;
    }
    .locked-banner i { color: #fcd34d; font-size: 1.3rem; }
    .locked-banner .title { font-weight: 700; color: white; font-size: 0.95rem; }
    .locked-banner .sub { color: var(--ink-3); font-size: 0.78rem; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass" style="max-width:1080px;">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ Edit Entry</span>
      <h1>Edit <em>exam</em></h1>
      <p class="sub">Update the details of this examination.</p>
    </div>
    <a href="exams.php" class="btn-glass"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-glass danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <div class="steps-nav">
    <div class="step-pill"><span class="num">1</span> Course (locked)</div>
    <div class="step-pill active"><span class="num">2</span> Subject</div>
    <div class="step-pill active"><span class="num">3</span> Date & Details</div>
  </div>

  <div class="wizard-wrap">

    <!-- FORM (KIRI) -->
    <div class="form-panel">

      <!-- LOCKED COURSE -->
      <div class="locked-banner">
        <i class="bi bi-lock-fill"></i>
        <div style="flex:1;">
          <div class="title"><?= htmlspecialchars($exam['course_code']) ?></div>
          <div class="sub"><?= htmlspecialchars($exam['course_title']) ?> · Course cannot be changed</div>
        </div>
      </div>

      <form method="POST" id="examForm">

        <div class="panel-title"><i class="bi bi-bookmark-fill"></i> Subject</div>
        <div class="field-glass" style="margin-bottom:2rem;">
          <label><i class="bi bi-bookmark-fill"></i> Subject <span class="req">*</span></label>
          <select name="subject_id" id="subjectSelect" class="select-glass" required>
            <?php foreach ($subjects as $s): ?>
              <option value="<?= $s['subject_id'] ?>" <?= $exam['subject_id']==$s['subject_id']?'selected':'' ?>>
                <?= htmlspecialchars($s['subject_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="panel-title"><i class="bi bi-clock-fill"></i> Date & Time</div>
        <div class="field-glass">
          <label><i class="bi bi-calendar-event-fill"></i> Exam Date <span class="req">*</span></label>
          <input type="date" name="exam_date" id="examDate" class="input-glass" value="<?= htmlspecialchars($exam['exam_date']) ?>" required>
        </div>
        <div class="grid-2" style="margin-bottom:2rem;">
          <div class="field-glass">
            <label><i class="bi bi-play-circle-fill"></i> Start Time <span class="req">*</span></label>
            <input type="time" name="start_time" id="startTime" class="input-glass" value="<?= htmlspecialchars($exam['start_time']) ?>" required>
          </div>
          <div class="field-glass">
            <label><i class="bi bi-stop-circle-fill"></i> End Time <span class="req">*</span></label>
            <input type="time" name="end_time" id="endTime" class="input-glass" value="<?= htmlspecialchars($exam['end_time']) ?>" required>
          </div>
        </div>

        <div class="panel-title"><i class="bi bi-geo-alt-fill"></i> Location & Status</div>
        <div class="grid-2">
          <div class="field-glass">
            <label><i class="bi bi-geo-alt-fill"></i> Venue <span class="req">*</span></label>
            <input type="text" name="venue" id="venue" class="input-glass" value="<?= htmlspecialchars($exam['venue']) ?>" required>
          </div>
          <div class="field-glass">
            <label><i class="bi bi-flag-fill"></i> Status <span class="req">*</span></label>
            <select name="status" id="status" class="select-glass" required>
              <?php foreach (['scheduled'=>'🕒 Scheduled','completed'=>'✓ Completed','cancelled'=>'✕ Cancelled'] as $s => $lbl): ?>
                <option value="<?= $s ?>" <?= $exam['status']==$s?'selected':'' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-actions" style="margin-top:2rem;">
          <a href="exams.php" class="btn-glass"><i class="bi bi-x-lg"></i> Cancel</a>
          <button type="submit" class="btn-glass primary"><i class="bi bi-check-circle-fill"></i> Update Exam</button>
        </div>
      </form>
    </div>

    <!-- PREVIEW (KANAN) -->
    <div class="preview-sticky">
      <div class="preview-panel">
        <div class="label">◆ Live Preview</div>
        <div class="preview-card">
          <div class="subject" id="previewSubject"><?= htmlspecialchars($exam['subject_name'] ?? 'Subject Name') ?></div>
          <span class="pill-glass <?= $exam['status']==='completed'?'green':($exam['status']==='cancelled'?'red':'blue') ?>" id="previewStatus" style="font-size:0.7rem;padding:0.3rem 0.65rem;">
            <span class="dot"></span> <?= ucfirst(htmlspecialchars($exam['status'])) ?>
          </span>
          <div class="meta">
            <div class="meta-row"><i class="bi bi-collection-fill"></i> <span><?= htmlspecialchars($exam['course_code']) ?></span></div>
            <div class="meta-row"><i class="bi bi-calendar-event-fill"></i> <span id="previewDate"><?= htmlspecialchars($exam['exam_date']) ?></span></div>
            <div class="meta-row"><i class="bi bi-clock-fill"></i> <span id="previewTime"><?= htmlspecialchars(substr($exam['start_time'],0,5)) ?>–<?= htmlspecialchars(substr($exam['end_time'],0,5)) ?></span></div>
            <div class="meta-row"><i class="bi bi-geo-alt-fill"></i> <span id="previewVenue"><?= htmlspecialchars($exam['venue']) ?></span></div>
          </div>
        </div>
        <p style="color:var(--ink-3);font-size:0.78rem;margin:1rem 0 0 0;text-align:center;">
          Preview updates as you edit.
        </p>
      </div>
    </div>

  </div>
</div>

<script>
document.getElementById('subjectSelect').addEventListener('change', function() {
    document.getElementById('previewSubject').textContent = this.options[this.selectedIndex]?.text || 'Subject Name';
});
document.getElementById('examDate').addEventListener('change', function() {
    document.getElementById('previewDate').textContent = this.value || '—';
});
['startTime','endTime'].forEach(id => {
    document.getElementById(id).addEventListener('change', updateTime);
});
function updateTime() {
    const s = document.getElementById('startTime').value;
    const e = document.getElementById('endTime').value;
    document.getElementById('previewTime').textContent = (s && e) ? (s + '–' + e) : '—';
}
document.getElementById('venue').addEventListener('input', function() {
    document.getElementById('previewVenue').textContent = this.value || '—';
});
document.getElementById('status').addEventListener('change', function() {
    const status = this.value;
    const map = { scheduled:'blue', completed:'green', cancelled:'red' };
    const el = document.getElementById('previewStatus');
    el.className = 'pill-glass ' + (map[status] || 'gray');
    el.innerHTML = '<span class="dot"></span> ' + status.charAt(0).toUpperCase() + status.slice(1);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>