<?php require_once __DIR__ . '/auth-guard.php'; require_admin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot Admin — Student Records</title>
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

/* section-summary cards: same visual language as the old grade-card, just semantically for sections now */
.grade-card.section-summary{cursor:pointer;}
.grade-card.section-summary.is-active{border-color:var(--green-dark);box-shadow:2px 2px 0 var(--green-dark);}
.section-tabs button{cursor:pointer;}
.section-tabs button.active{font-weight:800;}
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
        <a class="nav-item active" href="gradesec-admin.php"><i class="bx bx-layer"></i><span class="label">Student Records</span></a>
        <a class="nav-item" href="audit-trail-admin.php"><i class="bx bx-history"></i><span class="label">Audit Log</span></a>
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
        <h1>Student Records</h1>
        <div class="greet">Grade 3 roster · pick a section or search for a student.</div>
      </div>
      <div class="topbar-actions">
        <div class="search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input id="studentSearchInput" type="text" placeholder="Search by name or book..." style="border:none;outline:none;background:transparent;font:inherit;color:inherit;width:100%;">
        </div>
      </div>
    </div>

    <div id="studentRecordsPage">
      <div class="grade-grid">
        <div class="grade-card section-summary is-active" data-section="all">
          <div class="g-name">All Students</div>
          <div class="g-meta">3 sections · 12 students</div>
        </div>
        <div class="grade-card section-summary" data-section="A">
          <div class="g-name">Section A</div>
          <div class="g-meta">4 students · All on track</div>
        </div>
        <div class="grade-card section-summary" data-section="B">
          <div class="g-name">Section B</div>
          <div class="g-meta">4 students · 1 needs support</div>
        </div>
        <div class="grade-card section-summary" data-section="C">
          <div class="g-name">Section C</div>
          <div class="g-meta">4 students · 1 needs support</div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2Z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7Z"/></svg>
            <span id="rosterGradeLabel">Grade 3</span> roster
          </div>
        </div>

        <div class="section-tabs" id="sectionTabs">
          <button class="section-tab active" data-section="all">All</button>
          <button class="section-tab" data-section="A">Section A</button>
          <button class="section-tab" data-section="B">Section B</button>
          <button class="section-tab" data-section="C">Section C</button>
        </div>

        <div id="rosterWrap" class="table-scroll">
          <table class="data-table">
            <thead>
              <tr>
                <th>Student</th>
                <th>Section</th>
                <th>Currently Reading</th>
                <th>Avg WPM</th>
                <th>Accuracy</th>
                <th>Books Read</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="rosterBody">
              
          <div id="noResults" class="cell-muted" style="display:none;padding:24px 4px;text-align:center;">No students match your search.</div>
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

    // --- Section filtering (self-contained, no dependency on admin-shared-ui.js) ---
    (function () {
      var tabButtons = document.querySelectorAll('#sectionTabs button');
      var summaryCards = document.querySelectorAll('.section-summary');
      var rows = document.querySelectorAll('#rosterBody tr');
      var searchInput = document.getElementById('studentSearchInput');
      var noResults = document.getElementById('noResults');
      var currentSection = 'all';

      function applyFilters() {
        var query = (searchInput.value || '').trim().toLowerCase();
        var visibleCount = 0;

        rows.forEach(function (row) {
          var section = row.getAttribute('data-section');
          var name = row.querySelector('.u-name') ? row.querySelector('.u-name').textContent.toLowerCase() : '';
          var book = (row.getAttribute('data-book') || '').toLowerCase();
          var matchesSection = (currentSection === 'all') || (section === currentSection);
          var matchesSearch = !query || name.indexOf(query) !== -1 || book.indexOf(query) !== -1;
          var show = matchesSection && matchesSearch;
          row.style.display = show ? '' : 'none';
          if (show) visibleCount++;
        });

        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
      }

      function setActiveSection(section) {
        currentSection = section;
        tabButtons.forEach(function (btn) {
          btn.classList.toggle('active', btn.getAttribute('data-section') === section);
        });
        summaryCards.forEach(function (card) {
          card.classList.toggle('is-active', card.getAttribute('data-section') === section);
        });
        applyFilters();
      }

      tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () { setActiveSection(btn.getAttribute('data-section')); });
      });
      summaryCards.forEach(function (card) {
        card.addEventListener('click', function () { setActiveSection(card.getAttribute('data-section')); });
      });
      searchInput.addEventListener('input', applyFilters);
    })();
  </script>
  <script src="admin-shared-ui.js"></script>
</body>
</html>