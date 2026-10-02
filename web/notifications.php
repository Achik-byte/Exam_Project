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
  <title>Inbox · ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-lum">

  <div class="page-head-lum">
    <div>
      <span class="eyebrow">◆ Inbox</span>
      <h1>Noti<em>fications</em></h1>
      <p class="sub">
        <?php
          if ($role === 'student')       echo "Your personal notifications.";
          elseif ($role === 'lecturer')  echo "Send notifications to your students.";
          else                           echo "Broadcast messages to students and lecturers.";
        ?>
      </p>
    </div>
    <span class="pill indigo" style="padding:0.5rem 1rem;font-size:0.85rem;">
      <i class="bi bi-bell-fill"></i> <?= count($notifications) ?> messages
    </span>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert-lum danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <!-- ===== FORM SEND (Admin & Lecturer) ===== -->
  <?php if ($role === 'admin' || $role === 'lecturer'): ?>
  <div class="card-lum flat mb-3">
    <div class="form-section-head">
      <div class="form-section-icon" style="background:var(--indigo-soft);color:var(--indigo);">
        <i class="bi bi-send-fill"></i>
      </div>
      <div>
        <h3 class="form-section-title">Compose Notification</h3>
        <p class="form-section-desc">Send an announcement to students or a specific recipient.</p>
      </div>
    </div>

    <form method="POST" id="notifForm">
      <input type="hidden" name="create_notification" value="1">

      <div class="field-lum">
        <label><i class="bi bi-people-fill"></i> Recipient <span class="req">*</span></label>
        <select name="recipient_mode" id="recipientMode" class="select-lum" required>
          <option value="">— Select Recipient —</option>
          <option value="all">
            📢 Send to All <?= $role === 'admin' ? '(All Users)' : '(My Students)' ?>
          </option>
          <option value="single">👤 Specific Student</option>
        </select>
      </div>

      <div class="field-lum" id="studentSection" style="display:none">
        <label><i class="bi bi-person-fill"></i> Select Student</label>
        <select name="user_id" id="studentSelect" class="select-lum">
          <option value="">— Select Student —</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= $s['user_id'] ?>">
              <?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['matric_no'] ?? 'N/A') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <input type="hidden" name="send_to_all" id="sendToAll" value="0">

      <div class="field-lum">
        <label><i class="bi bi-type-fill"></i> Title <span class="req">*</span></label>
        <input type="text" name="title" class="input-lum" placeholder="e.g. Exam Schedule Released" required>
      </div>

      <div class="field-lum">
        <label><i class="bi bi-chat-left-text-fill"></i> Message <span class="req">*</span></label>
        <textarea name="message" class="input-lum" rows="3" placeholder="Write your notification here..." required></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-lum primary"><i class="bi bi-send-fill"></i> Send Notification</button>
      </div>
    </form>
  </div>

  <script>
    document.getElementById('recipientMode').addEventListener('change', function() {
        var s = document.getElementById('studentSection');
        var sel = document.getElementById('studentSelect');
        var all = document.getElementById('sendToAll');
        if (this.value === 'all') { s.style.display='none'; sel.required=false; all.value='1'; }
        else if (this.value === 'single') { s.style.display='block'; sel.required=true; all.value='0'; }
        else { s.style.display='none'; sel.required=false; all.value='0'; }
    });
  </script>
  <?php endif; ?>

  <!-- ===== SENARAI NOTIFICATIONS ===== -->
  <h3 style="font-size:1.4rem;margin:2rem 0 1rem;"><i class="bi bi-list-stars" style="color:var(--indigo)"></i> Recent messages</h3>

  <?php if (empty($notifications)): ?>
    <div class="empty-lum">
      <div class="icon"><i class="bi bi-bell-slash"></i></div>
      <h4>No notifications</h4>
      <p>Nothing to display at the moment.</p>
    </div>
  <?php else: ?>
    <div class="notif-lum">
      <?php foreach ($notifications as $n): ?>
        <?php $isRead = !empty($n['is_read']) && $role === 'student'; ?>
        <div class="item <?= $isRead ? 'read' : '' ?>">
          <div class="dot-ind"></div>
          <div style="flex:1;">
            <div class="ttl"><?= htmlspecialchars($n['title']) ?></div>
            <div class="msg"><?= htmlspecialchars($n['message']) ?></div>
            <div class="meta">
              <i class="bi bi-clock"></i>
              <?php if ($role === 'student'): ?>
                <?= htmlspecialchars($n['created_at']) ?>
              <?php else: ?>
                Sent to <strong><?= htmlspecialchars($n['recipient_count']) ?></strong> recipient(s) · <?= htmlspecialchars($n['created_at']) ?>
              <?php endif; ?>
            </div>
          </div>
          <div style="flex-shrink:0;">
            <?php if ($role === 'student'): ?>
              <?php if ($n['is_read']): ?>
                <span class="pill gray"><i class="bi bi-check2-all"></i> Read</span>
              <?php else: ?>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="mark_read_id" value="<?= $n['notification_id'] ?>">
                  <button type="submit" class="btn-lum mint sm"><i class="bi bi-check2"></i> Mark Read</button>
                </form>
              <?php endif; ?>
            <?php else: ?>
              <span class="pill indigo"><i class="bi bi-send-check-fill"></i> <?= htmlspecialchars($n['recipient_count']) ?> sent</span>
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