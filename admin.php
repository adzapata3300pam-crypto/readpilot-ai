<?php
require_once __DIR__ . '/auth-guard.php';
require_admin();

$currentUser = current_user();
$pdo = db();

$totalTeachers = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$totalAdmins = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$pendingApprovals = (int) $pdo->query("SELECT COUNT(*) FROM access_requests WHERE status = 'pending'")->fetchColumn();
$teacherSummary = $pdo->query("SELECT COUNT(*) AS total, COUNT(CASE WHEN status = 'active' THEN 1 END) AS active FROM users WHERE role = 'teacher'")->fetch();

$recentActivity = $pdo->query(
    "SELECT al.action, al.details, al.created_at, COALESCE(u.full_name, 'System') AS actor FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.created_at DESC LIMIT 4"
)->fetchAll();

$todayLabel = date('M j, Y');
$adminNotifications = $pendingApprovals > 0
    ? [['title' => $pendingApprovals . ' access request' . ($pendingApprovals === 1 ? '' : 's') . ' waiting for review', 'sub' => 'Review in User Management']]
    : [];
$adminNotificationsJson = htmlspecialchars(
    json_encode($adminNotifications, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP),
    ENT_QUOTES,
    'UTF-8'
);
$greet = '👋 Hello, ' . htmlspecialchars($currentUser['full_name'], ENT_QUOTES, 'UTF-8') . '!';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot Admin — Dashboard</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin-style.css">
<script>
(function () {
  try {
    var savedTheme = localStorage.getItem('readpilot-admin-theme');
    if (savedTheme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
  } catch (e) {}
  try {
    var savedSidebar = localStorage.getItem('readpilot-admin-sidebar');
    if (savedSidebar === 'collapsed') document.documentElement.setAttribute('data-sidebar', 'collapsed');
  } catch (e) {}
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
        <rect x="0" y="0" width="2"  height="1" fill="#c3cdf5"/>
        <rect x="0" y="1" width="4"  height="1" fill="#c3cdf5"/>
        <rect x="0" y="2" width="6"  height="1" fill="#c3cdf5"/>
        <rect x="0" y="3" width="9"  height="1" fill="#a6b3ef"/>
        <rect x="0" y="4" width="13" height="1" fill="#8497e9"/>
        <rect x="0" y="5" width="9"  height="1" fill="#5f74d6"/>
        <rect x="0" y="6" width="6"  height="1" fill="#5f74d6"/>
        <rect x="0" y="7" width="4"  height="1" fill="#5f74d6"/>
        <rect x="0" y="8" width="2"  height="1" fill="#5f74d6"/>
      </svg>
    </div>
    <div class="pixel-plane" style="--y:74%; --dur:17s; --delay:6s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2"  height="1" fill="#c3cdf5"/>
        <rect x="0" y="1" width="4"  height="1" fill="#c3cdf5"/>
        <rect x="0" y="2" width="6"  height="1" fill="#c3cdf5"/>
        <rect x="0" y="3" width="9"  height="1" fill="#a6b3ef"/>
        <rect x="0" y="4" width="13" height="1" fill="#8497e9"/>
        <rect x="0" y="5" width="9"  height="1" fill="#5f74d6"/>
        <rect x="0" y="6" width="6"  height="1" fill="#5f74d6"/>
        <rect x="0" y="7" width="4"  height="1" fill="#5f74d6"/>
        <rect x="0" y="8" width="2"  height="1" fill="#5f74d6"/>
      </svg>
    </div>

    <div>
      <div class="logo-row">
        <div class="logo-left">
          <div class="logo-icon"><i class='bx bxs-shield-alt-2'></i></div>
          <div class="logo-text">
            <span class="brand">ReadPilot</span>
            <span class="tagline">Admin Console</span>
          </div>
        </div>
        <button class="hamburger" id="sidebarToggle" aria-label="Toggle navigation"><span class="bar"></span></button>
      </div>

      <nav>
        <a class="nav-item active" href="admin.php"><i class="bx bxs-dashboard"></i><span class="label">Dashboard</span></a>
        <a class="nav-item" href="users-admin.php"><i class="bx bx-user-circle"></i><span class="label">User Management</span></a>
        <a class="nav-item" href="gradesec-admin.php"><i class="bx bx-layer"></i><span class="label">Student Records</span></a>
        <a class="nav-item" href="teacher-activity-admin.php"><i class="bx bx-pulse"></i><span class="label">Teacher Activity</span></a>
        <a class="nav-item" href="audit-trail-admin.php"><i class="bx bx-history"></i><span class="label">Audit Log</span></a>
        <a class="nav-item" href="settings-admin.php"><i class="bx bx-cog"></i><span class="label">Settings</span></a>
      </nav>
    </div>

    <div class="teacher-card">
      <div class="teacher-row">
        <div class="teacher-row-info">
          <div class="avatar"><?php include __DIR__ . '/profile-avatar.php'; ?></div>
          <div>
            <div class="teacher-name" id="sidebarName"><?= htmlspecialchars(current_user()['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="teacher-role">System Administrator</div>
          </div>
        </div>
        <button class="teacher-logout-btn" id="sidebarLogoutBtn" title="Log out" aria-label="Log out"><i class='bx bx-log-out'></i></button>
      </div>
      <div class="quote">"Good access control is what lets every teacher trust the system." <span class="heart">♥</span></div>
    </div>
  </aside>

  <main class="main">
    <div class="topbar">
      <div class="title-block">
        <h1>Admin Dashboard</h1>
        <div class="greet"><?= $greet ?></div>
      </div>
      <div class="topbar-actions">
        <div class="search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          Search users, grades, or logs...
        </div>
        <div class="date-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          <?= htmlspecialchars($todayLabel, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="bell" data-notifications="<?= $adminNotificationsJson ?>" data-notification-count="<?= $pendingApprovals ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
          <span class="badge"<?= $pendingApprovals > 0 ? '' : ' style="display:none;"' ?>><?= $pendingApprovals ?></span>
        </div>
      </div>
    </div>

    <div class="stats">
      <div class="stat-card">
        <div class="stat-icon green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2Z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7Z"/></svg>
        </div>
        <div>
          <div class="stat-label">Total Teachers</div>
          <div class="stat-value"><?= $totalTeachers ?></div>
        </div>
        <div class="stat-sub"><span class="kpi-trend up"><i class='bx bx-up-arrow-alt'></i><?= max(0, $teacherSummary['active'] - 1) ?></span>&nbsp;active this term</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon purple">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M17 11a4 4 0 1 0-3.2-6.4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/><path d="M15 15c3.5 0 6 2 6 6"/></svg>
        </div>
        <div>
          <div class="stat-label">Active Users</div>
          <div class="stat-value"><?= $totalTeachers + $totalAdmins ?></div>
        </div>
        <div class="stat-sub good"><?= $totalTeachers ?> teachers • <?= $totalAdmins ?> admins</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon orange">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5Z"/><path d="M6 12v5c0 1.5 2.5 3 6 3s6-1.5 6-3v-5"/></svg>
        </div>
        <div>
          <div class="stat-label">Total Students</div>
          <div class="stat-value"><?= $totalStudents ?></div>
        </div>
        <div class="stat-sub">Across active teacher rosters</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon red">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        </div>
        <div>
          <div class="stat-label">Pending Approvals</div>
          <div class="stat-value"><?= $pendingApprovals ?></div>
        </div>
        <div class="stat-sub warn"><?= $pendingApprovals > 0 ? 'Awaiting review' : 'All clear' ?></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div class="panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
          Recent Activity
          <span class="live-pill"><span class="live-dot"></span>Live</span>
        </div>
        <a class="view-all" href="teacher-activity-admin.php">View All</a>
      </div>

      <?php foreach ($recentActivity as $item):
        $label = strtolower($item['action']);
        $badgeClass = $label === 'login' ? 'style="background:var(--purple-light);color:var(--purple)"' : ($label === 'password_changed' ? 'style="background:var(--orange-light);color:var(--orange)"' : '');
        $badgeText = ucfirst(str_replace('_', ' ', $item['action']));
        $initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $item['actor'] ?? 'System'), 0, 2) ?: 'SY');
        $color = ['#4c63d2', '#8b6bd1', '#dd9636', '#3f95ac', '#d1495b'][abs(crc32($item['actor'] ?? 'System')) % 5];
      ?>
      <div class="session-row">
        <div class="s-avatar" style="background:<?= $color ?>"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="s-info">
          <div class="s-name"><?= htmlspecialchars($item['actor'], ENT_QUOTES, 'UTF-8') ?></div>
          <div class="s-book"><?= htmlspecialchars($item['details'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="s-time">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
          <?= htmlspecialchars(date('M j, Y • g:i A', strtotime((string) $item['created_at'])), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="s-badge" <?= $badgeClass ?>><?= htmlspecialchars($badgeText, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="s-arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg></div>
      </div>
      <?php endforeach; ?>

      <div class="session-row dashboard-extra-activity" style="display:none;">
        <div class="s-avatar" style="background:#8b6bd1">AC</div>
        <div class="s-info">
          <div class="s-name">Ana Cabrera</div>
          <div class="s-book">Added an admin account for Rafael Santos</div>
        </div>
        <div class="s-time">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
          Aug 17, 2026 • 5:15 PM
        </div>
        <div class="s-badge" style="background:var(--purple-light);color:var(--purple)">Created</div>
        <div class="s-arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg></div>
      </div>

      <div class="panel-footer">
        <button class="load-more">
          Load More
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        </button>
      </div>
    </div>

    <div class="tip">
      <div class="tip-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <div>
        <div class="tip-title">Admin tip of the day</div>
        <div class="tip-text">Deactivate accounts for staff who've left instead of deleting them — it preserves their history in the Audit Log while cutting off access immediately.</div>
      </div>
      <div class="tip-close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
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
      if (isCollapsed){
        document.documentElement.removeAttribute('data-sidebar');
        localStorage.setItem('readpilot-admin-sidebar', 'expanded');
      } else {
        document.documentElement.setAttribute('data-sidebar', 'collapsed');
        localStorage.setItem('readpilot-admin-sidebar', 'collapsed');
      }
    });
    var tipEl = document.querySelector('.tip');
    var tipCloseEl = document.querySelector('.tip-close');
    if (tipCloseEl) tipCloseEl.addEventListener('click', function(){ tipEl.style.display = 'none'; });
  </script>
  <script src="admin-shared-ui.js"></script>
</body>
</html>