<?php
require_once __DIR__ . '/auth-guard.php';
if (current_user()) redirect_for_role(current_user()['role']);
$requestDraft = $_SESSION['access_request_draft'] ?? [];
unset($_SESSION['access_request_draft']);
if (!is_array($requestDraft)) {
    $requestDraft = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Sign In</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script>
  /* Apply saved theme before paint to avoid a flash of the wrong theme */
  (function () {
    try {
      var savedTheme = localStorage.getItem('readpilot-theme');
      if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
      }
    } catch (e) { /* localStorage unavailable — default to light */ }
  })();
</script>
<style>
  /* =====================================================================
     Login / Request Access page — extends style.css.
     Layout inspired by a reference mock, restyled entirely in ReadPilot's
     own palette, type, and pixel-plane motif.
     ===================================================================== */

  body.auth-body{
    display:block;
    min-height:100vh;
    position:relative;
    overflow-x:hidden;
    overflow-y:auto;
    background:
      linear-gradient(var(--border) 1px, transparent 1px),
      linear-gradient(90deg, var(--border) 1px, transparent 1px),
      radial-gradient(circle at 18% 15%, rgba(111,191,90,0.09), transparent 40%),
      radial-gradient(circle at 85% 85%, rgba(139,107,209,0.08), transparent 42%),
      var(--bg);
    background-size:34px 34px, 34px 34px, auto, auto, auto;
    background-position:-1px -1px, -1px -1px, 0 0, 0 0, 0 0;
  }
  html[data-theme="dark"] body.auth-body{
    background:
      linear-gradient(rgba(255,255,255,0.035) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.035) 1px, transparent 1px),
      radial-gradient(circle at 18% 15%, rgba(111,191,90,0.08), transparent 40%),
      radial-gradient(circle at 85% 85%, rgba(168,145,232,0.08), transparent 42%),
      var(--bg);
    background-size:34px 34px, 34px 34px, auto, auto, auto;
    background-position:-1px -1px, -1px -1px, 0 0, 0 0, 0 0;
  }

  .auth-wrap{
    position:relative;
    z-index:2;
    min-height:100vh;
    display:flex;
    padding:32px 20px;
  }

  /* ---------- Decorative background: canvas-drawn plane with a fading trail ---------- */
  canvas#plane-canvas{
    position:fixed;
    inset:0;
    width:100%;
    height:100%;
    pointer-events:none;
    z-index:1;
  }
  @media (max-width:900px){ canvas#plane-canvas{ display:none; } }

  /* ---------- Card ---------- */
  .auth-card{
    width:100%;
    max-width:440px;
    margin:auto;            /* centers when it fits, scrolls from the top when it doesn't */
    background:var(--card);
    border-radius:28px;
    box-shadow:0 24px 64px rgba(16,32,22,0.14);
    padding:40px 38px 34px;
    position:relative;
    z-index:2;
  }
  html[data-theme="dark"] .auth-card{
    box-shadow:0 24px 64px rgba(0,0,0,0.55);
    border:1px solid var(--border);
  }

  .auth-brand{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:30px;
  }
  .auth-brand .logo-icon{
    width:44px;height:44px;
    border-radius:13px;
    background:linear-gradient(160deg, var(--green) 0%, var(--green-dark) 100%);
    display:flex;align-items:center;justify-content:center;
    flex-shrink:0;
    box-shadow:0 6px 14px rgba(111,191,90,0.35);
  }
  .auth-brand .logo-icon .bx{font-size:21px;color:#fff;}
  .auth-brand .brand-col{line-height:1.2;}
  .auth-brand .brand{
    font-family:'Poppins',sans-serif;
    font-weight:700;
    font-size:18px;
    color:var(--ink);
  }
  .auth-brand .tagline{
    font-size:10.5px;
    font-weight:800;
    color:var(--muted);
    letter-spacing:.6px;
    text-transform:uppercase;
  }

  .auth-heading h1{
    font-family:'Poppins',sans-serif;
    font-size:27px;
    font-weight:700;
    color:var(--ink);
    margin:0 0 6px 0;
  }
  .auth-heading h1 .accent{ color:var(--green-dark); }
  .auth-heading p{
    font-size:13.5px;
    color:var(--muted);
    font-weight:600;
    margin:0 0 24px 0;
  }

  /* Divider */
  .divider-row{
    display:flex;align-items:center;gap:12px;
    margin-bottom:22px;
  }
  .divider-row .line{flex:1;height:1px;background:var(--border);}
  .divider-row span{
    font-size:10.5px;font-weight:800;color:var(--muted);
    letter-spacing:.6px;text-transform:uppercase;white-space:nowrap;
  }

  .form-view{display:none;}
  .form-view.active{display:block;animation:fadeUp .25s ease;}
  @keyframes fadeUp{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}

  .form-row label{
    display:block;font-size:11px;font-weight:800;color:var(--ink);
    margin-bottom:7px;letter-spacing:.4px;text-transform:uppercase;
  }
  .field-icon-row{position:relative;}
  .field-icon-row input, .field-icon-row select{
    padding-left:42px !important;
    border-radius:13px !important;
  }
  .field-icon-row.password-row input{padding-right:42px !important;}
  .field-icon-row .bx.field-lead{
    position:absolute;left:14px;top:calc(50% + 13px);transform:translateY(-50%);
    font-size:16px;color:var(--muted);pointer-events:none;
  }
  .field-icon-row .field-trail{
    position:absolute;right:12px;top:calc(50% + 13px);transform:translateY(-50%);
    font-size:16px;color:var(--muted);cursor:pointer;background:none;border:none;padding:4px;
  }
  textarea.form-textarea{
    width:100%;border:1px solid var(--border);border-radius:13px;
    padding:11px 13px 11px 42px;font-family:inherit;font-size:13.5px;color:var(--ink);
    outline:none;background:var(--card);resize:vertical;min-height:66px;
  }
  textarea.form-textarea:focus{border-color:var(--green);}
  .form-row.field-icon-row.textarea-row .bx.field-lead{ top:calc(50% + 13px); transform:translateY(-50%); }
  .request-feedback-overlay{position:fixed;inset:0;z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(10,20,14,.62);}
  .request-feedback-overlay.show{display:flex;}
  .request-feedback-box{position:relative;width:min(100%,440px);padding:28px;background:var(--card);border:1px solid var(--border);border-radius:20px;box-shadow:0 18px 60px rgba(0,0,0,.28);color:var(--ink);}
  .request-feedback-box h2{margin:0 32px 10px 0;font:700 19px 'Poppins',sans-serif;}
  .request-feedback-box p{margin:0 0 22px;color:var(--muted);font-size:14px;line-height:1.6;font-weight:600;overflow-wrap:anywhere;}
  .request-feedback-box .feedback-close{position:absolute;right:16px;top:16px;width:34px;height:34px;border:0;border-radius:50%;background:var(--bg);color:var(--ink);font-size:20px;cursor:pointer;}
  .request-feedback-box .feedback-ok{width:100%;border:0;border-radius:12px;padding:12px 16px;background:var(--green);color:#16281d;font-family:inherit;font-size:14px;font-weight:800;cursor:pointer;}
  .request-feedback-box.success h2{color:var(--green-dark);}
  .request-feedback-box.error h2{color:#b42332;}
  html[data-theme="dark"] .request-feedback-box.error h2{color:#ff929d;}

  .row-between{
    display:flex;align-items:center;justify-content:space-between;
    font-size:12.5px;font-weight:700;margin:0 0 20px 0;
  }
  .remember{display:flex;align-items:center;gap:7px;color:var(--muted);}
  .remember input{accent-color:var(--green);}
  .forgot-link{color:var(--green-dark);text-decoration:none;cursor:pointer;}
  .forgot-link:hover{text-decoration:underline;}

  .btn-auth{
    width:100%;
    background:var(--green);
    color:#16281d;border:none;border-radius:999px;
    padding:14px 16px;font-size:14.5px;font-weight:800;
    cursor:pointer;font-family:inherit;
    display:flex;align-items:center;justify-content:center;gap:8px;
    box-shadow:0 8px 18px rgba(111,191,90,0.35);
    transition:transform .12s steps(2), box-shadow .12s steps(2), background .15s ease;
  }
  .btn-auth:hover{
    background:var(--green-dark);
    color:#fff;
    transform:translate(-2px,-2px);
    box-shadow:3px 3px 0 var(--ink), 0 8px 18px rgba(111,191,90,0.35);
  }
  .btn-auth .bx{font-size:17px;}
  .access-note{
    display:flex;gap:10px;
    background:var(--orange-light);
    border-radius:14px;
    padding:13px 15px;
    margin-bottom:20px;
    font-size:12px;
    color:var(--ink);
    line-height:1.5;
  }
  .access-note .bx{font-size:18px;color:var(--orange);flex-shrink:0;margin-top:1px;}
  .access-note b{display:block;font-size:12.5px;margin-bottom:2px;}

  .auth-footer{
    text-align:center;
    margin-top:22px;
    font-size:12.5px;
    color:var(--muted);
    font-weight:600;
  }
  .auth-footer a{color:var(--green-dark);font-weight:800;text-decoration:none;cursor:pointer;}
  .auth-footer a:hover{text-decoration:underline;}

  /* Shorter laptop screens: tighten the card so it needs little or no scrolling */
  @media (max-height:820px){
    .auth-wrap{padding:20px;}
    .auth-card{padding:26px 30px 22px;}
    .auth-brand{margin-bottom:16px;}
    .auth-heading h1{font-size:23px;}
    .auth-heading p{margin-bottom:14px;}
    .access-note{padding:10px 12px;margin-bottom:14px;font-size:11.5px;}
    .form-row label{margin-bottom:5px;}
    .form-row input, .form-row textarea{padding-top:9px;padding-bottom:9px;}
    textarea.form-textarea{min-height:52px;}
    .btn-auth{padding:12px 16px;}
    .auth-footer{margin-top:14px;}
  }

  /* Phones / narrow screens */
  @media (max-width:600px){
    .auth-wrap{padding:16px 12px;}
    .auth-card{max-width:440px;border-radius:22px;padding:24px 20px 22px;}
    .auth-heading h1{font-size:22px;}
  }
  @media (max-width:380px){
    .auth-wrap{padding:10px 8px;}
    .auth-card{padding:20px 14px;}
    .row-between{font-size:12px;}
  }
</style>
</head>
<body class="auth-body">

  <?php include __DIR__ . '/loader.php'; ?>

  <!-- ================= Decorative background: canvas plane + trail ================= -->
  <canvas id="plane-canvas" aria-hidden="true"></canvas>

  <!-- ================= Auth card ================= -->
  <div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-brand">
        <div class="logo-icon"><i class='bx bxs-paper-plane'></i></div>
        <div class="brand-col">
          <div class="brand">ReadPilot</div>
          <div class="tagline">Guide. Read. Grow.</div>
        </div>
      </div>

      <!-- ---------- LOG IN VIEW ---------- -->
      <div class="form-view active" id="view-login">
        <div class="auth-heading">
          <h1>Welcome <span class="accent">back.</span></h1>
          <p>Sign in to your classroom dashboard</p>
        </div>
        <div class="divider-row">
          <div class="line"></div><span>Sign in with email</span><div class="line"></div>
        </div>

        <form id="loginForm" action="auth.php" method="post">
          <input type="hidden" name="action" value="login">
          <div class="form-row field-icon-row">
            <label>Email address</label>
            <i class='bx bx-envelope field-lead'></i>
            <input type="email" name="email" placeholder="you@school.edu" required>
          </div>
          <div class="form-row field-icon-row password-row">
            <label>Password</label>
            <i class='bx bx-lock-alt field-lead'></i>
            <input type="password" name="password" id="pwInput" placeholder="Enter your password" required>
            <button type="button" class="field-trail" id="pwToggle" aria-label="Show password"><i class='bx bx-show'></i></button>
          </div>
          <div class="row-between">
            <a class="forgot-link">Forgot password?</a>
          </div>
          <button type="submit" class="btn-auth">
            Sign In <i class='bx bx-right-arrow-alt'></i>
          </button>
        </form>
        <div class="auth-footer">
          Don't have an account? <a id="goToRequest">Request access</a>
        </div>
      </div>

      <!-- ---------- REQUEST ACCESS VIEW ---------- -->
      <div class="form-view" id="view-request">
        <div class="auth-heading">
          <h1>Request <span class="accent">access.</span></h1>
          <p>Get your classroom dashboard set up</p>
        </div>

        <div class="access-note">
          <i class='bx bx-info-circle'></i>
          <div>
            <b>ReadPilot is invite-only</b>
            Reserved for verified educators and school administrators. Our team reviews every request before granting access.
          </div>
        </div>

        <form id="requestForm" action="auth.php" method="post" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="action" value="request_access">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          <div class="form-row field-icon-row">
            <label>Full name</label>
            <i class='bx bx-user field-lead'></i>
            <input type="text" id="reqName" name="name" placeholder="e.g. Maria Hernandez" value="<?= htmlspecialchars((string) ($requestDraft['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div class="form-row field-icon-row">
            <label>School email</label>
            <i class='bx bx-envelope field-lead'></i>
            <input type="email" id="reqEmail" name="email" placeholder="you@school.edu" value="<?= htmlspecialchars((string) ($requestDraft['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div class="form-row field-icon-row password-row">
            <label>Password</label>
            <i class='bx bx-lock-alt field-lead'></i>
            <input type="password" id="reqPassword" name="password" minlength="8" placeholder="At least 8 characters" autocomplete="new-password" required>
            <button type="button" class="field-trail" id="reqPasswordToggle" aria-label="Show password"><i class='bx bx-show'></i></button>
          </div>
          <div class="form-row field-icon-row password-row">
            <label>Confirm password</label>
            <i class='bx bx-lock-alt field-lead'></i>
            <input type="password" id="reqPassword2" name="password_confirm" minlength="8" placeholder="Re-enter your password" autocomplete="new-password" required>
            <button type="button" class="field-trail" id="reqPassword2Toggle" aria-label="Show password"><i class='bx bx-show'></i></button>
          </div>
          <div class="form-row field-icon-row">
            <label>School / institution</label>
            <i class='bx bx-building-house field-lead'></i>
            <input type="text" id="reqSchool" name="school" placeholder="e.g. Rosewood Elementary" value="<?= htmlspecialchars((string) ($requestDraft['school'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div class="form-row field-icon-row textarea-row">
            <label>Reason for access</label>
            <i class='bx bx-message-detail field-lead'></i>
            <textarea class="form-textarea" id="reqReason" name="reason" placeholder="Tell us about your role and how you'll use ReadPilot..." required><?= htmlspecialchars((string) ($requestDraft['reason'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
          <div class="form-row">
            <label for="reqIdDocument">Upload a school or work ID</label>
            <input type="file" id="reqIdDocument" name="id_document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required>
            <small>PDF, JPG, or PNG; maximum 5 MB. Only ReadPilot admins can access this file. It is kept until an admin deletes it.</small>
          </div>
          <button type="submit" class="btn-auth">
            Submit Request <i class='bx bx-paper-plane'></i>
          </button>
        </form>

        <div class="auth-footer">
          Already approved? <a id="goToLogin">Log in</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div class="toast" id="toast">
    <i class='bx bx-check-circle'></i>
    <span id="toastText">Done!</span>
  </div>
  <div class="request-feedback-overlay" id="requestFeedback" role="alertdialog" aria-modal="true" aria-labelledby="requestFeedbackTitle" aria-describedby="requestFeedbackMessage" tabindex="-1">
    <div class="request-feedback-box error" id="requestFeedbackBox">
      <button class="feedback-close" id="requestFeedbackClose" type="button" aria-label="Close message">&times;</button>
      <h2 id="requestFeedbackTitle">Request could not be submitted</h2>
      <p id="requestFeedbackMessage"></p>
      <button class="feedback-ok" id="requestFeedbackOk" type="button">Got it</button>
    </div>
  </div>

  <script>
    // ---- Decorative background: canvas plane flying an elliptical path
    //      with a fading dashed trail. Colors are read live from the page's
    //      own CSS variables, so it follows light/dark theme automatically.
    (function(){
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
      var canvas = document.getElementById('plane-canvas');
      if (!canvas) return;
      var ctx = canvas.getContext('2d');

      var css = getComputedStyle(document.documentElement);
      var planeFill   = (css.getPropertyValue('--green') || '#6fbf5a').trim();
      var planeStroke = (css.getPropertyValue('--green-dark') || '#3f7d4a').trim();

      function hexToRgb(hex){
        hex = hex.replace('#','');
        if (hex.length === 3){ hex = hex.split('').map(function(c){return c+c;}).join(''); }
        var num = parseInt(hex, 16);
        return { r:(num>>16)&255, g:(num>>8)&255, b:num&255 };
      }
      var trailRgb = hexToRgb(planeFill);
      var trailColor = 'rgba(' + trailRgb.r + ',' + trailRgb.g + ',' + trailRgb.b + ',';

      var W, H, cx, cy;
      function resize(){
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
        cx = W / 2;
        cy = H / 2;
      }
      resize();
      window.addEventListener('resize', resize);

      var TRAIL_LEN = 42;
      var TRAIL_GAP = 4.5;
      var DASH_LEN  = 9;
      var angle     = 0;
      var SPEED     = 0.010;
      var RX_FACTOR = 0.36;
      var RY_FACTOR = 0.24;

      function getPos(a){
        var rx = Math.min(W, H) * RX_FACTOR;
        var ry = Math.min(W, H) * RY_FACTOR;
        return { x: cx + rx * Math.cos(a), y: cy + ry * Math.sin(a) };
      }

      function drawPlane(x, y, dir){
        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(dir);
        var s = 15;

        ctx.beginPath();
        ctx.moveTo( s,       0        );
        ctx.lineTo(-s,      -s * 0.55 );
        ctx.lineTo(-s*0.3,   0        );
        ctx.lineTo(-s,       s * 0.55 );
        ctx.closePath();
        ctx.fillStyle   = planeFill;
        ctx.strokeStyle = trailColor + '0.5)';
        ctx.lineWidth   = 1;
        ctx.fill();
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(s, 0);
        ctx.lineTo(-s * 0.3, 0);
        ctx.strokeStyle = planeStroke;
        ctx.lineWidth   = 1.3;
        ctx.stroke();

        ctx.beginPath();
        ctx.arc(0, 0, s * 1.4, 0, Math.PI * 2);
        var g = ctx.createRadialGradient(0, 0, 0, 0, 0, s * 1.4);
        g.addColorStop(0, trailColor + '0.2)');
        g.addColorStop(1, trailColor + '0)');
        ctx.fillStyle = g;
        ctx.fill();

        ctx.restore();
      }

      function frame(){
        ctx.clearRect(0, 0, W, H);

        for (var i = 0; i < TRAIL_LEN; i++){
          var t      = (i + 1) / TRAIL_LEN;
          var trailA = angle - (TRAIL_LEN - i) * (TRAIL_GAP * Math.PI / 180);
          var nextA  = trailA + 0.018;

          var p1 = getPos(trailA);
          var p2 = getPos(nextA);

          var dx  = p2.x - p1.x;
          var dy  = p2.y - p1.y;
          var len = Math.hypot(dx, dy) || 1;
          var ux  = dx / len;
          var uy  = dy / len;

          var mx = (p1.x + p2.x) / 2;
          var my = (p1.y + p2.y) / 2;
          var hd = DASH_LEN * 0.5;
          var sx = mx - ux * hd;
          var sy = my - uy * hd;
          var ex = mx + ux * hd;
          var ey = my + uy * hd;

          ctx.beginPath();
          ctx.moveTo(sx, sy);
          ctx.lineTo(ex, ey);
          ctx.strokeStyle = trailColor + (t * 0.55) + ')';
          ctx.lineWidth   = 1 + t * 2;
          ctx.lineCap     = 'round';
          ctx.stroke();
        }

        var pos     = getPos(angle);
        var nextPos = getPos(angle + 0.01);
        var dir     = Math.atan2(nextPos.y - pos.y, nextPos.x - pos.x);
        drawPlane(pos.x, pos.y, dir);

        angle += SPEED;
        requestAnimationFrame(frame);
      }
      frame();
    })();

    // ---- Switch between Log In and Request Access views ----
    var viewLogin = document.getElementById('view-login');
    var viewRequest = document.getElementById('view-request');
    function showView(name){
      viewLogin.classList.toggle('active', name === 'login');
      viewRequest.classList.toggle('active', name === 'request');
    }
    document.getElementById('goToRequest').addEventListener('click', function(){ showView('request'); });
    document.getElementById('goToLogin').addEventListener('click', function(){ showView('login'); });

    // ---- Password show/hide ----
    function addPasswordToggle(inputId, buttonId){
      var input = document.getElementById(inputId);
      var button = document.getElementById(buttonId);
      button.addEventListener('click', function(){
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        button.innerHTML = showing ? "<i class='bx bx-show'></i>" : "<i class='bx bx-hide'></i>";
      });
    }
    addPasswordToggle('pwInput', 'pwToggle');
    addPasswordToggle('reqPassword', 'reqPasswordToggle');
    addPasswordToggle('reqPassword2', 'reqPassword2Toggle');

    // ---- Toast helper ----
    var toastEl = document.getElementById('toast');
    var toastTextEl = document.getElementById('toastText');
    function showToast(msg){
      toastTextEl.textContent = msg;
      toastEl.classList.add('show');
      setTimeout(function(){ toastEl.classList.remove('show'); }, 3200);
    }

    var requestFeedback = document.getElementById('requestFeedback');
    var requestFeedbackBox = document.getElementById('requestFeedbackBox');
    var requestFeedbackTitle = document.getElementById('requestFeedbackTitle');
    var requestFeedbackMessage = document.getElementById('requestFeedbackMessage');
    function showRequestFeedback(title, message, type){
      requestFeedbackTitle.textContent = title;
      requestFeedbackMessage.textContent = message;
      requestFeedbackBox.className = 'request-feedback-box ' + (type || 'error');
      requestFeedback.classList.add('show');
      document.getElementById('requestFeedbackOk').focus();
    }
    function closeRequestFeedback(){
      requestFeedback.classList.remove('show');
    }
    document.getElementById('requestFeedbackOk').addEventListener('click', closeRequestFeedback);
    document.getElementById('requestFeedbackClose').addEventListener('click', closeRequestFeedback);
    requestFeedback.addEventListener('click', function(event){
      if (event.target === requestFeedback) closeRequestFeedback();
    });
    document.addEventListener('keydown', function(event){
      if (event.key === 'Escape' && requestFeedback.classList.contains('show')) closeRequestFeedback();
    });

    // ---- Request form: validate fields and ID before sending ----
    var requestForm = document.getElementById('requestForm');
    requestForm.addEventListener('submit', function(e){
      if (!requestForm.checkValidity()) {
        e.preventDefault();
        var invalidField = requestForm.querySelector(':invalid');
        showRequestFeedback('Check the request form', invalidField ? invalidField.validationMessage : 'Complete all required fields.');
        if (invalidField) invalidField.focus();
        return;
      }
      var p1 = document.getElementById('reqPassword').value;
      var p2 = document.getElementById('reqPassword2').value;
      if (p1.length < 8) {
        e.preventDefault();
        showRequestFeedback('Password is too short', 'Use at least 8 characters for your password.');
        return;
      }
      if (p1 !== p2) {
        e.preventDefault();
        showRequestFeedback('Passwords do not match', 'Enter the same password in both password fields.');
        return;
      }

      var idInput = document.getElementById('reqIdDocument');
      var idFile = idInput.files && idInput.files[0];
      if (!idFile) {
        e.preventDefault();
        showRequestFeedback('ID document required', 'Choose a school or work ID file before submitting.');
        idInput.focus();
        return;
      }
      if (idFile.size > 5 * 1024 * 1024) {
        e.preventDefault();
        showRequestFeedback('ID file is too large', 'Choose a PDF, JPG, or PNG file that is 5 MB or smaller.');
        idInput.focus();
        return;
      }
      var fileExtension = idFile.name.split('.').pop().toLowerCase();
      if (['pdf', 'jpg', 'jpeg', 'png'].indexOf(fileExtension) === -1) {
        e.preventDefault();
        showRequestFeedback('Unsupported ID file', 'Use a PDF, JPG, or PNG file. Photos saved as HEIC must be converted to JPG or PNG first.');
        idInput.focus();
        return;
      }
    });

    // ---- Persistent request result dialog ----
    (function(){
      var params = new URLSearchParams(window.location.search);
      var err = params.get('error');
      if (params.get('requested') === '1') {
        showRequestFeedback('Request submitted', 'Your access request and ID document were received. An administrator will review them.', 'success');
      } else if (err === 'password') {
        showView('request');
        showRequestFeedback('Check your password', 'Passwords must match and be at least 8 characters long.');
      } else if (err === 'request') {
        showView('request');
        showRequestFeedback('Check the request details', 'Enter your name, a valid email address, your school or institution, and a reason for access.');
      } else if (err === 'csrf') {
        showView('request');
        showRequestFeedback('Form expired', 'Refresh the page, reselect your ID document, and submit the request again.');
      } else if (err === 'request_too_large') {
        showView('request');
        showRequestFeedback('Request could not be received', 'The web server rejected the total form upload size before it could read your fields. Reduce the ID file size and try again. If this continues with a file under 5 MB, the server upload limit needs to be increased.');
      } else if (err === 'id_missing') {
        showView('request');
        showRequestFeedback('ID document missing', 'The request arrived without an ID file. Select the file again and resubmit.');
      } else if (err === 'id_document') {
        showView('request');
        showRequestFeedback('ID file could not be read', 'Reselect the file and try again. Supported files are PDF, JPG, and PNG.');
      } else if (err === 'id_type') {
        showView('request');
        showRequestFeedback('Unsupported ID file type', 'The file contents are not a supported PDF, JPG, or PNG. If this is a phone photo saved as HEIC, convert it to JPG or PNG.');
      } else if (err === 'id_size') {
        showView('request');
        showRequestFeedback('ID file is too large', 'Choose a PDF, JPG, or PNG file that is 5 MB or smaller.');
      } else if (err === 'upload_partial') {
        showView('request');
        showRequestFeedback('ID upload was interrupted', 'The server received only part of the file. Check your connection, reselect the ID, and submit again.');
      } else if (err === 'upload_server') {
        showView('request');
        showRequestFeedback('ID upload could not be saved', 'The server could not temporarily receive the file. Contact the site administrator if trying again does not work.');
      } else if (err === 'upload_blocked') {
        showView('request');
        showRequestFeedback('ID upload was blocked', 'The server rejected this upload. Contact the site administrator for help.');
      } else if (err === 'id_storage') {
        showView('request');
        showRequestFeedback('ID could not be stored securely', 'The request was not submitted because the server could not securely save the ID document. Contact the site administrator.');
      } else if (err === 'database_schema') {
        showView('request');
        showRequestFeedback('Access-request setup is incomplete', 'The database needs the ID-upload update before it can accept requests. Ask the administrator to apply the latest database.sql migration.');
      } else if (err === 'database') {
        showView('request');
        showRequestFeedback('Request could not be saved', 'A database error stopped the request from being submitted. Your name, email, school, and reason have been kept; reselect your ID and try again. If this continues, contact the site administrator.');
      } else if (err === 'exists') {
        showView('request');
        showRequestFeedback('Email already registered', 'An account with this email already exists. Sign in with that email or use another address.');
      } else if (err === 'pending') {
        showView('request');
        showRequestFeedback('Request already pending', 'A request for this email is already waiting for administrator review.');
      } else if (err === 'invalid') {
        showToast('Incorrect email or password, or the account is inactive');
      }
      if (params.has('requested') || params.has('error')) {
        history.replaceState(null, '', window.location.pathname);
      }
    })();
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>