<?php require_once __DIR__ . '/auth-guard.php'; require_admin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot Admin — Audit Log</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin-style.css">
<script>
(function () {
  try { var t = localStorage.getItem('readpilot-admin-theme'); if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark'); } catch (e) {}
  try { var s = localStorage.getItem('readpilot-admin-sidebar'); if (s === 'collapsed') document.documentElement.setAttribute('data-sidebar', 'collapsed'); } catch (e) {}
})();
</script>
<style>
.modal-overlay{position:fixed;inset:0;background:rgba(12,15,30,0.5);display:none;align-items:center;justify-content:center;z-index:999;padding:20px;}
.modal-overlay.show{display:flex;}
.modal-box{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:26px 26px 22px;max-width:360px;width:100%;}
.modal-box .m-title{font-family:'Poppins',sans-serif;font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px;display:flex;align-items:center;gap:9px;}
.modal-box .m-title .bx{font-size:19px;color:var(--green-dark);}
.modal-box .m-desc{font-size:12.5px;color:var(--muted);font-weight:600;line-height:1.5;margin-bottom:20px;}
.modal-actions{display:flex;justify-content:flex-end;gap:10px;}
.btn-solid{background:var(--green);color:#fff;border:none;border-radius:11px;padding:11px 20px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;box-shadow:0 4px 10px rgba(76,99,210,0.3);transition:transform .12s steps(2), box-shadow .12s steps(2);}
.btn-solid:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark), 0 4px 10px rgba(76,99,210,0.3);}
.btn-outline{background:var(--card);border:1.5px solid var(--border);color:var(--ink);border-radius:11px;padding:10px 18px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;transition:transform .12s steps(2), box-shadow .12s steps(2), border-color .15s ease;}
.btn-outline:hover{transform:translate(-2px,-2px);border-color:var(--green-dark);box-shadow:2px 2px 0 var(--green-dark);}
</style>
</head>
<body>

  <aside class="sidebar">
    <div class="pixel-plane" style="--y:14%; --dur:13s; --delay:0s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2" height="1" fill="#c3cdf5"/>
        <rect x="0" y="1" width="4" height="1" fill="#c3cdf5"/>
        <rect x="0" y="2" width="6" height="1" fill="#c3cdf5"/>
        <rect x="0" y="3" width="9" height="1" fill="#a6b3ef"/>
        <rect x="0" y="4" width="13" height="1" fill="#8497e9"/>
        <rect x="0" y="5" width="9" height="1" fill="#5f74d6"/>
        <rect x="0" y="6" width="6" height="1" fill="#5f74d6"/>
        <rect x="0" y="7" width="4" height="1" fill="#5f74d6"/>
        <rect x="0" y="8" width="2" height="1" fill="#5f74d6"/>
      </svg>
    </div>
    <div class="pixel-plane" style="--y:74%; --dur:17s; --delay:6s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2" height="1" fill="#c3cdf5"/>
        <rect x="0" y="1" width="4" height="1" fill="#c3cdf5"/>
        <rect x="0" y="2" width="6" height="1" fill="#c3cdf5"/>
        <rect x="0" y="3" width="9" height="1" fill="#a6b3ef"/>
        <rect x="0" y="4" width="13" height="1" fill="#8497e9"/>
        <rect x="0" y="5" width="9" height="1" fill="#5f74d6"/>
        <rect x="0" y="6" width="6" height="1" fill="#5f74d6"/>
        <rect x="0" y="7" width="4" height="1" fill="#5f74d6"/>
        <rect x="0" y="8" width="2" height="1" fill="#5f74d6"/>
      </svg>
    </div>
    <div>
      <div class="logo-row">
        <div class="logo-left">
          <div class="logo-icon"><i class='bx bxs-shield-alt-2'></i></div>
          <div class="logo-text"><span class="brand">ReadPilot</span><span class="tagline">Admin Console</span></div>
        </div>
        <button class="hamburger" id="sidebarToggle" aria-label="Toggle navigation"><span class="bar"></span></button>
      </div>
      <nav>
        <a class="nav-item" href="admin.php"><i class="bx bxs-dashboard"></i><span class="label">Dashboard</span></a>
        <a class="nav-item" href="users-admin.php"><i class="bx bx-user-circle"></i><span class="label">User Management</span></a>
        <a class="nav-item" href="gradesec-admin.php"><i class="bx bx-layer"></i><span class="label">Student Records</span></a>
        <a class="nav-item active" href="audit-trail-admin.php"><i class="bx bx-history"></i><span class="label">Audit Log</span></a>
        <a class="nav-item" href="settings-admin.php"><i class="bx bx-cog"></i><span class="label">Settings</span></a>
      </nav>
    </div>
    <div class="teacher-card">
      <div class="teacher-row">
        <div class="teacher-row-info">
          <div class="avatar">🛡️</div>
          <div><div class="teacher-name"><?= htmlspecialchars(current_user()['full_name'], ENT_QUOTES, 'UTF-8') ?></div><div class="teacher-role">System Administrator</div></div>
        </div>
        <button class="teacher-logout-btn" id="sidebarLogoutBtn" title="Log out" aria-label="Log out"><i class='bx bx-log-out'></i></button>
      </div>
      <div class="quote">"Good access control is what lets every teacher trust the system." <span class="heart">♥</span></div>
    </div>
  </aside>

  <main class="main">
    <div class="topbar">
      <div class="title-block">
        <h1>Audit Log</h1>
        <div class="greet">Every account and roster change, in order.</div>
      </div>
      <div class="topbar-actions">
        <div class="search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          Search the activity log...
        </div>
        <div class="date-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Aug 21, 2026
        </div>
      </div>
    </div>

    <div id="auditPage">
      <div class="controls">
        <div class="tabs">
          <div class="tab active" data-filter="all">All Events</div>
          <div class="tab" data-filter="user">User Management</div>
          <div class="tab" data-filter="roster">Roster Changes</div>
          <div class="tab" data-filter="login">Login Activity</div>
        </div>
        <div class="controls-right">
          <span class="results-count" id="auditResultsCount"></span>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
            Activity
          </div>
        </div>

        <div class="audit-row" data-type="user">
          <div class="audit-icon create"><i class='bx bx-user-plus'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Rafael Santos</b> added a teacher account for <b>Priya Nair</b></div>
            <div class="audit-meta"><span class="audit-tag">User Management</span> Grade 1 · Section A</div>
          </div>
          <div class="audit-time">Aug 21 · 9:12 AM</div>
        </div>

        <div class="audit-row" data-type="login">
          <div class="audit-icon login"><i class='bx bx-log-in-circle'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Elena Hernandez</b> logged in from a new device</div>
            <div class="audit-meta"><span class="audit-tag">Login</span> Chrome on macOS</div>
          </div>
          <div class="audit-time">Aug 21 · 8:03 AM</div>
        </div>

        <div class="audit-row" data-type="user">
          <div class="audit-icon deactivate"><i class='bx bx-user-x'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Rafael Santos</b> deactivated the account for <b>Marco Villanueva</b></div>
            <div class="audit-meta"><span class="audit-tag">User Management</span> Reason: left the school</div>
          </div>
          <div class="audit-time">Aug 20 · 4:47 PM</div>
        </div>

        <div class="audit-row" data-type="roster">
          <div class="audit-icon update"><i class='bx bx-transfer'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Rafael Santos</b> moved <b>Carmen Reyes</b> to Grade 3 · Section B</div>
            <div class="audit-meta"><span class="audit-tag">Roster</span> Previously Section A</div>
          </div>
          <div class="audit-time">Aug 19 · 2:30 PM</div>
        </div>

        <div class="audit-row" data-type="login">
          <div class="audit-icon login"><i class='bx bx-log-in-circle'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Diego Fuentes</b> logged in</div>
            <div class="audit-meta"><span class="audit-tag">Login</span> Safari on iPad</div>
          </div>
          <div class="audit-time">Aug 19 · 7:41 AM</div>
        </div>

        <div class="audit-row" data-type="user">
          <div class="audit-icon update"><i class='bx bx-edit-alt'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Rafael Santos</b> updated contact details for <b>Liza Cortez</b></div>
            <div class="audit-meta"><span class="audit-tag">User Management</span> Email address changed</div>
          </div>
          <div class="audit-time">Aug 18 · 3:12 PM</div>
        </div>

        <div class="audit-row" data-type="roster">
          <div class="audit-icon update"><i class='bx bx-transfer'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Rafael Santos</b> assigned <b>Noel Bautista</b> to Grade 4 · Section A</div>
            <div class="audit-meta"><span class="audit-tag">Roster</span> New homeroom assignment</div>
          </div>
          <div class="audit-time">Aug 18 · 11:05 AM</div>
        </div>

        <div class="audit-row" data-type="user">
          <div class="audit-icon create"><i class='bx bx-user-plus'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Ana Cabrera</b> added an admin account for <b>Rafael Santos</b></div>
            <div class="audit-meta"><span class="audit-tag">User Management</span> Role: Admin</div>
          </div>
          <div class="audit-time">Aug 17 · 5:15 PM</div>
        </div>

        <div class="audit-row" data-type="login">
          <div class="audit-icon login"><i class='bx bx-log-in-circle'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Priya Nair</b> logged in for the first time</div>
            <div class="audit-meta"><span class="audit-tag">Login</span> Chrome on Windows</div>
          </div>
          <div class="audit-time">Aug 17 · 9:00 AM</div>
        </div>

        <div class="audit-row" data-type="user">
          <div class="audit-icon deactivate"><i class='bx bx-user-x'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Rafael Santos</b> deactivated the account for <b>Grace Salvador</b></div>
            <div class="audit-meta"><span class="audit-tag">User Management</span> Reason: role change</div>
          </div>
          <div class="audit-time">Aug 15 · 1:22 PM</div>
        </div>

        <div class="audit-row" data-type="roster">
          <div class="audit-icon update"><i class='bx bx-transfer'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Rafael Santos</b> moved <b>Joaquin Perez</b> to Grade 3 · Section B</div>
            <div class="audit-meta"><span class="audit-tag">Roster</span> Previously Section A</div>
          </div>
          <div class="audit-time">Aug 14 · 10:47 AM</div>
        </div>

        <div class="audit-row" data-type="user">
          <div class="audit-icon create"><i class='bx bx-user-plus'></i></div>
          <div class="audit-body">
            <div class="audit-text"><b>Rafael Santos</b> added a teacher account for <b>Noel Bautista</b></div>
            <div class="audit-meta"><span class="audit-tag">User Management</span> Grade 4 · Section A</div>
          </div>
          <div class="audit-time">Aug 12 · 4:00 PM</div>
        </div>

        <div class="panel-footer">
          <button class="load-more">
            Load More
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </button>
        </div>
      </div>
    </div>
  </main>

  <div class="modal-overlay" id="logoutModal">
    <div class="modal-box">
      <div class="m-title"><i class='bx bx-log-out'></i>Log out of Admin Console?</div>
      <div class="m-desc">You'll need to sign back in with your email and password to access the dashboard, user accounts, and audit log.</div>
      <div class="modal-actions">
        <button class="btn-outline" id="logoutCancelBtn">Cancel</button>
        <button class="btn-solid" id="logoutConfirmBtn"><i class='bx bx-check'></i>Log out</button>
      </div>
    </div>
  </div>

  <div id="toast" class="toast"><i class='bx bx-check-circle'></i><span id="toastMsg"></span></div>

  <script>
    document.getElementById('sidebarToggle').addEventListener('click', function(){
      var isCollapsed = document.documentElement.getAttribute('data-sidebar') === 'collapsed';
      if (isCollapsed){ document.documentElement.removeAttribute('data-sidebar'); localStorage.setItem('readpilot-admin-sidebar', 'expanded'); }
      else { document.documentElement.setAttribute('data-sidebar', 'collapsed'); localStorage.setItem('readpilot-admin-sidebar', 'collapsed'); }
    });
  </script>
  <script src="admin-shared-ui.js"></script>
  <script>
    fetch('admin-api.php?view=audit').then(response=>response.json()).then(result=>{
      if (!Array.isArray(result.audit)) return;
      var panel = document.querySelector('.panel');
      var footer = panel && panel.querySelector('.panel-footer');
      panel.querySelectorAll('.audit-row').forEach(function(row){ row.remove(); });
      result.audit.forEach(function(item){
        var row = document.createElement('div');
        row.className = 'audit-row';
        row.dataset.type = item.action.indexOf('login') !== -1 || item.action.indexOf('logout') !== -1 ? 'login' : item.action.indexOf('student') !== -1 || item.action.indexOf('section') !== -1 ? 'roster' : 'user';
        row.innerHTML = '<div class="audit-icon update"><i class="bx bx-history"></i></div><div class="audit-body"><div class="audit-text"></div><div class="audit-meta"><span class="audit-tag"></span> Database activity</div></div><div class="audit-time"></div>';
        row.querySelector('.audit-text').textContent = item.actor + ' · ' + item.details;
        row.querySelector('.audit-tag').textContent = item.action;
        row.querySelector('.audit-time').textContent = new Date(item.created_at).toLocaleString();
        if (footer) panel.insertBefore(row, footer); else panel.appendChild(row);
      });
    }).catch(function(){});
  </script>
</body>
</html>