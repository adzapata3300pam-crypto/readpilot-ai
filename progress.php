<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Student Progress</title>
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

  /* ---------- Sidebar (identical to other pages) ---------- */
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

  .back-link{
    display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:700;color:var(--muted);
    text-decoration:none;margin-bottom:14px;transition:color .15s ease, transform .12s steps(2);
  }
  .back-link svg{width:14px;height:14px;}
  .back-link:hover{color:var(--green-dark);transform:translateX(-2px);}

  .topbar{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:22px;}
  .topbar-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}

  /* ---------- Student switcher (custom dropdown) ----------
     A button that always shows the student's name as plain text,
     plus a menu of all students. Replaces the native <select>, which
     was being restyled/hidden by other stylesheets or scripts.        */
  .switcher{position:relative;}
  .switcher-btn{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    min-width:210px;max-width:280px;
    background:var(--card);color:var(--ink);
    border:1px solid var(--border);border-radius:12px;
    padding:11px 14px;font-family:inherit;font-size:13.5px;font-weight:700;
    cursor:pointer;box-shadow:var(--shadow);
    transition:border-color .15s ease;
  }
  .switcher-btn:hover,.switcher.open .switcher-btn{border-color:var(--green-dark);}
  .switcher-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
  .switcher-btn svg{width:14px;height:14px;flex-shrink:0;color:var(--muted);transition:transform .15s ease;}
  .switcher.open .switcher-btn svg{transform:rotate(180deg);}
  .switcher-menu{
    display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;min-width:100%;
    max-height:280px;overflow-y:auto;z-index:50;
    background:var(--card);border:1px solid var(--border);border-radius:12px;
    box-shadow:0 12px 30px rgba(16,32,22,0.18);padding:6px;
  }
  .switcher.open .switcher-menu{display:block;}
  .switcher-item{
    display:block;width:100%;text-align:left;background:none;border:none;
    padding:9px 12px;border-radius:8px;font-family:inherit;font-size:13px;font-weight:700;
    color:var(--ink);cursor:pointer;
  }
  .switcher-item:hover{background:var(--bg);}
  .switcher-item.active{background:var(--green-light);color:var(--green-dark);}

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

  /* ---------- Student header card ---------- */
  .profile-card{
    background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);
    padding:24px 26px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;margin-bottom:20px;
  }
  .profile-avatar{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;}
  .profile-info{flex-grow:1;min-width:180px;}
  .profile-name{font-family:'Poppins',sans-serif;font-size:20px;font-weight:700;color:var(--ink);}
  .profile-sub{font-size:13px;color:var(--muted);font-weight:700;margin-top:2px;}
  .status-pill{font-size:11.5px;font-weight:800;padding:6px 12px;border-radius:20px;white-space:nowrap;}
  .status-pill.ontrack{background:var(--green-light);color:var(--green-dark);}
  .status-pill.support{background:var(--red-light);color:var(--red);}
  .status-pill.unknown{background:var(--bg);color:var(--muted);}

  /* ---------- Progress summary (improved or not) ---------- */
  .verdict{display:flex;align-items:center;gap:14px;border-radius:14px;padding:16px 18px;margin-bottom:16px;}
  .verdict.improved{background:var(--green-light);color:var(--green-dark);}
  .verdict.declined{background:var(--red-light);color:var(--red);}
  .verdict.mixed{background:var(--orange-light);color:var(--ink);}
  .verdict.steady,.verdict.none{background:var(--bg);color:var(--muted);}
  .verdict-icon{width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,0.7);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px;font-weight:800;}
  .verdict-title{font-family:'Poppins',sans-serif;font-size:16px;font-weight:700;}
  .verdict-text{font-size:13px;font-weight:600;margin-top:2px;opacity:.9;}
  .summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;}
  .sum-card{background:var(--bg);border-radius:14px;padding:14px 16px;}
  .sum-label{font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.3px;}
  .sum-value{font-family:'Poppins',sans-serif;font-size:22px;font-weight:700;color:var(--ink);margin-top:4px;}
  .sum-sub{font-size:12px;font-weight:700;color:var(--muted);margin-top:2px;}
  .sum-sub.up{color:var(--green-dark);}
  .sum-sub.down{color:var(--red);}

  .change-pill{display:inline-flex;align-items:center;gap:3px;font-size:12px;font-weight:800;padding:3px 9px;border-radius:20px;white-space:nowrap;}
  .change-pill.up{background:var(--green-light);color:var(--green-dark);}
  .change-pill.down{background:var(--red-light);color:var(--red);}
  .change-pill.flat{background:var(--bg);color:var(--muted);}

  .period-table{width:100%;border-collapse:collapse;}
  .period-table thead th{text-align:left;font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;padding:10px 8px;border-bottom:1px solid var(--border);}
  .period-table thead th.num,.period-table tbody td.num{text-align:right;}
  .period-table tbody td{padding:12px 8px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--ink);}
  .period-table tbody tr:last-child td{border-bottom:none;}

  /* ---------- Panels & chart ---------- */
  .chart-row{display:grid;grid-template-columns:1.6fr 1fr;gap:20px;margin-bottom:20px;}
  .panel{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:24px 26px;margin-bottom:20px;}
  .progress-details{margin-bottom:22px;}
  .progress-details>summary{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:52px;padding:12px 18px;background:var(--card);border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow);font-size:16px;font-weight:800;color:var(--ink);cursor:pointer;}
  .progress-details>summary:hover{border-color:var(--green-dark);}
  .progress-details-hint{font-size:14px;font-weight:700;color:var(--muted);}
  .progress-details-content{padding-top:18px;}
  .panel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;}
  .panel-title{display:flex;align-items:center;gap:10px;font-family:'Poppins',sans-serif;font-size:16.5px;font-weight:700;color:var(--ink);}
  .panel-title svg{width:18px;height:18px;color:var(--green-dark);}
  .panel-sub{font-size:12px;color:var(--muted);font-weight:700;}
  .legend{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:var(--muted);}
  .legend .dot{width:8px;height:8px;border-radius:50%;background:var(--green);}
  .legend .dot.acc{background:var(--teal);}

  .trend-chart{width:100%;height:auto;display:block;}
  .trend-chart .axis-label{font-size:9.5px;fill:var(--muted);font-weight:700;font-family:'Nunito',sans-serif;}
  .trend-chart .grid-line{stroke:var(--border);stroke-width:1;}

  .book-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);}
  .book-row:last-child{border-bottom:none;}
  .book-icon{width:32px;height:32px;border-radius:9px;background:var(--green-light);color:var(--green-dark);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .book-icon svg{width:16px;height:16px;}
  .book-name{font-size:13px;font-weight:700;color:var(--ink);flex-grow:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
  .book-count{font-size:12px;font-weight:800;color:var(--muted);flex-shrink:0;}

  /* ---------- Session list (same pattern as sessions.php) ---------- */
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

  .empty-state{padding:40px 20px;text-align:center;color:var(--muted);}
  .empty-state .bx{font-size:30px;color:var(--green);margin-bottom:8px;display:block;}
  .empty-state b{color:var(--ink);display:block;font-size:14.5px;margin-bottom:4px;}

  /* ---------- Tip banner ---------- */
  .tip{display:flex;align-items:center;gap:16px;background:var(--green-light);border-radius:var(--radius);padding:20px 24px;position:relative;}
  .tip-icon{width:44px;height:44px;border-radius:50%;background:#fff3c4;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .tip-icon svg{width:22px;height:22px;color:#e8a531;}
  .tip-title{font-size:14.5px;font-weight:800;color:var(--ink);margin-bottom:2px;}
  .tip-text{font-size:13px;color:#3d5a48;font-weight:600;}
  .tip-close{position:absolute;right:20px;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.6);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--green-dark);}
  .tip-close svg{width:13px;height:13px;}

  /* ---------- Modals (same pattern as sessions.php) ---------- */
  .overlay{position:fixed;inset:0;background:rgba(16,32,22,0.45);display:none;align-items:center;justify-content:center;z-index:100;padding:20px;}
  .overlay.open{display:flex;}
  .modal{background:var(--card);color:var(--ink);border-radius:20px;max-width:480px;width:100%;max-height:88vh;overflow-y:auto;padding:26px;box-shadow:0 20px 60px rgba(16,32,22,0.25);position:relative;}
  .modal-close{position:absolute;top:18px;right:18px;width:32px;height:32px;border-radius:50%;background:var(--bg);border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--ink);}
  .modal-close svg{width:14px;height:14px;}
  .modal-head{display:flex;align-items:center;gap:14px;margin-bottom:20px;padding-right:30px;}
  .modal-avatar{width:52px;height:52px;border-radius:50%;color:#fff;font-weight:800;font-size:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .modal-head h2{font-family:'Poppins',sans-serif;font-size:18px;margin:0 0 2px 0;color:var(--ink);}
  .modal-head .sub{font-size:12.5px;color:var(--muted);font-weight:700;}
  .modal-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px;}
  .m-stat{background:var(--bg);border-radius:12px;padding:12px 10px;text-align:center;}
  .m-stat .v{font-family:'Poppins',sans-serif;font-size:18px;font-weight:700;color:var(--ink);}
  .m-stat .l{font-size:10px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.3px;margin-top:2px;}

  /* AI Diagnostic Components */
  .ai-status-pill{
    display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:12px;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;
  }
  .ai-status-pill.rapid_growth{background:var(--green-light);color:var(--green-dark);}
  .ai-status-pill.on_track{background:rgba(111,191,90,0.18);color:var(--green-dark);}
  .ai-status-pill.steady{background:var(--teal-light);color:var(--teal);}
  .ai-status-pill.needs_intervention{background:var(--red-light);color:var(--red);}
  .s-quiz-pill{
    display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:12px;font-size:10.5px;font-weight:700;background:var(--purple-light);color:var(--purple);
  }
  .modal-ai-box{
    margin:16px 0;background:var(--green-light);border:1.5px solid rgba(111,191,90,0.3);border-radius:14px;padding:16px;text-align:left;
  }
  html[data-theme="dark"] .modal-ai-box{background:rgba(111,191,90,0.08);border-color:rgba(111,191,90,0.25);}
  .modal-ai-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;gap:8px;flex-wrap:wrap;}
  .modal-ai-title{font-family:'Poppins',sans-serif;font-size:13px;font-weight:700;color:var(--green-dark);display:flex;align-items:center;gap:6px;}
  html[data-theme="dark"] .modal-ai-title{color:var(--green);}
  .modal-ai-narrative{font-size:12.5px;line-height:1.55;color:var(--ink);margin-bottom:10px;}
  .modal-ai-field{font-size:12px;line-height:1.5;color:var(--muted);margin-bottom:6px;}
  .modal-ai-field b{color:var(--ink);font-weight:700;}
  .modal-ai-field.advice{padding:8px 10px;background:var(--card);border-radius:9px;border-left:3px solid var(--green);margin-top:8px;}
  .ai-summary-card{
    margin-top:14px;background:linear-gradient(135deg, rgba(111,191,90,0.12), rgba(79,163,184,0.08));
    border:1px solid rgba(111,191,90,0.3);border-radius:14px;padding:16px 18px;
  }
  .ai-summary-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;gap:8px;}
  .ai-summary-title{font-family:'Poppins',sans-serif;font-size:13px;font-weight:700;color:var(--green-dark);display:flex;align-items:center;gap:6px;}
  html[data-theme="dark"] .ai-summary-title{color:var(--green);}

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
  .form-row input{width:100%;border:1px solid var(--border);border-radius:12px;padding:11px 13px;font-family:inherit;font-size:13.5px;color:var(--ink);outline:none;background:var(--card);}
  .form-row input:focus{border-color:var(--green);}
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

  .s-time-mobile{display:none;font-size:11.5px;color:var(--muted);font-weight:600;}
  @media (max-width:1200px){ .chart-row{grid-template-columns:1fr;} }
  @media (max-width:1000px){ .summary-grid{grid-template-columns:repeat(2,1fr);} }
  @media (max-width:900px){ .s-time{display:none;} .s-time-mobile{display:inline;} }
  @media (max-width:760px){ .progress-details>summary{align-items:flex-start;flex-direction:column;gap:4px;} }
  @media (max-width:440px){ .modal-stats{grid-template-columns:repeat(2,1fr) !important;} }

  /* Dark-mode overrides */
  html[data-theme="dark"] .profile-name,
  html[data-theme="dark"] .panel-title,
  html[data-theme="dark"] .sum-value,
  html[data-theme="dark"] .period-table tbody td,
  html[data-theme="dark"] .book-name{color:var(--ink);}
  html[data-theme="dark"] .btn-outline{background:var(--card);color:var(--ink);border-color:var(--border);}
  html[data-theme="dark"] .switcher-btn{background:var(--card);color:var(--ink);border-color:var(--border);}
  html[data-theme="dark"] .verdict-icon{background:rgba(0,0,0,0.25);}
  html[data-theme="dark"] .tip-icon{background:#4a3c1a;}
  html[data-theme="dark"] .tip-title{color:var(--ink);}
  html[data-theme="dark"] .tip-text{color:#bcd6c5;}
  html[data-theme="dark"] .tip-close{background:rgba(255,255,255,0.08);}
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

  @media print{
    .sidebar, .btn-new, .btn-outline, .tip, .back-link, .switcher{display:none !important;}
    .main{padding:0;max-width:100%;margin:0;width:100%;}
    body{display:block;background:#fff;}
    .panel, .profile-card{box-shadow:none;border:1px solid #ddd;}
    .progress-details>summary{display:none;}
    .progress-details:not([open])>.progress-details-content{display:block !important;}
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
    <a class="back-link" href="reports.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m11 17-5-5 5-5"/><path d="M18 12H6"/></svg>
      Back to Reports
    </a>

    <div class="topbar">
      <div class="title-block">
        <h1 style="font-family:'Poppins',sans-serif;font-size:28px;margin:0 0 4px 0;color:var(--ink);">Student Progress</h1>
        <div class="greet" style="font-size:14.5px;color:var(--muted);font-weight:600;">A full picture of one reader's growth over time.</div>
      </div>
      <div class="topbar-actions">
        <div class="switcher" id="studentSwitcher">
          <button type="button" class="switcher-btn" id="switcherBtn" aria-haspopup="listbox" aria-expanded="false">
            <span class="switcher-name" id="switcherName">Loading…</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="switcher-menu" id="switcherMenu" role="listbox"></div>
        </div>
        <button class="btn-outline" id="printBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
          Print
        </button>
        <button class="btn-new" id="newSessionBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
          Log Session
        </button>
      </div>
    </div>

    <div class="profile-card" id="profileCard">
      <div class="profile-info"><div class="profile-sub">Loading student…</div></div>
    </div>

    <!-- ===== Progress over time: did they improve? ===== -->
    <div class="panel">
      <div class="panel-head">
        <div class="panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/><path d="M17 7h4v4"/></svg>
          Progress Summary
        </div>
        <div class="panel-sub" id="summarySub"></div>
      </div>
      <div id="progressSummary"></div>
    </div>

    <details class="progress-details">
      <summary>
        <span>More reading details</span>
        <span class="progress-details-hint">Trend, books, weekly results, and session history</span>
      </summary>
      <div class="progress-details-content">
    <div class="chart-row">
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/><path d="M17 7h4v4"/></svg>
            Reading Trend
          </div>
          <div class="legend"><span class="dot"></span>WPM<span class="dot acc" style="margin-left:10px;"></span>Accuracy</div>
        </div>
        <div id="trendChartWrap"></div>
      </div>

      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
            Books Read
          </div>
          <div class="panel-sub" id="booksSub"></div>
        </div>
        <div id="booksList"></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div class="panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Week-by-Week Progress
        </div>
        <span class="panel-sub">Each row is compared with the week before it</span>
      </div>
      <div style="overflow-x:auto;" id="periodWrap"></div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div class="panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2Z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7Z"/></svg>
          Session History
        </div>
        <span class="panel-sub" id="historyCount"></span>
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
        <div class="tip-text">Use the dropdown above to jump between students without going back to Reports.</div>
      </div>
      <div class="tip-close" id="tipClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </div>
    </div>
      </div>
    </details>
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
        <div class="modal-avatar" id="newModalAvatar" style="background:var(--green);"></div>
        <div>
          <h2>Log a Session</h2>
          <div class="sub" id="newModalSub">Record how a reading session went</div>
        </div>
      </div>
      <form id="newForm">
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
    // Which student are we viewing? (?id=3 in the URL)
    //
    // FIX #1: the requested id from the URL is remembered in
    // `requestedId` and is NEVER overwritten while the page is still
    // waiting on live data. Before, the placeholder (mock) roster was
    // rendered first; its ids (1-12) don't match the real database
    // ids, so the "student not found -> use the first student" fallback
    // fired and silently replaced the requested id with 1 — which is
    // why every "View Progress" click landed on the same student
    // (Alexa). Now we wait for the live roster first and only fall back
    // to the first student if the requested id truly doesn't exist.
    // ============================================================
    const params = new URLSearchParams(window.location.search);
    const requestedId = Number(params.get('id')) || null;
    let currentStudentId = requestedId;

    // ============================================================
    // Placeholder roster + history (only used if the live API fails)
    // ============================================================
    let roster = [
      {id:1, name:'Carmen Reyes', color:'#6fbf5a', book:'A Rainy Day Surprise', section:'Section A'},
      {id:2, name:'Eva Mendoza', color:'#c9924d', book:'The Three Little Pigs', section:'Section A'},
      {id:3, name:'Isabella Ramos', color:'#f2a13a', book:'Journey to the Stars', section:'Section A'},
      {id:4, name:'Diego Santos', color:'#8b6bd1', book:'The Ugly Duckling', section:'Section A'},
      {id:5, name:'Maya Cruz', color:'#4fa3b8', book:"Charlotte's Web (excerpt)", section:'Section B'},
      {id:6, name:'Liam Torres', color:'#ea5d5d', book:'The Tortoise and the Hare', section:'Section B'},
      {id:7, name:'Sofia Delgado', color:'#6fbf5a', book:'Goldilocks and the Three Bears', section:'Section B'},
      {id:8, name:'Mateo Villanueva', color:'#f2a13a', book:'The Little Red Hen', section:'Section B'},
      {id:9, name:'Ana Bautista', color:'#8b6bd1', book:'The Boy Who Cried Wolf', section:'Section C'},
      {id:10, name:'Noah Garcia', color:'#4fa3b8', book:'Jack and the Beanstalk', section:'Section C'},
      {id:11, name:'Camila Flores', color:'#ea5d5d', book:'The Fox and the Grapes', section:'Section C'},
      {id:12, name:'Ethan Morales', color:'#c9924d', book:'The Little Mermaid (retold)', section:'Section C'},
    ];

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
    const growthByStudent = {1:55,2:35,3:40,4:30,5:38,6:20,7:28,8:25,9:15,10:32,11:26,12:30};
    const baseWpmByStudent = {1:300,2:280,3:265,4:230,5:250,6:180,7:240,8:200,9:165,10:290,11:220,12:250};
    const baseAccByStudent = {1:93,2:90,3:87,4:85,5:88,6:76,7:88,8:81,9:73,10:92,11:85,12:89};
    const accGrowthByStudent = {1:6,2:5,3:6,4:5,5:6,6:4,7:5,8:5,9:3,10:5,11:5,12:5};
    const DAY = 24*60*60*1000;
    const RANGE_DAYS = 60;
    const bookTitles = ['The Lion and the Mouse','The Three Little Pigs','Journey to the Stars','A Rainy Day Surprise','The Ugly Duckling',"Charlotte's Web (excerpt)",'The Tortoise and the Hare','Goldilocks and the Three Bears','The Little Red Hen','The Boy Who Cried Wolf','Jack and the Beanstalk','The Fox and the Grapes','The Little Mermaid (retold)'];

    let sessions = [];
    (function generateSessions(){
      let id = 1;
      roster.forEach(student=>{
        const growth = growthByStudent[student.id] || 25;
        const baseWpm = baseWpmByStudent[student.id] || 220;
        const baseAcc = baseAccByStudent[student.id] || 85;
        const accGrowth = accGrowthByStudent[student.id] || 5;
        let d = Math.floor(rand()*3);
        while(d <= RANGE_DAYS-1){
          const progress = 1 - (d/RANGE_DAYS);
          const wpm = Math.round(baseWpm - growth + growth*progress + (rand()*16-8));
          const accuracy = clamp(Math.round(baseAcc - accGrowth + accGrowth*progress + (rand()*6-3)), 55, 100);
          const book = bookTitles[Math.floor(rand()*bookTitles.length)];
          sessions.push({id:id++, studentId:student.id, book, wpm:Math.max(80,wpm), accuracy, ts:Date.now() - d*DAY - Math.floor(rand()*DAY)});
          d += 2 + Math.floor(rand()*3);
        }
      });
    })();

    // ============================================================
    // Helpers
    // ============================================================
    function studentById(id){ return roster.find(r=>r.id===id); }
    function initials(name){ return String(name||'?').split(' ').filter(Boolean).map(p=>p[0]).slice(0,2).join('').toUpperCase(); }
    function avg(arr, key){ return arr.length ? arr.reduce((a,x)=>a+x[key],0)/arr.length : 0; }
    function accClass(a){ if(a>=90) return 'good'; if(a>=80) return 'mid'; return 'low'; }
    function fmtDate(ts){
      const d = new Date(ts);
      return d.toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'}) +
             ' • ' + d.toLocaleTimeString(undefined, {hour:'numeric', minute:'2-digit'});
    }
    function fmtShort(ts){ return new Date(ts).toLocaleDateString(undefined,{month:'short', day:'numeric'}); }
    function showToast(msg){
      const t = document.getElementById('toast');
      document.getElementById('toastMsg').textContent = msg;
      t.classList.add('show');
      clearTimeout(window.__toastTimer);
      window.__toastTimer = setTimeout(()=>t.classList.remove('show'), 2800);
    }
    function esc(v){
      return String(v == null ? '' : v).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    let visibleCount = 6;
    const PAGE_SIZE = 6;

    // ============================================================
    // Normalise whatever the API returns.
    // PHP/PDO often returns ids as strings and timestamps as
    // strings or seconds, and key names can differ between
    // endpoints (studentId vs student_id). Everything is coerced to
    // the types the rest of this page expects (numbers + ms).
    // ============================================================
    function normStudent(s){
      return {
        ...s,
        id: Number(s.id != null ? s.id : s.student_id),
        name: s.name || s.full_name || s.student_name || s.fullName ||
              ((s.first_name || s.firstName || '') + ' ' + (s.last_name || s.lastName || '')).trim() || 'Student',
        color: s.color || '#6fbf5a',
        section: s.section || '',
        book: s.book || s.current_book || ''
      };
    }
    function normTs(v){
      if (typeof v === 'number') return v < 1e12 ? v*1000 : v;
      if (v && /^\d+$/.test(String(v))) { const n = Number(v); return n < 1e12 ? n*1000 : n; }
      const parsed = Date.parse(String(v||'').replace(' ', 'T'));
      return isNaN(parsed) ? Date.now() : parsed;
    }
    function normSession(s){
      return {
        ...s,
        id: Number(s.id),
        studentId: Number(s.studentId != null ? s.studentId : (s.student_id != null ? s.student_id : s.studentID)),
        book: s.book || s.book_title || s.title || 'Reading session',
        wpm: Number(s.wpm) || 0,
        accuracy: Number(s.accuracy) || 0,
        ts: normTs(s.ts != null ? s.ts : (s.created_at != null ? s.created_at : s.date))
      };
    }

    // ============================================================
    // Student switcher (custom dropdown)
    // The button always shows the current student's name as plain
    // text; the menu lists every student in the roster.
    // ============================================================
    function resolveStudent(){
      // Only fall back to the first student when the requested one
      // really doesn't exist in the loaded roster.
      if(!currentStudentId || !studentById(currentStudentId)){
        currentStudentId = roster[0] ? roster[0].id : null;
      }
    }
    function populateSwitcher(){
      const current = studentById(currentStudentId);
      document.getElementById('switcherName').textContent = current ? current.name : 'Select student';
      document.getElementById('switcherMenu').innerHTML = roster.map(r =>
        `<button type="button" class="switcher-item${r.id === currentStudentId ? ' active' : ''}" role="option" data-id="${r.id}">${esc(r.name)}</button>`
      ).join('');
    }

    (function initSwitcher(){
      const box = document.getElementById('studentSwitcher');
      const btn = document.getElementById('switcherBtn');
      const menu = document.getElementById('switcherMenu');

      function setOpen(open){
        box.classList.toggle('open', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      }
      btn.addEventListener('click', e=>{ e.stopPropagation(); setOpen(!box.classList.contains('open')); });
      menu.addEventListener('click', e=>{
        const item = e.target.closest('.switcher-item');
        if(!item) return;
        const id = Number(item.dataset.id);
        setOpen(false);
        if(id !== currentStudentId){
          window.location.href = 'progress.php?id=' + encodeURIComponent(id);
        }
      });
      document.addEventListener('click', e=>{ if(!box.contains(e.target)) setOpen(false); });
      document.addEventListener('keydown', e=>{ if(e.key === 'Escape') setOpen(false); });
    })();

    // ============================================================
    // Profile card
    // ============================================================
    function renderProfile(studentSessions){
      const student = studentById(currentStudentId);
      if(!student) return;
      const accuracy = Math.round(avg(studentSessions,'accuracy'));
      const status = studentSessions.length===0 ? 'unknown' : (accuracy < 82 ? 'support' : 'ontrack');
      const statusLabel = status==='support' ? 'Needs Support' : (status==='ontrack' ? 'On Track' : 'No Sessions Yet');

      document.getElementById('profileCard').innerHTML = `
        <div class="profile-avatar" style="background:${esc(student.color)}">${initials(student.name)}</div>
        <div class="profile-info">
          <div class="profile-name">${esc(student.name)}</div>
          <div class="profile-sub">${esc(student.section || 'Unassigned')} • ${studentSessions.length} session${studentSessions.length===1?'':'s'} logged</div>
        </div>
        <div class="status-pill ${status}">${statusLabel}</div>
      `;

      document.getElementById('newModalAvatar').style.background = student.color;
      document.getElementById('newModalAvatar').textContent = initials(student.name);
      document.getElementById('newModalSub').textContent = 'For ' + student.name;
    }

    // ============================================================
    // Progress summary: did this student improve over time?
    // Compares the average of their first few sessions against the
    // average of their most recent few, for both speed (WPM) and
    // accuracy, then gives a plain-language verdict.
    // ============================================================
    function sign(n){ return n>0 ? '+' : (n<0 ? '−' : ''); }

    function renderSummary(studentSessions){
      const wrap = document.getElementById('progressSummary');
      const sub = document.getElementById('summarySub');
      const sorted = [...studentSessions].sort((a,b)=>a.ts-b.ts);

      if(sorted.length < 2){
        sub.textContent = '';
        wrap.innerHTML = `
          <div class="verdict none">
            <div class="verdict-icon">…</div>
            <div>
              <div class="verdict-title">Not enough sessions yet</div>
              <div class="verdict-text">Log at least 2 reading sessions to see whether this student is improving.</div>
            </div>
          </div>`;
        return;
      }

      const n = Math.max(1, Math.min(3, Math.floor(sorted.length/2)));
      const first = sorted.slice(0, n);
      const last = sorted.slice(-n);
      const startWpm = Math.round(avg(first,'wpm'));
      const nowWpm = Math.round(avg(last,'wpm'));
      const startAcc = Math.round(avg(first,'accuracy'));
      const nowAcc = Math.round(avg(last,'accuracy'));
      const wpmDelta = nowWpm - startWpm;
      const wpmPct = startWpm ? Math.round((wpmDelta/startWpm)*100) : 0;
      const accDelta = nowAcc - startAcc;

      const wpmDir = wpmPct >= 3 ? 1 : (wpmPct <= -3 ? -1 : 0);
      const accDir = accDelta >= 2 ? 1 : (accDelta <= -2 ? -1 : 0);

      let verdict, icon, title, text;
      if(wpmDir + accDir >= 1 && wpmDir >= 0 && accDir >= 0){
        verdict='improved'; icon='▲'; title='Improving over time';
        text = wpmDir>0 && accDir>0 ? 'Both reading speed and accuracy are up compared to the first sessions.'
             : wpmDir>0 ? 'Reading speed has grown, and accuracy is holding steady.'
             : 'Accuracy has grown, and reading speed is holding steady.';
      } else if(wpmDir + accDir <= -1 && wpmDir <= 0 && accDir <= 0){
        verdict='declined'; icon='▼'; title='Has slipped recently';
        text = wpmDir<0 && accDir<0 ? 'Both reading speed and accuracy are lower than in the first sessions.'
             : wpmDir<0 ? 'Reading speed has dropped, while accuracy is holding steady.'
             : 'Accuracy has dropped, while reading speed is holding steady.';
      } else if(wpmDir !== 0 && accDir !== 0){
        verdict='mixed'; icon='↕'; title='Mixed progress';
        text = wpmDir>0 ? 'Reading speed is up, but accuracy has dropped — reading may be getting rushed.'
                        : 'Accuracy is up, but reading speed has dropped — reading may be more careful and slower.';
      } else {
        verdict='steady'; icon='—'; title='Holding steady';
        text = 'Reading speed and accuracy are about the same as in the first sessions.';
      }

      const spanDays = Math.max(1, Math.round((sorted[sorted.length-1].ts - sorted[0].ts)/DAY));
      sub.textContent = `${sorted.length} sessions over ${spanDays} day${spanDays===1?'':'s'}`;

      const best = sorted.reduce((m,s)=> s.wpm>m.wpm ? s : m, sorted[0]);
      const dirClass = d => d>0 ? 'up' : (d<0 ? 'down' : '');

      wrap.innerHTML = `
        <div class="verdict ${verdict}">
          <div class="verdict-icon">${icon}</div>
          <div>
            <div class="verdict-title">${title}</div>
            <div class="verdict-text">${text}</div>
          </div>
        </div>
        <div class="summary-grid">
          <div class="sum-card">
            <div class="sum-label">Words per minute</div>
            <div class="sum-value">${startWpm} → ${nowWpm}</div>
            <div class="sum-sub ${dirClass(wpmDelta)}">${wpmDelta===0?'No change':`${sign(wpmDelta)}${Math.abs(wpmDelta)} WPM (${sign(wpmPct)}${Math.abs(wpmPct)}%)`}</div>
          </div>
          <div class="sum-card">
            <div class="sum-label">Accuracy</div>
            <div class="sum-value">${startAcc}% → ${nowAcc}%</div>
            <div class="sum-sub ${dirClass(accDelta)}">${accDelta===0?'No change':`${sign(accDelta)}${Math.abs(accDelta)} points`}</div>
          </div>
          <div class="sum-card">
            <div class="sum-label">Best session</div>
            <div class="sum-value">${best.wpm} WPM</div>
            <div class="sum-sub">${fmtShort(best.ts)} • ${best.accuracy}%</div>
          </div>
          <div class="sum-card">
            <div class="sum-label">Latest session</div>
            <div class="sum-value">${sorted[sorted.length-1].wpm} WPM</div>
            <div class="sum-sub">${fmtShort(sorted[sorted.length-1].ts)} • ${sorted[sorted.length-1].accuracy}%</div>
          </div>
        </div>
        ${(()=>{
          const latestAi = sorted.slice().reverse().find(s => s.ai_narrative);
          if(!latestAi) return '';
          return `
            <div class="ai-summary-card">
              <div class="ai-summary-header">
                <div class="ai-summary-title"><i class='bx bxs-brain'></i> AI Literacy Trajectory Assessment</div>
                <span class="ai-status-pill ${latestAi.ai_status || 'on_track'}">${(latestAi.ai_status || 'on_track').replace('_',' ')}</span>
              </div>
              <div style="font-size:13px;line-height:1.55;color:var(--ink);margin-bottom:8px;">${esc(latestAi.ai_narrative)}</div>
              ${latestAi.ai_phonics ? `<div style="font-size:12px;color:var(--muted);margin-bottom:4px;"><b style="color:var(--ink);">Phonics Observation:</b> ${esc(latestAi.ai_phonics)}</div>` : ''}
              ${latestAi.ai_next_step ? `<div style="font-size:12px;color:var(--muted);"><b style="color:var(--ink);">Recommended Action:</b> ${esc(latestAi.ai_next_step)}</div>` : ''}
            </div>
          `;
        })()}
        <div class="sum-sub" style="margin-top:12px;">“Start” is the average of the first ${n} session${n===1?'':'s'}; “now” is the average of the latest ${n}.</div>
      `;
    }


    // ============================================================
    // Week-by-week table, each week compared with the previous one
    // ============================================================
    function changePill(delta, unit, pct){
      if(delta === null) return '<span class="change-pill flat">—</span>';
      const cls = delta>0 ? 'up' : (delta<0 ? 'down' : 'flat');
      const arrow = delta>0 ? '▲' : (delta<0 ? '▼' : '—');
      const label = delta===0 ? 'No change' : `${Math.abs(delta)}${unit}${pct!=null?` (${Math.abs(pct)}%)`:''}`;
      return `<span class="change-pill ${cls}">${arrow} ${label}</span>`;
    }

    function renderPeriods(studentSessions){
      const wrap = document.getElementById('periodWrap');
      if(!studentSessions.length){
        wrap.innerHTML = `<div style="font-size:12.5px;color:var(--muted);font-weight:600;padding:10px 0;">No sessions logged yet.</div>`;
        return;
      }
      const now = Date.now();
      const buckets = {};
      studentSessions.forEach(s=>{
        const idx = Math.max(0, Math.floor((now - s.ts)/(7*DAY)));
        (buckets[idx] = buckets[idx] || []).push(s);
      });
      // oldest -> newest, keep the latest 8 weeks that have data
      let keys = Object.keys(buckets).map(Number).sort((a,b)=>b-a).slice(-8);
      const rows = keys.map((idx, i)=>{
        const list = buckets[idx];
        const wpm = Math.round(avg(list,'wpm'));
        const acc = Math.round(avg(list,'accuracy'));
        const start = now - (idx+1)*7*DAY, end = now - idx*7*DAY;
        return {label: `${fmtShort(start)} – ${fmtShort(end)}`, count:list.length, wpm, acc};
      });
      rows.forEach((r,i)=>{
        if(i===0){ r.wpmD = null; r.accD = null; r.wpmP = null; return; }
        const p = rows[i-1];
        r.wpmD = r.wpm - p.wpm;
        r.accD = r.acc - p.acc;
        r.wpmP = p.wpm ? Math.round((r.wpmD/p.wpm)*100) : 0;
      });
      const display = [...rows].reverse();

      wrap.innerHTML = `
        <table class="period-table">
          <thead><tr>
            <th>Week</th><th class="num">Sessions</th><th class="num">Avg WPM</th>
            <th class="num">WPM change</th><th class="num">Avg accuracy</th><th class="num">Accuracy change</th>
          </tr></thead>
          <tbody>
            ${display.map(r=>`
              <tr>
                <td>${r.label}</td>
                <td class="num">${r.count}</td>
                <td class="num">${r.wpm}</td>
                <td class="num">${changePill(r.wpmD,' WPM',r.wpmP)}</td>
                <td class="num">${r.acc}%</td>
                <td class="num">${changePill(r.accD,' pts',null)}</td>
              </tr>`).join('')}
          </tbody>
        </table>`;
    }

    // ============================================================
    // Trend chart (WPM + accuracy over time, this student only)
    // ============================================================
    function renderTrendChart(studentSessions){
      const sorted = [...studentSessions].sort((a,b)=>a.ts-b.ts);
      const wrap = document.getElementById('trendChartWrap');
      if(sorted.length < 2){
        wrap.innerHTML = `<div style="font-size:12.5px;color:var(--muted);font-weight:600;padding:30px 0;text-align:center;">Not enough sessions yet to chart a trend.</div>`;
        return;
      }
      const points = sorted.slice(-14); // most recent 14 sessions

      const W = 640, H = 220, padL = 34, padR = 34, padT = 16, padB = 26;
      const wpmVals = points.map(p=>p.wpm);
      const minW = Math.min(...wpmVals), maxW = Math.max(...wpmVals);
      const rangeW = Math.max(maxW-minW, 20);
      const yForWpm = v => padT + (1 - (v-minW)/rangeW) * (H-padT-padB);
      const yForAcc = v => padT + (1 - v/100) * (H-padT-padB);
      const xFor = i => padL + (i/(points.length-1)) * (W-padL-padR);

      const wpmPath = points.map((p,i)=>`${i===0?'M':'L'} ${xFor(i).toFixed(1)} ${yForWpm(p.wpm).toFixed(1)}`).join(' ');
      const accPath = points.map((p,i)=>`${i===0?'M':'L'} ${xFor(i).toFixed(1)} ${yForAcc(p.accuracy).toFixed(1)}`).join(' ');
      const areaPath = `${wpmPath} L ${xFor(points.length-1).toFixed(1)} ${H-padB} L ${xFor(0).toFixed(1)} ${H-padB} Z`;

      const gridLines = [0,1,2,3].map(i=>{
        const y = padT + (i/3)*(H-padT-padB);
        return `<line class="grid-line" x1="${padL}" y1="${y.toFixed(1)}" x2="${W-padR}" y2="${y.toFixed(1)}"/>`;
      }).join('');

      const wpmDots = points.map((p,i)=>`
        <circle cx="${xFor(i).toFixed(1)}" cy="${yForWpm(p.wpm).toFixed(1)}" r="4" fill="#fff" stroke="var(--green-dark)" stroke-width="2">
          <title>${new Date(p.ts).toLocaleDateString()}: ${p.wpm} WPM</title>
        </circle>`).join('');
      const accDots = points.map((p,i)=>`
        <circle cx="${xFor(i).toFixed(1)}" cy="${yForAcc(p.accuracy).toFixed(1)}" r="3.5" fill="#fff" stroke="var(--teal)" stroke-width="2">
          <title>${new Date(p.ts).toLocaleDateString()}: ${p.accuracy}% accuracy</title>
        </circle>`).join('');

      const labelStep = Math.max(1, Math.floor(points.length/6));
      const labels = points.map((p,i)=>{
        if(i % labelStep !== 0 && i !== points.length-1) return '';
        return `<text class="axis-label" x="${xFor(i).toFixed(1)}" y="${H-8}" text-anchor="middle">${new Date(p.ts).toLocaleDateString(undefined,{month:'short',day:'numeric'})}</text>`;
      }).join('');

      wrap.innerHTML = `
        <svg class="trend-chart" viewBox="0 0 ${W} ${H}" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="progressFill" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#6fbf5a" stop-opacity="0.35"/>
              <stop offset="100%" stop-color="#6fbf5a" stop-opacity="0.02"/>
            </linearGradient>
          </defs>
          ${gridLines}
          <path d="${areaPath}" fill="url(#progressFill)" stroke="none"/>
          <path d="${wpmPath}" fill="none" stroke="var(--green-dark)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="${accPath}" fill="none" stroke="var(--teal)" stroke-width="2" stroke-dasharray="4 3" stroke-linecap="round" stroke-linejoin="round"/>
          ${wpmDots}
          ${accDots}
          ${labels}
        </svg>`;
    }

    // ============================================================
    // Books read panel
    // ============================================================
    function renderBooks(studentSessions){
      const counts = {};
      studentSessions.forEach(s=>{ counts[s.book] = (counts[s.book]||0) + 1; });
      const list = Object.keys(counts).map(book=>({book, count:counts[book]})).sort((a,b)=>b.count-a.count);
      document.getElementById('booksSub').textContent = list.length + (list.length===1 ? ' title' : ' titles');

      if(!list.length){
        document.getElementById('booksList').innerHTML = `<div style="font-size:12.5px;color:var(--muted);font-weight:600;padding:10px 0;">No books logged yet.</div>`;
        return;
      }
      document.getElementById('booksList').innerHTML = list.slice(0,6).map(item=>`
        <div class="book-row">
          <div class="book-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
          </div>
          <div class="book-name" title="${esc(item.book)}">${esc(item.book)}</div>
          <div class="book-count">${item.count}×</div>
        </div>
      `).join('');
    }

    // ============================================================
    // Session history list
    // ============================================================
    function renderSessionsList(studentSessions){
      const sorted = [...studentSessions].sort((a,b)=>b.ts-a.ts);
      document.getElementById('historyCount').textContent = sorted.length + (sorted.length===1 ? ' session' : ' sessions');
      const wrap = document.getElementById('sessionsList');

      if(!sorted.length){
        wrap.innerHTML = `
          <div class="empty-state">
            <i class='bx bx-book-open bx'></i>
            <b>No sessions yet</b>
            Log this student's first reading session to start tracking progress.
          </div>`;
        document.getElementById('loadMoreBtn').style.display = 'none';
        return;
      }

      const student = studentById(currentStudentId);
      const shown = sorted.slice(0, visibleCount);
      wrap.innerHTML = shown.map(s=>{
        const statusBadges = {
          rapid_growth: '<span class="ai-status-pill rapid_growth">Rapid Growth</span>',
          on_track: '<span class="ai-status-pill on_track">On Track</span>',
          steady: '<span class="ai-status-pill steady">Steady</span>',
          needs_intervention: '<span class="ai-status-pill needs_intervention">Needs Support</span>'
        };
        const aiBadge = s.ai_status && statusBadges[s.ai_status] ? statusBadges[s.ai_status] : '';
        const quizBadge = (s.quiz_score !== null && s.quiz_total) ? `<span class="s-quiz-pill"><i class='bx bx-brain'></i> Quiz: ${s.quiz_score}/${s.quiz_total}</span>` : '';
        return `
        <div class="session-row" data-id="${s.id}">
          <div class="s-avatar" style="background:${esc(student.color)}">${initials(student.name)}</div>
          <div class="s-info">
            <div class="s-name">${esc(s.book)}</div>
            <div class="s-book" style="display:flex;align-items:center;gap:7px;margin-top:3px;flex-wrap:wrap;">
              <span class="s-time-mobile">${fmtDate(s.ts)}</span>
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
      if(visibleCount < sorted.length){
        btn.style.display = 'flex';
        btn.innerHTML = `Load More (${sorted.length - visibleCount} more)
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>`;
      } else {
        btn.style.display = 'none';
      }
    }
    document.getElementById('loadMoreBtn').addEventListener('click', ()=>{
      visibleCount += PAGE_SIZE;
      renderAll();
    });

    // ============================================================
    // Master render
    // ============================================================
    function currentSessions(){
      return sessions.filter(s=>s.studentId===currentStudentId);
    }
    function renderAll(){
      const studentSessions = currentSessions();
      renderProfile(studentSessions);
      renderSummary(studentSessions);
      renderTrendChart(studentSessions);
      renderBooks(studentSessions);
      renderPeriods(studentSessions);
      renderSessionsList(studentSessions);
    }

    // ============================================================
    // Session detail modal
    // ============================================================
    function openDetail(id){
      const s = sessions.find(x=>x.id===id);
      if(!s) return;
      const student = studentById(s.studentId);
      const modal = document.getElementById('detailModal');

      const quizStatsCol = (s.quiz_score !== null && s.quiz_total)
        ? `<div class="m-stat"><div class="v" style="color:var(--purple);">${s.quiz_score}/${s.quiz_total}</div><div class="l">Quiz Score</div></div>`
        : '';

      const aiDiagnostic = s.ai_narrative ? `
        <div class="modal-ai-box">
          <div class="modal-ai-head">
            <div class="modal-ai-title"><i class='bx bxs-brain'></i> AI Diagnostic</div>
            <span class="ai-status-pill ${s.ai_status || 'on_track'}">${(s.ai_status || 'on_track').replace('_',' ')}</span>
          </div>
          <div class="modal-ai-narrative">${esc(s.ai_narrative)}</div>
          ${s.ai_phonics ? `<div class="modal-ai-field"><b>Phonics:</b> ${esc(s.ai_phonics)}</div>` : ''}
          ${s.ai_comprehension_insight ? `<div class="modal-ai-field"><b>Comprehension:</b> ${esc(s.ai_comprehension_insight)}</div>` : ''}
          ${s.ai_next_step ? `<div class="modal-ai-field advice"><b>Teacher Advice:</b> ${esc(s.ai_next_step)}</div>` : ''}
        </div>` : `
        <div style="margin:14px 0;text-align:center;">
          <button class="btn-outline" id="triggerAiBtn" style="font-size:12px;padding:8px 14px;margin:auto;"><i class='bx bxs-brain'></i> Generate AI Assessment</button>
        </div>`;

      modal.innerHTML = `
        <button class="modal-close" id="detailCloseBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
        <div class="modal-head">
          <div class="modal-avatar" style="background:${esc(student.color)}">${initials(student.name)}</div>
          <div>
            <h2>${esc(student.name)}</h2>
            <div class="sub">${esc(s.book)}</div>
          </div>
        </div>
        <div class="modal-stats" style="${quizStatsCol ? 'grid-template-columns:repeat(4,1fr);' : ''}">
          <div class="m-stat"><div class="v">${s.wpm}</div><div class="l">WPM</div></div>
          <div class="m-stat"><div class="v">${s.accuracy}%</div><div class="l">Accuracy</div></div>
          ${quizStatsCol}
          <div class="m-stat"><div class="v">${accClass(s.accuracy)==='good'?'Strong':accClass(s.accuracy)==='mid'?'Steady':'Needs Work'}</div><div class="l">Result</div></div>
        </div>
        ${aiDiagnostic}
        <div class="detail-line">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
          <span class="b">${fmtDate(s.ts)}</span>
        </div>
        <button class="btn-danger-text" id="deleteSessionBtn">Delete Session</button>
      `;

      const triggerBtn = document.getElementById('triggerAiBtn');
      if (triggerBtn) {
        triggerBtn.addEventListener('click', () => {
          triggerBtn.disabled = true;
          triggerBtn.textContent = 'Evaluating…';
          fetch('ai-api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ action: 'evaluate_session', session_id: s.id })
          })
          .then(r => r.json())
          .then(res => {
            if (res && res.evaluation) {
              s.ai_narrative = res.evaluation.progress_narrative;
              s.ai_status = res.evaluation.overall_progress_status;
              s.ai_phonics = res.evaluation.phonics_insight;
              s.ai_comprehension_insight = res.evaluation.comprehension_insight;
              s.ai_next_step = res.evaluation.actionable_next_step;
              openDetail(s.id);
              renderAll();
            }
          })
          .catch(() => { triggerBtn.textContent = 'Assessment failed'; });
        });
      }

      document.getElementById('detailCloseBtn').addEventListener('click', closeDetail);
      document.getElementById('deleteSessionBtn').addEventListener('click', ()=>deleteSession(s.id));
      document.getElementById('detailOverlay').classList.add('open');
    }

    function closeDetail(){ document.getElementById('detailOverlay').classList.remove('open'); }
    document.getElementById('detailOverlay').addEventListener('click', e=>{
      if(e.target.id==='detailOverlay') closeDetail();
    });

    function deleteSession(id){
      const s = sessions.find(x=>x.id===id);
      if(!s) return;
      if(!confirm(`Delete this session? This can't be undone.`)) return;
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
    // New session modal (scoped to the current student)
    // ============================================================
    function openNewModal(){
      document.getElementById('newForm').reset();
      const student = studentById(currentStudentId);
      if(student) document.getElementById('newBook').value = student.book || '';
      document.getElementById('newOverlay').classList.add('open');
    }
    function closeNewModal(){ document.getElementById('newOverlay').classList.remove('open'); }
    document.getElementById('newSessionBtn').addEventListener('click', openNewModal);
    document.getElementById('newCloseBtn').addEventListener('click', closeNewModal);
    document.getElementById('newCancelBtn').addEventListener('click', closeNewModal);
    document.getElementById('newOverlay').addEventListener('click', e=>{
      if(e.target.id==='newOverlay') closeNewModal();
    });

    document.getElementById('newForm').addEventListener('submit', e=>{
      e.preventDefault();
      const book = document.getElementById('newBook').value.trim();
      const wpm = Number(document.getElementById('newWpm').value);
      const accuracy = Number(document.getElementById('newAccuracy').value);
      if(!book || !wpm || !accuracy) return;

      fetch('student-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'record_session', student_id:currentStudentId, book:book, wpm:wpm, accuracy:accuracy})})
        .then(response=>response.json().then(data=>({ok:response.ok,data:data})))
        .then(result=>{
          if(!result.ok) throw new Error(result.data.error || 'Unable to save session');
          sessions.unshift({id:(sessions.reduce((max,item)=>Math.max(max,item.id),0)+1), studentId:currentStudentId, book, wpm, accuracy, ts:Date.now()});
          visibleCount = Math.max(visibleCount, PAGE_SIZE);
          closeNewModal();
          renderAll();
          showToast(`Session logged: ${wpm} WPM, ${accuracy}%`);
        })
        .catch(error=>showToast(error.message));
    });

    document.getElementById('printBtn').addEventListener('click', ()=>window.print());

    // ============================================================
    // Sidebar + tip banner
    // ============================================================
    document.getElementById('sidebarToggle').addEventListener('click', function(){
      var isCollapsed = document.documentElement.getAttribute('data-sidebar') === 'collapsed';
      try {
        if (isCollapsed){
          document.documentElement.removeAttribute('data-sidebar');
          localStorage.setItem('readpilot-sidebar', 'expanded');
        } else {
          document.documentElement.setAttribute('data-sidebar', 'collapsed');
          localStorage.setItem('readpilot-sidebar', 'collapsed');
        }
      } catch (e) {}
    });
    document.getElementById('tipClose').addEventListener('click', function(){
      this.closest('.tip').style.display = 'none';
    });
    document.addEventListener('keydown', e=>{
      if(e.key==='Escape'){ closeDetail(); closeNewModal(); }
    });

    // ============================================================
    // Init — wait for LIVE data before rendering any student, so the
    // student chosen on Reports (?id=…) is the one that shows up.
    //
    // Tries view=sessions first (the same endpoint sessions.php uses),
    // then view=reports (the endpoint reports.php uses, which is what
    // the "View Progress" buttons come from) if the requested student
    // isn't found. If both fail, the placeholder data above is used.
    // ============================================================
    function fetchView(view){
      return fetch('student-api.php?view=' + view).then(r=>r.json());
    }
    function applyLive(result){
      if(!result || !Array.isArray(result.students) || !result.students.length) return false;
      const liveRoster = result.students.map(normStudent).filter(s=>!isNaN(s.id));
      if(!liveRoster.length) return false;
      roster = liveRoster;
      sessions = Array.isArray(result.sessions)
        ? result.sessions.map(normSession).filter(s=>!isNaN(s.studentId))
        : [];
      return true;
    }
    function finish(){
      resolveStudent();
      populateSwitcher();
      renderAll();
    }

    fetchView('sessions')
      .then(result=>{
        const ok = applyLive(result);
        if(ok && (!requestedId || studentById(requestedId))) return;
        // Requested student wasn't in this view — try the reports view.
        return fetchView('reports').then(applyLive);
      })
      .catch(()=>{
        return fetchView('reports').then(applyLive).catch(()=>{});
      })
      .then(finish, finish);
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>