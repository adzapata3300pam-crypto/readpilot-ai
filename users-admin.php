<?php
require_once __DIR__ . '/auth-guard.php';
require_admin();
$accountRows = db()->query('SELECT id, full_name, email, role, grade_level, section_name, status, last_login FROM users ORDER BY role DESC, full_name ASC')->fetchAll();
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot Admin — User Management</title>
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
.btn-solid.btn-danger-solid{background:var(--red);box-shadow:0 4px 10px rgba(209,73,91,0.3);}
.btn-solid.btn-danger-solid:hover{box-shadow:3px 3px 0 #a53344, 0 4px 10px rgba(209,73,91,0.3);}
.btn-outline{background:var(--card);border:1.5px solid var(--border);color:var(--ink);border-radius:11px;padding:10px 18px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;transition:transform .12s steps(2), box-shadow .12s steps(2), border-color .15s ease;}
.btn-outline:hover{transform:translate(-2px,-2px);border-color:var(--green-dark);box-shadow:2px 2px 0 var(--green-dark);}

/* ---- Pending access requests panel ---- */
.pending-row .s-info{min-width:220px;}
.pending-row .row-actions{flex-shrink:0;}
.pending-row .s-time{width:170px;}
.review-reason{font-size:13px;color:var(--ink);font-weight:600;line-height:1.6;background:var(--bg);border-radius:12px;padding:12px 14px;}
.review-id-preview{margin-top:18px;}
.review-id-frame{width:100%;height:min(55vh,520px);border:1px solid var(--border);border-radius:12px;background:var(--bg);}
.review-id-image{display:block;max-width:100%;max-height:55vh;margin:0 auto;border:1px solid var(--border);border-radius:12px;object-fit:contain;background:var(--bg);}
.review-id-message{padding:16px;border-radius:12px;background:var(--bg);color:var(--muted);font-size:13px;font-weight:700;line-height:1.5;}
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
        <a class="nav-item active" href="users-admin.php"><i class="bx bx-user-circle"></i><span class="label">User Management</span></a>
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
        <h1>User Management</h1>
        <div class="greet">Add, edit, and deactivate teacher and admin accounts.</div>
      </div>
      <div class="topbar-actions">
        <div class="search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          Search by name or email...
        </div>
        <button class="btn-new" id="addUserBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
          Add User
        </button>
        <div class="bell" id="notifBell" aria-label="Notifications" tabindex="0" data-notifications-managed="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
          <span class="badge" id="notifBadge" style="display:none;">0</span>
          <div class="bell-panel" id="notifPanel">
            <div class="bell-item" style="font-weight:800;color:var(--muted);cursor:default;">Access requests</div>
            <div id="notifItemsList"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ================= Pending Access Requests ================= -->
    <div class="panel" id="pendingRequestsPanel">
      <div class="panel-head">
        <div class="panel-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Pending Access Requests
          <span class="live-pill" id="pendingCountPill" style="display:none;background:var(--orange-light);color:var(--orange);">
            <span class="live-dot" style="background:var(--orange);"></span><span id="pendingCountText">0 waiting</span>
          </span>
        </div>
      </div>
      <div id="pendingRequestsList">
        <div class="empty-state" id="pendingEmptyState" style="display:none;">
          <i class='bx bx-check-shield'></i>
          <b>No pending requests</b>
          New teacher and admin sign-up requests will show up here for review.
        </div>
      </div>
    </div>

    <div class="panel" id="accessIdDocumentsPanel">
      <div class="panel-head">
        <div class="panel-title">
          <i class="bx bx-id-card"></i>
          Retained Access ID Documents
        </div>
      </div>
      <div id="accessIdDocumentsStatus" class="empty-state" role="status">Loading ID documents…</div>
      <div class="table-scroll" id="accessIdDocumentsTable" style="display:none;">
        <table class="data-table">
          <thead>
            <tr><th>Applicant</th><th>School</th><th>Request status</th><th>Submitted</th><th style="text-align:right;">Actions</th></tr>
          </thead>
          <tbody id="accessIdDocumentsList"></tbody>
        </table>
      </div>
    </div>

    <div id="userMgmtPage">
      <div class="controls">
        <div class="tabs">
          <div class="tab active" data-filter="all">All Users</div>
          <div class="tab" data-filter="teachers">Teachers</div>
          <div class="tab" data-filter="admins">Admins</div>
          <div class="tab" data-filter="deactivated">Deactivated</div>
        </div>
        <div class="controls-right">
          <span class="results-count" id="resultsCount">8 users</span>
        </div>
      </div>

      <div class="panel">
        <div class="table-scroll">
          <table class="data-table">
            <thead>
              <tr>
                <th>User</th>
                <th>Role</th>
                <th>Grade / Section</th>
                <th>Status</th>
                <th>Last Login</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($accountRows)): ?>
                <tr>
                  <td colspan="6">
                    <div class="empty-state" style="margin: 0; padding: 28px 18px;">
                      <i class='bx bx-user-x'></i>
                      <b>No users found</b>
                      There are no teacher or admin accounts in the database yet.
                    </div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($accountRows as $account):
                    $gradeText = $account['role'] === 'teacher' ? trim(($account['grade_level'] ?? '') . ' · ' . ($account['section_name'] ?? ''), ' ·') : '—';
                    $lastLogin = $account['last_login'] ? date('M j, Y · g:i A', strtotime($account['last_login'])) : 'Never';
                    $roleLabel = $account['role'] === 'admin' ? 'Admin' : 'Teacher';
                    $statusText = $account['status'] === 'active' ? 'Active' : 'Deactivated';
                    $statusClass = $account['status'] === 'active' ? 'active' : 'inactive';
                    $avatarColor = strtolower($account['role']) === 'admin' ? '#4c63d2' : '#6fbf5a';
                    $initials = strtoupper(substr($account['full_name'], 0, 1));
                    if (preg_match('/\s+(\S)/', $account['full_name'], $matches)) {
                        $initials .= strtoupper($matches[1]);
                    }
                ?>
                  <tr data-id="<?= (int) $account['id'] ?>" data-name="<?= htmlspecialchars($account['full_name'], ENT_QUOTES, 'UTF-8') ?>" data-email="<?= htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8') ?>" data-role="<?= htmlspecialchars($account['role'], ENT_QUOTES, 'UTF-8') ?>" data-grade="<?= htmlspecialchars($gradeText, ENT_QUOTES, 'UTF-8') ?>" data-status="<?= htmlspecialchars($account['status'], ENT_QUOTES, 'UTF-8') ?>" data-last-login="<?= htmlspecialchars($lastLogin, ENT_QUOTES, 'UTF-8') ?>">
                    <td>
                      <div class="cell-user">
                        <div class="u-avatar" style="background: <?= htmlspecialchars($avatarColor, ENT_QUOTES, 'UTF-8') ?>;"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                        <div>
                          <div class="u-name"><?= htmlspecialchars($account['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
                          <div class="u-email"><?= htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                      </div>
                    </td>
                    <td><span class="role-badge <?= htmlspecialchars($account['role'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= htmlspecialchars($gradeText === '—' ? '—' : $gradeText, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="status-pill <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"><span class="dot"></span><?= htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="cell-muted"><?= htmlspecialchars($lastLogin, ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                      <div class="row-actions">
                        <button class="icon-btn edit" title="Edit user" aria-label="Edit user"><i class="bx bx-edit-alt"></i></button>
                        <button class="icon-btn <?= $account['status'] === 'active' ? 'danger' : 'success' ?> toggle-status" title="<?= $account['status'] === 'active' ? 'Deactivate user' : 'Activate user' ?>" aria-label="<?= $account['status'] === 'active' ? 'Deactivate user' : 'Activate user' ?>"><i class="bx <?= $account['status'] === 'active' ? 'bx-block' : 'bx-check-circle' ?>"></i></button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- ================= Add / Edit user modal ================= -->
  <div class="overlay" id="userModalOverlay">
    <div class="modal">
      <button class="modal-close" id="userModalCloseBtn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      <div class="modal-head">
        <div class="modal-avatar" style="background:var(--green)"><i class='bx bx-user-plus'></i></div>
        <div>
          <h2 id="userModalTitle">Add user</h2>
          <div class="sub" id="userModalSub">Create a new teacher or admin account.</div>
        </div>
      </div>
      <form id="userForm">
        <div class="form-row">
          <label for="userName">Full name</label>
          <input type="text" id="userName" placeholder="e.g. Priya Nair" required>
        </div>
        <div class="form-row">
          <label for="userEmail">Email address</label>
          <input type="email" id="userEmail" placeholder="name@readpilot.app" required>
        </div>
        <div class="form-row">
          <label for="userPassword">Temporary password</label>
          <input type="password" id="userPassword" minlength="8" placeholder="At least 8 characters">
        </div>
        <div class="form-grid">
          <div class="form-row">
            <label for="userRole">Role</label>
            <select id="userRole">
              <option value="Teacher">Teacher</option>
              <option value="Admin">Admin</option>
            </select>
          </div>
          <div class="form-row" id="userGradeRow">
            <label for="userGrade">Grade assigned</label>
            <select id="userGrade">
              <option>Grade 1</option>
              <option>Grade 2</option>
              <option selected>Grade 3</option>
              <option>Grade 4</option>
              <option>Grade 5</option>
              <option>Grade 6</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="toggle-row">
            <div>
              <div class="t-label">Account active</div>
              <div class="t-sub">Deactivated accounts can't sign in</div>
            </div>
            <label class="switch">
              <input type="checkbox" id="userStatusToggle" checked>
              <span class="track"></span>
            </label>
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-secondary" id="userCancelBtn">Cancel</button>
          <button type="submit" class="btn-primary"><i class='bx bx-check'></i>Save user</button>
        </div>
        <button type="button" class="btn-danger-text" id="userDeleteBtn" style="display:none;">Deactivate this account</button>
      </form>
    </div>
  </div>

  <!-- ================= Review access request modal ================= -->
  <div class="overlay" id="reviewModalOverlay">
    <div class="modal">
      <button class="modal-close" id="reviewModalCloseBtn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      <div class="modal-head">
        <div class="modal-avatar" id="reviewAvatar" style="background:var(--purple);color:#fff;font-size:18px;">—</div>
        <div>
          <h2 id="reviewName">—</h2>
          <div class="sub" id="reviewEmail">—</div>
        </div>
      </div>

      <div class="modal-stats" style="grid-template-columns:1fr 1fr;">
        <div class="m-stat"><div class="v" id="reviewRole">—</div><div class="l">Requested role</div></div>
        <div class="m-stat"><div class="v" id="reviewSchool" style="font-size:13px;">—</div><div class="l">School</div></div>
      </div>

      <div class="modal-section-title"><i class='bx bx-message-detail'></i>Reason for access</div>
      <div class="review-reason" id="reviewReason">—</div>

      <div class="review-id-preview" id="reviewIdPreview" style="display:none;">
        <div class="modal-section-title"><i class='bx bx-id-card'></i>Uploaded ID</div>
        <div class="review-id-message" id="reviewIdMessage">Loading ID document…</div>
        <img class="review-id-image" id="reviewIdImage" alt="Applicant's uploaded ID document" style="display:none;">
        <iframe class="review-id-frame" id="reviewIdPdf" title="Applicant's uploaded ID document" style="display:none;"></iframe>
      </div>

      <div class="form-row" id="reviewGradeRow" style="margin-top:18px;">
        <label for="reviewGrade">Assign grade (for teacher accounts)</label>
        <select id="reviewGrade">
          <option>Grade 1</option>
          <option>Grade 2</option>
          <option selected>Grade 3</option>
          <option>Grade 4</option>
          <option>Grade 5</option>
          <option>Grade 6</option>
        </select>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-secondary" id="reviewDeclineBtn">Decline</button>
        <button type="button" class="btn-primary" id="reviewAcceptBtn"><i class='bx bx-check'></i>Approve access</button>
      </div>
    </div>
  </div>

  <!-- ================= Decline confirm ================= -->
  <div class="modal-overlay" id="declineConfirmOverlay">
    <div class="modal-box">
      <div class="m-title"><i class='bx bx-user-x'></i>Decline this request?</div>
      <div class="m-desc" id="declineConfirmText">This will remove the request. The requester will need to submit again if they still need access.</div>
      <div class="modal-actions">
        <button class="btn-outline" id="declineConfirmCancel">Cancel</button>
        <button class="btn-solid btn-danger-solid" id="declineConfirmBtn">Decline request</button>
      </div>
    </div>
  </div>

  <!-- ================= Deactivate / activate confirm ================= -->
  <div class="modal-overlay" id="statusConfirmOverlay">
    <div class="modal-box">
      <div class="m-title"><i class='bx bx-shield-quarter'></i>Change account access?</div>
      <div class="m-desc" id="statusConfirmText">This will change what this user can access.</div>
      <div class="modal-actions">
        <button class="btn-outline" id="statusConfirmCancel">Cancel</button>
        <button class="btn-solid" id="statusConfirmBtn">Confirm</button>
      </div>
    </div>
  </div>

  <!-- ================= Log out confirm ================= -->
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
    document.getElementById('userModalCloseBtn').addEventListener('click', function(){
      document.getElementById('userModalOverlay').classList.remove('open');
    });

    // =====================================================================
    // Pending access requests: loaded from the database-backed queue.
    // login.php -> auth.php writes to access_requests; this panel reads
    // them from admin-api.php?view=requests and approves/declines them.
    // =====================================================================
    (function accessRequests(){
      function esc(value){
        return String(value == null ? '' : value)
          .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
      }
      function initials(name){ return String(name || '').split(' ').filter(Boolean).map(function(p){return p[0];}).slice(0,2).join('').toUpperCase() || '?'; }
      var colors = ['#4c63d2', '#8b6bd1', '#dd9636', '#3f95ac', '#d1495b'];
      function colorFor(name){ var sum = 0; name = String(name || ''); for (var i=0;i<name.length;i++) sum += name.charCodeAt(i); return colors[sum % colors.length]; }
      function formatDate(ts){
        var d = new Date(String(ts || '').replace(' ', 'T'));
        if (isNaN(d.getTime())) return '—';
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var h = d.getHours(); var ampm = h >= 12 ? 'PM' : 'AM'; var h12 = h % 12 || 12;
        var min = d.getMinutes(); var minStr = min < 10 ? '0' + min : '' + min;
        return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear() + ' · ' + h12 + ':' + minStr + ' ' + ampm;
      }

      var toastEl = document.getElementById('toast');
      var toastMsgEl = document.getElementById('toastMsg');
      var toastTimer;
      function toast(msg){
        toastMsgEl.textContent = msg;
        toastEl.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function(){ toastEl.classList.remove('show'); }, 2600);
      }

      var requests = [];
      var listEl = document.getElementById('pendingRequestsList');
      var emptyEl = document.getElementById('pendingEmptyState');
      var countPill = document.getElementById('pendingCountPill');
      var countText = document.getElementById('pendingCountText');
      var badgeEl = document.getElementById('notifBadge');
      var notifItemsList = document.getElementById('notifItemsList');
      var resultsCountEl = document.getElementById('resultsCount');

      function findRequest(id){
        for (var i = 0; i < requests.length; i++) {
          if (String(requests[i].id) === String(id)) return requests[i];
        }
        return null;
      }

      function refreshRequests(){
        fetch('admin-api.php?view=requests', { credentials: 'same-origin' })
          .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
          .then(function (result) {
            if (!result.ok || !Array.isArray(result.data.requests)) throw new Error('Unable to load requests');
            requests = result.data.requests.map(function (request) {
              return {
                id: request.id,
                name: request.full_name,
                email: request.email,
                school: request.school,
                role: request.requested_role === 'admin' ? 'Admin' : 'Teacher',
                reason: request.reason,
                submittedAt: request.created_at,
                status: request.status,
                hasIdDocument: request.has_id_document === true || request.has_id_document === 1 || request.has_id_document === '1',
                idDocumentType: request.id_document_type
              };
            });
            render();
          })
          .catch(function (error) {
            if (window.console) console.error('Access requests failed to load:', error);
            requests = [];
            render();
          });
      }

      function render(){
        var pending = requests.filter(function (r) { return r.status === 'pending'; });

        listEl.innerHTML = '';
        if (pending.length === 0){
          emptyEl.style.display = 'block';
          listEl.appendChild(emptyEl);
          countPill.style.display = 'none';
        } else {
          emptyEl.style.display = 'none';
          countPill.style.display = 'inline-flex';
          countText.textContent = pending.length + ' waiting';
          pending.forEach(function(r){
            var row = document.createElement('div');
            row.className = 'session-row pending-row';
            row.dataset.reqId = r.id;
            row.innerHTML =
              '<div class="s-avatar" style="background:' + colorFor(r.name) + '">' + esc(initials(r.name)) + '</div>' +
              '<div class="s-info">' +
                '<div class="s-name">' + esc(r.name) + ' <span class="role-badge ' + (r.role === 'Admin' ? 'admin' : 'teacher') + '" style="margin-left:6px;">' + esc(r.role) + '</span></div>' +
                '<div class="s-book">' + esc(r.school) + ' · ' + esc(r.email) + '</div>' +
              '</div>' +
              '<div class="s-time"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>' + esc(formatDate(r.submittedAt)) + '</div>' +
              '<div class="row-actions">' +
                '<button class="icon-btn" title="View request" aria-label="View request" data-action="view"><i class="bx bx-show"></i></button>' +
                '<button class="icon-btn success" title="Accept" aria-label="Accept request" data-action="accept"><i class="bx bx-check"></i></button>' +
                '<button class="icon-btn danger" title="Decline" aria-label="Decline request" data-action="decline"><i class="bx bx-x"></i></button>' +
              '</div>';
            listEl.appendChild(row);
          });
        }

        if (pending.length > 0){
          badgeEl.style.display = 'flex';
          badgeEl.textContent = pending.length > 9 ? '9+' : pending.length;
        } else {
          badgeEl.style.display = 'none';
        }

        notifItemsList.innerHTML = '';
        if (pending.length === 0){
          notifItemsList.textContent = 'No new notifications';
        } else {
          pending.slice(0, 4).forEach(function(r){
            var item = document.createElement('div');
            item.className = 'bell-item';
            item.style.cursor = 'pointer';
            item.dataset.reqId = r.id;
            item.innerHTML = esc(r.name) + ' wants ' + esc(r.role) + ' access<div class="sub">' + esc(r.school) + '</div>';
            notifItemsList.appendChild(item);
          });
        }

        if (resultsCountEl){
          var totalUsers = document.querySelectorAll('#userMgmtPage tbody tr[data-id]').length;
          resultsCountEl.textContent = totalUsers + (totalUsers === 1 ? ' user' : ' users');
        }
      }

      // ---- Review modal ----
      var reviewOverlay = document.getElementById('reviewModalOverlay');
      var reviewName = document.getElementById('reviewName');
      var reviewEmail = document.getElementById('reviewEmail');
      var reviewRole = document.getElementById('reviewRole');
      var reviewSchool = document.getElementById('reviewSchool');
      var reviewReason = document.getElementById('reviewReason');
      var reviewAvatar = document.getElementById('reviewAvatar');
      var reviewGradeRow = document.getElementById('reviewGradeRow');
      var reviewGrade = document.getElementById('reviewGrade');
      var reviewIdPreview = document.getElementById('reviewIdPreview');
      var reviewIdMessage = document.getElementById('reviewIdMessage');
      var reviewIdImage = document.getElementById('reviewIdImage');
      var reviewIdPdf = document.getElementById('reviewIdPdf');
      var currentReviewId = null;

      function clearReviewIdPreview(){
        reviewIdImage.removeAttribute('src');
        reviewIdPdf.removeAttribute('src');
        reviewIdImage.style.display = 'none';
        reviewIdPdf.style.display = 'none';
        reviewIdPreview.style.display = 'none';
      }

      function openReview(id){
        var r = findRequest(id);
        if (!r) return;
        currentReviewId = id;
        reviewName.textContent = r.name;
        reviewEmail.textContent = r.email;
        reviewRole.textContent = r.role;
        reviewSchool.textContent = r.school;
        reviewReason.textContent = r.reason || 'No reason provided.';
        reviewAvatar.style.background = colorFor(r.name);
        reviewAvatar.textContent = initials(r.name);
        reviewGradeRow.style.display = r.role === 'Teacher' ? 'block' : 'none';
        clearReviewIdPreview();
        if (r.hasIdDocument) {
          reviewIdPreview.style.display = 'block';
          reviewIdMessage.textContent = 'Loading ID document…';
          var documentUrl = 'admin-api.php?view=id_document&request_id=' + encodeURIComponent(r.id);
          if (r.idDocumentType === 'jpg' || r.idDocumentType === 'jpeg' || r.idDocumentType === 'png') {
            reviewIdImage.onload = function(){
              reviewIdImage.style.display = 'block';
              reviewIdMessage.style.display = 'none';
            };
            reviewIdImage.onerror = function(){
              reviewIdMessage.textContent = 'The ID image could not be displayed. Check that the document is still available.';
              reviewIdMessage.style.display = 'block';
            };
            reviewIdImage.src = documentUrl;
          } else if (r.idDocumentType === 'pdf') {
            reviewIdPdf.onload = function(){ reviewIdMessage.style.display = 'none'; };
            reviewIdPdf.src = documentUrl;
            reviewIdPdf.style.display = 'block';
          } else {
            reviewIdMessage.textContent = 'This request has an unsupported ID document format.';
          }
        }
        reviewOverlay.classList.add('open');
      }
      function closeReview(){
        reviewOverlay.classList.remove('open');
        currentReviewId = null;
        clearReviewIdPreview();
      }
      document.getElementById('reviewModalCloseBtn').addEventListener('click', closeReview);
      reviewOverlay.addEventListener('click', function(e){ if (e.target === reviewOverlay) closeReview(); });

      // ---- Decline confirm ----
      var declineOverlay = document.getElementById('declineConfirmOverlay');
      var declineText = document.getElementById('declineConfirmText');
      var pendingDeclineId = null;
      function openDeclineConfirm(id){
        var r = findRequest(id);
        if (!r) return;
        pendingDeclineId = id;
        declineText.textContent = "This will remove " + r.name + "'s request. They will need to submit a new request if they still need access.";
        declineOverlay.classList.add('show');
      }
      function closeDeclineConfirm(){ declineOverlay.classList.remove('show'); pendingDeclineId = null; }
      document.getElementById('declineConfirmCancel').addEventListener('click', closeDeclineConfirm);
      declineOverlay.addEventListener('click', function(e){ if (e.target === declineOverlay) closeDeclineConfirm(); });
      document.getElementById('declineConfirmBtn').addEventListener('click', function(){
        if (!pendingDeclineId) return;
        fetch('admin-api.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ action: 'decline_request', request_id: pendingDeclineId })
        }).then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
          .then(function (result) {
            if (!result.ok) throw new Error(result.data.error || 'Unable to decline request');
            closeDeclineConfirm();
            closeReview();
            refreshRequests();
            if (window.refreshAccessIdDocuments) window.refreshAccessIdDocuments();
            toast(result.data.message || 'Request declined');
          })
          .catch(function (error) { toast(error.message || 'Unable to decline request'); });
      });

      // ---- Approve ----
      document.getElementById('reviewAcceptBtn').addEventListener('click', function(){
        if (!currentReviewId) return;
        var r = findRequest(currentReviewId);
        if (!r) return;
        var payload = {
          action: 'approve_request',
          request_id: currentReviewId,
          grade_level: r.role === 'Teacher' ? reviewGrade.value : ''
        };
        closeReview();
        fetch('admin-api.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams(payload)
        }).then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
          .then(function (result) {
            if (!result.ok) throw new Error(result.data.error || 'Unable to approve request');
            refreshRequests();
            if (window.refreshAccessIdDocuments) window.refreshAccessIdDocuments();
            toast(result.data.message || 'Access request approved');
          })
          .catch(function (error) { toast(error.message || 'Unable to approve request'); });
      });
      document.getElementById('reviewDeclineBtn').addEventListener('click', function(){
        if (currentReviewId) openDeclineConfirm(currentReviewId);
      });

      // ---- Row buttons: view / accept (opens review so a grade can be assigned) / decline ----
      listEl.addEventListener('click', function(e){
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var row = e.target.closest('.pending-row');
        if (!row) return;
        var id = row.dataset.reqId;
        var action = btn.dataset.action;
        if (action === 'view' || action === 'accept') openReview(id);
        else if (action === 'decline') openDeclineConfirm(id);
      });

      // ---- Bell dropdown ----
      var bellEl = document.getElementById('notifBell');
      var notifPanel = document.getElementById('notifPanel');
      bellEl.addEventListener('click', function(e){
        e.stopPropagation();
        notifPanel.classList.toggle('open');
      });
      bellEl.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' '){ e.preventDefault(); notifPanel.classList.toggle('open'); }
      });
      document.addEventListener('click', function(){ notifPanel.classList.remove('open'); });
      notifPanel.addEventListener('click', function(e){ e.stopPropagation(); });
      notifItemsList.addEventListener('click', function(e){
        var item = e.target.closest('.bell-item[data-req-id]');
        if (!item) return;
        notifPanel.classList.remove('open');
        openReview(item.dataset.reqId);
      });

      refreshRequests();
    })();
  </script>
  <script>
    (function accessIdDocuments(){
      var statusEl = document.getElementById('accessIdDocumentsStatus');
      var tableEl = document.getElementById('accessIdDocumentsTable');
      var listEl = document.getElementById('accessIdDocumentsList');
      var csrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

      function escapeHtml(value){
        return String(value == null ? '' : value)
          .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
      }

      function refresh(){
        statusEl.style.display = 'block';
        statusEl.textContent = 'Loading ID documents…';
        tableEl.style.display = 'none';
        fetch('admin-api.php?view=id_documents', { credentials: 'same-origin', cache: 'no-store' })
          .then(function(response){ return response.json().then(function(data){ return { ok: response.ok, data: data }; }); })
          .then(function(result){
            if (!result.ok || !Array.isArray(result.data.documents)) {
              throw new Error(result.data.error || 'Unable to load retained ID documents.');
            }
            listEl.innerHTML = '';
            if (result.data.documents.length === 0) {
              statusEl.textContent = 'No retained ID documents.';
              return;
            }
            result.data.documents.forEach(function(documentRecord){
              var id = parseInt(documentRecord.id, 10);
              if (!Number.isInteger(id) || id < 1) return;
              var row = document.createElement('tr');
              row.innerHTML =
                '<td><div class="u-name">' + escapeHtml(documentRecord.full_name) + '</div><div class="u-email">' + escapeHtml(documentRecord.email) + '</div></td>' +
                '<td>' + escapeHtml(documentRecord.school) + '</td>' +
                '<td><span class="status-pill">' + escapeHtml(documentRecord.status) + '</span></td>' +
                '<td class="cell-muted">' + escapeHtml(documentRecord.created_at) + '</td>' +
                '<td><div class="row-actions">' +
                  '<a class="icon-btn" href="admin-api.php?view=id_document&amp;request_id=' + encodeURIComponent(id) + '" title="Download ID document" aria-label="Download ID document"><i class="bx bx-download"></i></a>' +
                  '<button class="icon-btn danger" type="button" data-delete-access-id="' + id + '" title="Delete ID document" aria-label="Delete ID document"><i class="bx bx-trash"></i></button>' +
                '</div></td>';
              listEl.appendChild(row);
            });
            statusEl.style.display = 'none';
            tableEl.style.display = 'block';
          })
          .catch(function(error){
            statusEl.textContent = error.message || 'Unable to load retained ID documents.';
            if (window.console) console.error('Access ID documents failed to load:', error);
          });
      }

      window.refreshAccessIdDocuments = refresh;
      listEl.addEventListener('click', function(event){
        var button = event.target.closest('button[data-delete-access-id]');
        if (!button) return;
        var requestId = button.dataset.deleteAccessId;
        if (!window.confirm('Permanently delete this applicant ID document? This cannot be undone.')) return;
        button.disabled = true;
        fetch('admin-api.php', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            action: 'delete_access_id',
            request_id: requestId,
            csrf_token: csrfToken
          })
        })
          .then(function(response){ return response.json().then(function(data){ return { ok: response.ok, data: data }; }); })
          .then(function(result){
            if (!result.ok) throw new Error(result.data.error || 'Unable to delete the ID document.');
            refresh();
          })
          .catch(function(error){
            button.disabled = false;
            window.alert(error.message || 'Unable to delete the ID document.');
          });
      });

      refresh();
    })();
  </script>
  <script src="admin-shared-ui.js"></script>
</body>
</html>