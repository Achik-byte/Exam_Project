<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: results.php"); exit; }

$res    = apiRequest('/results/' . $id, 'GET', null, $_SESSION['token']);
$result = $res['body']['data'] ?? null;
if (!$result) { die("Result not found or access denied."); }

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = apiRequest('/results/' . $id, 'PUT', [
        'marks' => $_POST['marks'],
        'grade' => $_POST['grade']
    ], $_SESSION['token']);
    if ($response['status_code'] === 200) { header("Location: results.php"); exit; }
    else $error = $response['body']['message'] ?? 'Failed to update';
}

$initial = strtoupper(substr($result['full_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Result · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
  <style>
    .wizard-wrap {
      display: grid; grid-template-columns: 1fr 360px;
      gap: 1.5rem; align-items: start;
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

    /* Grade picker */
    .grade-picker-glass {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 0.5rem;
    }
    @media (max-width: 480px) { .grade-picker-glass { grid-template-columns: repeat(3, 1fr); } }
    .grade-chip-glass {
      display: flex; align-items: center; justify-content: center;
      padding: 0.9rem 0.5rem;
      background: var(--glass-2);
      border: 1.5px solid var(--glass-line);
      border-radius: var(--r-md);
      cursor: pointer;
      font-family: 'Outfit', sans-serif;
      font-weight: 700;
      font-size: 1rem;
      color: var(--ink-2);
      transition: all .2s;
      user-select: none;
    }
    .grade-chip-glass:hover {
      border-color: var(--aurora-1);
      color: white;
      transform: translateY(-2px);
    }
    .grade-chip-glass.selected {
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      border-color: transparent;
      color: white;
      box-shadow: 0 8px 20px rgba(99, 102, 241, 0.45);
      transform: translateY(-2px);
    }

    /* Preview */
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
      background: var(--glass-2); border: 1px solid var(--glass-line);
      border-radius: var(--r-md); padding: 1.25rem;
    }
    .preview-panel .avatar {
      width: 56px; height: 56px; border-radius: 14px;
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      display: flex; align-items: center; justify-content: center;
      font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800;
      color: white; margin-bottom: 0.85rem;
      box-shadow: 0 8px 24px rgba(99,102,241,0.4);
    }
    .preview-panel .preview-card .name {
      font-family: 'Outfit', sans-serif; font-size: 1.05rem; font-weight: 700;
      color: white; margin-bottom: 0.35rem; line-height: 1.3;
    }
    .preview-panel .preview-card .course {
      font-size: 0.8rem; color: var(--ink-3); margin-bottom: 1rem;
    }
    .preview-panel .preview-card .score {
      display: flex; justify-content: space-between; align-items: flex-end;
      padding: 1rem; background: rgba(0,0,0,0.2);
      border-radius: var(--r-md); border: 1px solid var(--glass-line);
    }
    .preview-panel .preview-card .score .val {
      font-family: 'Outfit', sans-serif;
      font-size: 2rem; font-weight: 800;
      color: white; line-height: 1;
    }
    .preview-panel .preview-card .score .val-lbl {
      font-size: 0.65rem; text-transform: uppercase;
      letter-spacing: 0.1em; color: var(--ink-3);
      font-weight: 700; margin-bottom: 0.25rem;
    }
    .preview-panel .preview-card .score .grade-badge {
      padding: 0.6rem 1rem;
      border-radius: var(--r-md);
      font-family: 'Outfit', sans-serif;
      font-size: 1.3rem; font-weight: 800;
      color: white; line-height: 1;
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
    }
    .preview-panel .preview-card .score .grade-badge.pending {
      background: rgba(139,146,164,0.3);
      box-shadow: none;
      color: var(--ink-3);
      font-size: 0.85rem;
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass" style="max-width:1080px;">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ Grade Update</span>
      <h1>Update <em>result</em></h1>
      <p class="sub">Adjust the marks and grade for this student.</p>
    </div>
    <a href="results.php" class="btn-glass"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-glass danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <div class="steps-nav">
    <div class="step-pill active"><span class="num">1</span> Student (locked)</div>
    <div class="step-pill active"><span class="num">2</span> Marks</div>
    <div class="step-pill active"><span class="num">3</span> Grade</div>
  </div>

  <div class="wizard-wrap">

    <!-- FORM -->
    <div class="form-panel">
      <form method="POST" id="resultForm">

        <!-- LOCKED STUDENT BANNER -->
        <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.3);border-radius:var(--r-md);margin-bottom:2rem;">
          <i class="bi bi-lock-fill" style="color:#a5b4fc;font-size:1.3rem;"></i>
          <div style="flex:1;">
            <div style="font-weight:700;color:white;font-size:0.95rem;"><?= htmlspecialchars($result['full_name']) ?></div>
            <div style="color:var(--ink-3);font-size:0.78rem;">
              <?= htmlspecialchars($result['course_code']) ?> · <?= htmlspecialchars($result['course_title']) ?> · Cannot be changed
            </div>
          </div>
          <span class="pill-glass blue" style="font-size:0.7rem;">
            <i class="bi bi-hash"></i> <?= htmlspecialchars($result['result_id']) ?>
          </span>
        </div>

        <div class="panel-title"><i class="bi bi-123"></i> Marks</div>
        <div class="field-glass" style="margin-bottom:2rem;">
          <label><i class="bi bi-123"></i> Marks (0–100) <span class="req">*</span></label>
          <input type="number" step="0.01" min="0" max="100" name="marks" id="marks" class="input-glass"
                 value="<?= htmlspecialchars($result['marks']) ?>" required
                 style="font-size:1.3rem;font-weight:700;">
          <div class="hint"><i class="bi bi-info-circle"></i> Marks will affect grade point automatically.</div>
        </div>

        <div class="panel-title"><i class="bi bi-trophy-fill"></i> Grade</div>
        <div class="field-glass">
          <label><i class="bi bi-trophy-fill"></i> Select Grade <span class="req">*</span></label>
          <div class="grade-picker-glass">
            <?php foreach (['A','A-','B+','B','B-','C+','C','D','F'] as $g):
              $sel = ($result['grade'] ?? '') === $g;
            ?>
              <label class="grade-chip-glass <?= $sel ? 'selected' : '' ?>" data-grade="<?= $g ?>">
                <input type="radio" name="grade" value="<?= $g ?>" <?= $sel ? 'checked' : '' ?> required style="display:none;">
                <span><?= $g ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-actions" style="margin-top:2rem;">
          <a href="results.php" class="btn-glass"><i class="bi bi-x-lg"></i> Cancel</a>
          <button type="submit" class="btn-glass primary"><i class="bi bi-check-circle-fill"></i> Update Result</button>
        </div>
      </form>
    </div>

    <!-- PREVIEW -->
    <div class="preview-sticky">
      <div class="preview-panel">
        <div class="label">◆ Live Preview</div>
        <div class="preview-card">
          <div class="avatar"><?= $initial ?></div>
          <div class="name"><?= htmlspecialchars($result['full_name']) ?></div>
          <div class="course"><?= htmlspecialchars($result['course_code']) ?></div>
          <div class="score">
            <div>
              <div class="val-lbl">Marks</div>
              <div class="val" id="previewMarks"><?= htmlspecialchars($result['marks'] ?? '0') ?></div>
            </div>
            <div>
              <div class="val-lbl" style="text-align:right;">Grade</div>
              <div class="grade-badge <?= empty($result['grade'])?'pending':'' ?>" id="previewGrade">
                <?= !empty($result['grade']) ? htmlspecialchars($result['grade']) : '—' ?>
              </div>
            </div>
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
// Marks live preview
document.getElementById('marks').addEventListener('input', function() {
    document.getElementById('previewMarks').textContent = this.value || '0';
});

// Grade picker
document.querySelectorAll('.grade-chip-glass').forEach(chip => {
    chip.addEventListener('click', () => {
        document.querySelectorAll('.grade-chip-glass').forEach(c => c.classList.remove('selected'));
        chip.classList.add('selected');
        chip.querySelector('input').checked = true;

        const g = chip.dataset.grade;
        const el = document.getElementById('previewGrade');
        el.className = 'grade-badge';
        el.textContent = g;
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>