<?php
require_once __DIR__ . '/auth-guard.php';
require_admin();

$pdo = db();
$students = $pdo->query(
    "SELECT s.id, s.name, s.book, s.wpm, s.accuracy, s.sessions_count, s.status,
            sec.id AS section_id, sec.name AS section_name,
            u.full_name AS teacher_name,
            COALESCE(NULLIF(u.grade_level, ''), 'Unassigned') AS grade_level
     FROM students s
     INNER JOIN sections sec ON sec.id = s.section_id AND sec.teacher_id = s.teacher_id
     INNER JOIN users u ON u.id = s.teacher_id AND u.role = 'teacher'
     ORDER BY u.grade_level, sec.name, u.full_name, s.name"
)->fetchAll();

// Build the section dropdown options
$sections = [];
foreach ($students as $student) {
    $sectionId = (int) $student['section_id'];
    if (!isset($sections[$sectionId])) {
        $sections[$sectionId] = [
            'label' => $student['grade_level'] . ' · ' . $student['section_name'] . ' — ' . $student['teacher_name'],
            'count' => 0,
        ];
    }
    $sections[$sectionId]['count']++;
}

$studentCount = count($students);
$statusLabels = [
    'new' => 'New',
    'ontrack' => 'On Track',
    'support' => 'Needs Support',
    'inactive' => 'Deactivated',
];
$avatarColors = ['#4c63d2', '#8b6bd1', '#dd9636', '#3f95ac', '#d1495b'];
?>
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

