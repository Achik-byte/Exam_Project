<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: users.php"); exit; }

$res  = apiRequest('/users/' . $id, 'GET', null, $_SESSION['token']);
$user = $res['body']['data'] ?? null;
if (!$user) { die("User not found."); }
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = apiRequest('/users/' . $id, 'PUT', [
        'full_name' => $_POST['full_name'],
        'email'     => $_POST['email'],
        'role'      => $_POST['role'],
        'matric_no' => $_POST['matric_no'] ?? null,
        'no_ic'     => $_POST['no_ic'],
        'phone'     => $_POST['phone'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ], $_SESSION['token']);
    if ($response['status_code'] === 200) { header("Location: users.php"); exit; }
    else $error = $response['body']['message'] ?? 'Failed to update';
}
$initial = strtoupper(substr($user['full_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User · ExamSys</title>
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
    .role-picker { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
    .role-card {
      display: flex; flex-direction: column; align-items: flex-start;
      padding: 1rem; border-radius: var(--r-md);
      background: var(--glass-2);
      border: 1.5px solid var(--glass-line);
      cursor: pointer; transition: all .2s; position: relative;
    }
    .role-card:hover { border-color: var(--aurora-1); transform: translateY(-2px); }
    .role-card.selected {
      background: linear-gradient(180deg, rgba(99,102,241,0.2) 0%, var(--glass-2) 100%);
      border-color: var(--aurora-1);
      box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
    }
    .role-card.selected::before {
      content: '✓';
      position: absolute; top: 0.5rem; right: 0.6rem;
      width: 20px; height: 20px; border-radius: 50%;
      background: var(--aurora-1); color: white;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.7rem; font-weight: 700;
    }
    .role-card .role-icon {
      width: 36px; height: 36px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem; margin-bottom: 0.5rem;
    }
    .role-card .role-name { font-weight: 700; color: white; font-size: 0.9rem; margin-bottom: 0.15rem; }
    .role-card .role-desc { color: var(--ink-3); font-size: 0.72rem; line-height: 1.3; }
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
      color: white; margin-bottom: 0.5rem; line-height: 1.3;
    }
    .preview-panel .preview-card .meta {
      display: flex; flex-direction: column; gap: 0.4rem;
      font-size: 0.8rem; color: var(--ink-3);
      margin-top: 0.75rem; padding-top: 0.75rem;
      border-top: 1px solid var(--glass-line);
    }
    .preview-panel .preview-card .meta-row {
      display: flex; align-items: center; gap: 0.5rem;
    }
    .preview-panel .preview-card .meta-row i { width: 16px; color: #a5b4fc; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-glass" style="max-width:1080px;">

  <div class="head-glass">
    <div>
      <span class="eyebrow">◆ Edit Account</span>
      <h1>Edit <em>user</em></h1>
      <p class="sub">Update user details and account status.</p>
    </div>
    <a href="users.php" class="btn-glass"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert-glass danger"><i class="bi bi-exclamation-octagon-fill"></i><div><?= htmlspecialchars($error) ?></div></div>
  <?php endif; ?>

  <div class="steps-nav">
    <div class="step-pill active"><span class="num">1</span> Identity</div>
    <div class="step-pill active"><span class="num">2</span> Role</div>
    <div class="step-pill active"><span class="num">3</span> Contact</div>
  </div>

  <div class="wizard-wrap">

    <!-- FORM -->
    <div class="form-panel">
      <form method="POST">

        <div class="panel-title"><i class="bi bi-person-fill"></i> Identity</div>
        <div class="field-glass">
          <label><i class="bi bi-person-fill"></i> Full Name</label>
          <input type="text" name="full_name" id="fullName" class="input-glass" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>
        <div class="field-glass" style="margin-bottom:2rem;">
          <label><i class="bi bi-envelope-fill"></i> Email</label>
          <input type="email" name="email" id="email" class="input-glass" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="panel-title"><i class="bi bi-shield-fill"></i> Role</div>
        <div class="role-picker" style="margin-bottom:2rem;">
          <?php
            $roles = [
              'admin'    => ['icon'=>'bi-shield-fill-check', 'bg'=>'rgba(245,158,11,0.15)',  'color'=>'#fcd34d', 'desc'=>'Full system access'],
              'lecturer' => ['icon'=>'bi-mortarboard-fill',  'bg'=>'rgba(99,102,241,0.15)', 'color'=>'#a5b4fc', 'desc'=>'Manage results & exams'],
              'student'  => ['icon'=>'bi-backpack-fill',     'bg'=>'rgba(16,185,129,0.15)', 'color'=>'#6ee7b7', 'desc'=>'View results & exams'],
            ];
            foreach ($roles as $r => $m): $sel = ($user['role'] === $r);
          ?>
          <label class="role-card <?= $sel ? 'selected' : '' ?>" data-role="<?= $r ?>">
            <input type="radio" name="role" value="<?= $r ?>" <?= $sel ? 'checked' : '' ?> required style="display:none;">
            <div class="role-icon" style="background:<?= $m['bg'] ?>;color:<?= $m['color'] ?>;"><i class="bi <?= $m['icon'] ?>"></i></div>
            <div class="role-name"><?= ucfirst($r) ?></div>
            <div class="role-desc"><?= $m['desc'] ?></div>
          </label>
          <?php endforeach; ?>
        </div>

        <div class="panel-title"><i class="bi bi-telephone-fill"></i> Contact Details</div>
        <div class="grid-2">
          <div class="field-glass">
            <label><i class="bi bi-credit-card-2-front-fill"></i> IC Number</label>
            <input type="text" name="no_ic" class="input-glass" value="<?= htmlspecialchars($user['no_ic'] ?? '') ?>" required>
          </div>
          <div class="field-glass">
            <label><i class="bi bi-phone-fill"></i> Phone</label>
            <input type="text" name="phone" id="phone" class="input-glass" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
          </div>
        </div>
        <div class="field-glass">
          <label><i class="bi bi-person-vcard-fill"></i> Matric Number</label>
          <input type="text" name="matric_no" id="matricNo" class="input-glass" value="<?= htmlspecialchars($user['matric_no'] ?? '') ?>">
        </div>

        <div class="form-section" style="background:var(--glass-2);border-radius:var(--r-md);padding:1rem;margin-top:1.5rem;border:1px solid var(--glass-line);">
          <label class="check-glass" style="margin-bottom:0;border:none;background:transparent;padding:0;">
            <input type="checkbox" name="is_active" <?= $user['is_active']?'checked':'' ?>>
            <span><strong style="color:white;">Account is active</strong> — Inactive users cannot log in.</span>
          </label>
        </div>

        <div class="form-actions" style="margin-top:2rem;">
          <a href="users.php" class="btn-glass"><i class="bi bi-x-lg"></i> Cancel</a>
          <button type="submit" class="btn-glass primary"><i class="bi bi-check-circle-fill"></i> Save