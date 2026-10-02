<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];

// ===== Handle POST =====
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST['mark_read_id'])) {
        apiRequest('/notifications/' . $_POST['mark_read_id'], 'PUT', [
            'is_read' => 1
        ], $_SESSION['token']);
        header("Location: notifications.php"); exit;
    }

    if (isset($_POST['create_notification'])) {
        $payload = [
            'title'   => $_POST['title'],
            'message' => $_POST['message']
        ];

        if (isset($_POST['send_to_all']) && $_POST['send_to_all'] == '1') {
            $payload['to_all'] = 1;
        } else {
            $payload['user_id'] = $_POST['user_id'];
        }

        $response = apiRequest('/notifications', 'POST', $payload, $_SESSION['token']);

        if ($response['status_code'] === 201) {
            header("Location: notifications.php"); exit;
        } else {
            $error = $response['body']['message'] ?? 'Failed to create notification';
        }
    }
}

// ===== Ambil notifications =====
$response      = apiRequest('/notifications', 'GET', null, $_SESSION['token']);
$notifications = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];

// ===== Ambil senarai students (admin/lecturer sahaja) =====
$students = [];
if ($role === 'admin' || $role === 'lecturer') {
    $studentRes = apiRequest('/students', 'GET', null, $_SESSION['token']);
    $students   = ($studentRes['status_code'] === 200) ? ($studentRes['body']['data'] ?? []) : [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notifications - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern">
  <div class="page-header">
    <div>
      <h1>Notifications</h1>
      <p class="subtitle">
        <?php
          if ($role === 'student')       echo "Your notifications.";
          elseif ($role === 'lecturer')  echo "Send notifications to your students.";
          else                           echo "Send notifications to students and lecturers.";
        ?>
      </p>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert-modern danger">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <div><?= htmlspecialchars($error) ?></div>
    </div>
  <?php endif; ?>

  <!-- ===== FORM SEND (Admin & Lecturer) ===== -->
  <?php if ($role === 'admin' || $role === 'lecturer'): ?>
  <div class="card-modern mb-4">
    <h4 style="margin-top:0;color:var(--dark);">
      <i class="bi bi-send"></i> Send New Notification
    </h4>
    <form method="POST" id="notifForm" style="margin-top:1.25rem;">
      <input type="hidden" name="create_notification" value="1">

      <div class="form-group">
        <label class="form-label-modern">Recipient</label>
        <select name="recipient_mode" id="recipientMode" class="form-select-modern" required>
          <option value="">-- Select Recipient --</option>
          <option value="all">
            📢 SEND TO ALL 
            <?= $role === 'admin' ? '(All Users)' : '(My Students + Me)' ?>
          </option>
          <option value="single">👤 Specific Student</option>
        </select>
      </div>

      <div class="form-group" id="studentSection" style="display:none">
        <label class="form-label-modern">Select Student</label>
        <select name="user_id" id="studentSelect" class="form-select-modern">
          <option value="">-- Select Student --</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= $s['user_id'] ?>">
              <?= htmlspecialchars($s['full_name']) ?> 
              (<?= htmlspecialchars($s['matric_no'] ?? 'N/A') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <input type="hidden" name="send_to_all" id="sendToAll" value="0">

      <div class="form-group">
        <label class="form-label-modern">Title</label>
        <input type="text" name="title" class="form-control-modern" 
               placeholder="e.g., Exam Schedule Released" required>
      </div>

      <div class="form-group">
        <label class="form-label-modern">Message</label>
        <textarea name="message" class="form-control-modern" rows="3" 
                  placeholder="Write your notification here..." required></textarea>
      </div>

      <button type="submit" class="btn-modern primary">
        <i class="bi bi-send"></i> Send Notification
      </button>
    </form>
  </div>

  <script>
    document.getElementById('recipientMode').addEventListener('change', function() {
        var studentSection = document.getElementById('studentSection');
        var studentSelect  = document.getElementById('studentSelect');
        var sendToAll      = document.getElementById('sendToAll');

        if (this.value === 'all') {
            studentSection.style.display = 'none';
            studentSelect.required = false;
            sendToAll.value = '1';
        } else if (this.value === 'single') {
            studentSection.style.display = 'block';
            studentSelect.required = true;
            sendToAll.value = '0';
        } else {
            studentSection.style.display = 'none';
            studentSelect.required = false;
            sendToAll.value = '0';
        }
    });
  </script>
  <?php endif; ?>

  <!-- ===== SENARAI NOTIFICATIONS ===== -->
  <h4 style="margin-top:2rem;">Notification List</h4>
  <?php if (empty($notifications)): ?>
    <div class="empty-state">
      <div class="icon"><i class="bi bi-bell-slash"></i></div>
      <h4>No Notifications</h4>
      <p>There are no notifications at the moment.</p>
    </div>
  <?php else: ?>
    <div class="notif-list" style="margin-top:1rem;">
      <?php foreach ($notifications as $n): ?>
      <div class="notif-item <?= (!empty($n['is_read']) && $role === 'student') ? 'read' : '' ?>">
        <div style="flex:1;">
          <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
          <div class="notif-message"><?= htmlspecialchars($n['message']) ?></div>
          <div class="notif-meta">
            <i class="bi bi-clock"></i>
            <?php if ($role === 'student'): ?>
              <?= htmlspecialchars($n['created_at']) ?>
            <?php else: ?>
              Sent to <strong><?= htmlspecialchars($n['recipient_count']) ?></strong> recipient(s)
              on <?= htmlspecialchars($n['created_at']) ?>
            <?php endif; ?>
          </div>
        </div>
        <div>
          <?php if ($role === 'student'): ?>
            <?php if ($n['is_read']): ?>
              <span class="badge-modern gray">Read</span>
            <?php else: ?>
              <span class="badge-modern primary">Unread</span>
              <form method="POST" style="display:inline">
                <input type="hidden" name="mark_read_id" value="<?= $n['notification_id'] ?>">
                <button type="submit" class="btn-modern success sm">
                  <i class="bi bi-check"></i> Mark Read
                </button>
              </form>
            <?php endif; ?>
          <?php else: ?>
            <span class="badge-modern info"><?= htmlspecialchars($n['recipient_count']) ?> sent</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>