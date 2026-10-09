<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Sessions</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script>
  /* Apply saved theme/sidebar state before paint to avoid a flash of the
     wrong state. This sets data-theme / data-sidebar on <html>, which is
     the same mechanism used on every other page (see style.css). */
(function () {
  try {
    var savedTheme = localStorage.getItem('readpilot-theme');
    if (savedTheme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  } catch (e) {
    /* localStorage unavailable (e.g. private browsing) — default to light */
  }
 
  try {
    var savedSidebar = localStorage.getItem('readpilot-sidebar');
    if (savedSidebar === 'collapsed') {
      document.documentElement.setAttribute('data-sidebar', 'collapsed');
    }
  } catch (e) {
    /* localStorage unavailable — default to expanded */
  }
})();
</script>
<style>
  :root{
    --sidebar-bg: #163828;
    --sidebar-bg-light: #1d4633;
    --sidebar-active: #dff0d8;
    --sidebar-text: #cfe3d7;
    --bg: #f6f8f2;
    --card: #ffffff;
    --ink: #1c2b23;
    --muted: #7c8d82;
    --green: #6fbf5a;
    --green-dark: #3f7d4a;
    --green-light: #e6f4e1;
    --purple: #8b6bd1;
    --purple-light: #ece5fb;
    --orange: #f2a13a;
    --orange-light: #fdecd6;
    --red: #ea5d5d;
    --red-light: #fce3e3;
    --teal: #4fa3b8;
    --teal-light: #e1f1f5;
    --tan: #c9924d;
    --tan-light: #faf0dd;
    --border: #ebefe6;
    --radius: 18px;
    --shadow: 0 4px 18px rgba(30, 60, 40, 0.06);
  }
  *{box-sizing:border-box;}
  body{
    margin:0;
    font-family:'Nunito', sans-serif;
    background:var(--bg);
    color:var(--ink);
    display:flex;
    min-height:100vh;
  }

  /* ---------- Sidebar ---------- */
  .sidebar{
    width:250px;
    flex-shrink:0;
    background:linear-gradient(180deg, var(--sidebar-bg) 0%, #102c1f 100%);
    color:var(--sidebar-text);
    padding:28px 20px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    min-height:100vh;
    position:relative;
    overflow:hidden;
  }
  .logo-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin-bottom:34px;
    padding:0 4px;
  }
  .logo-left{display:flex;align-items:center;gap:10px;}
  .logo-icon{
    width:30px;height:30px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
  }
  .logo-icon .bx{font-size:26px;color:#b1db65;transition:transform .25s ease;}
  .logo-left:hover .logo-icon .bx{transform:translate(2px,-2px) rotate(-4deg);}

  .hamburger{
    width:32px;height:32px;flex-shrink:0;background:transparent;border:none;cursor:pointer;
    display:flex;align-items:center;justify-content:center;border-radius:8px;
    transition:background .15s ease;
  }
  .hamburger:hover{background:rgba(255,255,255,0.08);}
  .hamburger .bar{display:block;width:18px;height:2px;background:#cfe3d7;border-radius:1px;position:relative;}
  .hamburger .bar::before,.hamburger .bar::after{
    content:"";position:absolute;left:0;width:18px;height:2px;background:#cfe3d7;border-radius:1px;transition:transform .2s ease;
  }
  .hamburger .bar::before{top:-6px;}
  .hamburger .bar::after{top:6px;}

  /* Collapsed / full-screen nav mode
     Driven by data-sidebar="collapsed" on <html> (set by the preload
     script above and by the toggle handler below) instead of a plain
     class on .sidebar — this is the same mechanism style.css and
     index.php use, so the collapsed state now persists correctly
     across every page instead of resetting on navigation. */
  html[data-sidebar="collapsed"] .sidebar{width:80px;padding-left:16px;padding-right:16px;}
  html[data-sidebar="collapsed"] .sidebar .logo-text,
  html[data-sidebar="collapsed"] .sidebar .nav-item span.label,
  html[data-sidebar="collapsed"] .sidebar .teacher-name,
  html[data-sidebar="collapsed"] .sidebar .teacher-role,
  html[data-sidebar="collapsed"] .sidebar .quote{display:none;}
  html[data-sidebar="collapsed"] .sidebar .logo-row{justify-content:center;flex-direction:column;gap:14px;}
  html[data-sidebar="collapsed"] .sidebar .nav-item{justify-content:center;padding:11px 0;}
  html[data-sidebar="collapsed"] .sidebar .teacher-row{width:36px;gap:0;justify-content:center;}
  html[data-sidebar="collapsed"] .sidebar .teacher-card{padding:10px 4px;display:flex;justify-content:center;}
  html[data-sidebar="collapsed"] .sidebar .avatar{margin-inline:auto;}
  .sidebar{transition:width .25s ease, padding .25s ease;}
  .logo-text{display:flex;flex-direction:column;line-height:1.15;}
  .logo-text .brand{font-family:'Poppins',sans-serif;font-weight:700;font-size:19px;color:#ffffff;letter-spacing:.2px;}
  .logo-text .tagline{font-size:10.5px;color:#9fc3ac;font-weight:600;letter-spacing:.3px;}

  nav{display:flex;flex-direction:column;gap:4px;flex-grow:1;}
  .nav-item{
    display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:12px;
    color:var(--sidebar-text);font-size:14.5px;font-weight:600;cursor:pointer;
    transition:background .15s ease, color .15s ease, transform .12s steps(2);
    text-decoration:none;
  }
  .nav-item .bx,
  .nav-item svg{width:18px;height:18px;font-size:18px;opacity:.9;flex-shrink:0;}
  .nav-item[href="settings.php"] svg{display:none;}
  .nav-item:hover{background:rgba(255,255,255,0.06);transform:translate(-2px,-2px);}
  .nav-item.active{background:var(--sidebar-active);color:#1c3d2b;}
  .nav-item.active svg{opacity:1;}

  .teacher-card{background:rgba(255,255,255,0.06);border-radius:16px;padding:14px;}
  .teacher-row{display:flex;align-items:center;gap:10px;}
  .avatar{
    width:36px;height:36px;border-radius:50%;background:#e7c58a;
    display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;
  }
  .teacher-name{font-size:13.5px;font-weight:700;color:#fff;}
  .teacher-role{font-size:11.5px;color:#9fc3ac;}
  .quote{
    margin-top:12px;font-size:11.5px;font-style:italic;color:#a9c9b8;line-height:1.5;
    border-top:1px solid rgba(255,255,255,0.08);padding-top:10px;
  }
  .quote .heart{color:#e88;}

  /* ---------- Main ---------- */
  .main{flex-grow:1; width:calc(100% - 250px); max-width:none; margin:0 0 0 250px; padding:32px 40px 48px; position:relative;}

  .topbar{
    display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:26px;
  }
  .title-block h1{font-family:'Poppins',sans-serif;font-size:28px;margin:0 0 4px 0;color:var(--ink);}
  .title-block .greet{font-size:14.5px;color:var(--muted);font-weight:600;}
  .topbar-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
  .search{
    display:flex;align-items:center;gap:8px;background:var(--card);border:1px solid var(--border);
    border-radius:12px;padding:10px 16px;width:300px;color:var(--muted);font-size:13.5px;box-shadow:var(--shadow);
  }
  .search svg{width:15px;height:15px;flex-shrink:0;}
  .search input{border:none;outline:none;background:transparent;font-family:inherit;font-size:13.5px;color:var(--ink);width:100%;}
  .search input::placeholder{color:var(--muted);}
  .btn-new{
    display:flex;align-items:center;gap:8px;background:var(--green);color:#fff;border:none;border-radius:12px;
    padding:11px 18px;font-size:13.5px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(111,191,90,0.35);
    font-family:inherit;transition:transform .12s steps(2), box-shadow .12s steps(2);
  }
  .btn-new svg{width:15px;height:15px;}
  .btn-new:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark), 0 4px 12px rgba(111,191,90,0.35);}
  /* ---------- Controls row ---------- */
  .controls{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px;}
  .tabs{display:flex;gap:6px;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:5px;box-shadow:var(--shadow);}
  .tab{padding:8px 14px;border-radius:9px;font-size:13px;font-weight:700;color:var(--muted);cursor:pointer;white-space:nowrap;transition:background .15s ease, color .15s ease;}
  .tab:hover{color:var(--ink);}
  .tab.active{background:var(--green-light);color:var(--green-dark);}
  .filter-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
  .sel{
    background:var(--card);border:1px solid var(--border);border-radius:12px;padding:10px 14px;font-size:13px;font-weight:700;
    color:var(--ink);font-family:inherit;box-shadow:var(--shadow);cursor:pointer;outline:none;
  }
  .results-count{font-size:12.5px;color:var(--muted);font-weight:700;}

  /* ---------- Sessions panel ---------- */
  .panel{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:24px 26px;margin-bottom:24px;}
  .panel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:10px;}
  .panel-title{display:flex;align-items:center;gap:10px;font-family:'Poppins',sans-serif;font-size:16.5px;font-weight:700;color:var(--ink);}
  .panel-title svg{width:18px;height:18px;color:var(--green-dark);}
  .live-pill{display:flex;align-items:center;gap:5px;background:var(--green-light);color:var(--green-dark);font-size:11px;font-weight:800;padding:4px 10px;border-radius:20px;}
  .live-dot{width:6px;height:6px;border-radius:50%;background:var(--green-dark);}

  .table-head{
    display:flex;align-items:center;gap:14px;padding:10px 4px;
    font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;
    border-bottom:1px solid var(--border);
  }
  .session-row{display:flex;align-items:center;gap:14px;padding:14px 4px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s ease;}
  .session-row:hover{background:var(--bg);}
  .session-row:last-of-type{border-bottom:none;}
  .s-avatar{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff;flex-shrink:0;}
  .s-info{flex-grow:1;min-width:180px;}
  .s-name{font-size:14px;font-weight:700;color:var(--ink);}
  .s-book{font-size:12.5px;color:var(--muted);font-weight:600;}
  .s-time{display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--muted);font-weight:600;width:190px;flex-shrink:0;}
  .s-time svg{width:13px;height:13px;flex-shrink:0;}
  .s-wpm{width:80px;flex-shrink:0;font-size:13.5px;font-weight:700;color:var(--ink);text-align:right;}
  .s-badge{width:70px;flex-shrink:0;text-align:center;font-size:12px;font-weight:800;padding:5px 0;border-radius:20px;}
  .s-badge.good{background:var(--green-light);color:var(--green-dark);}
  .s-badge.mid{background:var(--orange-light);color:var(--orange);}
  .s-badge.low{background:var(--red-light);color:var(--red);}
  .s-arrow{
    width:34px;height:34px;border-radius:50%;border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;
    color:var(--green-dark);flex-shrink:0;cursor:pointer;background:var(--card);transition:transform .12s steps(2), box-shadow .12s steps(2), border-color .12s ease;
  }
  .s-arrow svg{width:14px;height:14px;}
  .s-arrow:hover{transform:translate(-2px,-2px);box-shadow:2px 2px 0 var(--green-dark);border-color:var(--green-dark);}

  .load-more{
    display:flex;align-items:center;gap:8px;margin:18px auto 0 auto;background:var(--green-light);color:var(--green-dark);
    border:none;border-radius:12px;padding:12px 26px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;
    transition:transform .12s steps(2), box-shadow .12s steps(2);
  }
  .load-more svg{width:14px;height:14px;}
  .load-more:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark);}

  .empty-state{padding:50px 20px;text-align:center;color:var(--muted);}
  .empty-state .bx{font-size:34px;color:var(--green);margin-bottom:10px;display:block;}
  .empty-state b{color:var(--ink);display:block;font-size:15px;margin-bottom:4px;}

  /* ---------- Tip banner ---------- */
  .tip{display:flex;align-items:center;gap:16px;background:var(--green-light);border-radius:var(--radius);padding:20px 24px;position:relative;}
  .tip-icon{width:44px;height:44px;border-radius:50%;background:#fff3c4;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .tip-icon svg{width:22px;height:22px;color:#e8a531;}
  .tip-title{font-size:14.5px;font-weight:800;color:var(--ink);margin-bottom:2px;}
  .tip-text{font-size:13px;color:#3d5a48;font-weight:600;}
  .tip-close{
    position:absolute;right:20px;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:50%;
    background:rgba(255,255,255,0.6);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--green-dark);
  }
  .tip-close svg{width:13px;height:13px;}

  /* Dark-mode overrides for the handful of colors above that are
     intentionally NOT theme variables (custom accent tones), plus
     anywhere this page's local styles previously hardcoded a raw
     hex value and blocked the shared dark-mode variables from
     reaching it — see style.css for the equivalent pattern used on
     every other page. */
  html[data-theme="dark"] .tip-icon{background:#4a3c1a;}
  html[data-theme="dark"] .tip-text{color:#bcd6c5;}

  @media (max-width:1100px){
    .search{width:200px;}
  }
  @media (max-width:900px){
    .s-time{display:none;}
    .table-head .th-time{display:none;}
  }

  /* ---------- Modals ---------- */
  .overlay{position:fixed;inset:0;background:rgba(16,32,22,0.45);display:none;align-items:center;justify-content:center;z-index:100;padding:20px;}
  .overlay.open{display:flex;}
  .modal{background:var(--card);color:var(--ink);border-radius:20px;max-width:480px;width:100%;max-height:88vh;overflow-y:auto;padding:26px;box-shadow:0 20px 60px rgba(16,32,22,0.25);position:relative;}
  .modal-close{
    position:absolute;top:18px;right:18px;width:32px;height:32px;border-radius:50%;background:var(--bg);border:none;
    display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--ink);
  }
  .modal-close svg{width:14px;height:14px;}
  .modal-head{display:flex;align-items:center;gap:14px;margin-bottom:20px;padding-right:30px;}
  .modal-avatar{width:52px;height:52px;border-radius:50%;color:#fff;font-weight:800;font-size:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .modal-head h2{font-family:'Poppins',sans-serif;font-size:18px;margin:0 0 2px 0;color:var(--ink);}
  .modal-head .sub{font-size:12.5px;color:var(--muted);font-weight:700;}
  .modal-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px;}
  .m-stat{background:var(--bg);border-radius:12px;padding:12px 10px;text-align:center;}
  .m-stat .v{font-family:'Poppins',sans-serif;font-size:18px;font-weight:700;color:var(--ink);}
  .m-stat .l{font-size:10px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.3px;margin-top:2px;}
  .detail-line{display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted);font-weight:600;padding:9px 0;border-top:1px solid var(--border);}
  .detail-line svg{width:14px;height:14px;color:var(--green-dark);flex-shrink:0;}
  .detail-line span.b{color:var(--ink);font-weight:700;}
  .modal-actions{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap;}
  .btn-primary{
    flex:1;background:var(--green);color:#fff;border:none;border-radius:12px;padding:11px 16px;font-size:13px;
    font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:6px;
  }
  .btn-primary svg{width:14px;height:14px;}
  .btn-primary:hover{background:var(--green-dark);}
  .btn-secondary{background:var(--bg);color:var(--ink);border:1px solid var(--border);border-radius:12px;padding:11px 16px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;}
  .btn-secondary:hover{background:var(--border);}
  .btn-danger-text{width:100%;text-align:center;background:none;border:none;color:var(--red);font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;padding:10px;margin-top:6px;}
  .btn-danger-text:hover{text-decoration:underline;}

  .form-row{margin-bottom:14px;}
  .form-row label{display:block;font-size:12.5px;font-weight:700;color:var(--ink);margin-bottom:6px;}
  .form-row input, .form-row select{width:100%;border:1px solid var(--border);border-radius:12px;padding:11px 13px;font-family:inherit;font-size:13.5px;color:var(--ink);outline:none;background:var(--card);}
  .form-row input:focus, .form-row select:focus{border-color:var(--green);}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
  .hint{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:-8px;margin-bottom:14px;}

  /* Toast */
  .toast{
    position:fixed;bottom:24px;right:24px;background:#16281d;color:#fff;padding:13px 20px;border-radius:12px;
    font-size:13px;font-weight:700;box-shadow:0 10px 30px rgba(0,0,0,0.25);z-index:200;display:flex;align-items:center;gap:10px;
    opacity:0;transform:translateY(10px);pointer-events:none;transition:opacity .25s ease, transform .25s ease;
  }
  .toast.show{opacity:1;transform:translateY(0);}
  .toast .bx{color:#b1db65;font-size:16px;}
  html[data-theme="dark"] .toast{background:var(--card);color:var(--ink);}
  html[data-theme="dark"] .toast .bx{color:#8fd67c;}

  /* Pixel decorations */
  .pixel-plane{position:absolute;top:var(--y, 12%);left:0;z-index:1;pointer-events:none;image-rendering:pixelated;opacity:0;animation:flyThroughSidebar var(--dur, 14s) linear infinite;animation-delay:var(--delay, 0s);}
  .pixel-plane svg{width:26px;height:18px;display:block;shape-rendering:crispEdges;}
  @keyframes flyThroughSidebar{
    0%{transform:translate(-30px, 0);opacity:0;}
    10%{opacity:.9;}
    50%{transform:translate(115px, -6px);}
    90%{opacity:.9;}
    100%{transform:translate(270px, 4px);opacity:0;}
  }
  @media (prefers-reduced-motion: reduce){.pixel-plane{display:none;}}
</style>
</head>
<body>

  <!-- ================= SIDEBAR ================= -->
  <aside class="sidebar">
    <div class="pixel-plane" style="--y:14%; --dur:13s; --delay:0s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2"  height="1" fill="#cbe98f"/>
        <rect x="0" y="1" width="4"  height="1" fill="#cbe98f"/>
        <rect x="0" y="2" width="6"  height="1" fill="#cbe98f"/>
        <rect x="0" y="3" width="9"  height="1" fill="#cbe98f"/>
        <rect x="0" y="4" width="13" height="1" fill="#b1db65"/>
        <rect x="0" y="5" width="9"  height="1" fill="#7fae55"/>
        <rect x="0" y="6" width="6"  height="1" fill="#7fae55"/>
        <rect x="0" y="7" width="4"  height="1" fill="#7fae55"/>
        <rect x="0" y="8" width="2"  height="1" fill="#7fae55"/>
      </svg>
    </div>
    <div class="pixel-plane" style="--y:74%; --dur:17s; --delay:6s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2"  height="1" fill="#cbe98f"/>
        <rect x="0" y="1" width="4"  height="1" fill="#cbe98f"/>
        <rect x="0" y="2" width="6"  height="1" fill="#cbe98f"/>
        <rect x="0" y="3" width="9"  height="1" fill="#cbe98f"/>
        <rect x="0" y="4" width="13" height="1" fill="#b1db65"/>
        <rect x="0" y="5" width="9"  height="1" fill="#7fae55"/>
        <rect x="0" y="6" width="6"  height="1" fill="#7fae55"/>
        <rect x="0" y="7" width="4"  height="1" fill="#7fae55"/>
        <rect x="0" y="8" width="2"  height="1" fill="#7fae55"/>
      </svg>
    </div>

    <div>
      <div class="logo-row">
        <div class="logo-left">
          <div class="logo-icon"><i class='bx bxs-paper-plane'></i></div>
          <div class="logo-text">
            <span class="brand">ReadPilot</span>
            <span class="tagline">Guide. Read. Grow.</span>
          </div>
        </div>
        <button class="hamburger" id="sidebarToggle" aria-label="Toggle navigation">
          <span class="bar"></span>
        </button>
      </div>

      <nav>
        <a class="nav-item" href="index.php">
          <i class="bx bxs-dashboard"></i>
          <span class="label">Dashboard</span>
        </a>
        <a class="nav-item" href="students.php">
          <i class="bx bx-group"></i>
          <span class="label">Sections</span>
        </a>
        <a class="nav-item active" href="sessions.php">
          <i class="bx bx-calendar"></i>
          <span class="label">Sessions</span>
        </a>
        <a class="nav-item" href="reports.php">
          <i class="bx bx-file"></i>
          <span class="label">Reports</span>
        </a>
        <a class="nav-item" href="struggle-map.php">
          <i class="bx bx-target-lock"></i>
          <span class="label">Struggle Map</span>
        </a>
        <a class="nav-item" href="recommendations.php">
          <i class="bx bx-bulb"></i>
          <span class="label">Recommendations</span>
        </a>
        <a class="nav-item" href="resources.php">
          <i class="bx bx-book-open"></i>
          <span class="label">Resources</span>
        </a>
        <a class="nav-item" href="recordings.php">
          <i class="bx bx-cloud-upload"></i>
          <span class="label">Cloud Recordings</span>
        </a>
        <a class="nav-item" href="settings.php">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.14.36.19.75.15 1.15Z"/></svg>
          <i class="bx bx-cog"></i>
          <span class="label">Settings</span>
        </a>
      </nav>
    </div>

    <div class="teacher-card">
      <div class="teacher-row">
        <div class="teacher-row-info">
          <div class="avatar"><?php include __DIR__ . '/profile-avatar.php'; ?></div>
          <div>
            <div class="teacher-name"><?= htmlspecialchars(current_user()['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="teacher-role">Grade 3 Teacher</div>
          </div>
        </div>
        <a class="teacher-logout-btn" href="logout.php" title="Log out" aria-label="Log out"><i class='bx bx-log-out'></i></a>
      </div>
      <div class="quote">"Every page a child reads today is a step toward a brighter tomorrow." <span class="heart">♥</span></div>
    </div>
  </aside>

  <!-- ================= MAIN ================= -->
  <main class="main">
    <div class="topbar">
      <div class="title-block">
        <h1>Sessions</h1>
        <div class="greet">Every reading session your class has logged, in one place.</div>
      </div>
      <div class="topbar-actions">
        <div class="search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" id="searchInput" placeholder="Search by student or book...">
        </div>
        <button class="btn-new" id="newSessionBtn" disabled>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
          New Session
        </button>
      </div>
    </div>

    <div class="controls">
      <div class="tabs" id="filterTabs">
        <div class="tab active" data-range="all">All Time</div>
        <div class="tab" data-range="today">Today</div>
        <div class="tab" data-range="week">This Week</div>
      </div>
      <div class="filter-right">
        <span class="results-count" id="resultsCount"></span>
        <select class="sel" id="sectionFilter">
          <option value="all">All Sections</option>
        </select>
        <select class="sel" id="sortSelect">
          <option value="newest">Sort: Newest First</option>
          <option value="oldest">Sort: Oldest First</option>
          <option value="wpm">Sort: WPM (High–Low)</option>
          <option value="accuracy">Sort: Accuracy (High–Low)</option>
        </select>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div class="panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2Z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7Z"/></svg>
          All Reading Sessions
          <span class="live-pill"><span class="live-dot"></span>Live</span>
        </div>
      </div>

      <div class="table-head">
        <div style="width:38px;"></div>
        <div style="flex-grow:1;min-width:180px;">Student &amp; Book</div>
        <div class="th-time" style="width:190px;">Date &amp; Time</div>
        <div style="width:80px;text-align:right;">WPM</div>
        <div style="width:70px;text-align:center;">Accuracy</div>
        <div style="width:34px;"></div>
      </div>

      <div id="sessionsList"></div>

      <button class="load-more" id="loadMoreBtn">
        Load More
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
      </button>
    </div>

    <div class="tip">
      <div class="tip-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
      </div>
      <div>
        <div class="tip-title">Tip of the day</div>
        <div class="tip-text">Tap any session to see the full breakdown, or log a new one for a student in seconds.</div>
      </div>
      <div class="tip-close" id="tipClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </div>
    </div>
  </main>

  <!-- ================= SESSION DETAIL MODAL ================= -->
  <div class="overlay" id="detailOverlay">
    <div class="modal" id="detailModal"></div>
  </div>

  <!-- ================= NEW SESSION MODAL ================= -->
  <div class="overlay" id="newOverlay">
    <div class="modal">
      <button class="modal-close" id="newCloseBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
      <div class="modal-head">
        <div class="modal-avatar" style="background:var(--green);">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2Z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7Z"/></svg>
        </div>
        <div>
          <h2>Log a Session</h2>
          <div class="sub">Record how a reading session went</div>
        </div>
      </div>
      <form id="newForm">
        <div class="form-row">
          <label for="newStudent">Student</label>
          <select id="newStudent" required></select>
        </div>
        <div class="form-row">
          <label for="newBook">Book</label>
          <input type="text" id="newBook" placeholder="e.g. The Ugly Duckling" required>
        </div>
        <div class="form-grid">
          <div class="form-row">
            <label for="newWpm">Words per minute</label>
            <input type="number" id="newWpm" min="1" max="600" placeholder="e.g. 250" required>
          </div>
          <div class="form-row">
            <label for="newAccuracy">Accuracy (%)</label>
            <input type="number" id="newAccuracy" min="1" max="100" placeholder="e.g. 92" required>
          </div>
        </div>
        <div class="hint">Session will be logged with today's date and time.</div>
        <div class="modal-actions">
          <button type="button" class="btn-secondary" id="newCancelBtn">Cancel</button>
          <button type="submit" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Save Session
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="toast" id="toast"><i class='bx bx-check-circle'></i><span id="toastMsg"></span></div>

  <script>
    // ============================================================
    // Roster (shared naming/colors with the Students page)
    // ============================================================
    let roster = [];
    function sectionOf(student){ return (student && student.section) ? student.section : 'Unassigned'; }
    function studentById(id){ return roster.find(r=>r.id===id); }
    function initials(name){ return name.split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase(); }

    const HOUR = 60*60*1000;
    let sessions = [];
    let searchTerm = '';
    let activeRange = 'all';
    let sectionFilterVal = 'all';
    let sortMode = 'newest';
    let visibleCount = 6;
    const PAGE_SIZE = 6;

    // ============================================================
    // Helpers
    // ============================================================
    function fmtDate(ts){
      const d = new Date(ts);
      return d.toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'}) +
             ' • ' + d.toLocaleTimeString(undefined, {hour:'numeric', minute:'2-digit'});
    }
    function accClass(a){
      if(a>=90) return 'good';
      if(a>=80) return 'mid';
      return 'low';
    }
    function isToday(ts){
      const d = new Date(ts), now = new Date();
      return d.getFullYear()===now.getFullYear() && d.getMonth()===now.getMonth() && d.getDate()===now.getDate();
    }
    function isThisWeek(ts){
      return Date.now() - ts <= 7*24*HOUR;
    }
    function showToast(msg){
      const t = document.getElementById('toast');
      document.getElementById('toastMsg').textContent = msg;
      t.classList.add('show');
      clearTimeout(window.__toastTimer);
      window.__toastTimer = setTimeout(()=>t.classList.remove('show'), 2800);
    }

    // ============================================================
    // Populate section filter + new-session select
    // ============================================================
    function populateSectionSelect(){
      const sel = document.getElementById('sectionFilter');
      const sections = [...new Set(roster.map(sectionOf))].sort();
      const current = sel.value || 'all';
      sel.innerHTML = '';
      const allOption = document.createElement('option');
      allOption.value = 'all';
      allOption.textContent = 'All Sections';
      sel.appendChild(allOption);
      sections.forEach(section=>{
        const option = document.createElement('option');
        option.value = section;
        option.textContent = section;
        sel.appendChild(option);
      });
      sel.value = sections.includes(current) ? current : 'all';
    }

    function populateStudentSelects(){
      const formSel = document.getElementById('newStudent');
      formSel.innerHTML = '';
      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = roster.length ? 'Choose a student' : 'No students available';
      formSel.appendChild(placeholder);
      roster.filter(student=>sectionFilterVal === 'all' || sectionOf(student) === sectionFilterVal).forEach(student=>{
        const option = document.createElement('option');
        option.value = student.id;
        option.textContent = student.name;
        formSel.appendChild(option);
      });
    }

    // ============================================================
    // List rendering
    // ============================================================
    function getFilteredSorted(){
      let list = sessions.filter(s=>{
        const student = studentById(s.studentId);
        if(!student) return false;
        if(activeRange==='today' && !isToday(s.ts)) return false;
        if(activeRange==='week' && !isThisWeek(s.ts)) return false;
        if(sectionFilterVal!=='all' && sectionOf(student)!==sectionFilterVal) return false;
        if(searchTerm){
          const q = searchTerm.toLowerCase();
          if(!student.name.toLowerCase().includes(q) && !s.book.toLowerCase().includes(q)) return false;
        }
        return true;
      });
      list.sort((a,b)=>{
        if(sortMode==='oldest') return a.ts-b.ts;
        if(sortMode==='wpm') return b.wpm-a.wpm;
        if(sortMode==='accuracy') return b.accuracy-a.accuracy;
        return b.ts-a.ts;
      });
      return list;
    }

    function renderList(){
      const wrap = document.getElementById('sessionsList');
      const full = getFilteredSorted();
      document.getElementById('resultsCount').textContent = full.length + (full.length===1 ? ' session' : ' sessions');

      if(full.length===0){
        wrap.innerHTML = `
          <div class="empty-state">
            <i class='bx bx-book-open bx'></i>
            <b>No sessions found</b>
            Try a different search, filter, or log a new session.
          </div>`;
        document.getElementById('loadMoreBtn').style.display = 'none';
        return;
      }

      const shown = full.slice(0, visibleCount);
      function esc(str){
        return String(str||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
      }

      wrap.innerHTML = shown.map(s=>{
        const student = studentById(s.studentId);
        const aiBadge = s.ai_status ? `<span class="ai-status-pill ${s.ai_status}">${s.ai_status.replace(/_/g, ' ')}</span>` : '';
        const quizBadge = (s.quiz_score !== null && s.quiz_total) ? `<span class="s-quiz-pill"><i class='bx bx-brain'></i> Quiz: ${s.quiz_score}/${s.quiz_total}</span>` : '';
        return `
        <div class="session-row" data-id="${s.id}">
          <div class="s-avatar" style="background:${student.color}">${initials(student.name)}</div>
          <div class="s-info">
            <div class="s-name">${esc(student.name)}</div>
            <div class="s-book" style="display:flex;align-items:center;gap:7px;margin-top:2px;flex-wrap:wrap;">
              <span>${esc(s.book)} • ${esc(sectionOf(student))}</span>
              ${aiBadge}
              ${quizBadge}
            </div>
          </div>
          <div class="s-time">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            ${fmtDate(s.ts)}
          </div>
          <div class="s-wpm">${s.wpm} WPM</div>
          <div class="s-badge ${accClass(s.accuracy)}">${s.accuracy}%</div>
          <div class="s-arrow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
          </div>
        </div>`;
      }).join('');

      wrap.querySelectorAll('.session-row').forEach(row=>{
        row.addEventListener('click', ()=>openDetail(Number(row.dataset.id)));
      });

      const btn = document.getElementById('loadMoreBtn');
      if(visibleCount < full.length){
        btn.style.display = 'flex';
        btn.textContent = '';
        btn.innerHTML = `Load More (${full.length - visibleCount} more)
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>`;
      } else {
        btn.style.display = 'none';
      }
    }

    function renderAll(){
      renderList();
    }

    document.getElementById('loadMoreBtn').addEventListener('click', ()=>{
      visibleCount += PAGE_SIZE;
      renderList();
    });

    // ============================================================
    // Detail modal
    // ============================================================
    function openDetail(id){
      const s = sessions.find(x=>x.id===id);
      if(!s) return;
      const student = studentById(s.studentId);
      const modal = document.getElementById('detailModal');

      const quizCol = (s.quiz_score !== null && s.quiz_total)
        ? `<div class="m-stat"><div class="v" style="color:var(--purple);">${s.quiz_score}/${s.quiz_total}</div><div class="l">Quiz Score</div></div>`
        : '';

      const aiDiagnostic = s.ai_narrative ? `
        <div class="modal-ai-box">
          <div class="modal-ai-head">
            <div class="modal-ai-title"><i class='bx bxs-brain'></i> AI Diagnostic</div>
            <span class="ai-status-pill ${s.ai_status || 'on_track'}">${(s.ai_status || 'on_track').replace(/_/g,' ')}</span>
          </div>
          <div class="modal-ai-narrative">${esc(s.ai_narrative)}</div>
          ${s.ai_fluency ? `<div class="modal-ai-field"><b>Fluency:</b> ${esc(s.ai_fluency)}</div>` : ''}
          ${s.actionable_next_step ? `<div class="modal-ai-field advice"><b>Teacher Advice:</b> ${esc(s.actionable_next_step)}</div>` : ''}
        </div>` : '';

      modal.innerHTML = `
        <button class="modal-close" id="detailCloseBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
        <div class="modal-head">
          <div class="modal-avatar" style="background:${student.color}">${initials(student.name)}</div>
          <div>
            <h2>${esc(student.name)}</h2>
            <div class="sub">${esc(s.book)}</div>
          </div>
        </div>
        <div class="modal-stats" style="${quizCol ? 'grid-template-columns:repeat(4,1fr);' : ''}">
          <div class="m-stat"><div class="v">${s.wpm}</div><div class="l">WPM</div></div>
          <div class="m-stat"><div class="v">${s.accuracy}%</div><div class="l">Accuracy</div></div>
          ${quizCol}
          <div class="m-stat"><div class="v">${accClass(s.accuracy)==='good'?'Strong':accClass(s.accuracy)==='mid'?'Steady':'Needs Work'}</div><div class="l">Result</div></div>
        </div>
        ${aiDiagnostic}
        <div class="detail-line">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
          <span class="b">${fmtDate(s.ts)}</span>
        </div>
        <div class="detail-line">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2Z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7Z"/></svg>
          <span class="b">${esc(s.book)}</span>
        </div>
        <div class="modal-actions">
          <button class="btn-secondary" id="viewStudentBtn">View Student</button>
        </div>
        <button class="btn-danger-text" id="deleteSessionBtn">Delete Session</button>
      `;
      document.getElementById('detailCloseBtn').addEventListener('click', closeDetail);
      document.getElementById('viewStudentBtn').addEventListener('click', ()=>{
        window.location.href = 'students.php';
      });
      document.getElementById('deleteSessionBtn').addEventListener('click', ()=>deleteSession(s.id));
      document.getElementById('detailOverlay').classList.add('open');
    }
    function closeDetail(){
      document.getElementById('detailOverlay').classList.remove('open');
    }
    document.getElementById('detailOverlay').addEventListener('click', e=>{
      if(e.target.id==='detailOverlay') closeDetail();
    });

    function deleteSession(id){
      const s = sessions.find(x=>x.id===id);
      if(!s) return;
      const student = studentById(s.studentId);
      if(!confirm(`Delete this session for ${student.name}? This can't be undone.`)) return;
      fetch('student-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'delete_session', id:id})})
        .then(response=>response.json().then(data=>({ok:response.ok,data:data})))
        .then(result=>{
          if(!result.ok) throw new Error(result.data.error || 'Unable to delete session');
          sessions = sessions.filter(x=>x.id!==id);
          closeDetail();
          renderAll();
          showToast('Session deleted');
        })
        .catch(error=>showToast(error.message));
    }

    // ============================================================
    // New session modal
    // ============================================================
    function openNewModal(){
      document.getElementById('newForm').reset();
      document.getElementById('newOverlay').classList.add('open');
      syncBookPlaceholder();
    }
    function closeNewModal(){
      document.getElementById('newOverlay').classList.remove('open');
    }
    function syncBookPlaceholder(){
      const sel = document.getElementById('newStudent');
      const student = studentById(Number(sel.value));
      if(student) document.getElementById('newBook').value = student.book;
    }
    document.getElementById('newSessionBtn').addEventListener('click', openNewModal);
    document.getElementById('newCloseBtn').addEventListener('click', closeNewModal);
    document.getElementById('newCancelBtn').addEventListener('click', closeNewModal);
    document.getElementById('newOverlay').addEventListener('click', e=>{
      if(e.target.id==='newOverlay') closeNewModal();
    });
    document.getElementById('newStudent').addEventListener('change', syncBookPlaceholder);

    document.getElementById('newForm').addEventListener('submit', e=>{
      e.preventDefault();
      const studentId = Number(document.getElementById('newStudent').value);
      const book = document.getElementById('newBook').value.trim();
      const wpm = Number(document.getElementById('newWpm').value);
      const accuracy = Number(document.getElementById('newAccuracy').value);
      if(!book || !wpm || !accuracy) return;

      fetch('student-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'record_session', student_id:studentId, book:book, wpm:wpm, accuracy:accuracy})})
        .then(response=>response.json().then(data=>({ok:response.ok,data:data})))
        .then(result=>{
          if(!result.ok) throw new Error(result.data.error || 'Unable to save session');
          sessions.unshift({id:Number(result.data.session_id), studentId, book, wpm, accuracy, ts:Date.now()});
          visibleCount = Math.max(visibleCount, PAGE_SIZE);
          closeNewModal();
          renderAll();
          const student = studentById(studentId);
          showToast(`Session logged for ${student.name}: ${wpm} WPM, ${accuracy}%`);
        })
        .catch(error=>showToast(error.message));
    });

    // ============================================================
    // Search, filter tabs, student filter, sort
    // ============================================================
    document.getElementById('searchInput').addEventListener('input', e=>{
      searchTerm = e.target.value;
      visibleCount = PAGE_SIZE;
      renderList();
    });
    document.getElementById('filterTabs').addEventListener('click', e=>{
      const tab = e.target.closest('.tab');
      if(!tab) return;
      document.querySelectorAll('#filterTabs .tab').forEach(t=>t.classList.remove('active'));
      tab.classList.add('active');
      activeRange = tab.dataset.range;
      visibleCount = PAGE_SIZE;
      renderList();
    });
    document.getElementById('sectionFilter').addEventListener('change', e=>{
      sectionFilterVal = e.target.value;
      populateStudentSelects();
      visibleCount = PAGE_SIZE;
      renderList();
    });
    document.getElementById('sortSelect').addEventListener('change', e=>{
      sortMode = e.target.value;
      renderList();
    });

    // ============================================================
    // Sidebar, bell, tip banner
    // ============================================================
    // Uses data-sidebar="collapsed" on <html> (saved to localStorage),
    // matching the pattern used on every other page (see the preload
    // script in <head> and style.css). This is what makes the sidebar
    // stay minimized as you navigate between pages, instead of
    // resetting to expanded every time.
    document.getElementById('sidebarToggle').addEventListener('click', function(){
      var isCollapsed = document.documentElement.getAttribute('data-sidebar') === 'collapsed';
      if (isCollapsed){
        document.documentElement.removeAttribute('data-sidebar');
        localStorage.setItem('readpilot-sidebar', 'expanded');
      } else {
        document.documentElement.setAttribute('data-sidebar', 'collapsed');
        localStorage.setItem('readpilot-sidebar', 'collapsed');
      }
    });
    document.getElementById('tipClose').addEventListener('click', function(){
      this.closest('.tip').style.display = 'none';
    });
    document.addEventListener('keydown', e=>{
      if(e.key==='Escape'){ closeDetail(); closeNewModal(); }
    });

    // ============================================================
    // Init
    // ============================================================
    populateSectionSelect();
    populateStudentSelects();
    renderAll();
    fetch('student-api.php?view=sessions').then(response=>response.json().then(result=>{
      if(!response.ok) throw new Error(result.error || 'Unable to load sessions');
      return result;
    })).then(result=>{
      if(!Array.isArray(result.students) || !Array.isArray(result.sessions)) throw new Error('Session data is incomplete');
      roster = result.students;
      sessions = result.sessions;
      document.getElementById('newSessionBtn').disabled = roster.length === 0;
      populateSectionSelect();
      populateStudentSelects();
      renderAll();
    }).catch(error=>{
      roster = [];
      sessions = [];
      document.getElementById('newSessionBtn').disabled = true;
      populateSectionSelect();
      populateStudentSelects();
      renderAll();
      showToast(error.message);
    });
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>