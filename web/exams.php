<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) { header("Location: login.php"); exit; }
require_once __DIR__ . '/config.php';
$role = $_SESSION['role'];
$response = apiRequest('/examinations', 'GET', null, $_SESSION['token']);
$exams    = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];

// Susun ikut tarikh
usort($exams, function($a, $b) {
    return strcmp($a['exam_date'] ?? '', $b['exam_date'] ?? '');
});

// Group ikut tarikh
$grouped = [];
foreach ($exams as $e) {
    $key = $e['exam_date'] ?? 'No Date';
    $grouped[$key][] = $e;
}

function statusPill($s) {
  $map = ['scheduled'=>'blue', 'completed'=>'green', 'cancelled'=>'red'];
  $t = $map[strtolower($s)] ?? 'gray';
  return "<span class='pill-glass $t'><span class='dot'></span> ".htmlspecialchars($s)."</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Exams · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
  <style>
    /* === DATE GROUP === */
    .date-group {
      display: grid;
      grid-template-columns: 90px 1fr;
      gap: 1.5rem;
      margin-bottom: 2rem;
      align-items: start;
    }
    .date-label {
      text-align: right;
      padding-top: 0.5rem;
      position: sticky;
      top: 100px;
    }
    .date-label .day {
      font-family: 'Outfit', sans-serif;
      font-size: 2.2rem;
      font-weight: 800;
      line-height: 1;
      color: white;
      margin-bottom: 0.15rem;
    }
    .date-label .month {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.15em;
      color: var(--ink-3);
      font-weight: 700;
    }
    .date-label .weekday {
      font-size: 0.7rem;
      color: var(--ink-3);
      margin-top: 0.15rem;
    }

    /* === EXAM CARD === */
    .exam-cards {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      position: relative;
    }
    .exam-cards::before {
      content: '';
      position: absolute;
      left: -1.5rem; top: 2rem; bottom: 0;
      width: 2px;
      background: linear-gradient(180deg, rgba(99,102,241,0.6), rgba(99,102,241,0.05));
    }
    .exam-card {
      background: var(--glass);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-line);
      border-radius: var(--r-lg);
      padding: 1.5rem;
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 1.5rem;
      align-items: center;
      transition: all .25s;
      position: relative;
    }
    .exam-card::before {
      content: '';
      position: absolute;
      left: -1.9rem; top: 2rem;
      width: 14px; height: 14px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--aurora-1), var(--aurora-2));
      border: 3px solid #0f0f1e;
      box-shadow: 0 0 0 3px rgba(99,102,241,0.3);
    }
    .exam-card:hover {
      border-color: var(--glass-line-2);
      background: var(--glass-2);
      transform: translateX(4px);
      box-shadow: 0 8px 32px rgba(99, 102, 241, 0.2);
    }
    .exam-card .subject-name {
      font-family: 'Outfit', sans-serif;
      font-size: 1.15rem;
      font-weight: 700;
      color: white;
      margin-bottom: 0.5rem;
      line-height: 1.3;
    }
    .exam-card .meta-row {
      display: flex;
      flex-wrap: wrap;
      gap: 1.25rem;
      font-size: 0.85rem;
      color: var(--ink-3);
    }
    .exam-card .meta-row .item {
      display: flex; align-items: center; gap: 0.4rem;
    }
    .exam-card .meta-row .item i { color: #a5b4fc; }
    .exam-card .meta-row .item strong {
      color: white; font-weight: 600;
    }
    .exam-card .right-col {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 0.75rem;
      min-width: 140px;
    }
    .exam-card .time-badge {
      font-family: 'SF Mono', monospace;
      font-size: 0.88rem;
      font-weight: 700;
      color: white;
      background: rgba(99,102,241,0.2);
      border: 1px solid rgba(99,102,241,0.4);
      border-radius: 8px;
      padding: 0.5rem 0.85rem;
      white-space: nowrap;
    }
    .exam-card .venue-badge {
      font-size: 0.78rem;
      color: var(--ink-2);
      display: flex;
      align-items: center;
      gap: 0.35rem;
      text-align: right;
    }
    .exam-card .venue-badge i { color: #f9a8d4; }
    .exam-card .actions {
      display: flex;
      gap: 0.4rem;
      margin-top: 0.25rem;
    }

    @media (max-width: 720px) {
      .date-group { grid-template-columns: 1fr; gap: 1rem; }
      .date-label { text-align: left; position: static; }
      .date-label .day { font-size: 1.5rem; }
      .exam-cards::before { display: none; }
      .exam-card { grid-template-columns: 1fr; }
      .exam-card::before { display: none; }
      .exam-card .right-col { align-items: flex-start; }
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ Schedule</span>
      <h1>Exam <em>timeline</em></h1>
      <p class="sub">
        <?php
          if ($role === 'admin')        echo "Full exam control — sorted by date.";
          elseif ($role === 'lecturer') echo "View only. Sorted by date.";
          else                          echo "Exams for subjects you are enrolled in.";
        ?>
      </p>
    </div>
    <?php if ($role === 'admin'): ?>
      <a href="exam_add.php" class="btn-glass primary"><i class="bi bi-plus-lg"></i> New Exam</a>
    <?php endif; ?>
  </div>

  <?php if (empty($exams)): ?>
    <div class="empty-glass">
      <div class="icon"><i class="bi bi-calendar-x"></i></div>
      <h4>No exams scheduled</h4>
      <p>No exams have been added yet.</p>
    </div>
  <?php else: ?>

    <?php foreach ($grouped as $date => $items):
      $ts = strtotime($date);
      $day = date('d', $ts);
      $month = date('M', $ts);
      $weekday = date('D', $ts);
    ?>
      <div class="date-group">
        <div class="date-label">
          <div class="day"><?= $day ?></div>
          <div class="month"><?= $month ?></div>
          <div class="weekday"><?= $weekday ?></div>
        </div>
        <div class="exam-cards">
          <?php foreach ($items as $e): ?>
            <div class="exam-card">
              <div>
                <div class="subject-name"><?= htmlspecialchars($e['subject_name'] ?? '—') ?></div>
                <div class="meta-row">
                  <div class="item"><i class="bi bi-collection-fill"></i> <strong><?= htmlspecialchars($e['course_code']) ?></strong></div>
                  <div class="item"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($e['venue']) ?></div>
                  <?= statusPill($e['status']) ?>
                </div>
              </div>
              <div class="right-col">
                <div class="time-badge">
                  <i class="bi bi-clock-fill"></i>
                  <?= htmlspecialchars(substr($e['start_time'],0,5)) ?>–<?= htmlspecialchars(substr($e['end_time'],0,5)) ?>
                </div>
                <?php if ($role === 'admin'): ?>
                  <div class="actions">
                    <a href="exam_edit.php?id=<?= $e['exam_id'] ?>" class="btn-glass sm" title="Edit">
                      <i class="bi bi-pencil-fill"></i>
                    </a>
                    <a href="exam_delete.php?id=<?= $e['exam_id'] ?>" class="btn-glass sm red" title="Delete" onclick="return confirm('Delete this exam?')">
                      <i class="bi bi-trash3-fill"></i>
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

  <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>