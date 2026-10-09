<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Settings</title>
<!-- Theme preload MUST come first, before style.css and before anything
     else paints, so a saved dark-mode preference applies with no flash.
     This same line goes at the top of <head> on every other page too. -->
<script src="theme-init.js"></script>
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
  /* Dark-mode variables and shared-element overrides now live in style.css
     so every page gets them automatically. Only settings-page-specific
     rules (and their dark-mode tweaks) stay in this file. */

  /* ================= Settings page layout ================= */
  .settings-grid{display:grid;grid-template-columns:1fr 320px;gap:22px;align-items:start;}
  @media (max-width:1050px){.settings-grid{grid-template-columns:1fr;}}

  .settings-col{display:flex;flex-direction:column;gap:22px;}

  .settings-card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:24px 26px;}
  .settings-card-head{display:flex;align-items:center;gap:10px;margin-bottom:6px;}
  .settings-card-head .bx{font-size:19px;color:var(--green-dark);}
  .settings-title{font-family:'Poppins',sans-serif;font-size:16.5px;font-weight:700;color:var(--ink);}
  .settings-sub{font-size:12.5px;color:var(--muted);font-weight:600;margin-bottom:20px;}

  /* ---- Profile picture uploader ---- */
  .pfp-row{display:flex;align-items:center;gap:20px;flex-wrap:wrap;margin-bottom:22px;}
  .pfp-wrap{position:relative;width:88px;height:88px;flex-shrink:0;}
  .pfp-circle{width:88px;height:88px;border-radius:50%;background:var(--green-light);color:var(--green-dark);display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:800;overflow:hidden;border:3px solid var(--border);}
  .pfp-circle img{width:100%;height:100%;object-fit:cover;}
  .pfp-edit{position:absolute;bottom:-2px;right:-2px;width:30px;height:30px;border-radius:50%;background:var(--green);color:#fff;border:3px solid var(--card);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;transition:transform .12s steps(2);}
  .pfp-edit:hover{transform:translate(-1px,-1px) scale(1.05);}
  .pfp-actions{display:flex;flex-direction:column;gap:8px;}
  .pfp-actions-row{display:flex;gap:10px;flex-wrap:wrap;}
  .pfp-hint{font-size:11.5px;color:var(--muted);font-weight:600;}

  .field-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
  @media (max-width:560px){.field-grid{grid-template-columns:1fr;}}
  .field{margin-bottom:16px;}
  .field-label{display:block;font-size:12.5px;font-weight:700;color:var(--ink);margin-bottom:7px;}
  .field input, .field select, .field textarea{
    width:100%;border:1px solid var(--border);border-radius:12px;background:var(--card);
    padding:11px 13px;font-family:inherit;font-size:13.5px;color:var(--ink);outline:none;
  }
  .field input:focus, .field select:focus, .field textarea:focus{border-color:var(--green);}
  .field textarea{resize:vertical;min-height:80px;}
  .field-help{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:6px;}

  /* ---- Appearance: theme picker ---- */
  .theme-options{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:8px;}
  @media (max-width:480px){.theme-options{grid-template-columns:1fr;}}
  .theme-card{border:2px solid var(--border);border-radius:16px;padding:12px;cursor:pointer;transition:border-color .15s ease, transform .12s steps(2);}
  .theme-card:hover{transform:translate(-2px,-2px);}
  .theme-card.selected{border-color:var(--green);}
  .theme-preview{border-radius:10px;overflow:hidden;display:flex;height:78px;margin-bottom:10px;border:1px solid rgba(0,0,0,0.06);}
  .theme-preview .prev-side{width:26%;}
  .theme-preview .prev-main{flex-grow:1;display:flex;flex-direction:column;gap:5px;padding:8px;}
  .theme-preview .prev-bar{border-radius:4px;height:8px;}
  .theme-preview .prev-card{border-radius:5px;height:24px;}
  .theme-preview.light{background:#f6f8f2;}
  .theme-preview.light .prev-side{background:linear-gradient(180deg,#163828,#102c1f);}
  .theme-preview.light .prev-bar{background:#e2ece0;}
  .theme-preview.light .prev-card{background:#ffffff;box-shadow:0 1px 4px rgba(0,0,0,0.06);}
  .theme-preview.dark{background:#0e1712;}
  .theme-preview.dark .prev-side{background:linear-gradient(180deg,#0c1912,#060d09);}
  .theme-preview.dark .prev-bar{background:#23342a;}
  .theme-preview.dark .prev-card{background:#16221b;box-shadow:0 1px 4px rgba(0,0,0,0.3);}
  .theme-label{display:flex;align-items:center;justify-content:space-between;font-size:13px;font-weight:700;color:var(--ink);}
  .theme-label .bx{font-size:16px;color:var(--green-dark);}
  .theme-radio{width:16px;height:16px;border-radius:50%;border:2px solid var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center;}
  .theme-card.selected .theme-radio{border-color:var(--green);}
  .theme-card.selected .theme-radio::after{content:"";width:8px;height:8px;border-radius:50%;background:var(--green);}

  .accent-note{display:flex;align-items:center;gap:8px;background:var(--bg);border-radius:10px;padding:10px 13px;font-size:12px;color:var(--muted);font-weight:600;margin-top:14px;}
  .accent-note .bx{color:var(--green-dark);font-size:15px;flex-shrink:0;}

  /* ---- Toggle switches ---- */
  .toggle-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px 0;border-bottom:1px solid var(--border);}
  .toggle-row:last-child{border-bottom:none;}
  .toggle-text .t-name{font-size:13.5px;font-weight:700;color:var(--ink);}
  .toggle-text .t-desc{font-size:12px;color:var(--muted);font-weight:600;margin-top:2px;}
  .switch{position:relative;width:44px;height:25px;flex-shrink:0;}
  .switch input{opacity:0;width:0;height:0;}
  .slider{position:absolute;inset:0;background:var(--border);border-radius:20px;cursor:pointer;transition:background .2s ease;}
  .slider::before{content:"";position:absolute;width:19px;height:19px;left:3px;top:3px;background:#fff;border-radius:50%;transition:transform .2s ease;box-shadow:0 1px 3px rgba(0,0,0,0.25);}
  .switch input:checked + .slider{background:var(--green);}
  .switch input:checked + .slider::before{transform:translateX(19px);}

  /* ---- Sidebar summary card (right column) ---- */
  .mini-card{display:flex;flex-direction:column;gap:12px;}
  .mini-stat{display:flex;align-items:center;gap:12px;}
  .mini-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .mini-icon .bx{font-size:18px;}
  .mini-name{font-size:13px;font-weight:700;color:var(--ink);}
  .mini-sub{font-size:11.5px;color:var(--muted);font-weight:600;}

  /* ---- Danger zone ---- */
  .danger-row{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:14px 0;}
  .danger-row .t-name{font-size:13.5px;font-weight:700;color:var(--ink);}
  .danger-row .t-desc{font-size:12px;color:var(--muted);font-weight:600;margin-top:2px;}

  /* ---- Buttons (reused pattern) ---- */
  .btn-solid{background:var(--green);color:#fff;border:none;border-radius:11px;padding:11px 20px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;box-shadow:0 4px 10px rgba(111,191,90,0.3);transition:transform .12s steps(2), box-shadow .12s steps(2);}
  .btn-solid:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark), 0 4px 10px rgba(111,191,90,0.3);}
  .btn-outline{background:var(--card);border:1.5px solid var(--border);color:var(--ink);border-radius:11px;padding:10px 18px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;transition:transform .12s steps(2), box-shadow .12s steps(2), border-color .15s ease;}
  .btn-outline:hover{transform:translate(-2px,-2px);border-color:var(--green-dark);box-shadow:2px 2px 0 var(--green-dark);}
  .btn-outline.danger{color:var(--red);border-color:var(--red-light);}
  .btn-outline.danger:hover{border-color:var(--red);box-shadow:2px 2px 0 var(--red);}

  .save-bar{position:sticky;bottom:0;display:flex;justify-content:flex-end;gap:10px;background:linear-gradient(180deg, transparent, var(--bg) 40%);padding:18px 0 4px;margin-top:4px;}

  /* .toast base styles + dark-mode tweak now live in style.css */

  /* ---- Log out row ---- */
  .logout-row{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:14px 0 0;border-top:1px solid var(--border);margin-top:6px;}
  .logout-row .t-name{font-size:13.5px;font-weight:700;color:var(--ink);}
  .logout-row .t-desc{font-size:12px;color:var(--muted);font-weight:600;margin-top:2px;}

  /* ---- Confirm modal (used for logout confirmation) ---- */
  .modal-overlay{position:fixed;inset:0;background:rgba(10,20,14,0.45);display:none;align-items:center;justify-content:center;z-index:999;padding:20px;}
  .modal-overlay.show{display:flex;}
  .modal-box{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:26px 26px 22px;max-width:360px;width:100%;}
  .modal-box .m-title{font-family:'Poppins',sans-serif;font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px;display:flex;align-items:center;gap:9px;}
  .modal-box .m-title .bx{font-size:19px;color:var(--green-dark);}
  .modal-box .m-desc{font-size:12.5px;color:var(--muted);font-weight:600;line-height:1.5;margin-bottom:20px;}
  .modal-actions{display:flex;justify-content:flex-end;gap:10px;}

  /* ---- Sidebar teacher-card logout button ---- */
  .teacher-row{display:flex;align-items:center;justify-content:space-between;gap:10px;}
  .teacher-row-info{display:flex;align-items:center;gap:10px;min-width:0;}
  .teacher-logout-btn{
    flex-shrink:0;width:30px;height:30px;border-radius:9px;
    background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.14);
    color:#e7f3df;display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:15px;transition:background .15s ease, transform .12s steps(2);
  }
  .teacher-logout-btn.danger{background:var(--red-light);border-color:var(--red);color:var(--red);}
  .teacher-logout-btn.danger:hover{background:var(--red-light);border-color:var(--red);color:var(--red);}
  .teacher-logout-btn:hover{background:rgba(255,255,255,0.16);transform:translate(-1px,-1px);}
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
        <a class="nav-item" href="index.php"><i class="bx bxs-dashboard"></i><span class="label">Dashboard</span></a>
        <a class="nav-item" href="students.php"><i class="bx bx-group"></i><span class="label">Sections</span></a>
        <a class="nav-item" href="sessions.php"><i class="bx bx-calendar"></i><span class="label">Sessions</span></a>
        <a class="nav-item" href="reports.php"><i class="bx bx-file"></i><span class="label">Reports</span></a>
        <a class="nav-item" href="struggle-map.php"><i class="bx bx-target-lock"></i><span class="label">Struggle Map</span></a>
        <a class="nav-item" href="recommendations.php"><i class="bx bx-bulb"></i><span class="label">Recommendations</span></a>
        <a class="nav-item" href="resources.php"><i class="bx bx-book-open"></i><span class="label">Resources</span></a>
        <a class="nav-item" href="recordings.php"><i class="bx bx-cloud-upload"></i><span class="label">Cloud Recordings</span></a>
        <a class="nav-item active" href="settings.php"><i class="bx bx-cog"></i><span class="label">Settings</span></a>
      </nav>
    </div>

    <div class="teacher-card">
      <div class="teacher-row">
        <div class="teacher-row-info">
          <div class="avatar" id="sidebarAvatar"><?php include __DIR__ . '/profile-avatar.php'; ?></div>
          <div>
            <div class="teacher-name" id="sidebarName" data-account-name="<?= htmlspecialchars(current_user()['full_name'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(current_user()['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="teacher-role">Grade 3 Teacher</div>
          </div>
        </div>
        <button class="teacher-logout-btn danger" id="sidebarLogoutBtn" title="Log out"><i class='bx bx-log-out'></i></button>
      </div>
      <div class="quote">"Every page a child reads today is a step toward a brighter tomorrow." <span class="heart">♥</span></div>
    </div>
  </aside>

  <!-- ================= MAIN ================= -->
  <main class="main">
    <div class="topbar">
      <div class="title-block">
        <h1>Settings</h1>
        <div class="greet">⚙️ Manage your profile, appearance, and account</div>
      </div>
      <div class="topbar-actions">
        <div class="date-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Aug 21, 2026
        </div>
      </div>
    </div>

    <div class="settings-grid">

      <!-- ===== LEFT COLUMN ===== -->
      <div class="settings-col">

        <!-- Profile -->
        <div class="settings-card">
          <div class="settings-card-head"><i class='bx bx-user-circle'></i><span class="settings-title">Profile</span></div>
          <div class="settings-sub">This is how you'll appear across ReadPilot</div>

          <div class="pfp-row">
            <div class="pfp-wrap">
              <div class="pfp-circle" id="pfpCircle"><?php include __DIR__ . '/profile-avatar.php'; ?></div>
              <div class="pfp-edit" id="pfpEditBtn" title="Change photo"><i class='bx bx-camera'></i></div>
              <input type="file" id="pfpInput" accept="image/*" style="display:none;">
            </div>
            <div class="pfp-actions">
              <div class="pfp-actions-row">
                <button class="btn-outline" id="pfpUploadBtn"><i class='bx bx-upload'></i>Upload photo</button>
                <button class="btn-outline danger" id="pfpRemoveBtn"><i class='bx bx-trash'></i>Remove</button>
              </div>
              <div class="pfp-hint">JPG or PNG, square crop with a centered 1.2× zoom. Max 5MB.</div>
            </div>
          </div>

          <div class="field-grid">
            <div class="field">
              <label class="field-label">Full name</label>
              <input type="text" id="inputName" value="<?= htmlspecialchars(current_user()['full_name'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="field">
              <label class="field-label">Role</label>
              <input type="text" id="inputRole" value="<?= htmlspecialchars(ucfirst(current_user()['role']), ENT_QUOTES, 'UTF-8') ?>" readonly>
            </div>
            <div class="field">
              <label class="field-label">Email address</label>
              <input type="email" id="inputEmail" value="<?= htmlspecialchars(current_user()['email'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="field">
              <label class="field-label">School / Organization</label>
              <input type="text" id="inputSchool" value="<?= htmlspecialchars(current_user()['school'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
          <div class="field">
            <label class="field-label">Bio / Teaching note</label>
            <textarea id="inputBio"><?= htmlspecialchars(current_user()['bio'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>

        <!-- Appearance -->
        <div class="settings-card">
          <div class="settings-card-head"><i class='bx bx-palette'></i><span class="settings-title">Appearance</span></div>
          <div class="settings-sub">Choose how ReadPilot looks on your screen</div>

          <div class="theme-options" id="themeOptions">
            <div class="theme-card" data-theme-choice="light">
              <div class="theme-preview light">
                <div class="prev-side"></div>
                <div class="prev-main">
                  <div class="prev-bar" style="width:60%;"></div>
                  <div class="prev-card"></div>
                  <div class="prev-card"></div>
                </div>
              </div>
              <div class="theme-label"><span><i class='bx bx-sun'></i> Light</span><span class="theme-radio"></span></div>
            </div>
            <div class="theme-card" data-theme-choice="dark">
              <div class="theme-preview dark">
                <div class="prev-side"></div>
                <div class="prev-main">
                  <div class="prev-bar" style="width:60%;"></div>
                  <div class="prev-card"></div>
                  <div class="prev-card"></div>
                </div>
              </div>
              <div class="theme-label"><span><i class='bx bx-moon'></i> Dark</span><span class="theme-radio"></span></div>
            </div>
          </div>
          <div class="accent-note"><i class='bx bx-info-circle'></i>Dark mode keeps ReadPilot's signature green, just deepened for low-light reading sessions and evening lesson planning.</div>
        </div>

        <!-- Notifications -->
        <div class="settings-card">
          <div class="settings-card-head"><i class='bx bx-bell'></i><span class="settings-title">Notifications</span></div>
          <div class="settings-sub">Decide what ReadPilot should notify you about</div>

          <div class="toggle-row">
            <div class="toggle-text"><div class="t-name">Session summaries</div><div class="t-desc">Get notified when a student finishes a reading session</div></div>
            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
          </div>
          <div class="toggle-row">
            <div class="toggle-text"><div class="t-name">New AI recommendations</div><div class="t-desc">Alert me when the AI has new suggestions to review</div></div>
            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
          </div>
          <div class="toggle-row">
            <div class="toggle-text"><div class="t-name">Weekly progress report</div><div class="t-desc">A summary email every Monday morning</div></div>
            <label class="switch"><input type="checkbox"><span class="slider"></span></label>
          </div>
          <div class="toggle-row">
            <div class="toggle-text"><div class="t-name">Students needing support</div><div class="t-desc">Notify me immediately if a student falls behind</div></div>
            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
          </div>
        </div>

        <!-- Account & Security -->
        <div class="settings-card">
          <div class="settings-card-head"><i class='bx bx-lock-alt'></i><span class="settings-title">Account &amp; Security</span></div>
          <div class="settings-sub">Manage your password and account access</div>

          <div class="field-grid">
            <div class="field">
              <label class="field-label">Current password</label>
              <input type="password" id="currentPassword" placeholder="Current password" autocomplete="current-password">
            </div>
            <div class="field"></div>
            <div class="field">
              <label class="field-label">New password</label>
              <input type="password" id="newPassword" placeholder="New password" autocomplete="new-password">
            </div>
            <div class="field">
              <label class="field-label">Confirm new password</label>
              <input type="password" id="confirmPassword" placeholder="Confirm new password" autocomplete="new-password">
            </div>
          </div>

          <!-- Log out -->
          <div class="logout-row">
            <div>
              <div class="t-name">Log out</div>
              <div class="t-desc">Sign out of ReadPilot on this device</div>
            </div>
            <button class="btn-outline danger" id="logoutBtn"><i class='bx bx-log-out'></i>Log out</button>
          </div>
        </div>

        <!-- Danger zone -->
        <div class="settings-card">
          <div class="settings-card-head"><i class='bx bx-error' style="color:var(--red);"></i><span class="settings-title">Danger Zone</span></div>
          <div class="settings-sub">These actions are permanent</div>
          <div class="danger-row">
            <div>
              <div class="t-name">Delete my account</div>
              <div class="t-desc">All student data, sessions, and recommendations will be permanently removed</div>
            </div>
            <button class="btn-outline danger" id="deleteAccountBtn"><i class='bx bx-trash'></i>Delete account</button>
          </div>
        </div>

        <div class="save-bar">
          <button class="btn-outline" id="discardBtn">Discard changes</button>
          <button class="btn-solid" id="saveSettingsBtn"><i class='bx bx-check'></i>Save changes</button>
        </div>
      </div>

      <!-- ===== RIGHT COLUMN ===== -->
      <div class="settings-col">
        <div class="settings-card mini-card">
          <div class="settings-card-head" style="margin-bottom:6px;"><i class='bx bx-shield-quarter'></i><span class="settings-title">Account status</span></div>
          <div class="mini-stat">
            <div class="mini-icon" style="background:var(--green-light);color:var(--green-dark);"><i class='bx bx-check-shield'></i></div>
            <div>
              <div class="mini-name">Email verified</div>
              <div class="mini-sub"><?= htmlspecialchars(current_user()['email'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
          <div class="mini-stat">
            <div class="mini-icon" style="background:var(--purple-light);color:var(--purple);"><i class='bx bx-calendar-check'></i></div>
            <div>
              <div class="mini-name">Member since</div>
              <div class="mini-sub"><?= htmlspecialchars(date('F Y', strtotime((string) (current_user()['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
          <div class="mini-stat">
            <div class="mini-icon" style="background:var(--orange-light);color:var(--orange);"><i class='bx bx-time-five'></i></div>
            <div>
              <div class="mini-name">Last login</div>
              <div class="mini-sub"><?= htmlspecialchars(current_user()['last_login'] ?? 'Not recorded', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
        </div>

        <div class="settings-card">
          <div class="settings-card-head"><i class='bx bx-help-circle'></i><span class="settings-title">Need help?</span></div>
          <div class="settings-sub" style="margin-bottom:14px;">Reach out if something doesn't look right</div>
          <button class="btn-outline" style="width:100%;justify-content:center;"><i class='bx bx-support'></i>Contact support</button>
        </div>
      </div>

    </div>
  </main>

  <!-- ================= TOAST ================= -->
  <div class="toast" id="toast"><i class='bx bx-check-circle'></i><span id="toastMsg">Saved</span></div>

  <!-- ================= LOG OUT CONFIRM MODAL ================= -->
  <div class="modal-overlay" id="logoutModal">
    <div class="modal-box">
      <div class="m-title"><i class='bx bx-log-out'></i>Log out of ReadPilot?</div>
      <div class="m-desc">You'll need to sign back in with your email and password to access your dashboard, students, and reports.</div>
      <div class="modal-actions">
        <button class="btn-outline" id="logoutCancelBtn">Cancel</button>
        <button class="btn-solid" id="logoutConfirmBtn"><i class='bx bx-check'></i>Log out</button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="saveConfirmModal">
    <div class="modal-box">
      <div class="m-title"><i class='bx bx-save'></i>Save changes?</div>
      <div class="m-desc">Your profile, appearance, and password changes will be saved to your ReadPilot account.</div>
      <div class="modal-actions">
        <button class="btn-outline" id="saveCancelBtn">Cancel</button>
        <button class="btn-solid" id="saveConfirmBtn"><i class='bx bx-check'></i>Confirm changes</button>
      </div>
    </div>
  </div>

  <script src="profile-photo.js"></script>
  <script>
    // ---- Hamburger: collapse sidebar ----
    // The collapsed/expanded state now lives on <html data-sidebar="collapsed">
    // (set here AND saved to localStorage) instead of a class on .sidebar,
    // so theme-init.js can restore it on every other page before paint —
    // that's what keeps the sidebar from popping back open when you navigate.
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

    // ---- Toast helper ----
    var toast = document.getElementById('toast');
    var toastMsg = document.getElementById('toastMsg');
    var toastTimer;
    function showToast(msg){
      toastMsg.textContent = msg;
      toast.classList.add('show');
      clearTimeout(toastTimer);
      toastTimer = setTimeout(function(){ toast.classList.remove('show'); }, 2400);
    }

    // ================= Theme (light/dark) =================
    // Keep the draft choice separate from the saved theme. The current page
    // stays unchanged until the user explicitly saves the settings.
    var themeCards = document.querySelectorAll('.theme-card');
    var savedTheme = localStorage.getItem('readpilot-theme') === 'dark' ? 'dark' : 'light';
    var pendingTheme = savedTheme;

    function updateThemeSelection(mode){
      themeCards.forEach(function(card){
        card.classList.toggle('selected', card.dataset.themeChoice === mode);
      });
    }
    function applyTheme(mode, persist){
      if (mode === 'dark'){
        document.documentElement.setAttribute('data-theme', 'dark');
      } else {
        document.documentElement.removeAttribute('data-theme');
      }
      if (persist) localStorage.setItem('readpilot-theme', mode);
    }
    updateThemeSelection(savedTheme);
    themeCards.forEach(function(card){
      card.addEventListener('click', function(){
        pendingTheme = this.dataset.themeChoice;
        updateThemeSelection(pendingTheme);
        applyTheme(pendingTheme, false);
        showToast('Theme previewed. Save changes to keep it');
      });
    });

    // ================= Profile picture upload =================
    var pfpCircle = document.getElementById('pfpCircle');
    var pfpInput = document.getElementById('pfpInput');
    var sidebarAvatar = document.getElementById('sidebarAvatar');

    function openFilePicker(){ pfpInput.click(); }
    document.getElementById('pfpEditBtn').addEventListener('click', openFilePicker);
    document.getElementById('pfpUploadBtn').addEventListener('click', openFilePicker);

    pfpInput.addEventListener('change', function(e){
      var file = e.target.files && e.target.files[0];
      if (!file) return;
      if (!file.type.startsWith('image/')){
        showToast('Please choose an image file');
        return;
      }
      if (file.size > 5 * 1024 * 1024){
        showToast('Image is larger than 5MB');
        return;
      }
      window.createProfilePhotoCrop(file).then(function(croppedFile){
        var formData = new FormData();
        formData.append('action', 'upload_profile_photo');
        formData.append('profile_photo', croppedFile);
        return fetch('account-api.php', {method:'POST', body:formData})
          .then(response=>response.json().then(data=>({ok:response.ok,data:data})))
          .then(function(result){
            if(!result.ok) throw new Error(result.data.error || 'Unable to save profile photo');
            var photo = '<img src="' + result.data.profile_image + '" alt="Profile photo">';
            pfpCircle.innerHTML = photo;
            sidebarAvatar.innerHTML = photo;
            showToast('Profile photo saved');
          });
      }).catch(function(error){ showToast(error.message); })
        .finally(function(){ pfpInput.value = ''; });
    });

    document.getElementById('pfpRemoveBtn').addEventListener('click', function(){
      fetch('account-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'remove_profile_photo'})})
        .then(response=>response.json().then(data=>({ok:response.ok,data:data})))
        .then(result=>{ if(!result.ok) throw new Error(result.data.error || 'Unable to remove profile photo'); pfpCircle.innerHTML='👩‍🏫'; sidebarAvatar.innerHTML='👩‍🏫'; pfpInput.value=''; showToast('Profile photo removed'); })
        .catch(error=>showToast(error.message));
    });

    // ================= Name sync (profile field -> sidebar) =================
    document.getElementById('inputName').addEventListener('input', function(){
      var sidebarName = document.getElementById('sidebarName');
      sidebarName.textContent = this.value.trim() || sidebarName.dataset.accountName;
    });

    // ================= Save / discard =================
    function saveSettings(){
      // Persist the appearance choice immediately and unconditionally.
      // This used to happen only after both the profile-update and
      // change-password requests to account-api.php resolved successfully,
      // which meant a failed/slow API call silently left the theme choice
      // out of localStorage entirely — so every other page (which reads
      // localStorage before paint via theme-init.js) kept loading in light
      // mode no matter how many times "Save changes" was clicked. Theme is
      // a client-side/device preference, not account data, so it shouldn't
      // depend on the account API succeeding at all.
      applyTheme(pendingTheme, true);

      var current = document.getElementById('currentPassword').value, next = document.getElementById('newPassword').value, confirm = document.getElementById('confirmPassword').value;
      var sidebarName = document.getElementById('sidebarName');
      fetch('account-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'update_profile', full_name:document.getElementById('inputName').value, email:document.getElementById('inputEmail').value, school:document.getElementById('inputSchool').value, bio:document.getElementById('inputBio').value})})
        .then(response=>response.json().then(data=>({ok:response.ok,data:data})))
        .then(result=>{ if(!result.ok) throw new Error(result.data.error || 'Unable to save profile'); sidebarName.dataset.accountName = result.data.user.full_name; sidebarName.textContent = result.data.user.full_name; if (!current && !next && !confirm) return null; return fetch('account-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'change_password', current_password:current, new_password:next, confirm_password:confirm})}); })
        .then(response=>response ? response.json().then(data=>({ok:response.ok,data:data})) : {ok:true,data:{}})
        .then(result=>{ if(!result.ok) throw new Error(result.data.error || 'Unable to change password'); showToast('Settings saved'); })
        .catch(error=>showToast(error.message));
      }
      var saveConfirmModal = document.getElementById('saveConfirmModal');
      document.getElementById('saveSettingsBtn').addEventListener('click', function(){ saveConfirmModal.classList.add('show'); });
      document.getElementById('saveCancelBtn').addEventListener('click', function(){ saveConfirmModal.classList.remove('show'); });
      document.getElementById('saveConfirmBtn').addEventListener('click', function(){ saveConfirmModal.classList.remove('show'); saveSettings(); });
      saveConfirmModal.addEventListener('click', function(e){ if (e.target === saveConfirmModal) saveConfirmModal.classList.remove('show'); });
    document.getElementById('discardBtn').addEventListener('click', function(){
      pendingTheme = localStorage.getItem('readpilot-theme') === 'dark' ? 'dark' : 'light';
      applyTheme(pendingTheme, false);
      updateThemeSelection(pendingTheme);
      showToast('Changes discarded');
    });
    document.getElementById('deleteAccountBtn').addEventListener('click', function(){ showToast('Account deletion is disabled. Contact an administrator to deactivate this account.'); });

    // ================= Log out =================
    // Three entry points (sidebar avatar row, topbar quick action, and the
    // Account & Security row) all open the same confirm modal so a stray
    // click can't sign someone out.
    var logoutModal = document.getElementById('logoutModal');
    function openLogoutModal(){ logoutModal.classList.add('show'); }
    function closeLogoutModal(){ logoutModal.classList.remove('show'); }

    document.getElementById('logoutBtn').addEventListener('click', openLogoutModal);
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
      showToast('Logging out…');
      setTimeout(function(){
        window.location.href = 'logout.php';
      }, 700);
    });
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>