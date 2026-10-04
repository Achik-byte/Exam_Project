<?php
session_start();
require_once __DIR__ . '/config.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $no_ic = trim($_POST["no_ic"]);

    if ($email === "" || $no_ic === "") {
        $error = "Please enter both email and IC number.";
    } else {
        $response = apiRequest('/login', 'POST', ['email' => $email, 'no_ic' => $no_ic]);

        if ($response['status_code'] === 200 && ($response['body']['status'] ?? '') === 'success') {
            $_SESSION['user_id']   = $response['body']['data']['user_id'];
            $_SESSION['full_name'] = $response['body']['data']['full_name'];
            $_SESSION['role']      = $response['body']['data']['role'];
            $_SESSION['token']     = $response['body']['data']['token'];
            header("Location: index.php");
            exit;
        } else {
            $error = $response['body']['message'] ?? 'Login failed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - ExamSys</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  
  <style>
    /* ===== LOGIN PAGE SPECIFIC - ULTRA PREMIUM ===== */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
      height: 100%;
      overflow: hidden;
      font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
      background: #05070f;
    }

    /* ===== FULL SCREEN AURORA BACKGROUND ===== */
    .login-aurora {
      position: fixed;
      inset: 0;
      z-index: 0;
      background:
        radial-gradient(ellipse at 15% 20%, rgba(124, 58, 237, 0.35) 0%, transparent 45%),
        radial-gradient(ellipse at 85% 80%, rgba(236, 72, 153, 0.28) 0%, transparent 45%),
        radial-gradient(ellipse at 50% 50%, rgba(6, 182, 212, 0.15) 0%, transparent 55%),
        linear-gradient(135deg, #05070f 0%, #0a0e1a 100%);
      animation: auroraMove 20s ease-in-out infinite;
    }

    @keyframes auroraMove {
      0%, 100% { transform: scale(1) rotate(0deg); }
      50% { transform: scale(1.15) rotate(4deg); }
    }

    /* ===== GRID OVERLAY ===== */
    .login-grid {
      position: fixed;
      inset: 0;
      z-index: 1;
      background-image:
        linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
      background-size: 55px 55px;
      mask-image: radial-gradient(ellipse at center, black 20%, transparent 75%);
      -webkit-mask-image: radial-gradient(ellipse at center, black 20%, transparent 75%);
      pointer-events: none;
    }

    /* ===== FLOATING ORBS ===== */
    .orb {
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      opacity: 0.5;
      z-index: 1;
      pointer-events: none;
    }
    .orb-1 {
      width: 400px; height: 400px;
      background: #7c3aed;
      top: -100px; left: -100px;
      animation: orbFloat1 15s ease-in-out infinite;
    }
    .orb-2 {
      width: 350px; height: 350px;
      background: #ec4899;
      bottom: -100px; right: -100px;
      animation: orbFloat2 18s ease-in-out infinite;
    }
    .orb-3 {
      width: 300px; height: 300px;
      background: #06b6d4;
      top: 50%; left: 50%;
      animation: orbFloat3 20s ease-in-out infinite;
    }

    @keyframes orbFloat1 {
      0%, 100% { transform: translate(0, 0) scale(1); }
      50% { transform: translate(80px, 60px) scale(1.2); }
    }
    @keyframes orbFloat2 {
      0%, 100% { transform: translate(0, 0) scale(1); }
      50% { transform: translate(-60px, -80px) scale(1.15); }
    }
    @keyframes orbFloat3 {
      0%, 100% { transform: translate(-50%, -50%) scale(1); }
      50% { transform: translate(-30%, -70%) scale(1.3); }
    }

    /* ===== MAIN WRAPPER ===== */
    .login-wrapper {
      position: relative;
      z-index: 10;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .login-container {
      width: 100%;
      max-width: 1100px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      background: rgba(10, 14, 26, 0.65);
      backdrop-filter: blur(30px) saturate(180%);
      -webkit-backdrop-filter: blur(30px) saturate(180%);
      border-radius: 32px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow:
        0 60px 120px rgba(0, 0, 0, 0.7),
        0 0 80px rgba(124, 58, 237, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.08);
      overflow: hidden;
      animation: containerIn 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes containerIn {
      from { opacity: 0; transform: translateY(40px) scale(0.96); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ===== LEFT SIDE - BRANDING ===== */
    .login-brand {
      padding: 3.5rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
      background: linear-gradient(135deg, rgba(124, 58, 237, 0.15) 0%, rgba(236, 72, 153, 0.08) 50%, rgba(6, 182, 212, 0.1) 100%);
      border-right: 1px solid rgba(255, 255, 255, 0.06);
      overflow: hidden;
    }

    .login-brand::before {
      content: '';
      position: absolute;
      top: -50%; right: -30%;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(124, 58, 237, 0.4) 0%, transparent 65%);
      border-radius: 50%;
      animation: float 10s ease-in-out infinite;
      pointer-events: none;
    }

    .login-brand::after {
      content: '';
      position: absolute;
      bottom: -30%; left: -30%;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(236, 72, 153, 0.25) 0%, transparent 65%);
      border-radius: 50%;
      animation: float 12s ease-in-out infinite reverse;
      pointer-events: none;
    }

    .brand-logo {
      display: inline-flex;
      align-items: center;
      gap: 0.85rem;
      margin-bottom: 2.5rem;
      position: relative;
      z-index: 2;
    }

    .brand-logo-icon {
      width: 56px;
      height: 56px;
      background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      color: white;
      box-shadow:
        0 12px 32px rgba(124, 58, 237, 0.5),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
      position: relative;
      overflow: hidden;
      animation: pulseLogo 3s ease-in-out infinite;
    }

    @keyframes pulseLogo {
      0%, 100% { box-shadow: 0 12px 32px rgba(124, 58, 237, 0.5), inset 0 1px 0 rgba(255,255,255,0.3); }
      50% { box-shadow: 0 12px 48px rgba(124, 58, 237, 0.8), inset 0 1px 0 rgba(255,255,255,0.3); }
    }

    .brand-logo-icon::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.4) 50%, transparent 70%);
      animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }

    .brand-logo-text {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 1.65rem;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: -0.03em;
    }

    .brand-title {
      font-size: 2.75rem;
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -0.04em;
      color: #ffffff;
      margin-bottom: 1.25rem;
      position: relative;
      z-index: 2;
    }

    .brand-title .gradient-text {
      background: linear-gradient(135deg, #a78bfa 0%, #f472b6 50%, #67e8f9 100%);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
      display: inline-block;
    }

    .brand-subtitle {
      font-size: 1.02rem;
      color: #94a3b8;
      line-height: 1.7;
      margin-bottom: 2.5rem;
      max-width: 400px;
      position: relative;
      z-index: 2;
      font-weight: 500;
    }

    .brand-features {
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
      position: relative;
      z-index: 2;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.95rem 1.15rem;
      background: rgba(255, 255, 255, 0.04);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 14px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .feature-item:hover {
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(124, 58, 237, 0.3);
      transform: translateX(6px);
    }

    .feature-icon {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      background: linear-gradient(135deg, rgba(124, 58, 237, 0.25), rgba(236, 72, 153, 0.15));
      border: 1px solid rgba(124, 58, 237, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
      color: #c4b5fd;
      flex-shrink: 0;
    }

    .feature-text {
      display: flex;
      flex-direction: column;
    }

    .feature-text strong {
      color: #ffffff;
      font-size: 0.92rem;
      font-weight: 700;
      margin-bottom: 0.15rem;
    }

    .feature-text span {
      color: #94a3b8;
      font-size: 0.8rem;
      font-weight: 500;
    }

    /* ===== RIGHT SIDE - FORM ===== */
    .login-form-side {
      padding: 3.5rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
    }

    .form-header {
      margin-bottom: 2.25rem;
    }

    .form-header .welcome-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 0.9rem;
      background: rgba(124, 58, 237, 0.15);
      border: 1px solid rgba(124, 58, 237, 0.3);
      border-radius: 999px;
      color: #c4b5fd;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      margin-bottom: 1rem;
    }

    .form-header .welcome-badge i {
      font-size: 0.85rem;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .form-header h2 {
      font-size: 2.1rem;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: -0.03em;
      margin-bottom: 0.5rem;
      line-height: 1.15;
    }

    .form-header p {
      color: #94a3b8;
      font-size: 0.95rem;
      font-weight: 500;
    }

    /* ===== ERROR ALERT ===== */
    .error-alert {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      padding: 1rem 1.25rem;
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.18) 0%, rgba(239, 68, 68, 0.08) 100%);
      border: 1px solid rgba(239, 68, 68, 0.35);
      border-left: 3px solid #ef4444;
      border-radius: 12px;
      color: #fca5a5;
      font-size: 0.88rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      animation: shakeIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .error-alert i { font-size: 1.15rem; flex-shrink: 0; }

    @keyframes shakeIn {
      0% { opacity: 0; transform: translateX(-20px); }
      50% { transform: translateX(5px); }
      100% { opacity: 1; transform: translateX(0); }
    }

    /* ===== FORM FIELDS ===== */
    .form-field {
      margin-bottom: 1.35rem;
      position: relative;
    }

    .form-field label {
      display: flex;
      align-items: center;
      gap: 0.45rem;
      font-size: 0.82rem;
      font-weight: 700;
      color: #cbd5e1;
      margin-bottom: 0.6rem;
      letter-spacing: 0.02em;
      text-transform: uppercase;
    }

    .form-field label i {
      color: #a78bfa;
      font-size: 0.9rem;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-wrapper input {
      width: 100%;
      padding: 0.95rem 1.15rem;
      background: rgba(255, 255, 255, 0.04);
      border: 1.5px solid rgba(255, 255, 255, 0.08);
      border-radius: 13px;
      color: #ffffff;
      font-size: 0.95rem;
      font-weight: 500;
      font-family: inherit;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      outline: none;
    }

    .input-wrapper input::placeholder {
      color: #64748b;
      font-weight: 400;
    }

    .input-wrapper input:hover {
      border-color: rgba(255, 255, 255, 0.15);
      background: rgba(255, 255, 255, 0.06);
    }

    .input-wrapper input:focus {
      border-color: #7c3aed;
      background: rgba(124, 58, 237, 0.08);
      box-shadow:
        0 0 0 4px rgba(124, 58, 237, 0.15),
        0 8px 24px rgba(124, 58, 237, 0.2);
    }

    /* ===== SUBMIT BUTTON ===== */
    .btn-login {
      width: 100%;
      padding: 1.05rem 1.5rem;
      background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #ec4899 100%);
      background-size: 200% 200%;
      border: none;
      border-radius: 13px;
      color: white;
      font-size: 0.98rem;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.6rem;
      letter-spacing: -0.01em;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow:
        0 12px 32px rgba(124, 58, 237, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
      position: relative;
      overflow: hidden;
      animation: gradientShift 4s ease infinite;
      margin-top: 0.5rem;
    }

    @keyframes gradientShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    .btn-login::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.7s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-login:hover::before { left: 100%; }

    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow:
        0 20px 48px rgba(124, 58, 237, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }

    .btn-login:active { transform: translateY(-1px); }

    .btn-login i { font-size: 1.1rem; }

    /* ===== DEMO ACCOUNTS ===== */
    .demo-section {
      margin-top: 2rem;
      padding-top: 1.75rem;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    .demo-header {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: #94a3b8;
      margin-bottom: 1rem;
    }

    .demo-header::before,
    .demo-header::after {
      content: '';
      flex: 1;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    }

    .demo-list {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .demo-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      padding: 0.7rem 0.95rem;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 10px;
      font-size: 0.78rem;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
      text-align: left;
      font-family: inherit;
      color: inherit;
      width: 100%;
    }

    .demo-item:hover {
      background: rgba(124, 58, 237, 0.1);
      border-color: rgba(124, 58, 237, 0.3);
      transform: translateX(4px);
    }

    .demo-role {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 700;
      color: #c4b5fd;
      flex-shrink: 0;
    }

    .demo-role-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      box-shadow: 0 0 8px currentColor;
    }

    .demo-role.admin .demo-role-dot { background: #fbbf24; color: #fbbf24; }
    .demo-role.lecturer .demo-role-dot { background: #60a5fa; color: #60a5fa; }
    .demo-role.student .demo-role-dot { background: #34d399; color: #34d399; }

    .demo-credentials {
      color: #64748b;
      font-size: 0.72rem;
      font-weight: 500;
      text-align: right;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 900px) {
      .login-container {
        grid-template-columns: 1fr;
        max-width: 480px;
      }
      .login-brand {
        padding: 2.5rem 2rem;
        border-right: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      }
      .brand-title { font-size: 2rem; }
      .brand-subtitle { font-size: 0.92rem; margin-bottom: 1.75rem; }
      .brand-features { display: none; }
      .login-form-side { padding: 2.5rem 2rem; }
      .form-header h2 { font-size: 1.75rem; }
    }

    @media (max-width: 480px) {
      .login-wrapper { padding: 1rem; }
      .login-brand { padding: 2rem 1.5rem; }
      .login-form-side { padding: 2rem 1.5rem; }
      .brand-logo-text { font-size: 1.35rem; }
      .brand-title { font-size: 1.65rem; }
      .form-header h2 { font-size: 1.5rem; }
      .orb { display: none; }
    }
  </style>
</head>
<body>

  <!-- AURORA BACKGROUND -->
  <div class="login-aurora"></div>
  <div class="login-grid"></div>

  <!-- FLOATING ORBS -->
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <!-- MAIN LOGIN -->
  <div class="login-wrapper">
    <div class="login-container">

      <!-- LEFT: BRANDING -->
      <div class="login-brand">
        <div class="brand-logo">
          <div class="brand-logo-icon">📚</div>
          <span class="brand-logo-text">ExamSys</span>
        </div>

        <h1 class="brand-title">
          The Future of<br>
          <span class="gradient-text">Exam Management</span>
        </h1>

        <p class="brand-subtitle">
          A premium platform for scheduling, grading, and verifying examinations across your institution.
        </p>

        <div class="brand-features">
          <div class="feature-item">
            <div class="feature-icon"><i class="bi bi-shield-lock-fill"></i></div>
            <div class="feature-text">
              <strong>JWT Authentication</strong>
              <span>Secure token-based login</span>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
            <div class="feature-text">
              <strong>Role-Based Access</strong>
              <span>Admin, Lecturer & Student</span>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon"><i class="bi bi-qr-code-scan"></i></div>
            <div class="feature-text">
              <strong>QR Code Verification</strong>
              <span>Third-party API integration</span>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT: FORM -->
      <div class="login-form-side">
        <div class="form-header">
          <div class="welcome-badge">
            <i class="bi bi-star-fill"></i>
            Welcome Back
          </div>
          <h2>Sign in to continue</h2>
          <p>Enter your credentials to access your dashboard</p>
        </div>

        <?php if ($error): ?>
          <div class="error-alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars($error) ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm">
          <div class="form-field">
            <label for="email">
              <i class="bi bi-envelope-fill"></i>
              Email Address
            </label>
            <div class="input-wrapper">
              <input type="text" id="email" name="email" placeholder="admin@uni.edu" required autocomplete="email">
            </div>
          </div>

          <div class="form-field">
            <label for="no_ic">
              <i class="bi bi-person-vcard-fill"></i>
              IC Number
            </label>
            <div class="input-wrapper">
              <input type="text" id="no_ic" name="no_ic" placeholder="900101010101" required autocomplete="off">
            </div>
          </div>

          <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right"></i>
            Continue
          </button>
        </form>

        <!-- DEMO ACCOUNTS -->
        <div class="demo-section">
          <div class="demo-header">Demo Accounts</div>
          <div class="demo-list">
            <button type="button" class="demo-item" onclick="fillLogin('admin@uni.edu', '900101010101')">
              <span class="demo-role admin"><span class="demo-role-dot"></span>Admin</span>
              <span class="demo-credentials">admin@uni.edu</span>
            </button>
            <button type="button" class="demo-item" onclick="fillLogin('tan@uni.edu', '800202020202')">
              <span class="demo-role lecturer"><span class="demo-role-dot"></span>Lecturer</span>
              <span class="demo-credentials">tan@uni.edu</span>
            </button>
            <button type="button" class="demo-item" onclick="fillLogin('siti@student.edu', '010203040506')">
              <span class="demo-role student"><span class="demo-role-dot"></span>Student</span>
              <span class="demo-credentials">siti@student.edu</span>
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
    function fillLogin(email, ic) {
      const emailInput = document.getElementById('email');
      const icInput = document.getElementById('no_ic');
      
      emailInput.value = '';
      icInput.value = '';
      
      // Typewriter effect
      let i = 0;
      const typeEmail = setInterval(() => {
        if (i < email.length) {
          emailInput.value += email[i];
          i++;
        } else {
          clearInterval(typeEmail);
          let j = 0;
          const typeIC = setInterval(() => {
            if (j < ic.length) {
              icInput.value += ic[j];
              j++;
            } else {
              clearInterval(typeIC);
              emailInput.focus();
            }
          }, 30);
        }
      }, 30);
    }

    // Add floating label effect
    document.querySelectorAll('.input-wrapper input').forEach(input => {
      input.addEventListener('focus', () => {
        input.parentElement.style.transform = 'scale(1.01)';
      });
      input.addEventListener('blur', () => {
        input.parentElement.style.transform = 'scale(1)';
      });
    });
  </script>

</body>
</html>