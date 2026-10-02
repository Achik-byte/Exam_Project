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
    .grade-picker-glass {
      display: grid;
      grid-template-columns: repeat(9, 1fr);
      gap: 0.5rem;
      margin-top: 0.5rem;
    }
    @media (max-width: 720px) { .grade-picker-glass { grid-template-columns: repeat(5, 1fr); } }
    @media (max-width: 400px) { .grade-picker-glass { grid-template-columns: repeat(3, 1fr); } }

    .grade-chip-glass {
      display: flex; align-items: center; justify-content: center;
      padding: 0.85rem 0.5rem;
      background: var(--glass-2);
      border: 1.5px solid var(--glass-line);
      border-radius: var(--r-md);
      cursor: pointer;
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
      background: var(--glass-3);
    }
    .grade-chip-glass.selected {
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      border-color: transparent;
      color: white;
      box-shadow: 0 8px 20px rgba(99, 102, 241, 0.45);
      transform: translateY(-2px);
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass" style="max-width:820px;">

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

  <!-- STUDENT CARD -->
  <div class="card-glass" style="margin-bottom:1.25rem;">
    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
      <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,var(--aurora-1),var(--aurora-2));display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:white;flex-shrink:0;">
        <?= $initial ?>
      </div>
      <div style="flex:1;min-width:200px;">
        <div style="font-weight:700;color:white;font-size:1.05rem;"><?= htmlspecialchars($result['full_name']) ?></div>
        <div style="color:var(--ink-3);font-size:0.85rem;margin-top:0.15rem;">
          <i class="bi bi-collection-fill"></i> <?= htmlspecialchars($result['course_code']) ?> · <?= htmlspecialchars($result['course_title']) ?>
        </div>
      </div>
      <span class="pill-glass blue" style="padding:0.5rem 0.9rem;font-size:0.8rem;">
        <i class="bi bi-hash"></i> ID <?= htmlspecialchars($result['result_id']) ?>
      </span>
    </div>
  </div>

  <!-- FORM -->
  <div class="card-glass">
    <form method="POST">

      <div class="form-section">
        <div class="form-section-head">
          <div class="form-section-icon" style="color:#d8b4fe;"><i class="bi bi-award-fill"></i></div>
          <div>
            <h3 class="form-section-title">Grading</h3>
            <p class="form-section-desc">Enter the student's marks and assign a grade.</p>
          </div>
        </div>

        <div class="field-glass">
          <label><i class="bi bi-123"></i> Marks (0–100) <span class="req">*</span></label>
          <input type="number" step="0.01" min="0" max="100" name="marks" class="input-glass"
                 value="<?= htmlspecialchars($result['marks']) ?>" required
                 style="font-size:1.15rem;font-weight:700;">
          <div class="hint"><i class="bi bi-info-circle"></i> Marks akan affect grade point automatically.</div>
        </div>

        <div class="field-glass">
          <label><i class="bi bi-trophy-fill"></i> Grade <span class="req">*</span></label>
          <div class="grade-picker-glass">
            <?php foreach (['A','A-','B+','B','B-','C+','C','D','F'] as $g):
              $sel = ($result['grade'] ?? '') === $g;
            ?>
              <label class="grade-chip-glass <?= $sel ? 'selected' : '' ?>">
                <input type="radio" name="grade" value="<?= $g ?>" <?= $sel ? 'checked' : '' ?> required style="display:none;">
                <span><?= $g ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <a href="results.php" class="btn-glass"><i class="bi bi-x-lg"></i> Cancel</a>
        <button type="submit" class="btn-glass primary"><i class="bi bi-check-circle-fill"></i> Update Result</button>
      </div>
    </form>
  </div>

</div>

<script>
document.querySelectorAll('.grade-chip-glass').forEach(chip => {
    chip.addEventListener('click', () => {
        document.querySelectorAll('.grade-chip-glass').forEach(c => c.classList.remove('selected'));
        chip.classList.add('selected');
        chip.querySelector('input').checked = true;
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>