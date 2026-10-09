<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Teacher Dashboard</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script>
(function () {
  try {
    var savedTheme = localStorage.getItem('readpilot-theme');
    if (savedTheme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
  } catch (e) {}
  try {
    var savedSidebar = localStorage.getItem('readpilot-sidebar');
    if (savedSidebar === 'collapsed') document.documentElement.setAttribute('data-sidebar', 'collapsed');
  } catch (e) {}
})();
</script>
<style>
  .modal-overlay{position:fixed;inset:0;background:rgba(10,20,14,0.45);display:none;align-items:center;justify-content:center;z-index:999;padding:20px;}
  .modal-overlay.show{display:flex;}
  .modal-box{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:26px 26px 22px;max-width:360px;width:100%;}
  .modal-box .m-title{font-family:'Poppins',sans-serif;font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px;display:flex;align-items:center;gap:9px;}
  .modal-box .m-title .bx{font-size:19px;color:var(--green-dark);}
  .modal-box .m-desc{font-size:12.5px;color:var(--muted);font-weight:600;line-height:1.5;margin-bottom:20px;}
  .modal-actions{display:flex;justify-content:flex-end;gap:10px;}
  .btn-solid{background:var(--green);color:#fff;border:none;border-radius:11px;padding:11px 20px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;box-shadow:0 4px 10px rgba(111,191,90,0.3);transition:transform .12s steps(2), box-shadow .12s steps(2);}
  .btn-solid:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark), 0 4px 10px rgba(111,191,90,0.3);}
  .btn-outline{background:var(--card);border:1.5px solid var(--border);color:var(--ink);border-radius:11px;padding:10px 18px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;transition:transform .12s steps(2), box-shadow .12s steps(2), border-color .15s ease;}
  .btn-outline:hover{transform:translate(-2px,-2px);border-color:var(--green-dark);box-shadow:2px 2px 0 var(--green-dark);}
  /* Loading placeholder shown only until the real sessions arrive from
     student-api.php — replaces the old hardcoded/fake rows so nothing
     ever shows sample data that could disagree with sessions.php. */
  .dashboard-loading{padding:30px 20px;text-align:center;color:var(--muted);font-size:12.5px;font-weight:700;}
