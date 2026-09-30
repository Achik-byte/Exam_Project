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
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h3>Notifications</h3>
  <p class="text-muted">
    <?php
      if ($role === 'student')       echo "Your notifications.";
      elseif ($role === 'lecturer')  echo "Send notifications to your students.";
      else                           echo "Send notifications to students and lecturers.";
    ?>
  </p>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- ===== FORM SEND (Admin & Lecturer) ===== -->
  <?php if ($role === 'admin' || $role === 'lecturer'): ?>
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <strong>Send New Notification</strong>
    </div>
    <div class="card-body">
      <form method="POST" id="notifForm">
        <input type="hidden" name="create_notification" value="1">

        <div class="mb-3">
          <label>Recipient</label>
          <select name="recipient_mode" id="recipientMode" class="form-control" required>
            <option value="">-- Select Recipient --</option>
            <option value="all">
              📢 SEND TO ALL 
              <?= $role === 'admin' ? '(All Users)' : '(My Students + Me)' ?>
            </option>
            <option value="single">👤 Specific Student</option>
          </select>
        </div>

        <div class="mb-3" id="studentSection" style="display:none">
          <label>Select Student</label>
          <select name="user_id" id="studentSelect" class="form-control">
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

        <div class="mb-3">
          <label>Title</label>
          <input type="text" name="title" class="form-control" 
                 placeholder="e.g., Exam Schedule Released" required>
        </div>

        <div class="mb-3">
          <label>Message</label>
          <textarea name="message" class="form-control" rows="3" 
                    placeholder="Write your notification here..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Send Notification</button>
      </form>
    </div>
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
  <h5>Notification List</h5>
  <?php if (empty($notifications)): ?>
    <div class="alert alert-info">No notifications found.</div>
  <?php else: ?>
    <ul class="list-group">
      <?php foreach ($notifications as $n): ?>
      <li class="list-group-item">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-bold"><?= htmlspecialchars($n['title']) ?></div>
            <?= htmlspecialchars($n['message']) ?><br>
            <small class="text-muted">
              <?php if ($role === 'student'): ?>
                On <?= htmlspecialchars($n['created_at']) ?>
              <?php else: ?>
                Sent to <strong><?= htmlspecialchars($n['recipient_count']) ?></strong> recipient(s)
                on <?= htmlspecialchars($n['created_at']) ?>
              <?php endif; ?>
            </small>
          </div>
          <div>
            <?php if ($role === 'student'): ?>
              <?php if ($n['is_read']): ?>
                <span class="badge bg-secondary">Read</span>
              <?php else: ?>
                <span class="badge bg-primary">Unread</span>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="mark_read_id" value="<?= $n['notification_id'] ?>">
                  <button type="submit" class="btn btn-sm btn-success">Mark Read</button>
                </form>
              <?php endif; ?>
            <?php else: ?>
              <span class="badge bg-info"><?= htmlspecialchars($n['recipient_count']) ?> sent</span>
            <?php endif; ?>
          </div>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
</body>
</html>