/* One simple filter row: search + section dropdown + count */
.records-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-bottom:18px;}
.records-toolbar .search{flex:1 1 260px;min-width:0;}
/* Custom dropdown, styled like the admin buttons */
.dropdown{position:relative;flex:0 1 320px;min-width:220px;}
.dropdown-btn{width:100%;display:flex;align-items:center;justify-content:space-between;gap:10px;background:var(--card);border:1.5px solid var(--border);color:var(--ink);border-radius:11px;padding:10px 14px;font-family:inherit;font-size:13.5px;font-weight:700;cursor:pointer;text-align:left;transition:transform .12s steps(2), box-shadow .12s steps(2), border-color .15s ease;}
.dropdown-btn:hover,.dropdown.open .dropdown-btn{transform:translate(-2px,-2px);border-color:var(--green-dark);box-shadow:2px 2px 0 var(--green-dark);}
.dropdown-btn:focus{outline:none;}
.dropdown-btn:focus-visible{border-color:var(--green-dark);box-shadow:0 0 0 3px rgba(76,99,210,0.2);}
.dropdown-label{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.dropdown-btn .bx{font-size:18px;color:var(--muted);flex-shrink:0;transition:transform .15s ease;}
.dropdown.open .dropdown-btn .bx{transform:rotate(180deg);color:var(--green-dark);}
.dropdown-menu{position:absolute;top:calc(100% + 8px);left:0;right:0;min-width:260px;background:var(--card);border:1.5px solid var(--border);border-radius:12px;box-shadow:var(--shadow);padding:6px;margin:0;list-style:none;z-index:50;display:none;max-height:300px;overflow-y:auto;}
.dropdown.open .dropdown-menu{display:block;}
.dropdown-item{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 11px;border-radius:8px;font-size:13px;font-weight:600;color:var(--ink);cursor:pointer;}
.dropdown-item:hover,.dropdown-item:focus{background:rgba(76,99,210,0.09);outline:none;}
.dropdown-item .d-count{font-size:12px;color:var(--muted);font-weight:700;flex-shrink:0;}
.dropdown-item.selected{color:var(--green-dark);font-weight:800;background:rgba(76,99,210,0.12);}
.records-count{font-size:12.5px;color:var(--muted);font-weight:700;margin-left:auto;white-space:nowrap;}
.cell-sub{display:block;font-size:11.5px;color:var(--muted);font-weight:600;margin-top:2px;}
</style>
</head>
<body>

  <aside class="sidebar">
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
        <a class="nav-item" href="teacher-activity-admin.php"><i class="bx bx-pulse"></i><span class="label">Teacher Activity</span></a>
        <a class="nav-item" href="audit-trail-admin.php"><i class="bx bx-history"></i><span class="label">Audit Log</span></a>
        <a class="nav-item" href="settings-admin.php"><i class="bx bx-cog"></i><span class="label">Settings</span></a>
      </nav>
    </div>
    <div class="teacher-card">
      <div class="teacher-row">
        <div class="teacher-row-info">
          <div class="avatar"><?php include __DIR__ . '/profile-avatar.php'; ?></div>
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
        <div class="greet">Live student rosters from teacher dashboards.</div>
      </div>
    </div>

    <div class="panel">
      <div class="records-toolbar">
        <div class="search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input id="studentSearchInput" type="text" placeholder="Search by name or book..." style="border:none;outline:none;background:transparent;font:inherit;color:inherit;width:100%;">
        </div>
        <div class="dropdown" id="sectionDropdown">
          <button type="button" class="dropdown-btn" id="sectionBtn" aria-haspopup="listbox" aria-expanded="false">
            <span class="dropdown-label" id="sectionLabel">All sections</span>
            <i class='bx bx-chevron-down'></i>
          </button>
          <ul class="dropdown-menu" id="sectionMenu" role="listbox" tabindex="-1">
            <li class="dropdown-item selected" role="option" tabindex="0" data-value="all" aria-selected="true"><span>All sections</span><span class="d-count"><?= $studentCount ?></span></li>
            <?php foreach ($sections as $id => $section): ?>
            <li class="dropdown-item" role="option" tabindex="0" data-value="<?= (int) $id ?>" aria-selected="false"><span><?= htmlspecialchars($section['label'], ENT_QUOTES, 'UTF-8') ?></span><span class="d-count"><?= (int) $section['count'] ?></span></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="records-count" id="recordsCount"></div>
      </div>

      <div id="rosterWrap" class="table-scroll">
        <table class="data-table">
          <thead>
            <tr>
              <th>Student</th>
              <th>Section</th>
              <th>Teacher</th>
              <th>Currently Reading</th>
              <th>Performance</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="rosterBody">
            <?php foreach ($students as $student):
              $nameParts = preg_split('/\s+/u', trim($student['name'])) ?: [];
              $initials = '';
              foreach (array_slice($nameParts, 0, 2) as $part) {
                  $initials .= function_exists('mb_substr') ? mb_substr($part, 0, 1) : substr($part, 0, 1);
              }
              $avatarColor = $avatarColors[abs(crc32($student['name'])) % count($avatarColors)];
              $status = $student['status'];
              $statusClass = $status === 'inactive' ? 'inactive' : (in_array($status, ['new', 'support'], true) ? 'pending' : 'active');
              $statusLabel = $statusLabels[$status] ?? ucfirst($status);
              $hasSessions = (int) $student['sessions_count'] > 0;
            ?>
            <tr data-section="<?= (int) $student['section_id'] ?>" data-book="<?= htmlspecialchars($student['book'], ENT_QUOTES, 'UTF-8') ?>">
              <td>
                <div class="cell-user">
                  <div class="u-avatar" style="background:<?= $avatarColor ?>"><?= htmlspecialchars(strtoupper($initials), ENT_QUOTES, 'UTF-8') ?></div>
                  <div class="u-name"><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
              </td>
              <td><?= htmlspecialchars($student['grade_level'] . ' · ' . $student['section_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($student['teacher_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($student['book'] !== '' ? $student['book'] : '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php if ($hasSessions): ?>
                  <?= (int) $student['wpm'] ?> WPM<span class="cell-sub"><?= (int) $student['accuracy'] ?>% accuracy</span>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td><span class="status-pill <?= $statusClass ?>"><span class="dot"></span><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div id="noResults" class="cell-muted" style="display:none;padding:24px 4px;text-align:center;"><?= $studentCount === 0 ? 'No students have been added to teacher rosters yet.' : 'No students match your search.' ?></div>
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

    // Search + section filter
    (function () {
      var rows = document.querySelectorAll('#rosterBody tr');
      var searchInput = document.getElementById('studentSearchInput');
      var dropdown = document.getElementById('sectionDropdown');
      var dropBtn = document.getElementById('sectionBtn');
      var dropLabel = document.getElementById('sectionLabel');
      var dropItems = dropdown.querySelectorAll('.dropdown-item');
      var noResults = document.getElementById('noResults');
      var countEl = document.getElementById('recordsCount');
      var currentSection = 'all';

      function setOpen(open) {
        dropdown.classList.toggle('open', open);
        dropBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      }

      function selectItem(item) {
        currentSection = item.getAttribute('data-value');
        dropItems.forEach(function (i) {
          var on = i === item;
          i.classList.toggle('selected', on);
          i.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        dropLabel.textContent = item.querySelector('span').textContent;
        setOpen(false);
        dropBtn.focus();
        applyFilters();
      }

      dropBtn.addEventListener('click', function () { setOpen(!dropdown.classList.contains('open')); });
      dropItems.forEach(function (item) {
        item.addEventListener('click', function () { selectItem(item); });
        item.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectItem(item); }
          if (e.key === 'ArrowDown' && item.nextElementSibling) { e.preventDefault(); item.nextElementSibling.focus(); }
          if (e.key === 'ArrowUp' && item.previousElementSibling) { e.preventDefault(); item.previousElementSibling.focus(); }
        });
      });
      document.addEventListener('click', function (e) { if (!dropdown.contains(e.target)) setOpen(false); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });

      function applyFilters() {
        var query = (searchInput.value || '').trim().toLowerCase();
        var section = currentSection;
        var visible = 0;

        rows.forEach(function (row) {
          var nameEl = row.querySelector('.u-name');
          var name = nameEl ? nameEl.textContent.toLowerCase() : '';
          var book = (row.getAttribute('data-book') || '').toLowerCase();
          var matchesSection = section === 'all' || row.getAttribute('data-section') === section;
          var matchesSearch = !query || name.indexOf(query) !== -1 || book.indexOf(query) !== -1;
          var show = matchesSection && matchesSearch;
          row.style.display = show ? '' : 'none';
          if (show) visible++;
        });

        noResults.style.display = visible === 0 ? 'block' : 'none';
        countEl.textContent = visible + (visible === 1 ? ' student' : ' students');
      }

      searchInput.addEventListener('input', applyFilters);
      applyFilters();
    })();
  </script>
  <script src="admin-shared-ui.js"></script>
</body>
</html>