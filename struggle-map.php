<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Struggle Map</title>
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
  /* ================= Struggle Map — page-specific styles ================= */
  /* Uses the shared color tokens already declared in style.css (--green, --purple,
     --orange, --red, --teal, --tan, etc.) so the page stays on-brand with the rest
     of the dashboard, and reuses the .overlay/.modal system from students.php. */

  .cloud-icon{
    width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;
    color:var(--ink);flex-shrink:0;font-size:20px;
  }
  .cloud-header{display:flex;align-items:flex-start;gap:12px;}
  .cloud-header-sub{font-size:12.5px;color:var(--muted);font-weight:600;margin-top:2px;}

  .cloud-filter-group{display:flex;gap:6px;flex-wrap:wrap;}
  .cloud-filter-btn{
    padding:8px 15px;border-radius:20px;font-size:12.5px;font-weight:700;
    background:var(--card);border:1.5px solid var(--border);color:var(--muted);
    cursor:pointer;font-family:inherit;white-space:nowrap;
    transition:background .15s ease, color .15s ease, border-color .15s ease, transform .12s steps(2);
  }
  .cloud-filter-btn:hover{transform:translate(-1px,-1px);}
  .cloud-filter-btn.active{background:var(--green);border-color:var(--green);color:#fff;}

  .cloud-controls{display:flex;align-items:center;justify-content:flex-end;gap:14px;flex-wrap:wrap;margin:14px 0;}
  .cloud-search{
    display:flex;align-items:center;gap:8px;background:var(--card);border:1px solid var(--border);
    border-radius:12px;padding:10px 16px;width:240px;box-shadow:var(--shadow);
  }
  .cloud-search svg{width:15px;height:15px;color:var(--muted);flex-shrink:0;}
  .cloud-search input{border:none;outline:none;background:transparent;font-family:inherit;font-size:13.5px;color:var(--ink);width:100%;}
  .cloud-search input::placeholder{color:var(--muted);}
  .cloud-toggle{display:flex;gap:6px;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:5px;box-shadow:var(--shadow);}
  .cloud-toggle button{
    padding:8px 14px;border-radius:9px;font-size:12.5px;font-weight:700;color:var(--muted);
    background:none;border:none;cursor:pointer;font-family:inherit;
    transition:background .15s ease, color .15s ease;
  }
  .cloud-toggle button.active{background:var(--green-light);color:var(--green-dark);}

  .word-cloud{
    position:relative;overflow:hidden;
    background:#eef2ea;
    border-radius:16px;
    min-height:280px;
  }
  html[data-theme="dark"] .word-cloud{background:#0f1c15;}
  .cloud-word{
    position:absolute;
    font-family:'Poppins',sans-serif;font-weight:700;line-height:1;cursor:pointer;
    white-space:nowrap;user-select:none;
    transition:transform .12s steps(2), filter .12s ease;
  }
  .cloud-word:hover{transform:translate(-2px,-3px) scale(1.04);filter:brightness(1.08);}
  .cloud-word.hidden{display:none;}

  .cloud-empty{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;text-align:center;color:var(--muted);font-weight:600;font-size:13.5px;padding:0 20px;}

  /* category colors */
  .cat-vowel{color:var(--green-dark);}
  .cat-silent{color:var(--purple);}
  .cat-multi{color:var(--orange);}
  .cat-blend{color:var(--red);}
  .cat-irregular{color:var(--teal, #4fa3b8);}

  .rank-list{margin-top:6px;}
  .rank-row{
    display:flex;align-items:center;gap:14px;padding:13px 4px;border-bottom:1px solid var(--border);
    cursor:pointer;transition:background .12s ease;
  }
  .rank-row:hover{background:var(--bg);}
  .rank-row:last-of-type{border-bottom:none;}
  .rank-num{width:22px;flex-shrink:0;font-family:'Poppins',sans-serif;font-weight:700;color:var(--muted);font-size:13px;}
  .rank-word{flex-grow:1;min-width:120px;font-weight:700;color:var(--ink);font-size:14.5px;}
  .rank-cat{
    font-size:10.5px;font-weight:800;padding:4px 10px;border-radius:20px;white-space:nowrap;
  }
  .rank-bar-wrap{flex-grow:1;max-width:220px;height:8px;background:var(--bg);border-radius:6px;overflow:hidden;}
  .rank-bar{height:100%;border-radius:6px;}
  .rank-count{width:70px;flex-shrink:0;text-align:right;font-weight:800;color:var(--ink);font-size:13.5px;}

  /* Modal specifics for word detail (reuses .overlay / .modal from students.php) */
  .word-modal-head{display:flex;align-items:center;gap:14px;margin-bottom:6px;padding-right:30px;}
  .word-modal-badge{
    width:54px;height:54px;border-radius:14px;display:flex;align-items:center;justify-content:center;
    font-family:'Poppins',sans-serif;font-weight:700;font-size:20px;flex-shrink:0;color:#fff;
  }
  .word-modal-title{font-family:'Poppins',sans-serif;font-size:21px;font-weight:700;color:var(--ink);}
  .word-modal-sub{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.3px;}
  .word-modal-syll{
    font-size:15px;font-weight:700;color:var(--green-dark);background:var(--green-light);
    border-radius:10px;padding:10px 14px;margin:14px 0;letter-spacing:.5px;
  }
  .word-modal-tip{font-size:13px;color:#3d5a48;font-weight:600;line-height:1.55;margin-bottom:16px;}
  html[data-theme="dark"] .word-modal-tip{color:#c3d6c9;}
  .student-chip-list{display:flex;flex-wrap:wrap;gap:8px;}
  .student-chip{
    display:flex;align-items:center;gap:7px;background:var(--bg);border-radius:20px;
    padding:6px 12px 6px 6px;font-size:12.5px;font-weight:700;color:var(--ink);
  }
  .student-chip .mini-avatar{
    width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;
    font-size:10px;font-weight:800;color:#fff;flex-shrink:0;
  }

  /* ================= Two-column layout + Students side panel ================= */
  .layout-grid{display:grid;grid-template-columns:1fr 330px;gap:24px;align-items:start;}
  .main-col{min-width:0;}
  .side-col{position:sticky;top:20px;display:flex;flex-direction:column;gap:20px;}

  .side-panel{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:22px;}
  .side-panel-head{display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;}
  .side-panel-icon{
    width:38px;height:38px;border-radius:11px;background:var(--red-light);color:var(--red);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
  }
  .side-panel-icon .bx{font-size:19px;}
  .side-panel-title{font-family:'Poppins',sans-serif;font-size:16px;font-weight:700;color:var(--ink);}
  .side-panel-sub{font-size:12px;color:var(--muted);font-weight:600;margin-top:1px;}

  .student-row{
    display:flex;align-items:center;gap:12px;padding:11px 10px;border-radius:14px;
    cursor:pointer;border:1.5px solid transparent;margin-bottom:4px;
    transition:background .12s ease, border-color .12s ease, transform .12s steps(2);
  }
  .student-row:hover{background:var(--bg);transform:translate(-1px,-1px);}
  .student-row.selected{background:var(--green-light);border-color:var(--green);}
  .sr-avatar{
    width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;
    font-family:'Poppins',sans-serif;font-weight:700;font-size:14.5px;flex-shrink:0;
  }
  .sr-info{flex-grow:1;min-width:0;}
  .sr-name{font-size:13.5px;font-weight:700;color:var(--ink);}
  .sr-meta{font-size:11.5px;color:var(--muted);font-weight:600;margin-top:1px;}
  .sr-badge{
    width:26px;height:26px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    font-size:11.5px;font-weight:800;
  }

  .struggles-panel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
  .struggles-panel-title{font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;color:var(--ink);}
  .struggles-close{
    width:26px;height:26px;border-radius:8px;background:var(--bg);border:none;color:var(--muted);
    display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;
  }
  .struggles-close:hover{background:var(--border);color:var(--ink);}
  .struggle-chip{
    display:inline-flex;align-items:center;gap:5px;border-radius:20px;padding:7px 12px;
    font-size:12.5px;font-weight:800;margin:0 8px 8px 0;
  }
  .struggle-chip .x{opacity:.75;font-weight:700;}
  .struggles-chip-wrap{display:flex;flex-wrap:wrap;}
  .struggles-section-label{font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin:16px 0 6px 0;}
  .no-session{font-size:12.5px;color:var(--muted);font-weight:600;}
  .side-empty{font-size:12.5px;color:var(--muted);font-weight:600;text-align:center;padding:10px 0;}

  @media (max-width:1300px){
    .layout-grid{grid-template-columns:1fr;}
    .side-col{position:static;}
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
          <div class="logo-icon">
            <i class='bx bxs-paper-plane'></i>
          </div>
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
        <a class="nav-item active" href="struggle-map.php"><i class="bx bx-target-lock"></i><span class="label">Struggle Map</span></a>
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
        <a class="teacher-logout-btn" href="logout.php" title="Log out" aria-label="Log out"><i class='bx bx-log-out'></i></a>
      </div>
      <div class="quote">"Every page a child reads today is a step toward a brighter tomorrow." <span class="heart">♥</span></div>
    </div>
  </aside>

  <!-- ================= MAIN ================= -->
  <main class="main">
    <div class="topbar">
      <div class="title-block">
        <h1>Struggle Map</h1>
        <div class="greet">Every word a student mispronounces during a session lands here. Bigger word, more trouble.</div>
      </div>
      <div class="topbar-actions">
        <div class="date-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Aug 21, 2026
        </div>
        <div class="bell">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
          <span class="badge">3</span>
        </div>
      </div>
    </div>

    <div class="layout-grid">
      <div class="main-col">
        <!-- ============ WORD CLOUD PANEL ============ -->
        <div class="panel">
          <div class="panel-head">
            <div class="cloud-header">
              <div class="cloud-icon"><i class='bx bx-cloud'></i></div>
              <div>
                <div class="panel-title" style="margin-bottom:1px;">Struggle Word Cloud</div>
                <div class="cloud-header-sub">Larger = more students struggled with it</div>
              </div>
            </div>
            <div class="cloud-filter-group" id="cloudFilterGroup"><!-- filled by JS --></div>
          </div>

          <div class="cloud-controls">
            <div class="cloud-search">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
              <input type="text" id="wordSearch" placeholder="Find a word...">
            </div>
            <div class="cloud-toggle">
              <button class="active" data-range="all">All time</button>
              <button data-range="week">This week</button>
              <button data-range="month">This month</button>
            </div>
          </div>

          <div class="word-cloud" id="wordCloud"><!-- filled by JS --></div>
        </div>

        <!-- ============ RANKED LIST PANEL ============ -->
        <div class="panel">
          <div class="panel-head">
            <div class="panel-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
              Most Flagged Words
            </div>
            <span class="view-all" id="rankCount">-- words</span>
          </div>
          <div class="rank-list" id="rankList"><!-- filled by JS --></div>
        </div>

        <div class="tip">
          <div class="tip-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
          </div>
          <div>
            <div class="tip-title">Tip of the day</div>
            <div class="tip-text">Tap any word in the cloud to see which students struggled with it and a quick syllable breakdown you can use in your next mini-lesson.</div>
          </div>
          <div class="tip-close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </div>
        </div>
      </div>

      <!-- ============ STUDENTS SIDE PANEL ============ -->
      <aside class="side-col">
        <div class="side-panel">
          <div class="side-panel-head">
            <div class="side-panel-icon"><i class="bx bx-group"></i></div>
            <div>
              <div class="side-panel-title">Students</div>
              <div class="side-panel-sub">Click to see their struggles</div>
            </div>
          </div>
          <div id="studentList"><!-- filled by JS --></div>
        </div>

        <div class="side-panel" id="strugglesPanel"><!-- filled by JS --></div>
      </aside>
    </div>
  </main>

  <!-- ============ WORD DETAIL MODAL ============ -->
  <div class="overlay" id="wordOverlay">
    <div class="modal">
      <button class="modal-close" id="wordModalClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
      <div class="word-modal-head">
        <div class="word-modal-badge" id="wmBadge">?</div>
        <div>
          <div class="word-modal-title" id="wmTitle">word</div>
          <div class="word-modal-sub" id="wmCategory">category</div>
        </div>
      </div>
      <div class="word-modal-syll" id="wmSyllables">syl · la · bles</div>
      <div class="word-modal-tip" id="wmTip">Coaching tip goes here.</div>
      <div class="modal-section-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M17 11a4 4 0 1 0-3.2-6.4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/><path d="M15 15c3.5 0 6 2 6 6"/></svg>
        Students who stumbled on this word
      </div>
      <div class="student-chip-list" id="wmStudents"><!-- filled by JS --></div>
    </div>
  </div>

  <script>
    // ---- Hamburger: collapse sidebar for full-screen navigation ----
    // Toggles the same data-sidebar="collapsed" attribute on <html> that the
    // shared stylesheet's collapsed rules key off of, and persists the choice
    // to localStorage so it stays collapsed/expanded across every page (the
    // inline <head> script above reads it back before paint on load).
    document.getElementById('sidebarToggle').addEventListener('click', function(){
      const html = document.documentElement;
      const isCollapsed = html.getAttribute('data-sidebar') === 'collapsed';

      if (isCollapsed) {
        html.removeAttribute('data-sidebar');
      } else {
        html.setAttribute('data-sidebar', 'collapsed');
      }

      try {
        localStorage.setItem('readpilot-sidebar', isCollapsed ? 'expanded' : 'collapsed');
      } catch (e) {
        /* localStorage unavailable (e.g. private browsing) — toggle still
           works for this page load, it just won't persist across pages */
      }
    });

    // ---- Tip banner dismiss ----
    document.querySelector('.tip-close').addEventListener('click', function(){
      document.querySelector('.tip').style.display = 'none';
    });

    /* =====================================================================
       DATA
       Each entry represents one word teachers/the app has flagged as hard
       to pronounce, aggregated from reading sessions. Every word below is
       pulled directly from the STORY_TEXT passages used in resources.php /
       reading.php, so a word only shows up here if a student could
       actually have encountered it in a real assigned story:
         - "though / island / knight / sword / gnome / wrapped / whispered"
           etc. come from "The Kind Knight," "The Lion and the Mouse," and
           "The Grumpy Garden Gnome"
         - "chrysalis / caterpillar / cycle" come from "How Butterflies Are
           Born"
         - "straw / crawled / aboard / journey / sparkled / twinkling" come
           from "The Three Little Pigs," "A Rainy Day Surprise," and
           "Journey to the Stars"
       Swap this array (or fetch it from your backend, ideally generated by
       scanning session transcripts against STORY_TEXT) to drive the cloud
       with real data — everything below renders dynamically from it.
    ===================================================================== */
    const STUDENT_COLORS = {
      "Carmen Reyes": "#6fbf5a",
      "Eva Mendoza": "#e7c58a",
      "Isabella Ramos": "#f2a13a",
      "Diego Torres": "#8b6bd1",
      "Mateo Cruz": "#4fa3b8",
      "Sofia Lopez": "#ea5d5d"
    };

    const CATEGORIES = {
      vowel:     { label: "Vowel Teams",       color: "#3f7d4a", light: "#e6f4e1", cls: "cat-vowel",     group: "phonics"  },
      silent:    { label: "Silent Letters",    color: "#8b6bd1", light: "#ece5fb", cls: "cat-silent",    group: "phonics"  },
      multi:     { label: "Multisyllabic",     color: "#f2a13a", light: "#fdecd6", cls: "cat-multi",     group: "multisyl" },
      blend:     { label: "Consonant Blends",  color: "#ea5d5d", light: "#fce3e3", cls: "cat-blend",     group: "blends"   },
      irregular: { label: "Irregular Spelling",color: "#4fa3b8", light: "#e1f1f5", cls: "cat-irregular", group: "sight"    }
    };

    const FILTER_GROUPS = [
      { key: "all",      label: "All" },
      { key: "phonics",  label: "Phonics" },
      { key: "blends",   label: "Blends" },
      { key: "sight",    label: "Sight" },
      { key: "multisyl", label: "Multi-syl." }
    ];

    let WORDS = [
      // ---- Silent Letters ----
      { word: "knight",         cat: "silent",    count: 8,  syll: "nite",              tip: "Silent 'k' and silent 'gh' — pair with 'know' and 'knee' to teach the kn- family. From 'The Kind Knight.'", students: ["Eva Mendoza","Isabella Ramos","Mateo Cruz"], recent: true },
      { word: "sword",          cat: "silent",    count: 6,  syll: "sord",              tip: "Silent 'w' — the word is said just like 'sord'. Related to 'answer', another silent-w case. From 'The Kind Knight.'", students: ["Carmen Reyes","Diego Torres"], recent: false },
      { word: "gnome",          cat: "silent",    count: 5,  syll: "nohm",              tip: "Silent 'g' — pair with 'gnat' and 'gnaw' to teach the gn- family. From 'The Grumpy Garden Gnome.'", students: ["Diego Torres","Sofia Lopez"], recent: true },
      { word: "wrapped",        cat: "silent",    count: 6,  syll: "rapt",              tip: "Silent 'w' — group with 'write' and 'wrist' to reinforce the wr- pattern. From 'The Grumpy Garden Gnome.'", students: ["Mateo Cruz"], recent: false },
      { word: "whispered",      cat: "silent",    count: 7,  syll: "whis · pered",      tip: "The 'wh' softens to almost just a 'w' sound — model it slowly before speeding up. From 'Journey to the Stars.'", students: ["Carmen Reyes","Eva Mendoza"], recent: true },

      // ---- Vowel Teams ----
      { word: "straw",          cat: "vowel",     count: 9,  syll: "straw",             tip: "'aw' makes one long sound — hold it out: 'strawww'. Compare to 'paw' and 'draw'. From 'The Three Little Pigs.'", students: ["Isabella Ramos","Eva Mendoza","Carmen Reyes"], recent: false },
      { word: "crawled",        cat: "vowel",     count: 6,  syll: "crawled",           tip: "Same 'aw' pattern as 'straw', with a blended 'cr' up front. Sound out the blend first. From 'A Rainy Day Surprise.'", students: ["Sofia Lopez"], recent: false },
      { word: "mushroom",       cat: "vowel",     count: 5,  syll: "mush · room",       tip: "The 'oo' in 'room' is a long, rounded sound — contrast it with the short 'oo' in 'book'. From 'The Grumpy Garden Gnome.'", students: ["Diego Torres","Mateo Cruz"], recent: true },
      { word: "aboard",         cat: "vowel",     count: 4,  syll: "a · board",         tip: "'oar' makes one long 'or' sound. Link it to 'board' and 'roar' to reinforce the pattern. From 'Journey to the Stars.'", students: ["Isabella Ramos"], recent: false },

      // ---- Multisyllabic ----
      { word: "beautiful",      cat: "multi",     count: 10, syll: "beau · ti · ful",   tip: "The 'eau' spelling is unusual for the 'yoo' sound — best learned as a sight pattern, then broken into 3 beats. From 'How Butterflies Are Born.'", students: ["Carmen Reyes","Eva Mendoza","Diego Torres","Sofia Lopez"], recent: true },
      { word: "caterpillar",    cat: "multi",     count: 9,  syll: "cat · er · pil · lar", tip: "Four syllables — clap each one before blending the whole word together. From 'How Butterflies Are Born.'", students: ["Eva Mendoza","Isabella Ramos","Mateo Cruz"], recent: true },
      { word: "definitely",     cat: "multi",     count: 8,  syll: "def · i · nite · ly", tip: "Kids often add an extra syllable ('defin-ATE-ly'). Model the natural 4-beat pronunciation. From 'The Kind Knight.'", students: ["Diego Torres","Sofia Lopez"], recent: true },
      { word: "difference",     cat: "multi",     count: 6,  syll: "dif · fer · ence",  tip: "The middle syllable often gets swallowed in speech. Practice all three beats slowly, then speed up. From 'How Butterflies Are Born.'", students: ["Carmen Reyes"], recent: false },
      { word: "disappointment", cat: "multi",     count: 5,  syll: "dis · ap · point · ment", tip: "A long word built from smaller parts — point out the 'dis-' prefix and '-ment' ending as chunks they already know. From 'A Rainy Day Surprise.'", students: ["Eva Mendoza","Mateo Cruz"], recent: false },
      { word: "extremely",      cat: "multi",     count: 5,  syll: "ex · treme · ly",   tip: "Watch the 'x' blend smoothly into the next syllable — 'ex-TREME-ly', not 'ek-streme-ly'. From 'How Butterflies Are Born.'", students: ["Isabella Ramos"], recent: false },
      { word: "patiently",      cat: "multi",     count: 4,  syll: "pa · tient · ly",   tip: "The 'ti' in the middle makes a 'sh' sound, same pattern as 'ocean'. Point out the pattern. From 'How Butterflies Are Born.'", students: ["Sofia Lopez"], recent: false },

      // ---- Consonant Blends ----
      { word: "scurried",       cat: "blend",     count: 7,  syll: "scur · ried",       tip: "The 'scu' blend moves fast — slow it down, then speed up to match the word's own hurried meaning. From 'The Lion and the Mouse.'", students: ["Carmen Reyes","Isabella Ramos"], recent: true },
      { word: "sparkled",       cat: "blend",     count: 6,  syll: "spar · kled",       tip: "Two blends in one word ('sp' and 'kl'). Break it into two chunks before joining them. From 'Journey to the Stars.'", students: ["Eva Mendoza"], recent: false },
      { word: "squeaked",       cat: "blend",     count: 8,  syll: "squeaked",          tip: "The 'squ' blend is three sounds squeezed together — practice 'skw' on its own first. From 'The Lion and the Mouse.'", students: ["Diego Torres","Mateo Cruz","Sofia Lopez"], recent: true },
      { word: "tangled",        cat: "blend",     count: 5,  syll: "tan · gled",        tip: "The 'gl' blend at the end can get dropped in fast speech. Hold onto both sounds: tan-GLD. From 'The Lion and the Mouse.'", students: ["Carmen Reyes"], recent: false },
      { word: "twinkling",      cat: "blend",     count: 6,  syll: "twin · kling",      tip: "Two blends back to back ('tw' and 'kl'). Say each part slowly, then blend the whole word. From 'Journey to the Stars.'", students: ["Isabella Ramos","Eva Mendoza"], recent: false },

      // ---- Irregular Spelling ----
      { word: "chrysalis",      cat: "irregular", count: 9,  syll: "kris · uh · lis",   tip: "The 'ch' says 'k' here, unlike most English words. Best taught as a memorized science term. From 'How Butterflies Are Born.'", students: ["Diego Torres","Carmen Reyes","Mateo Cruz"], recent: true },
      { word: "journey",        cat: "irregular", count: 7,  syll: "jur · nee",         tip: "The 'ou' makes an unexpected 'ur' sound. Compare it to 'journal', which shares the pattern. From 'Journey to the Stars.'", students: ["Eva Mendoza","Sofia Lopez"], recent: true },
      { word: "cycle",          cat: "irregular", count: 4,  syll: "sy · kul",          tip: "The 'c' says 's' before the 'y' — contrast with 'cat', where 'c' says 'k'. From 'How Butterflies Are Born.'", students: ["Isabella Ramos"], recent: false }
    ];

    // ---- Category filter pills (All / Phonics / Blends / Sight / Multi-syl.) ----
    const filterGroupEl = document.getElementById('cloudFilterGroup');
    let activeGroup = 'all';
    FILTER_GROUPS.forEach(g => {
      const btn = document.createElement('button');
      btn.className = 'cloud-filter-btn' + (g.key === 'all' ? ' active' : '');
      btn.textContent = g.label;
      btn.dataset.group = g.key;
      btn.addEventListener('click', () => {
        activeGroup = g.key;
        document.querySelectorAll('.cloud-filter-btn').forEach(b => b.classList.toggle('active', b.dataset.group === g.key));
        renderCloud();
        renderRankList();
      });
      filterGroupEl.appendChild(btn);
    });

    // ---- Sizing helper: returns an actual font-size in px based on flag count ----
    function sizePx(count){
      if (count >= 9) return 42;
      if (count >= 7) return 32;
      if (count >= 5) return 25;
      if (count >= 3) return 19;
      return 15;
    }

    // ---- Color helper: interpolates amber -> deep red based on how often a word was flagged ----
    const COLOR_LOW  = { r: 242, g: 161, b: 58  }; // amber, low struggle count
    const COLOR_HIGH = { r: 196, g: 58,  b: 43  }; // deep red, high struggle count
    function colorForCount(count, minC, maxC){
      const ratio = maxC === minC ? 1 : (count - minC) / (maxC - minC);
      const r = Math.round(COLOR_LOW.r + (COLOR_HIGH.r - COLOR_LOW.r) * ratio);
      const g = Math.round(COLOR_LOW.g + (COLOR_HIGH.g - COLOR_LOW.g) * ratio);
      const b = Math.round(COLOR_LOW.b + (COLOR_HIGH.b - COLOR_LOW.b) * ratio);
      return `rgb(${r},${g},${b})`;
    }

    let currentRange = 'all';
    let searchTerm = '';

    function visibleWords(){
      return WORDS.filter(w => {
        if (currentRange === 'week' && !w.recent) return false;
        if (activeGroup !== 'all' && CATEGORIES[w.cat].group !== activeGroup) return false;
        if (searchTerm && !w.word.toLowerCase().includes(searchTerm)) return false;
        return true;
      });
    }

    // ---- Render word cloud: measures real text size per word, then places each one
    //      on an outward spiral so bounding boxes never collide (with a safety gap). ----
    const cloudEl = document.getElementById('wordCloud');
    const measureCanvas = document.createElement('canvas');
    const measureCtx = measureCanvas.getContext('2d');
    const GAP = 16; // minimum px gap enforced between any two words
    const PAD = 22; // inner padding from the container edge

    function layoutCloud(list){
      const containerW = Math.max(cloudEl.clientWidth || cloudEl.parentElement.clientWidth, 320);
      const counts = list.map(w => w.count);
      const minC = Math.min(...counts), maxC = Math.max(...counts);

      // Measure every word at its target font size first.
      const items = [...list].sort((a,b) => b.count - a.count).map(w => {
        const fs = sizePx(w.count);
        measureCtx.font = `800 ${fs}px 'Poppins', sans-serif`;
        const width = measureCtx.measureText(w.word).width;
        const height = fs * 1.05;
        return { data: w, fs, w: width + GAP, h: height + GAP, color: colorForCount(w.count, minC, maxC) };
      });

      // Spiral placement in a generous virtual workspace, constrained horizontally
      // to the real container width so it stays responsive.
      const placed = [];
      const cx = containerW / 2;
      const cy = 260; // virtual vertical center, workspace grows as needed below
      const usableW = containerW - PAD * 2;

      items.forEach(item => {
        let angle = Math.random() * Math.PI * 2;
        let radius = 0;
        let x, y, tries = 0;
        const maxTries = 2000;
        while (tries < maxTries) {
          x = cx + radius * Math.cos(angle) - item.w / 2;
          y = cy + radius * Math.sin(angle) * 0.72 - item.h / 2; // flatten vertically for a wider, shorter cloud
          const withinX = x >= PAD && (x + item.w) <= (PAD + usableW);
          const rect = { x, y, w: item.w, h: item.h };
          const collides = withinX ? placed.some(p => rectsOverlap(rect, p)) : true;
          if (!collides) break;
          angle += 0.28;
          radius += 2.4;
          tries++;
        }
        placed.push({ x, y, w: item.w, h: item.h, item });
      });

      // Trim/normalize the bounding box so the cloud sits neatly inside the container.
      const minY = Math.min(...placed.map(p => p.y));
      const maxY = Math.max(...placed.map(p => p.y + p.h));
      const minX = Math.min(...placed.map(p => p.x));
      const maxX = Math.max(...placed.map(p => p.x + p.w));
      const offsetY = PAD - minY;
      const offsetX = (containerW - (maxX - minX)) / 2 - minX;
      const totalHeight = Math.max(280, (maxY - minY) + PAD * 2);

      return { placed, offsetX, offsetY, totalHeight };
    }

    function rectsOverlap(a, b){
      return !(a.x + a.w < b.x || b.x + b.w < a.x || a.y + a.h < b.y || b.y + b.h < a.y);
    }

    function renderCloud(){
      const list = visibleWords();
      cloudEl.innerHTML = '';
      if (!list.length){
        cloudEl.style.height = '280px';
        cloudEl.innerHTML = `<div class="cloud-empty">No words match right now — nice work, or try clearing your filters.</div>`;
        return;
      }
      const { placed, offsetX, offsetY, totalHeight } = layoutCloud(list);
      cloudEl.style.height = totalHeight + 'px';
      placed.forEach(p => {
        const w = p.item.data;
        const el = document.createElement('span');
        el.className = 'cloud-word';
        el.textContent = w.word;
        el.title = `Flagged ${w.count} time${w.count === 1 ? '' : 's'}`;
        el.style.left = (p.x + offsetX) + 'px';
        el.style.top = (p.y + offsetY) + 'px';
        el.style.fontSize = p.item.fs + 'px';
        el.style.color = p.item.color;
        el.addEventListener('click', () => openWordModal(w));
        cloudEl.appendChild(el);
      });
    }

    // ---- Render ranked list ----
    const rankListEl = document.getElementById('rankList');
    function renderRankList(){
      const list = [...visibleWords()].sort((a,b) => b.count - a.count);
      document.getElementById('rankCount').textContent = `${list.length} word${list.length === 1 ? '' : 's'}`;
      rankListEl.innerHTML = '';
      const max = list.length ? list[0].count : 1;
      list.forEach((w, i) => {
        const cat = CATEGORIES[w.cat];
        const row = document.createElement('div');
        row.className = 'rank-row';
        row.innerHTML = `
          <div class="rank-num">${i + 1}</div>
          <div class="rank-word">${w.word}</div>
          <span class="rank-cat" style="background:${cat.light};color:${cat.color}">${cat.label}</span>
          <div class="rank-bar-wrap"><div class="rank-bar" style="width:${(w.count/max*100).toFixed(0)}%;background:${cat.color}"></div></div>
          <div class="rank-count">${w.count}×</div>
        `;
        row.addEventListener('click', () => openWordModal(w));
        rankListEl.appendChild(row);
      });
    }

    // ---- Word detail modal ----
    const overlay = document.getElementById('wordOverlay');
    function openWordModal(w){
      const cat = CATEGORIES[w.cat];
      document.getElementById('wmBadge').textContent = w.word[0].toUpperCase();
      document.getElementById('wmBadge').style.background = cat.color;
      document.getElementById('wmTitle').textContent = w.word;
      document.getElementById('wmCategory').textContent = `${cat.label} · flagged ${w.count}×`;
      document.getElementById('wmSyllables').textContent = w.syll;
      document.getElementById('wmTip').textContent = w.tip;
      const chipsEl = document.getElementById('wmStudents');
      chipsEl.innerHTML = '';
      w.students.forEach(name => {
        const color = STUDENT_COLORS[name] || '#6fbf5a';
        const chip = document.createElement('div');
        chip.className = 'student-chip';
        chip.innerHTML = `<span class="mini-avatar" style="background:${color}">${name[0]}</span>${name}`;
        chipsEl.appendChild(chip);
      });
      overlay.classList.add('open');
    }
    document.getElementById('wordModalClose').addEventListener('click', () => overlay.classList.remove('open'));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('open'); });

    // ---- Controls ----
    document.querySelectorAll('.cloud-toggle button').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.cloud-toggle button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentRange = btn.dataset.range === 'month' ? 'all' : btn.dataset.range; // demo data only tags "recent"; month falls back to all
        renderCloud();
        renderRankList();
      });
    });
    document.getElementById('wordSearch').addEventListener('input', (e) => {
      searchTerm = e.target.value.trim().toLowerCase();
      renderCloud();
      renderRankList();
    });

    renderCloud();
    renderRankList();

    // Re-measure and re-place words once the real Poppins font is fully loaded
    // (initial metrics may use a fallback font for a moment), and on resize.
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(() => renderCloud());
    }
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(renderCloud, 150);
    });

    /* =====================================================================
       STUDENTS SIDE PANEL — derived from the same WORDS array, so it always
       stays in sync with the cloud and ranked list above.
    ===================================================================== */
    const AVATAR_PALETTE = [
      { bg: "#fce3e3", text: "#c94848" }, // red
      { bg: "#fdecd6", text: "#c47c1f" }, // orange
      { bg: "#faf0dd", text: "#a8752f" }, // tan
      { bg: "#ece5fb", text: "#7a5cc0" }, // purple
      { bg: "#e1f1f5", text: "#357c8f" }, // teal
      { bg: "#e6f4e1", text: "#3f7d4a" }  // green
    ];

    function buildStudentSummary(){
      const map = {};
      WORDS.forEach(w => {
        w.students.forEach(name => {
          if (!map[name]) map[name] = {};
          map[name][w.word] = (map[name][w.word] || 0) + 1;
        });
      });
      return Object.keys(map).map((name, i) => {
        const words = Object.entries(map[name]).map(([word, count]) => {
          const src = WORDS.find(w => w.word === word);
          return { word, count, cat: src ? src.cat : 'irregular' };
        }).sort((a,b) => b.count - a.count);
        const total = words.reduce((s,w) => s + w.count, 0);
        return {
          name,
          words,
          unique: words.length,
          total,
          palette: AVATAR_PALETTE[i % AVATAR_PALETTE.length]
        };
      }).sort((a,b) => b.total - a.total);
    }

    let STUDENT_SUMMARY = buildStudentSummary();
    let selectedStudent = STUDENT_SUMMARY.length ? STUDENT_SUMMARY[0].name : null;

    fetch('student-api.php?view=struggle').then(response=>response.json()).then(result=>{
      WORDS = (Array.isArray(result.words) ? result.words : []).map(item=>({word:item.word,count:Number(item.count),students:item.students,recent:item.recent,cat:'irregular',syll:item.word,tip:'Review this word with the student during the next guided reading session.'}));
      STUDENT_SUMMARY = buildStudentSummary();
      selectedStudent = STUDENT_SUMMARY.length ? STUDENT_SUMMARY[0].name : null;
      renderCloud();
      renderRankList();
      renderStudentList();
      renderStrugglesPanel();
    }).catch(()=>{});

    function renderStudentList(){
      const listEl = document.getElementById('studentList');
      listEl.innerHTML = '';
      if (!STUDENT_SUMMARY.length){
        listEl.innerHTML = `<div class="side-empty">No struggle data yet.</div>`;
        return;
      }
      STUDENT_SUMMARY.forEach(s => {
        const row = document.createElement('div');
        row.className = 'student-row' + (s.name === selectedStudent ? ' selected' : '');
        row.innerHTML = `
          <div class="sr-avatar" style="background:${s.palette.bg};color:${s.palette.text}">${s.name[0]}</div>
          <div class="sr-info">
            <div class="sr-name">${s.name}</div>
            <div class="sr-meta">${s.unique} unique word${s.unique === 1 ? '' : 's'} · ${s.total} total</div>
          </div>
          <div class="sr-badge" style="background:${s.palette.bg};color:${s.palette.text}">${s.total}</div>
        `;
        row.addEventListener('click', () => {
          selectedStudent = s.name;
          renderStudentList();
          renderStrugglesPanel();
        });
        listEl.appendChild(row);
      });
    }

    function renderStrugglesPanel(){
      const panelEl = document.getElementById('strugglesPanel');
      const s = STUDENT_SUMMARY.find(x => x.name === selectedStudent);
      if (!s){
        panelEl.innerHTML = `<div class="side-empty">Select a student to see their struggles.</div>`;
        return;
      }
      const chips = s.words.map(w => {
        const cat = CATEGORIES[w.cat];
        return `<span class="struggle-chip" style="background:${cat.light};color:${cat.color}">${w.word} <span class="x">×${w.count}</span></span>`;
      }).join('');
      panelEl.innerHTML = `
        <div class="struggles-panel-head">
          <div class="struggles-panel-title">${s.name}'s Struggles</div>
          <button class="struggles-close" id="strugglesCloseBtn" title="Clear selection">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="struggles-chip-wrap">${chips}</div>
        <div class="struggles-section-label">Recent Sessions</div>
        <div class="no-session">No session data available.</div>
      `;
      document.getElementById('strugglesCloseBtn').addEventListener('click', () => {
        selectedStudent = null;
        renderStudentList();
        renderStrugglesPanel();
      });
    }

    renderStudentList();
    renderStrugglesPanel();
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>