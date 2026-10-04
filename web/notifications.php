<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$role = $_SESSION['role'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['mark_read_id'])) {
        apiRequest('/notifications/' . $_POST['mark_read_id'], 'PUT', ['is_read' => 1], $_SESSION['token']);
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

$response      = apiRequest('/notifications', 'GET', null, $_SESSION['token']);
$notifications = ($response['status_code'] === 200) ? ($response['body']['data'] ?? []) : [];

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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-modern">

  <div class="page-header">
    <div>
      <h1>Notifications</h1>
      <p class="subtitle">
        <?php
          if ($role === 'student')       echo "Your personal inbox.";
          elseif ($role === 'lecturer')  echo "Send notifications to your students.";
          else                           echo "Send broadcasts across the entire system.";
        ?>
      </p>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($role === 'admin' || $role === 'lecturer'): ?>
  <div class="card-modern mb-4">
    <h4 style="color:#ffffff;margin-bottom:1.5rem;"><i class="bi bi-send-fill"></i> Compose a Notification</h4>
    <form method="POST" id="notifForm">
      <input type="hidden" name="create_notification" value="1">

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-people-fill"></i> Recipient</label>
        <select name="recipient_mode" id="recipientMode" class="form-select" required>
          <option value="">-- Select Recipient --</option>
          <option value="all">📢 Send to All <?= $role === 'admin' ? '(All Users)' : '(My Students)' ?></option>
          <option value="single">👤 Specific Student</option>
        </select>
      </div>

      <div class="mb-3" id="studentSection" style="display:none">
        <label class="form-label"><i class="bi bi-person-fill"></i> Select Student</label>
        <select name="user_id" id="studentSelect" class="form-select">
          <option value="">-- Select Student --</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= $s['user_id'] ?>">
              <?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['matric_no'] ?? 'N/A') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <input type="hidden" name="send_to_all" id="sendToAll" value="0">

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-tag-fill"></i> Title</label>
        <input type="text" name="title" class="form-control" placeholder="e.g. Exam Schedule Released" required>
      </div>

      <div class="mb-4">
        <label class="form-label"><i class="bi bi-chat-left-text-fill"></i> Message</label>
        <textarea name="message" class="form-control" rows="4" placeholder="Write your notification here..." required></textarea>
      </div>

      <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill"></i> Send Notification</button>
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

  <h5 style="color:#ffffff;margin-bottom:1rem;"><i class="bi bi-inbox-fill"></i> Notification List</h5>

  <?php if (empty($notifications)): ?>
    <div class="empty-state">
      <div class="icon"><i class="bi bi-bell-slash"></i></div>
      <h4>No Notifications</h4>
      <p>There are no notifications yet.</p>
    </div>
  <?php else: ?>
    <?php foreach ($notifications as $n): ?>
      <div class="notif-item <?= ($n['is_read'] ?? 0) ? 'read' : '' ?>">
        <div style="flex:1;">
          <div style="font-weight:700;color:#ffffff;font-size:1.05rem;margin-bottom:0.35rem;">
            <i class="bi bi-bell-fill" style="color:var(--primary-light);"></i>
            <?= htmlspecialchars($n['title']) ?>
          </div>
          <p style="color:var(--gray-light);margin-bottom:0.5rem;line-height:1.6;">
            <?= htmlspecialchars($n['message']) ?>
          </p>
          <small class="text-muted">
            <i class="bi bi-clock"></i>
            <?php if ($role === 'student'): ?>
              On <?= htmlspecialchars($n['created_at']) ?>
            <?php else: ?>
              Sent to <strong><?= htmlspecialchars($n['recipient_count'] ?? '?') ?></strong> recipient(s)
              on <?= htmlspecialchars($n['created_at']) ?>
            <?php endif; ?>
          </small>
        </div>
        <div>
          <?php if ($role === 'student'): ?>
            <?php if (!empty($n['is_read'])): ?>
              <span class="badge bg-secondary"><i class="bi bi-check2-all"></i> Read</span>
            <?php else: ?>
              <form method="POST" style="display:inline">
                <input type="hidden" name="mark_read_id" value="<?= $n['notification_id'] ?>">
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check2"></i> Mark Read</button>
              </form>
            <?php endif; ?>
          <?php else: ?>
            <span class="badge bg-info"><i class="bi bi-send"></i> <?= $n['recipient_count'] ?? 0 ?> sent</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>