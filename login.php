<?php
require_once __DIR__ . '/auth-guard.php';
if (current_user()) redirect_for_role(current_user()['role']);
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

  /* Role tabs: Teacher / Admin only */
  .role-tabs{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
    margin-bottom:24px;
  }
  .role-tab{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:6px;
    padding:14px 8px;
    border-radius:14px;
    border:1.5px solid var(--border);
    background:var(--bg);
    font-size:12.5px;
    font-weight:700;
    color:var(--muted);
    cursor:pointer;
    user-select:none;
    transition:background .15s ease, color .15s ease, border-color .15s ease, transform .12s steps(2);
  }
  .role-tab .bx{font-size:19px;}
  .role-tab:hover{transform:translate(-1px,-1px);}
  .role-tab.active{
    background:var(--green-light);
    color:var(--green-dark);
    border-color:var(--green);
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
    .role-tabs{margin-bottom:14px;gap:8px;}
    .role-tab{flex-direction:row;padding:10px 8px;gap:8px;}
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
          <div class="form-row field-icon-row">
            <label>Password</label>
            <i class='bx bx-lock-alt field-lead'></i>
            <input type="password" name="password" id="pwInput" placeholder="Enter your password" required>
            <button type="button" class="field-trail" id="pwToggle" aria-label="Show password"><i class='bx bx-hide'></i></button>
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

        <div class="role-tabs">
          <div class="role-tab active" data-role="teacher">
            <i class='bx bx-chalkboard'></i> Teacher
          </div>
          <div class="role-tab" data-role="admin">
            <i class='bx bx-shield-quarter'></i> Admin
          </div>
        </div>

        <div class="access-note">
          <i class='bx bx-info-circle'></i>
          <div>
            <b>ReadPilot is invite-only</b>
            Reserved for verified educators and school administrators. Our team reviews every request before granting access.
          </div>
        </div>

        <form id="requestForm" action="auth.php" method="post">
          <input type="hidden" name="action" value="request_access">
          <input type="hidden" name="role" id="requestRole" value="teacher">
          <div class="form-row field-icon-row">
            <label>Full name</label>
            <i class='bx bx-user field-lead'></i>
            <input type="text" id="reqName" name="name" placeholder="e.g. Maria Hernandez" required>
          </div>
          <div class="form-row field-icon-row">
            <label>School email</label>
            <i class='bx bx-envelope field-lead'></i>
            <input type="email" id="reqEmail" name="email" placeholder="you@school.edu" required>
          </div>
          <div class="form-row field-icon-row">
            <label>Password</label>
            <i class='bx bx-lock-alt field-lead'></i>
            <input type="password" id="reqPassword" name="password" minlength="8" placeholder="At least 8 characters" autocomplete="new-password" required>
          </div>
          <div class="form-row field-icon-row">
            <label>Confirm password</label>
            <i class='bx bx-lock-alt field-lead'></i>
            <input type="password" id="reqPassword2" name="password_confirm" minlength="8" placeholder="Re-enter your password" autocomplete="new-password" required>
          </div>
          <div class="form-row field-icon-row">
            <label>School / institution</label>
            <i class='bx bx-building-house field-lead'></i>
            <input type="text" id="reqSchool" name="school" placeholder="e.g. Rosewood Elementary" required>
          </div>
          <div class="form-row field-icon-row textarea-row">
            <label>Reason for access</label>
            <i class='bx bx-message-detail field-lead'></i>
            <textarea class="form-textarea" id="reqReason" name="reason" placeholder="Tell us about your role and how you'll use ReadPilot..." required></textarea>
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

    // ---- Role tabs (Teacher / Admin) — kept in sync across both views ----
    var selectedRole = 'teacher';
    document.querySelectorAll('.role-tab').forEach(function(tab){
      tab.addEventListener('click', function(){
        var role = tab.getAttribute('data-role');
        selectedRole = role;
        document.getElementById('requestRole').value = role;
        document.querySelectorAll('.role-tab').forEach(function(t){
          t.classList.toggle('active', t.getAttribute('data-role') === role);
        });
      });
    });

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
    var pwInput = document.getElementById('pwInput');
    var pwToggle = document.getElementById('pwToggle');
    pwToggle.addEventListener('click', function(){
      var showing = pwInput.type === 'text';
      pwInput.type = showing ? 'password' : 'text';
      pwToggle.innerHTML = showing ? "<i class='bx bx-hide'></i>" : "<i class='bx bx-show'></i>";
    });

    // ---- Toast helper ----
    var toastEl = document.getElementById('toast');
    var toastTextEl = document.getElementById('toastText');
    function showToast(msg){
      toastTextEl.textContent = msg;
      toastEl.classList.add('show');
      setTimeout(function(){ toastEl.classList.remove('show'); }, 3200);
    }

    // ---- Request form: role + password validation ----
    document.getElementById('requestForm').addEventListener('submit', function(e){
      document.getElementById('requestRole').value = selectedRole;
      var p1 = document.getElementById('reqPassword').value;
      var p2 = document.getElementById('reqPassword2').value;
      if (p1.length < 8) { e.preventDefault(); showToast('Password must be at least 8 characters'); return; }
      if (p1 !== p2)     { e.preventDefault(); showToast('Passwords do not match'); }
    });

    // ---- Messages coming back from auth.php (?requested=1 / ?error=password) ----
    (function(){
      var params = new URLSearchParams(window.location.search);
      var err = params.get('error');
      if (params.get('requested') === '1') {
        showToast('Request submitted! We\'ll review it soon.');
      } else if (err === 'password') {
        showView('request');
        showToast('Passwords must match and be at least 8 characters');
      } else if (err === 'request') {
        showView('request');
        showToast('Please fill in all fields with a valid email');
      } else if (err === 'database') {
        showView('request');
        showToast('We could not submit your request. Please try again or contact an administrator.');
      } else if (err === 'exists') {
        showToast('An account with that email already exists. Please sign in.');
      } else if (err === 'pending') {
        showView('request');
        showToast('A request for that email is already waiting for review');
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