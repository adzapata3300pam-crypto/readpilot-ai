<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Reports</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script>
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
  .logo-row{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:34px;padding:0 4px;}
  .logo-left{display:flex;align-items:center;gap:10px;}
  .logo-icon{width:30px;height:30px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .logo-icon .bx{font-size:26px;color:#b1db65;transition:transform .25s ease;}
  .logo-left:hover .logo-icon .bx{transform:translate(2px,-2px) rotate(-4deg);}

  .hamburger{width:32px;height:32px;flex-shrink:0;background:transparent;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;border-radius:8px;transition:background .15s ease;}
  .hamburger:hover{background:rgba(255,255,255,0.08);}
  .hamburger .bar{display:block;width:18px;height:2px;background:#cfe3d7;border-radius:1px;position:relative;}
  .hamburger .bar::before,.hamburger .bar::after{content:"";position:absolute;left:0;width:18px;height:2px;background:#cfe3d7;border-radius:1px;transition:transform .2s ease;}
  .hamburger .bar::before{top:-6px;}
  .hamburger .bar::after{top:6px;}

  /* Collapsed / full-screen nav mode
     Driven by data-sidebar="collapsed" on <html> (set by the preload
     script above and by the toggle handler below) instead of a plain
     class on .sidebar — this is the same mechanism style.css and
     index.php use, so the collapsed state persists correctly across
     every page instead of resetting on navigation. */
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
    transition:background .15s ease, color .15s ease, transform .12s steps(2);text-decoration:none;
  }
  .nav-item .bx,
  .nav-item svg{width:18px;height:18px;font-size:18px;opacity:.9;flex-shrink:0;}
  .nav-item[href="settings.php"] svg{display:none;}
  .nav-item:hover{background:rgba(255,255,255,0.06);transform:translate(-2px,-2px);}
  .nav-item.active{background:var(--sidebar-active);color:#1c3d2b;}
  .nav-item.active svg{opacity:1;}

  .teacher-card{background:rgba(255,255,255,0.06);border-radius:16px;padding:14px;}
  .teacher-row{display:flex;align-items:center;gap:10px;}
  .avatar{width:36px;height:36px;border-radius:50%;background:#e7c58a;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
  .teacher-name{font-size:13.5px;font-weight:700;color:#fff;}
  .teacher-role{font-size:11.5px;color:#9fc3ac;}
  .quote{margin-top:12px;font-size:11.5px;font-style:italic;color:#a9c9b8;line-height:1.5;border-top:1px solid rgba(255,255,255,0.08);padding-top:10px;}
  .quote .heart{color:#e88;}

  /* ---------- Main ---------- */
  .main{flex-grow:1; width:calc(100% - 250px); max-width:none; margin:0 0 0 250px; padding:32px 40px 48px; position:relative;}

  .topbar{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:26px;}
  .title-block h1{font-family:'Poppins',sans-serif;font-size:28px;margin:0 0 4px 0;color:var(--ink);}
  .title-block .greet{font-size:14.5px;color:var(--muted);font-weight:600;}
  .topbar-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
  .tabs{display:flex;gap:6px;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:5px;box-shadow:var(--shadow);}
  .tab{padding:8px 14px;border-radius:9px;font-size:13px;font-weight:700;color:var(--muted);cursor:pointer;white-space:nowrap;transition:background .15s ease, color .15s ease;}
  .tab:hover{color:var(--ink);}
  .tab.active{background:var(--green-light);color:var(--green-dark);}
  .btn-new{
    display:flex;align-items:center;gap:8px;background:var(--green);color:#fff;border:none;border-radius:12px;
    padding:11px 18px;font-size:13.5px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(111,191,90,0.35);
    font-family:inherit;transition:transform .12s steps(2), box-shadow .12s steps(2);
  }
  .btn-new svg{width:15px;height:15px;}
  .btn-new:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark), 0 4px 12px rgba(111,191,90,0.35);}
  .btn-outline{
    display:flex;align-items:center;gap:8px;background:var(--card);color:var(--ink);border:1px solid var(--border);border-radius:12px;
    padding:11px 16px;font-size:13.5px;font-weight:700;cursor:pointer;box-shadow:var(--shadow);font-family:inherit;
    transition:transform .12s steps(2), box-shadow .12s steps(2);
  }
  .btn-outline svg{width:15px;height:15px;}
  .btn-outline:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark);}
  .bell{
    position:relative;width:42px;height:42px;border-radius:12px;background:var(--card);border:1px solid var(--border);
    display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow);flex-shrink:0;cursor:pointer;
  }
  .bell svg{width:17px;height:17px;color:var(--ink);}
  .bell .badge{position:absolute;top:-5px;right:-5px;background:var(--red);color:#fff;font-size:10px;font-weight:800;width:17px;height:17px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid var(--bg);}
  .bell-panel{position:absolute;top:52px;right:0;width:260px;background:var(--card);border-radius:14px;box-shadow:0 10px 30px rgba(20,40,28,0.16);border:1px solid var(--border);padding:8px;display:none;z-index:40;}
  .bell-panel.open{display:block;}
  .bell-item{padding:10px 10px;border-radius:10px;font-size:12.5px;font-weight:600;color:var(--ink);}
  .bell-item:hover{background:var(--bg);}
  .bell-item .sub{color:var(--muted);font-weight:600;font-size:11px;margin-top:2px;}

  /* ---------- Stat cards ---------- */
  .stats{display:grid;grid-template-columns:repeat(4, 1fr);gap:20px;margin-bottom:22px;}
  .stat-card{background:var(--card);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);display:flex;flex-direction:column;gap:14px;}
  .stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;}
  .stat-icon svg{width:22px;height:22px;}
  .stat-icon.green{background:var(--green-light);color:var(--green-dark);}
  .stat-icon.purple{background:var(--purple-light);color:var(--purple);}
  .stat-icon.orange{background:var(--orange-light);color:var(--orange);}
  .stat-icon.red{background:var(--red-light);color:var(--red);}
  .stat-label{font-size:13px;color:var(--muted);font-weight:700;}
  .stat-value{font-family:'Poppins',sans-serif;font-size:30px;font-weight:700;color:var(--ink);}
  .stat-sub{font-size:12.5px;color:var(--muted);font-weight:600;}
  .stat-sub.good{color:var(--green-dark);}
  .stat-sub.warn{color:var(--red);}

  /* ---------- Chart row ---------- */
  .chart-row{display:grid;grid-template-columns:1.6fr 1fr;gap:20px;margin-bottom:20px;}
  .panel{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:24px 26px;}
  .panel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;}
  .panel-title{display:flex;align-items:center;gap:10px;font-family:'Poppins',sans-serif;font-size:16.5px;font-weight:700;color:var(--ink);}
  .panel-title svg{width:18px;height:18px;color:var(--green-dark);}
  .panel-sub{font-size:12px;color:var(--muted);font-weight:700;}
  .legend{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:var(--muted);}
  .legend .dot{width:8px;height:8px;border-radius:50%;background:var(--green);}

  .trend-chart{width:100%;height:auto;display:block;}
  .trend-chart .axis-label{font-size:9.5px;fill:var(--muted);font-weight:700;font-family:'Nunito',sans-serif;}
  .trend-chart .grid-line{stroke:var(--border);stroke-width:1;}
  .trend-chart .point:hover{r:6;}

  .bar-chart-row{display:flex;align-items:center;gap:10px;padding:8px 0;}
  .bar-chart-avatar{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10.5px;font-weight:800;color:#fff;flex-shrink:0;}
  .bar-chart-name{width:98px;flex-shrink:0;font-size:12px;font-weight:700;color:var(--ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
  .bar-chart-track{flex-grow:1;height:10px;background:var(--bg);border-radius:6px;overflow:hidden;}
  .bar-chart-fill{height:100%;border-radius:6px;}
  .bar-chart-value{width:36px;flex-shrink:0;text-align:right;font-size:12px;font-weight:800;color:var(--ink);}

  /* ---------- Leaderboard row ---------- */
  .lists-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}
  .lead-row{display:flex;align-items:center;gap:12px;padding:11px 4px;border-bottom:1px solid var(--border);}
  .lead-row:last-child{border-bottom:none;}
  .lead-rank{
    width:24px;height:24px;border-radius:50%;background:var(--green-light);color:var(--green-dark);
    font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;
  }
  .needs .lead-rank{background:var(--red-light);color:var(--red);}
  .lead-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;}
  .lead-info{flex-grow:1;min-width:0;}
  .lead-name{font-size:13px;font-weight:700;color:var(--ink);}
  .lead-sub{font-size:11px;color:var(--muted);font-weight:600;}
  .lead-metric{font-size:12.5px;font-weight:800;flex-shrink:0;white-space:nowrap;}
  .lead-metric.up{color:var(--green-dark);}
  .lead-metric.down{color:var(--red);}

  /* ---------- Table panel ---------- */
  .table-controls{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:6px;}
  .sel{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:9px 13px;font-size:12.5px;font-weight:700;color:var(--ink);font-family:inherit;cursor:pointer;outline:none;}
  .report-table{width:100%;border-collapse:collapse;}
  .report-table thead th{
    text-align:left;font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;
    padding:10px 8px;border-bottom:1px solid var(--border);
  }
  .report-table thead th.num{text-align:right;}
  .report-table tbody td{padding:12px 8px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle;}
  .report-table tbody tr:last-child td{border-bottom:none;}
  .report-table tbody tr:hover{background:var(--bg);}
  .table-empty{text-align:center;color:var(--muted);font-weight:700;padding:28px 8px !important;}
  .t-student{display:flex;align-items:center;gap:10px;}
  .t-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;}
  .t-name{font-weight:700;color:var(--ink);}
  .t-section{font-size:11px;font-weight:700;color:var(--muted);margin-top:1px;}
  .num{text-align:right;font-weight:700;color:var(--ink);}
  .trend-pill{display:inline-flex;align-items:center;gap:3px;font-size:12px;font-weight:800;padding:3px 8px;border-radius:20px;}
  .trend-pill.up{background:var(--green-light);color:var(--green-dark);}
  .trend-pill.down{background:var(--red-light);color:var(--red);}
  .trend-pill.flat{background:var(--bg);color:var(--muted);}
  .status-pill{font-size:11px;font-weight:800;padding:5px 10px;border-radius:20px;white-space:nowrap;}
  .status-pill.ontrack{background:var(--green-light);color:var(--green-dark);}
  .status-pill.support{background:var(--red-light);color:var(--red);}
  .t-view{font-size:12px;font-weight:800;color:var(--green-dark);cursor:pointer;white-space:nowrap;}

  .ai-status-pill{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:800;padding:4px 9px;border-radius:12px;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap;}
  .ai-status-pill.rapid_growth{background:var(--green-light);color:var(--green-dark);}
  .ai-status-pill.on_track{background:rgba(111,191,90,0.18);color:var(--green-dark);}
  .ai-status-pill.steady{background:var(--teal-light);color:var(--teal);}
  .ai-status-pill.needs_intervention{background:var(--red-light);color:var(--red);}
  .ai-class-card{margin-bottom:20px;background:linear-gradient(135deg, rgba(111,191,90,0.12), rgba(79,163,184,0.08));border:1.5px solid rgba(111,191,90,0.3);border-radius:16px;padding:18px 22px;display:flex;align-items:flex-start;gap:16px;}
  .ai-class-icon{width:42px;height:42px;border-radius:12px;background:var(--card);display:flex;align-items:center;justify-content:center;color:var(--green-dark);font-size:22px;flex-shrink:0;box-shadow:var(--shadow);}


  /* ---------- Tip banner ---------- */
  .tip{display:flex;align-items:center;gap:16px;background:var(--green-light);border-radius:var(--radius);padding:20px 24px;position:relative;}
  .tip-icon{width:44px;height:44px;border-radius:50%;background:#fff3c4;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .tip-icon svg{width:22px;height:22px;color:#e8a531;}
  .tip-title{font-size:14.5px;font-weight:800;color:var(--ink);margin-bottom:2px;}
  .tip-text{font-size:13px;color:#3d5a48;font-weight:600;}
  .tip-close{position:absolute;right:20px;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.6);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--green-dark);}
  .tip-close svg{width:13px;height:13px;}

  @media (max-width:1200px){
    .chart-row{grid-template-columns:1fr;}
    .lists-row{grid-template-columns:1fr;}
  }
  @media (max-width:1100px){
    .stats{grid-template-columns:repeat(2,1fr);}
  }
  @media (max-width:760px){
    .report-table thead th.hide-sm, .report-table tbody td.hide-sm{display:none;}
  }

  /* Dark-mode overrides for local light-mode defaults. */
  html[data-theme="dark"] .title-block h1,
  html[data-theme="dark"] .panel-title,
  html[data-theme="dark"] .stat-value,
  html[data-theme="dark"] .bar-chart-name,
  html[data-theme="dark"] .bar-chart-value,
  html[data-theme="dark"] .lead-name,
  html[data-theme="dark"] .t-name,
  html[data-theme="dark"] .num{color:var(--ink);}
  html[data-theme="dark"] .tabs,
  html[data-theme="dark"] .btn-outline,
  html[data-theme="dark"] .bell,
  html[data-theme="dark"] .bell-panel,
  html[data-theme="dark"] .sel{background:var(--card);color:var(--ink);border-color:var(--border);}
  html[data-theme="dark"] .bell svg{color:var(--ink);}
  html[data-theme="dark"] .bell-item:hover,
  html[data-theme="dark"] .report-table tbody tr:hover{background:var(--bg);}
  html[data-theme="dark"] .tip-icon{background:#4a3c1a;}
  html[data-theme="dark"] .tip-title{color:var(--ink);}
  html[data-theme="dark"] .tip-text{color:#bcd6c5;}
  html[data-theme="dark"] .tip-close{background:rgba(255,255,255,0.08);}
  /* ---------- Toast ---------- */
  .toast{
    position:fixed;bottom:24px;right:24px;background:#16281d;color:#fff;padding:13px 20px;border-radius:12px;
    font-size:13px;font-weight:700;box-shadow:0 10px 30px rgba(0,0,0,0.25);z-index:200;display:flex;align-items:center;gap:10px;
    opacity:0;transform:translateY(10px);pointer-events:none;transition:opacity .25s ease, transform .25s ease;
  }
  .toast.show{opacity:1;transform:translateY(0);}
  .toast .bx{color:#b1db65;font-size:16px;}

  /* ---------- Pixel decorations ---------- */
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

  /* ---------- Print ---------- */
  @media print{
    .sidebar, .btn-new, .btn-outline, .bell, .tip, .tab, .table-controls, .t-view{display:none !important;}
    .main{padding:0;max-width:100%;}
    body{display:block;background:#fff;}
    .panel, .stat-card{box-shadow:none;border:1px solid #ddd;}
    .chart-row, .lists-row{page-break-inside:avoid;}
  }
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
        <a class="nav-item" href="sessions.php">
          <i class="bx bx-calendar"></i>
          <span class="label">Sessions</span>
        </a>
        <a class="nav-item active" href="reports.php">
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
        <h1>Reports</h1>
        <div class="greet" id="greetLine">A class-wide look at how your readers are progressing.</div>
      </div>
      <div class="topbar-actions">
        <div class="tabs" id="rangeTabs">
          <div class="tab" data-range="week">This Week</div>
          <div class="tab active" data-range="month">This Month</div>
          <div class="tab" data-range="all">All Time</div>
        </div>
        <button class="btn-outline" id="printBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
          Print
        </button>
        <button class="btn-new" id="exportBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
          Export CSV
        </button>
        <div class="bell" id="bellBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
          <span class="badge" id="bellBadge">3</span>
          <div class="bell-panel" id="bellPanel">
            <div class="bell-item">Monthly report is ready <div class="sub">Export it as a CSV to share</div></div>
            <div class="bell-item">Class average WPM is up this month <div class="sub">Keep up the momentum</div></div>
            <div class="bell-item">2 students need a check-in <div class="sub">See "Needs Attention" below</div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="chart-row">
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/><path d="M17 7h4v4"/></svg>
            Class Reading Trend
          </div>
          <div class="legend"><span class="dot"></span>Average words per minute</div>
        </div>
        <div id="trendChartWrap"></div>
      </div>

      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5a2 2 0 0 1 2-2h11l3 3v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
            Accuracy by Student
          </div>
          <div class="panel-sub" id="accChartSub"></div>
        </div>
        <div id="accChartWrap"></div>
      </div>
    </div>

    <div class="lists-row">
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M17 3H7v6a5 5 0 0 0 10 0V3Z"/><path d="M7 5H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4M17 5h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/></svg>
            Top Readers
          </div>
          <div class="panel-sub">Biggest WPM gains</div>
        </div>
        <div id="topReadersList"></div>
      </div>

      <div class="panel needs">
        <div class="panel-head">
          <div class="panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15V4"/><path d="M4 15c2-1.5 4-1.5 6 0s4 1.5 6 0V4c-2 1.5-4 1.5-6 0S6 2.5 4 4Z"/></svg>
            Needs Attention
          </div>
          <div class="panel-sub">Lower accuracy or fewer sessions</div>
        </div>
        <div id="needsAttentionList"></div>
      </div>

    </div>

    <!-- AI Cohort Summary Banner -->
    <div class="ai-class-card" id="aiClassCard">
      <div class="ai-class-icon"><i class='bx bxs-brain'></i></div>
      <div style="flex-grow:1;min-width:0;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;flex-wrap:wrap;">
          <b style="font-family:'Poppins',sans-serif;font-size:14px;color:var(--ink);">AI Class Progress & Trajectory Diagnostic</b>
          <span class="ai-status-pill on_track" id="aiCohortBadge">Active Analysis</span>
        </div>
        <div id="aiCohortSummaryText" style="font-size:12.5px;color:var(--muted);line-height:1.5;">Synthesizing oral fluency, decoding accuracy, and comprehension testing across student sessions…</div>
      </div>
    </div>

    <div class="panel" style="margin-bottom:24px;">
      <div class="panel-head">
        <div class="panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M17 11a4 4 0 1 0-3.2-6.4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/><path d="M15 15c3.5 0 6 2 6 6"/></svg>
          Student Progress
        </div>
        <div class="table-controls">
          <select class="sel" id="sectionSelect" aria-label="Filter by section">
            <option value="all">All Sections</option>
          </select>
        </div>
      </div>
      <div style="overflow-x:auto;">
        <table class="report-table">
          <thead>
            <tr>
              <th>Student</th>
              <th class="num">Sessions</th>
              <th class="num">Avg WPM</th>
              <th class="num hide-sm">Avg Accuracy</th>
              <th class="num">Trend</th>
              <th>AI Diagnostic</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="reportTableBody"></tbody>
        </table>
      </div>
    </div>


    <div class="tip">
      <div class="tip-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
      </div>
      <div>
        <div class="tip-title">Tip of the day</div>
        <div class="tip-text">Switch the date range above to compare this week against the full month, then export a CSV to share with your school.</div>
      </div>
      <div class="tip-close" id="tipClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </div>
    </div>
  </main>

  <div class="toast" id="toast"><i class='bx bx-check-circle'></i><span id="toastMsg"></span></div>

  <script>
    // ============================================================
    // Roster
    // ============================================================
    let roster = [
      {id:1,  name:'Carmen Reyes',      section:'Section A', color:'#6fbf5a', baseWpm:300, growth:55, baseAcc:93, accGrowth:6},
      {id:2,  name:'Eva Mendoza',       section:'Section A', color:'#c9924d', baseWpm:280, growth:35, baseAcc:90, accGrowth:5},
      {id:3,  name:'Isabella Ramos',    section:'Section A', color:'#f2a13a', baseWpm:265, growth:40, baseAcc:87, accGrowth:6},
      {id:4,  name:'Diego Santos',      section:'Section A', color:'#8b6bd1', baseWpm:230, growth:30, baseAcc:85, accGrowth:5},
      {id:5,  name:'Maya Cruz',         section:'Section B', color:'#4fa3b8', baseWpm:250, growth:38, baseAcc:88, accGrowth:6},
      {id:6,  name:'Liam Torres',       section:'Section B', color:'#ea5d5d', baseWpm:180, growth:20, baseAcc:76, accGrowth:4},
      {id:7,  name:'Sofia Delgado',     section:'Section B', color:'#6fbf5a', baseWpm:240, growth:28, baseAcc:88, accGrowth:5},
      {id:8,  name:'Mateo Villanueva',  section:'Section B', color:'#f2a13a', baseWpm:200, growth:25, baseAcc:81, accGrowth:5},
      {id:9,  name:'Ana Bautista',      section:'Section C', color:'#8b6bd1', baseWpm:165, growth:15, baseAcc:73, accGrowth:3},
      {id:10, name:'Noah Garcia',       section:'Section C', color:'#4fa3b8', baseWpm:290, growth:32, baseAcc:92, accGrowth:5},
      {id:11, name:'Camila Flores',     section:'Section C', color:'#ea5d5d', baseWpm:220, growth:26, baseAcc:85, accGrowth:5},
      {id:12, name:'Ethan Morales',     section:'Section C', color:'#c9924d', baseWpm:250, growth:30, baseAcc:89, accGrowth:5},
    ];
    function studentById(id){ return roster.find(r=>r.id===id); }
    function initials(name){ return name.split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase(); }
    function escapeHtml(value){
      return String(value).replace(/[&<>"']/g, character=>({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[character]));
    }

    // ============================================================
    // Deterministic pseudo-random generator (so the report looks
    // the same on every load, like a real stored dataset would)
    // ============================================================
    function mulberry32(seed){
      return function(){
        seed |= 0; seed = (seed + 0x6D2B79F5) | 0;
        let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
      };
    }
    const rand = mulberry32(2026);
    function clamp(v,min,max){ return Math.max(min, Math.min(max, v)); }

    const DAY = 24*60*60*1000;
    const RANGE_DAYS = 60; // dataset spans the last 60 days

    let sessions = [];
    (function generateSessions(){
      let id = 1;
      roster.forEach(student=>{
        let d = Math.floor(rand()*3); // stagger start day per student
        while(d <= RANGE_DAYS-1){
          const progress = 1 - (d/RANGE_DAYS); // 0 = 60 days ago, 1 = today
          const wpm = Math.round(student.baseWpm - student.growth + student.growth*progress + (rand()*16-8));
          const accuracy = clamp(Math.round(student.baseAcc - student.accGrowth + student.accGrowth*progress + (rand()*6-3)), 55, 100);
          sessions.push({id:id++, studentId:student.id, wpm:Math.max(80,wpm), accuracy, ts:Date.now() - d*DAY - Math.floor(rand()*DAY)});
          d += 2 + Math.floor(rand()*3); // read roughly every 2-4 days
        }
      });
    })();

    // ============================================================
    // State
    // ============================================================
    let activeRange = 'month'; // week | month | all
    let sectionFilter = 'all'; // 'all' or a section name
    let extraSectionNames = []; // sections returned by the API (may include empty ones)

    function rangeDays(){
      if(activeRange==='week') return 7;
      if(activeRange==='month') return 30;
      return RANGE_DAYS;
    }
    function inCurrentPeriod(ts){ return Date.now()-ts <= rangeDays()*DAY; }
    function inPreviousPeriod(ts){
      const d = rangeDays();
      const age = Date.now()-ts;
      return age > d*DAY && age <= 2*d*DAY;
    }

    function showToast(msg){
      const t = document.getElementById('toast');
      document.getElementById('toastMsg').textContent = msg;
      t.classList.add('show');
      clearTimeout(window.__toastTimer);
      window.__toastTimer = setTimeout(()=>t.classList.remove('show'), 2800);
    }
    function avg(arr, key){ return arr.length ? arr.reduce((a,x)=>a+x[key],0)/arr.length : 0; }
    function accClass(a){ if(a>=90) return 'good'; if(a>=80) return 'mid'; return 'low'; }
    const classColor = {good:'var(--green-dark)', mid:'var(--orange)', low:'var(--red)'};

    // ============================================================
    // Section dropdown
    // Lists every section that exists (from the API when available,
    // plus any section a student belongs to), so renamed or newly
    // added sections show up automatically.
    // ============================================================
    function getSectionNames(){
      const names = [];
      const add = name=>{
        const n = (name || '').trim();
        if(n && !names.includes(n)) names.push(n);
      };
      extraSectionNames.forEach(add);
      roster.forEach(r=>add(r.section));
      return names.sort((a,b)=>a.localeCompare(b, undefined, {numeric:true, sensitivity:'base'}));
    }

    function renderSectionOptions(){
      const select = document.getElementById('sectionSelect');
      const names = getSectionNames();
      if(sectionFilter !== 'all' && !names.includes(sectionFilter)) sectionFilter = 'all';

      select.innerHTML = '';
      const allOption = document.createElement('option');
      allOption.value = 'all';
      allOption.textContent = 'All Sections';
      select.appendChild(allOption);

      names.forEach(name=>{
        const option = document.createElement('option');
        option.value = name;
        option.textContent = name;
        select.appendChild(option);
      });
      select.value = sectionFilter;
    }

    // ============================================================
    // Per-student aggregation for the current + previous period
    // ============================================================
    function computeStudentStats(){
      return roster.map(student=>{
        const current = sessions.filter(s=>s.studentId===student.id && inCurrentPeriod(s.ts));
        const previous = sessions.filter(s=>s.studentId===student.id && inPreviousPeriod(s.ts));
        const wpm = Math.round(avg(current,'wpm'));
        const accuracy = Math.round(avg(current,'accuracy'));
        const prevWpm = avg(previous,'wpm');
        const trendPct = (prevWpm>0 && current.length) ? Math.round(((wpm-prevWpm)/prevWpm)*100) : (current.length? 100 : 0);
        const status = (current.length===0) ? 'unknown' : (accuracy < 82 ? 'support' : 'ontrack');
        return {
          student, sessionsCount: current.length, wpm, accuracy, trendPct, status
        };
      });
    }

    // ============================================================
    // Trend chart (avg WPM over time, bucketed)
    // ============================================================
    function bucketConfig(){
      if(activeRange==='week') return {count:7, spanDays:1, fmt:{weekday:'short'}};
      if(activeRange==='month') return {count:6, spanDays:5, fmt:{month:'short', day:'numeric'}};
      return {count:6, spanDays:10, fmt:{month:'short', day:'numeric'}};
    }

    function renderTrendChart(){
      const cfg = bucketConfig();
      const points = [];
      let lastVal = Math.round(avg(sessions.filter(s=>inCurrentPeriod(s.ts)),'wpm')) || 220;

      for(let i=cfg.count-1; i>=0; i--){
        const endAgoDays = i*cfg.spanDays;
        const startAgoDays = endAgoDays + cfg.spanDays;
        const bucketSessions = sessions.filter(s=>{
          const age = Date.now()-s.ts;
          return age >= endAgoDays*DAY && age < startAgoDays*DAY;
        });
        const val = bucketSessions.length ? Math.round(avg(bucketSessions,'wpm')) : lastVal;
        lastVal = val;
        const labelDate = new Date(Date.now() - endAgoDays*DAY);
        points.push({label: labelDate.toLocaleDateString(undefined, cfg.fmt), value: val});
      }

      const W = 640, H = 220, padL = 34, padR = 14, padT = 16, padB = 26;
      const vals = points.map(p=>p.value);
      const minV = Math.min(...vals), maxV = Math.max(...vals);
      const range = Math.max(maxV-minV, 20);
      const yFor = v => padT + (1 - (v-minV)/range) * (H-padT-padB);
      const xFor = i => padL + (i/(points.length-1)) * (W-padL-padR);

      const linePath = points.map((p,i)=>`${i===0?'M':'L'} ${xFor(i).toFixed(1)} ${yFor(p.value).toFixed(1)}`).join(' ');
      const areaPath = `${linePath} L ${xFor(points.length-1).toFixed(1)} ${H-padB} L ${xFor(0).toFixed(1)} ${H-padB} Z`;

      const gridLines = [0,1,2,3].map(i=>{
        const y = padT + (i/3)*(H-padT-padB);
        return `<line class="grid-line" x1="${padL}" y1="${y.toFixed(1)}" x2="${W-padR}" y2="${y.toFixed(1)}"/>`;
      }).join('');

      const dots = points.map((p,i)=>`
        <circle class="point" cx="${xFor(i).toFixed(1)}" cy="${yFor(p.value).toFixed(1)}" r="4" fill="#fff" stroke="var(--green-dark)" stroke-width="2">
          <title>${p.label}: ${p.value} WPM</title>
        </circle>`).join('');

      const labels = points.map((p,i)=>`
        <text class="axis-label" x="${xFor(i).toFixed(1)}" y="${H-8}" text-anchor="middle">${p.label}</text>`).join('');

      document.getElementById('trendChartWrap').innerHTML = `
        <svg class="trend-chart" viewBox="0 0 ${W} ${H}" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="trendFill" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#6fbf5a" stop-opacity="0.35"/>
              <stop offset="100%" stop-color="#6fbf5a" stop-opacity="0.02"/>
            </linearGradient>
          </defs>
          ${gridLines}
          <path d="${areaPath}" fill="url(#trendFill)" stroke="none"/>
          <path d="${linePath}" fill="none" stroke="var(--green-dark)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          ${dots}
          ${labels}
        </svg>`;
    }

    // ============================================================
    // Accuracy-by-student bar chart
    // ============================================================
    function renderAccChart(stats){
      const withData = stats.filter(s=>s.sessionsCount>0).sort((a,b)=>b.accuracy-a.accuracy);
      document.getElementById('accChartSub').textContent = withData.length + ' active readers';

      if(withData.length===0){
        document.getElementById('accChartWrap').innerHTML = `<div style="font-size:12.5px;color:var(--muted);font-weight:600;padding:20px 0;">No sessions in this period yet.</div>`;
        return;
      }

      document.getElementById('accChartWrap').innerHTML = withData.map(s=>`
        <div class="bar-chart-row">
          <div class="bar-chart-avatar" style="background:${s.student.color}">${initials(s.student.name)}</div>
          <div class="bar-chart-name" title="${escapeHtml(s.student.name)}">${escapeHtml(s.student.name)}</div>
          <div class="bar-chart-track">
            <div class="bar-chart-fill" style="width:${s.accuracy}%; background:${classColor[accClass(s.accuracy)]};"></div>
          </div>
          <div class="bar-chart-value">${s.accuracy}%</div>
        </div>
      `).join('');
    }

    // ============================================================
    // Top Readers / Needs Attention
    // ============================================================
    function renderLists(stats){
      const active = stats.filter(s=>s.sessionsCount>0);

      const top = [...active].sort((a,b)=>b.trendPct-a.trendPct).slice(0,4);
      document.getElementById('topReadersList').innerHTML = top.length ? top.map((s,i)=>`
        <div class="lead-row">
          <div class="lead-rank">${i+1}</div>
          <div class="lead-avatar" style="background:${s.student.color}">${initials(s.student.name)}</div>
          <div class="lead-info">
            <div class="lead-name">${escapeHtml(s.student.name)}</div>
            <div class="lead-sub">${s.wpm} WPM avg • ${s.sessionsCount} sessions</div>
          </div>
          <div class="lead-metric ${s.trendPct>=0?'up':'down'}">${s.trendPct>=0?'▲':'▼'} ${Math.abs(s.trendPct)}%</div>
        </div>
      `).join('') : `<div style="font-size:12.5px;color:var(--muted);font-weight:600;padding:10px 0;">No sessions in this period yet.</div>`;

      const needs = [...active].filter(s=>s.status==='support' || s.trendPct<0).sort((a,b)=>a.accuracy-b.accuracy).slice(0,4);
      document.getElementById('needsAttentionList').innerHTML = needs.length ? needs.map(s=>`
        <div class="lead-row">
          <div class="lead-rank">!</div>
          <div class="lead-avatar" style="background:${s.student.color}">${initials(s.student.name)}</div>
          <div class="lead-info">
            <div class="lead-name">${escapeHtml(s.student.name)}</div>
            <div class="lead-sub">${s.accuracy}% accuracy • ${s.sessionsCount} sessions</div>
          </div>
          <div class="lead-metric ${s.trendPct>=0?'up':'down'}">${s.trendPct>=0?'▲':'▼'} ${Math.abs(s.trendPct)}%</div>
        </div>
      `).join('') : `<div style="font-size:12.5px;color:var(--muted);font-weight:600;padding:10px 0;">Nobody needs extra attention right now — great job! ♥</div>`;
    }

    // ============================================================
    // Student progress table
    // Default sort is by Section (Section A, B, C…), then by name
    // within each section. Students with no section go last.
    // The section dropdown above the table filters the rows.
    // ============================================================
    function compareSection(a, b){
      const sa = a.student.section || '', sb = b.student.section || '';
      if(sa && !sb) return -1;
      if(!sa && sb) return 1;
      const bySection = sa.localeCompare(sb, undefined, {numeric:true, sensitivity:'base'});
      if(bySection !== 0) return bySection;
      return a.student.name.localeCompare(b.student.name);
    }

    function sortedStats(stats){
      const list = [...stats];
      list.sort(compareSection);
      return list;
    }

    function renderTable(stats){
      const list = sortedStats(stats).filter(s=>
        sectionFilter==='all' || (s.student.section || '') === sectionFilter
      );

      if(list.length===0){
        document.getElementById('reportTableBody').innerHTML =
          `<tr><td class="table-empty" colspan="8">No students in ${sectionFilter==='all' ? 'this view' : escapeHtml(sectionFilter)} yet.</td></tr>`;
        return;
      }

      document.getElementById('reportTableBody').innerHTML = list.map(s=>`
        <tr>
          <td>
            <div class="t-student">
              <div class="t-avatar" style="background:${s.student.color}">${initials(s.student.name)}</div>
              <div>
                <div class="t-name">${escapeHtml(s.student.name)}</div>
                <div class="t-section">${escapeHtml(s.student.section || 'Unassigned')}</div>
              </div>
            </div>
          </td>
          <td class="num">${s.sessionsCount}</td>
          <td class="num">${s.sessionsCount ? s.wpm : '—'}</td>
          <td class="num hide-sm">${s.sessionsCount ? s.accuracy+'%' : '—'}</td>
          <td class="num">
            ${s.sessionsCount ? `<span class="trend-pill ${s.trendPct>0?'up':(s.trendPct<0?'down':'flat')}">${s.trendPct>0?'▲':(s.trendPct<0?'▼':'—')} ${Math.abs(s.trendPct)}%</span>` : '—'}
          </td>
          <td>
            ${s.student.ai_progress_status
              ? `<span class="ai-status-pill ${s.student.ai_progress_status}" title="${escapeHtml(s.student.ai_narrative || '')}">${s.student.ai_progress_status.replace('_',' ')}</span>`
              : (s.sessionsCount ? `<span class="ai-status-pill on_track">Evaluating</span>` : `<span style="color:var(--muted);font-size:11px;">No Data</span>`)
            }
          </td>
          <td>
            ${s.sessionsCount
              ? `<span class="status-pill ${s.status}">${s.status==='support'?'Needs Support':'On Track'}</span>`
              : `<span class="status-pill" style="background:var(--bg);color:var(--muted);">No Sessions</span>`}
          </td>
          <td><span class="t-view" data-id="${s.student.id}">View Progress →</span></td>
        </tr>
      `).join('');

      document.querySelectorAll('.t-view').forEach(el=>{
        el.addEventListener('click', ()=>{ window.location.href = 'progress.php?id=' + encodeURIComponent(el.dataset.id); });
      });
    }

    function renderCohortSummary(stats){
      const badge = document.getElementById('aiCohortBadge');
      const text = document.getElementById('aiCohortSummaryText');
      if(!badge || !text) return;
      const withSessions = stats.filter(s => s.sessionsCount > 0);
      if(!withSessions.length){
        text.textContent = 'No reading sessions logged in this period yet. Complete reading and quiz sessions to view AI progress analytics.';
        return;
      }
      const rapid = withSessions.filter(s => s.student.ai_progress_status === 'rapid_growth').length;
      const onTrack = withSessions.filter(s => s.student.ai_progress_status === 'on_track').length;
      const needsIntervention = withSessions.filter(s => s.student.ai_progress_status === 'needs_intervention' || s.status === 'support').length;
      
      const pctGood = Math.round(((rapid + onTrack) / withSessions.length) * 100);
      badge.textContent = `${pctGood}% On Track`;
      badge.className = 'ai-status-pill ' + (pctGood >= 80 ? 'on_track' : (pctGood >= 60 ? 'steady' : 'needs_intervention'));
      
      text.innerHTML = `<strong>${withSessions.length} active readers</strong> evaluated. <strong>${rapid} students</strong> show rapid fluency acceleration, <strong>${onTrack} students</strong> meet grade benchmarks, and <strong>${needsIntervention} student${needsIntervention===1?'':'s'}</strong> flagged for phonics or comprehension scaffolding.`;
    }

    // ============================================================
    // Master render
    // ============================================================
    function renderAll(){
      const stats = computeStudentStats();
      renderSectionOptions();
      renderTrendChart();
      renderAccChart(stats);
      renderLists(stats);
      renderTable(stats);
      renderCohortSummary(stats);
    }


    // ============================================================
    // Controls
    // ============================================================
    document.getElementById('rangeTabs').addEventListener('click', e=>{
      const tab = e.target.closest('.tab');
      if(!tab) return;
      document.querySelectorAll('#rangeTabs .tab').forEach(t=>t.classList.remove('active'));
      tab.classList.add('active');
      activeRange = tab.dataset.range;
      renderAll();
    });
    document.getElementById('sectionSelect').addEventListener('change', e=>{
      sectionFilter = e.target.value;
      renderAll();
    });

    // ============================================================
    // Export CSV
    // ============================================================
    document.getElementById('exportBtn').addEventListener('click', ()=>{
      const stats = sortedStats(computeStudentStats());
      const rows = [['Student','Section','Sessions','Avg WPM','Avg Accuracy (%)','Trend (%)','Status','AI Progress Status','AI Narrative','AI Recommended Action']];
      stats.forEach(s=>{
        rows.push([
          s.student.name,
          s.student.section || '',
          s.sessionsCount,
          s.sessionsCount ? s.wpm : '',
          s.sessionsCount ? s.accuracy : '',
          s.sessionsCount ? s.trendPct : '',
          s.sessionsCount ? (s.status==='support'?'Needs Support':'On Track') : 'No Sessions',
          s.student.ai_progress_status ? s.student.ai_progress_status.replace(/_/g, ' ') : (s.sessionsCount ? 'Evaluating' : 'No Data'),
          s.student.ai_narrative || '',
          s.student.ai_next_step || ''
        ]);
      });
      const csv = rows.map(r => r.map(v => {
        const str = String(v ?? '');
        return /[",\n\r]/.test(str) ? `"${str.replace(/"/g, '""')}"` : str;
      }).join(',')).join('\r\n');

      const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      const rangeLabel = activeRange==='week' ? 'this-week' : activeRange==='month' ? 'this-month' : 'all-time';
      const dateStr = new Date().toISOString().slice(0,10);
      a.href = url;
      a.download = `readpilot-report-${rangeLabel}-${dateStr}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      showToast('Report exported as CSV');
    });

    document.getElementById('printBtn').addEventListener('click', ()=>window.print());

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
    const bellBtn = document.getElementById('bellBtn');
    const bellPanel = document.getElementById('bellPanel');
    bellBtn.addEventListener('click', e=>{
      e.stopPropagation();
      bellPanel.classList.toggle('open');
      document.getElementById('bellBadge').style.display = 'none';
    });
    document.addEventListener('click', ()=>bellPanel.classList.remove('open'));
    document.getElementById('tipClose').addEventListener('click', function(){
      this.closest('.tip').style.display = 'none';
    });
    // ============================================================
    // Init
    // ============================================================
    renderAll();
    fetch('student-api.php?view=reports').then(response=>response.json()).then(result=>{
      if (Array.isArray(result.students) && Array.isArray(result.sessions)) {
        roster = result.students.map(student=>({
          id:Number(student.id),
          name:student.name,
          section:student.section || '',
          color:student.color,
          baseWpm:Number(student.wpm),
          growth:0,
          baseAcc:Number(student.accuracy),
          accGrowth:0,
          ai_progress_status: student.ai_progress_status || '',
          ai_narrative: student.ai_narrative || '',
          ai_phonics: student.ai_phonics || '',
          ai_next_step: student.ai_next_step || ''
        }));

        sessions = result.sessions.map(session=>({...session, id:Number(session.id), studentId:Number(session.studentId)}));
        // Include empty sections, matching the Sections page.
        extraSectionNames = Array.isArray(result.sections)
          ? result.sections.map(section=> typeof section === 'string' ? section : (section && section.name) || '').filter(Boolean)
          : [];
        renderAll();
      }
    }).catch(()=>{});
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>