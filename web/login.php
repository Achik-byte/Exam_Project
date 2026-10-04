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
  <style>
    /* ============================================
       ExamSys ULTRA PREMIUM LOGIN v3.0
       Layout: Centered floating card + Aurora mesh
       ============================================ */

    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
      height: 100%;
      overflow: hidden;
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #04060d;
      color: #cbd5e1;
    }

    /* ============================================
       BACKGROUND — LAYERED AURORA MESH
       ============================================ */
    .bg-base {
      position: fixed;
      inset: 0;
      background: linear-gradient(135deg, #04060d 0%, #0a0e1a 50%, #0d0819 100%);
      z-index: 0;
    }

    .bg-aurora {
      position: fixed;
      inset: -20%;
      z-index: 1;
      background:
        radial-gradient(ellipse 60% 40% at 20% 30%, rgba(124, 58, 237, 0.5) 0%, transparent 60%),
        radial-gradient(ellipse 50% 60% at 80% 70%, rgba(236, 72, 153, 0.4) 0%, transparent 60%),
        radial-gradient(ellipse 70% 50% at 50% 50%, rgba(6, 182, 212, 0.25) 0%, transparent 60%);
      filter: blur(60px);
      animation: auroraMove 25s ease-in-out infinite;
      opacity: 0.85;
    }

    @keyframes auroraMove {
      0%, 100% { transform: rotate(0deg) scale(1); }
      33% { transform: rotate(8deg) scale(1.15); }
      66% { transform: rotate(-6deg) scale(1.08); }
    }

    /* ============================================
       NOISE TEXTURE OVERLAY
       ============================================ */
    .bg-noise {
      position: fixed;
      inset: 0;
      z-index: 2;
      opacity: 0.35;
      mix-blend-mode: overlay;
      pointer-events: none;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    }

    /* ============================================
       GRID LINES
       ============================================ */
    .bg-grid {
      position: fixed;
      inset: 0;
      z-index: 3;
      background-image:
        linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
      background-size: 70px 70px;
      mask-image: radial-gradient(ellipse 70% 60% at 50% 50%, black 20%, transparent 80%);
      -webkit-mask-image: radial-gradient(ellipse 70% 60% at 50% 50%, black 20%, transparent 80%);
      pointer-events: none;
    }

    /* ============================================
       FLOATING PARTICLES
       ============================================ */
    .particle {
      position: fixed;
      width: 3px;
      height: 3px;
      background: rgba(196, 181, 253, 0.6);
      border-radius: 50%;
      z-index: 4;
      pointer-events: none;
      box-shadow: 0 0 12px rgba(196, 181, 253, 0.8);
      animation: particleFloat linear infinite;
    }

    .particle:nth-child(1) { left: 10%; top: 100%; animation-duration: 18s; animation-delay: 0s; }
    .particle:nth-child(2) { left: 25%; top: 100%; animation-duration: 22s; animation-delay: 2s; }
    .particle:nth-child(3) { left: 40%; top: 100%; animation-duration: 20s; animation-delay: 4s; }
    .particle:nth-child(4) { left: 60%; top: 100%; animation-duration: 24s; animation-delay: 1s; }
    .particle:nth-child(5) { left: 75%; top: 100%; animation-duration: 19s; animation-delay: 3s; }
    .particle:nth-child(6) { left: 90%; top: 100%; animation-duration: 21s; animation-delay: 5s; }

    @keyframes particleFloat {
      0% { transform: translateY(0) scale(1); opacity: 0; }
      10% { opacity: 1; }
      90% { opacity: 1; }
      100% { transform: translateY(-110vh) scale(0.3); opacity: 0; }
    }

    /* ============================================
       MAIN WRAPPER
       ============================================ */
    .login-stage {
      position: relative;
      z-index: 10;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      gap: 1.5rem;
    }

    /* ============================================
       BRAND HEADER (di atas card)
       ============================================ */
    .brand-top {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      padding: 0.65rem 1.4rem 0.65rem 0.65rem;
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 999px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
      animation: fadeDown 0.8s cubic-bezier(0.4, 0, 0.2, 1);
      margin-bottom: 0.5rem;
    }

    @keyframes fadeDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .brand-top-logo {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
      color: white;
      position: relative;
      overflow: hidden;
      box-shadow:
        0 0 24px rgba(124, 58, 237, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
      animation: pulseLogo 3s ease-in-out infinite;
    }

    @keyframes pulseLogo {
      0%, 100% { box-shadow: 0 0 24px rgba(124, 58, 237, 0.6), inset 0 1px 0 rgba(255,255,255,0.3); }
      50% { box-shadow: 0 0 40px rgba(124, 58, 237, 0.9), inset 0 1px 0 rgba(255,255,255,0.4); }
    }

    .brand-top-name {
      font-family: 'Space Grotesk', sans-serif;
      font-weight: 700;
      color: #ffffff;
      font-size: 1.05rem;
      letter-spacing: -0.02em;
    }

    .brand-top-name .accent {
      background: linear-gradient(135deg, #a78bfa, #f472b6);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    /* ============================================
       FLOATING GLASS CARD (Utama)
       ============================================ */
    .glass-card {
      position: relative;
      width: 100%;
      max-width: 460px;
      padding: 3rem 2.75rem 2.5rem;
      background: rgba(12, 16, 28, 0.55);
      backdrop-filter: blur(40px) saturate(180%);
      -webkit-backdrop-filter: blur(40px) saturate(180%);
      border-radius: 32px;
      box-shadow:
        0 60px 120px rgba(0, 0, 0, 0.75),
        0 0 100px rgba(124, 58, 237, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
      animation: cardIn 0.9s cubic-bezier(0.4, 0, 0.2, 1) 0.15s both;
      overflow: hidden;
    }

    @keyframes cardIn {
      from { opacity: 0; transform: translateY(60px) scale(0.94); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Animated gradient border */
    .glass-card::before {
      content: '';
      position: absolute;
      inset: 0;
      padding: 1.5px;
      border-radius: 32px;
      background: linear-gradient(135deg,
        rgba(124, 58, 237, 0.6) 0%,
        rgba(236, 72, 153, 0.4) 25%,
        rgba(6, 182, 212, 0.5) 50%,
        rgba(124, 58, 237, 0.6) 100%);
      background-size: 300% 300%;
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
      pointer-events: none;
      animation: borderGlow 6s linear infinite;
      z-index: 1;
    }

    @keyframes borderGlow {
      0% { background-position: 0% 50%; }
      100% { background-position: 300% 50%; }
    }

    /* Card inner glow top */
    .glass-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 60%;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
      pointer-events: none;
    }

    /* ============================================
       CARD HEADER
       ============================================ */
    .card-head {
      text-align: center;
      margin-bottom: 2.25rem;
      position: relative;
      z-index: 2;
    }

    .head-icon {
      width: 72px;
      height: 72px;
      margin: 0 auto 1.25rem;
      background: linear-gradient(135deg, rgba(124, 58, 237, 0.25), rgba(236, 72, 153, 0.15));
      border: 1px solid rgba(124, 58, 237, 0.4);
      border-radius: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.85rem;
      color: #c4b5fd;
      position: relative;
      overflow: hidden;
      animation: headIconFloat 4s ease-in-out infinite;
    }

    @keyframes headIconFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-6px); }
    }

    .head-icon::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.2) 50%, transparent 70%);
      animation: shimmer 3.5s infinite;
    }

    @keyframes shimmer {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }

    .card-head h1 {
      font-size: 1.85rem;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: -0.035em;
      margin-bottom: 0.5rem;
      line-height: 1.15;
    }

    .card-head h1 .gradient-text {
      background: linear-gradient(135deg, #a78bfa 0%, #f472b6 100%);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .card-head p {
      color: #94a3b8;
      font-size: 0.9rem;
      font-weight: 500;
    }

    /* ============================================
       ERROR ALERT
       ============================================ */
    .err-box {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.9rem 1.1rem;
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.05));
      border: 1px solid rgba(239, 68, 68, 0.3);
      border-radius: 12px;
      color: #fca5a5;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      position: relative;
      z-index: 2;
      animation: shakeIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .err-box i { font-size: 1.1rem; flex-shrink: 0; color: #ef4444; }

    @keyframes shakeIn {
      0% { opacity: 0; transform: translateX(-15px); }
      50% { transform: translateX(4px); }
      100% { opacity: 1; transform: translateX(0); }
    }

    /* ============================================
       FORM
       ============================================ */
    .form-body { position: relative; z-index: 2; }

    .field {
      margin-bottom: 1.25rem;
      position: relative;
    }

    .field label {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.72rem;
      font-weight: 700;
      color: #94a3b8;
      margin-bottom: 0.55rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
    }

    .field label i { color: #a78bfa; font-size: 0.85rem; }

    .field input {
      width: 100%;
      padding: 1rem 1.15rem 1rem 3rem;
      background: rgba(255, 255, 255, 0.035);
      border: 1.5px solid rgba(255, 255, 255, 0.08);
      border-radius: 14px;
      color: #ffffff;
      font-size: 0.95rem;
      font-weight: 500;
      font-family: inherit;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      outline: none;
    }

    .field input::placeholder {
      color: #475569;
      font-weight: 400;
    }

    .field input:hover {
      border-color: rgba(255, 255, 255, 0.15);
      background: rgba(255, 255, 255, 0.05);
    }

    .field input:focus {
      border-color: #7c3aed;
      background: rgba(124, 58, 237, 0.08);
      box-shadow:
        0 0 0 4px rgba(124, 58, 237, 0.15),
        0 8px 28px rgba(124, 58, 237, 0.25);
    }

    .field-icon {
      position: absolute;
      left: 1.15rem;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
      font-size: 1.05rem;
      pointer-events: none;
      transition: color 0.3s;
      margin-top: 0.9rem;
    }

    .field:focus-within .field-icon { color: #a78bfa; }

    /* ============================================
       SUBMIT BUTTON
       ============================================ */
    .btn-submit {
      width: 100%;
      margin-top: 0.85rem;
      padding: 1.05rem 1.5rem;
      background: linear-gradient(135deg, #7c3aed 0%, #a855f7 40%, #ec4899 100%);
      background-size: 200% 200%;
      border: none;
      border-radius: 14px;
      color: #ffffff;
      font-size: 0.95rem;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.55rem;
      letter-spacing: -0.01em;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow:
        0 14px 36px rgba(124, 58, 237, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.25);
      position: relative;
      overflow: hidden;
      animation: gradientShift 5s ease infinite;
    }

    @keyframes gradientShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    .btn-submit::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
      transition: left 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-submit:hover::before { left: 100%; }

    .btn-submit:hover {
      transform: translateY(-3px);
      box-shadow:
        0 22px 52px rgba(124, 58, 237, 0.65),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }

    .btn-submit:active { transform: translateY(-1px); }

    .btn-submit i {
      font-size: 1.05rem;
      transition: transform 0.3s;
    }

    .btn-submit:hover i { transform: translateX(4px); }

    /* ============================================
       DIVIDER + DEMO ACCOUNTS
       ============================================ */
    .divider {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      margin: 1.75rem 0 1.15rem;
      position: relative;
      z-index: 2;
    }

    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    }

    .divider span {
      font-size: 0.68rem;
      font-weight: 700;
      color: #64748b;
      letter-spacing: 0.15em;
      text-transform: uppercase;
    }

    .demo-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.55rem;
      position: relative;
      z-index: 2;
    }

    .demo-chip {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.35rem;
      padding: 0.85rem 0.5rem;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.06);
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-family: inherit;
      color: inherit;
    }

    .demo-chip:hover {
      background: rgba(124, 58, 237, 0.12);
      border-color: rgba(124, 58, 237, 0.4);
      transform: translateY(-3px);
      box-shadow: 0 10px 28px rgba(124, 58, 237, 0.25);
    }

    .demo-chip:active { transform: translateY(-1px); }

    .demo-chip .chip-icon {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.95rem;
      position: relative;
    }

    .demo-chip.admin .chip-icon {
      background: linear-gradient(135deg, rgba(251, 191, 36, 0.2), rgba(251, 191, 36, 0.08));
      color: #fbbf24;
      border: 1px solid rgba(251, 191, 36, 0.3);
    }

    .demo-chip.lecturer .chip-icon {
      background: linear-gradient(135deg, rgba(96, 165, 250, 0.2), rgba(96, 165, 250, 0.08));
      color: #60a5fa;
      border: 1px solid rgba(96, 165, 250, 0.3);
    }

    .demo-chip.student .chip-icon {
      background: linear-gradient(135deg, rgba(52, 211, 153, 0.2), rgba(52, 211, 153, 0.08));
      color: #34d399;
      border: 1px solid rgba(52, 211, 153, 0.3);
    }

    .demo-chip .chip-label {
      font-size: 0.72rem;
      font-weight: 700;
      color: #cbd5e1;
      letter-spacing: 0.02em;
    }

    /* ============================================
       FOOTER TEXT
       ============================================ */
    .stage-footer {
      font-size: 0.75rem;
      color: #475569;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      animation: fadeUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.4s both;
      margin-top: 0.5rem;
    }

    .stage-footer i { color: #7c3aed; font-size: 0.85rem; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 520px) {
      .login-stage { padding: 1.25rem; }
      .glass-card { padding: 2.25rem 1.75rem 2rem; border-radius: 26px; }
      .card-head h1 { font-size: 1.5rem; }
      .head-icon { width: 62px; height: 62px; font-size: 1.55rem; }
      .brand-top { padding: 0.5rem 1.15rem 0.5rem 0.5rem; }
      .brand-top-logo { width: 34px; height: 34px; font-size: 1rem; }
      .brand-top-name { font-size: 0.92rem; }
    }

    @media (max-width: 380px) {
      .demo-grid { grid-template-columns: 1fr; }
      .demo-chip { flex-direction: row; justify-content: flex-start; padding: 0.7rem 1rem; }
    }
  </style>
</head>
<body>

  <!-- BACKGROUND LAYERS -->
  <div class="bg-base"></div>
  <div class="bg-aurora"></div>
  <div class="bg-noise"></div>
  <div class="bg-grid"></div>

  <!-- FLOATING PARTICLES -->
  <div class="particle"></div>
  <div class="particle"></div>
  <div class="particle"></div>
  <div class="particle"></div>
  <div class="particle"></div>
  <div class="particle"></div>

  <!-- MAIN STAGE -->
  <div class="login-stage">

    <!-- BRAND CHIP (di atas card) -->
    <div class="brand-top">
      <div class="brand-top-logo">📚</div>
      <span class="brand-top-name">Exam<span class="accent">Sys</span></span>
    </div>

    <!-- FLOATING GLASS CARD -->
    <div class="glass-card">

      <!-- HEAD -->
      <div class="card-head">
        <div class="head-icon">
          <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h1>Welcome <span class="gradient-text">Back</span></h1>
        <p>Sign in to access your examination dashboard</p>
      </div>

      <!-- ERROR -->
      <?php if ($error): ?>
        <div class="err-box">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <!-- FORM -->
      <form method="POST" action="login.php" class="form-body" autocomplete="off">

        <div class="field">
          <label for="email">
            <i class="bi bi-envelope-fill"></i> Email Address
          </label>
          <div style="position: relative;">
            <i class="bi bi-at field-icon"></i>
            <input type="text" id="email" name="email" placeholder="admin@uni.edu" required>
          </div>
        </div>

        <div class="field">
          <label for="no_ic">
            <i class="bi bi-person-vcard-fill"></i> IC Number
          </label>
          <div style="position: relative;">
            <i class="bi bi-key-fill field-icon"></i>
            <input type="text" id="no_ic" name="no_ic" placeholder="900101010101" required>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          Sign In
          <i class="bi bi-arrow-right"></i>
        </button>

      </form>

      <!-- DIVIDER -->
      <div class="divider"><span>Quick Access</span></div>

      <!-- DEMO ACCOUNTS -->
      <div class="demo-grid">
        <button type="button" class="demo-chip admin" onclick="fillLogin('admin@uni.edu', '900101010101')">
          <div class="chip-icon"><i class="bi bi-shield-fill-check"></i></div>
          <span class="chip-label">Admin</span>
        </button>
        <button type="button" class="demo-chip lecturer" onclick="fillLogin('tan@uni.edu', '800202020202')">
          <div class="chip-icon"><i class="bi bi-mortarboard-fill"></i></div>
          <span class="chip-label">Lecturer</span>
        </button>
        <button type="button" class="demo-chip student" onclick="fillLogin('siti@student.edu', '010203040506')">
          <div class="chip-icon"><i class="bi bi-person-fill"></i></div>
          <span class="chip-label">Student</span>
        </button>
      </div>

    </div>

    <!-- FOOTER -->
    <div class="stage-footer">
      <i class="bi bi-shield-check"></i>
      Secured with JWT Authentication · ExamSys © <?= date('Y') ?>
    </div>

  </div>

  <script>
    function fillLogin(email, ic) {
      const emailInput = document.getElementById('email');
      const icInput = document.getElementById('no_ic');

      emailInput.value = '';
      icInput.value = '';
      emailInput.focus();

      let i = 0;
      const typeEmail = setInterval(() => {
        if (i < email.length) {
          emailInput.value += email[i++];
        } else {
          clearInterval(typeEmail);
          icInput.focus();
          let j = 0;
          const typeIC = setInterval(() => {
            if (j < ic.length) {
              icInput.value += ic[j++];
            } else {
              clearInterval(typeIC);
              icInput.blur();
            }
          }, 28);
        }
      }, 28);
    }

    // Input glow effect on focus
    document.querySelectorAll('.field input').forEach(input => {
      input.addEventListener('focus', function () {
        this.parentElement.style.transform = 'translateY(-1px)';
        this.parentElement.style.transition = 'transform 0.3s';
      });
      input.addEventListener('blur', function () {
        this.parentElement.style.transform = 'translateY(0)';
      });
    });
  </script>

</body>
</html>