</style>
</head>
<body>
  <!-- ================= SIDEBAR ================= -->
  <aside class="sidebar">
    <div class="pixel-plane" style="--y:14%; --dur:13s; --delay:0s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2" height="1" fill="#cbe98f"/><rect x="0" y="1" width="4" height="1" fill="#cbe98f"/><rect x="0" y="2" width="6" height="1" fill="#cbe98f"/><rect x="0" y="3" width="9" height="1" fill="#cbe98f"/><rect x="0" y="4" width="13" height="1" fill="#b1db65"/><rect x="0" y="5" width="9" height="1" fill="#7fae55"/><rect x="0" y="6" width="6" height="1" fill="#7fae55"/><rect x="0" y="7" width="4" height="1" fill="#7fae55"/><rect x="0" y="8" width="2" height="1" fill="#7fae55"/>
      </svg>
    </div>
    <div class="pixel-plane" style="--y:74%; --dur:17s; --delay:6s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2" height="1" fill="#cbe98f"/><rect x="0" y="1" width="4" height="1" fill="#cbe98f"/><rect x="0" y="2" width="6" height="1" fill="#cbe98f"/><rect x="0" y="3" width="9" height="1" fill="#cbe98f"/><rect x="0" y="4" width="13" height="1" fill="#b1db65"/><rect x="0" y="5" width="9" height="1" fill="#7fae55"/><rect x="0" y="6" width="6" height="1" fill="#7fae55"/><rect x="0" y="7" width="4" height="1" fill="#7fae55"/><rect x="0" y="8" width="2" height="1" fill="#7fae55"/>
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
        <a class="nav-item active" href="index.php"><i class="bx bxs-dashboard"></i><span class="label">Dashboard</span></a>
        <a class="nav-item" href="students.php"><i class="bx bx-group"></i><span class="label">Sections</span></a>
        <a class="nav-item" href="sessions.php"><i class="bx bx-calendar"></i><span class="label">Sessions</span></a>
        <a class="nav-item" href="reports.php"><i class="bx bx-file"></i><span class="label">Reports</span></a>
        <a class="nav-item" href="struggle-map.php"><i class="bx bx-target-lock"></i><span class="label">Struggle Map</span></a>
        <a class="nav-item" href="recommendations.php"><i class="bx bx-bulb"></i><span class="label">Recommendations</span></a>
        <a class="nav-item" href="resources.php"><i class="bx bx-book-open"></i><span class="label">Resources</span></a>
        <a class="nav-item" href="recordings.php"><i class="bx bx-cloud-upload"></i><span class="label">Cloud Recordings</span></a>
        <a class="nav-item" href="settings.php"><i class="bx bx-cog"></i><span class="label">Settings</span></a>
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
        <button class="teacher-logout-btn" id="sidebarLogoutBtn" title="Log out" aria-label="Log out"><i class='bx bx-log-out'></i></button>
      </div>
      <div class="quote">"Every page a child reads today is a step toward a brighter tomorrow." <span class="heart">♥</span></div>
    </div>
  </aside>

  <!-- ================= MAIN ================= -->
  <main class="main">
    <div class="topbar">
      <div class="title-block">
        <h1>Teacher Dashboard</h1>
        <div class="greet">👋 Good morning, <?= htmlspecialchars(current_user()['full_name'], ENT_QUOTES, 'UTF-8') ?>!</div>
      </div>
      <div class="topbar-actions">
        <div class="search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          Search students, sessions, or reports...
        </div>
        <div class="date-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Aug 21, 2026
        </div>
      </div>
    </div>

    <div class="stats">
      <div class="stat-card">
        <div class="stat-icon green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M17 11a4 4 0 1 0-3.2-6.4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/><path d="M15 15c3.5 0 6 2 6 6"/></svg>
        </div>
        <div>
          <div class="stat-label">Total Students</div>
          <div class="stat-value" id="dashboardStudentCount">0</div>
        </div>
        <div class="stat-sub" id="dashboardStudentSub">active learners</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon purple">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
        </div>
        <div>
          <div class="stat-label">Sessions Completed</div>
          <div class="stat-value" id="dashboardSessionCount">0</div>
        </div>
        <div class="stat-sub">This week</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon orange">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/><path d="M17 7h4v4"/></svg>
        </div>
        <div>
          <div class="stat-label">Average WPM</div>
          <div class="stat-value" id="dashboardWpm">0</div>
        </div>
        <div class="stat-sub">Across 5 sessions</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon red">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15V4"/><path d="M4 15c2-1.5 4-1.5 6 0s4 1.5 6 0V4c-2 1.5-4 1.5-6 0S6 2.5 4 4"/></svg>
        </div>
        <div>
          <div class="stat-label">Students Needing Support</div>
          <div class="stat-value" id="dashboardSupportCount">0</div>
        </div>
        <div class="stat-sub good">Great job! ♥</div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div class="panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2Z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7Z"/></svg>
          Recent Reading Sessions
          <span class="live-pill"><span class="live-dot"></span>Live</span>
        </div>
        <a class="view-all" href="sessions.php">View All</a>
      </div>

      <!--
        NOTE: the previous version of this file hardcoded 5 sample
        session rows here (Carmen Reyes / May 12, 2024 / etc.). Those
        rows had nothing to do with sessions.php's data, so the two
        pages always looked out of sync. They've been removed in
        favor of a single loading placeholder — the script below
        fetches the real sessions from student-api.php (the same
        backend sessions.php reads from) and renders them here, so
        both pages now show the same underlying data.
      -->
      <div class="dashboard-loading" id="dashboardLoading">Loading recent sessions…</div>

      <div class="panel-footer">
        <button class="load-more">
          Load More
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="pixel-books" aria-hidden="true">
          <svg viewBox="0 0 14 10" xmlns="http://www.w3.org/2000/svg">
            <!-- bottom book (widest) -->
            <rect x="0" y="8" width="14" height="1" fill="#4c8c3c"/>
            <rect x="0" y="9" width="14" height="1" fill="#2d5c32"/>
            <rect x="0" y="8" width="1"  height="2" fill="#6fae45"/>

            <!-- middle book -->
            <rect x="1" y="5" width="10" height="1" fill="#9b81dd"/>
            <rect x="1" y="6" width="10" height="1" fill="#6b4fa8"/>
            <rect x="1" y="5" width="1"  height="2" fill="#b39ce8"/>

            <!-- top book (smallest) -->
            <rect x="2" y="2" width="7" height="1" fill="#f6b96a"/>
            <rect x="2" y="3" width="7" height="1" fill="#d98f3a"/>
            <rect x="2" y="2" width="1" height="2" fill="#ffd08a"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="tip">
      <div class="tip-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
      </div>
      <div>
        <div class="tip-title">Tip of the day</div>
        <div class="tip-text">Encourage your students to read a little every day. Consistency helps build confident readers!</div>
      </div>
      <div class="tip-close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </div>
    </div>
  </main>

  <!-- ================= LOG OUT CONFIRM MODAL ================= -->
  <div class="modal-overlay" id="logoutModal">
    <div class="modal-box">
      <div class="m-title"><i class='bx bx-log-out'></i>Are you sure you want to log out?</div>
      <div class="m-desc">Choose “Log out” to end your ReadPilot session, or “Cancel” to stay signed in.</div>
      <div class="modal-actions">
        <button class="btn-outline" id="logoutCancelBtn">Cancel</button>
        <button class="btn-solid" id="logoutConfirmBtn"><i class='bx bx-check'></i>Log out</button>
      </div>
    </div>
  </div>

  <script>
    // ---- Hamburger: collapse sidebar for full-screen navigation ----
    // Uses data-sidebar="collapsed" on <html> (saved to localStorage) instead
    // of a plain class, so the preload script in <head> can restore this
    // state on every page load — that's what keeps the sidebar collapsed
    // as you navigate, instead of resetting to expanded on each new page.
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

    // ---- Tip banner close ----
    var tipEl = document.querySelector('.tip');
    var tipCloseEl = document.querySelector('.tip-close');
    if (tipCloseEl){
      tipCloseEl.addEventListener('click', function(){ tipEl.style.display = 'none'; });
    }

    // ================= Log out =================
    // Icon button beside the notification bell opens a confirm modal so a
    // stray click can't sign someone out.
    var logoutModal = document.getElementById('logoutModal');
    function openLogoutModal(){ logoutModal.classList.add('show'); }
    function closeLogoutModal(){ logoutModal.classList.remove('show'); }

    document.getElementById('sidebarLogoutBtn').addEventListener('click', openLogoutModal);
    document.getElementById('logoutCancelBtn').addEventListener('click', closeLogoutModal);
    logoutModal.addEventListener('click', function(e){
      if (e.target === logoutModal) closeLogoutModal();
    });

    document.getElementById('logoutConfirmBtn').addEventListener('click', function(){
      // Clear anything session-specific. Saved appearance prefs
      // (theme/sidebar state) are left in place on purpose, since those
      // are device preferences rather than auth state.
      try {
        sessionStorage.clear();
        localStorage.removeItem('readpilot-auth-token');
        localStorage.removeItem('readpilot-user');
      } catch (e) { /* storage unavailable, nothing to clear */ }

      closeLogoutModal();
      setTimeout(function(){
        window.location.href = 'logout.php';
      }, 700);
    });

    // ================= Recent Reading Sessions =================
    // Single fetch to the same backend endpoint/view that sessions.php's
    // stats are ultimately reconciled against, so this list can never
    // drift out of sync with the full Sessions page again. (Previously
    // this called student-api.php?view=dashboard twice in a nested
    // fashion, which was redundant and could race with itself.)
    fetch('student-api.php?view=dashboard').then(function(response){ return response.json(); }).then(function(result){
      var panel = document.querySelector('.main .panel');
      var loadingEl = document.getElementById('dashboardLoading');
      if (loadingEl) loadingEl.remove();
      if (!Array.isArray(result.students) || !Array.isArray(result.sessions)) return;

      var active = result.students.filter(function(student){ return student.status !== 'inactive'; }).length;
      var support = result.students.filter(function(student){ return student.status === 'support'; }).length;
      var average = Number(result.summary && result.summary.average_wpm) || 0;
      document.getElementById('dashboardStudentCount').textContent = result.students.length;
      document.getElementById('dashboardStudentSub').textContent = active + ' active learners';
      document.getElementById('dashboardSessionCount').textContent = result.summary ? result.summary.total : 0;
      document.getElementById('dashboardWpm').textContent = average;
      document.getElementById('dashboardSupportCount').textContent = support;

      if (!panel) return;
      var footer = panel.querySelector('.panel-footer');
      panel.querySelectorAll('.session-row, .dashboard-empty').forEach(function(row){ row.remove(); });

      if (!result.sessions.length) {
        var empty = document.createElement('div');
        empty.className = 'dashboard-empty empty-state';
        empty.innerHTML = '<i class="bx bx-book-open"></i><b>No reading sessions yet</b>Completed student sessions will appear here.';
        if (footer) panel.insertBefore(empty, footer); else panel.appendChild(empty);
        return;
      }

      result.sessions.forEach(function(session){
        var row = document.createElement('div');
        row.className = 'session-row';
        row.innerHTML = '<div class="s-avatar"></div><div class="s-info"><div class="s-name"></div><div class="s-book"></div></div><div class="s-time"><i class="bx bx-time-five"></i></div><div class="s-wpm"></div><div class="s-badge"></div><div class="s-arrow"><i class="bx bx-chevron-right"></i></div>';
        row.querySelector('.s-avatar').textContent = session.studentName.split(/\s+/).map(function(part){ return part.charAt(0); }).slice(0, 2).join('').toUpperCase();
        row.querySelector('.s-avatar').style.background = session.color;
        row.querySelector('.s-name').textContent = session.studentName;
        row.querySelector('.s-book').textContent = session.book;
        row.querySelector('.s-wpm').textContent = session.wpm + ' WPM';
        row.querySelector('.s-badge').textContent = session.accuracy + '%';
        row.querySelector('.s-time').appendChild(document.createTextNode(new Date(Number(session.ts)).toLocaleString(undefined, {month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'2-digit'})));
        if (footer) panel.insertBefore(row, footer); else panel.appendChild(row);
      });
    }).catch(function(){
      var loadingEl = document.getElementById('dashboardLoading');
      if (loadingEl) loadingEl.textContent = 'Unable to load recent sessions right now.';
    });
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>