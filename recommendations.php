<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Recommendations</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script>
  /* Apply saved theme before paint to avoid a flash of the wrong theme */
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
  /* ================= Recommendations page (simplified) =================
     Same colors, rounded cards and pixel-style hover as the rest of ReadPilot,
     but with bigger text, plainer words, fewer things on screen at once,
     and one clear main button per card. */

  /* ---- "Waiting for you" banner ---- */
  .wait-banner{display:flex;align-items:center;gap:14px;flex-wrap:wrap;background:var(--orange-light);border-radius:var(--radius);padding:14px 18px;margin-bottom:16px;}
  .wait-banner.all-clear{background:var(--green-light);}
  .wait-banner .wb-icon{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,0.75);display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--orange);flex-shrink:0;}
  .wait-banner.all-clear .wb-icon{color:var(--green-dark);}
  .wait-banner .wb-text{flex-grow:1;min-width:200px;font-size:15px;font-weight:800;color:var(--ink);line-height:1.35;}
  .wait-banner .wb-text small{display:block;font-size:13px;font-weight:600;color:var(--muted);margin-top:2px;}
  html[data-theme="dark"] .wait-banner .wb-icon{background:rgba(0,0,0,0.25);}
  .progress-snapshot{display:flex;align-items:flex-start;gap:10px;background:var(--card);border-left:4px solid var(--purple);border-radius:12px;padding:12px 15px;margin-bottom:16px;color:var(--ink);font-size:13px;font-weight:700;line-height:1.5;box-shadow:var(--shadow);}
  .progress-snapshot .bx{flex:0 0 auto;margin-top:1px;color:var(--purple);font-size:18px;}

  /* ---- Simple filter row ---- */
  .simple-tabs{display:flex;gap:8px;flex-wrap:wrap;}
  .simple-tabs .tab{font-size:13.5px;font-weight:800;padding:9px 16px;border-radius:12px;cursor:pointer;}
  .simple-controls{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;}
  .simple-controls .sort-wrap{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--muted);}
  .simple-controls .sort-select{font-size:13px;padding:8px 10px;}

  /* ---- Recommendation list ---- */
  .rec-list{display:flex;flex-direction:column;gap:14px;margin-bottom:22px;}
  .rec-section-group{display:flex;flex-direction:column;gap:12px;margin-bottom:6px;}
  .rec-section-group.is-hidden{display:none;}
  .rec-section-heading{display:flex;align-items:center;gap:10px;padding:0 2px;color:var(--ink);font-family:'Poppins',sans-serif;font-size:16.5px;font-weight:700;}
  .rec-section-heading .count{font:700 12px 'Nunito',sans-serif;color:var(--muted);}

  .rec-card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:18px 22px;border-left:6px solid var(--border);}
  .rec-card.is-hidden{display:none;}
  .rec-card[data-status="pending"]{border-left-color:var(--orange);}
  .rec-card[data-status="approved"]{border-left-color:var(--tan);}
  .rec-card[data-status="applied"]{border-left-color:var(--green);}
  .rec-card[data-status="dismissed"]{border-left-color:var(--muted);}

  .rec-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px;}
  .rec-who{display:flex;align-items:center;gap:12px;}
  .rec-avatar{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;color:#fff;flex-shrink:0;}
  .rec-name{font-family:'Poppins',sans-serif;font-size:16px;font-weight:700;color:var(--ink);}
  .rec-meta{font-size:12.5px;color:var(--muted);font-weight:700;margin-top:1px;}

  .status-pill{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:800;padding:6px 12px;border-radius:20px;white-space:nowrap;}
  .status-pill .bx{font-size:14px;}
  .status-pill.pending{background:var(--orange-light);color:#b36b0a;}
  .status-pill.approved{background:var(--tan-light);color:var(--tan);}
  .status-pill.applied{background:var(--green-light);color:var(--green-dark);}
  .status-pill.dismissed{background:var(--bg);color:var(--muted);}
  html[data-theme="dark"] .status-pill.pending{color:var(--orange);}

  .rec-ask{font-size:14.5px;font-weight:700;color:var(--ink);margin-bottom:12px;line-height:1.5;}

  .rec-block{margin-bottom:12px;}
  .rec-label{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;}
  .rec-label .bx{font-size:15px;color:var(--green-dark);}

  .skill-focus{display:flex;align-items:flex-start;gap:9px;background:var(--purple-light);color:var(--ink);border-radius:12px;padding:11px 14px;font-size:14.5px;font-weight:800;line-height:1.45;}
  .skill-focus .bx{color:var(--purple);font-size:18px;flex-shrink:0;margin-top:1px;}
  html[data-theme="dark"] .skill-focus{background:rgba(139,107,209,0.18);}

  .practice-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;}
  .practice-list li{display:flex;align-items:flex-start;gap:9px;font-size:14px;color:var(--ink);font-weight:600;line-height:1.5;}
  .practice-list li .bx{color:var(--green-dark);font-size:17px;margin-top:1px;flex-shrink:0;}

  .rec-more{margin-top:2px;}
  .rec-more summary{cursor:pointer;font-size:13px;font-weight:800;color:var(--green-dark);padding:4px 0;}
  html[data-theme="dark"] .rec-more summary{color:var(--green);}
  .rec-more p{margin:4px 0 0;font-size:13px;color:var(--muted);font-weight:600;line-height:1.5;}

  /* ---- Buttons: one main action, one quiet secondary ---- */
  .rec-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border-top:1px solid var(--border);margin-top:14px;padding-top:14px;}
  .rec-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
  .btn-solid{background:var(--green);color:#fff;border:none;border-radius:12px;padding:10px 18px;min-height:42px;font-size:14px;font-weight:800;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px;box-shadow:0 4px 12px rgba(111,191,90,0.35);transition:transform .12s steps(2), box-shadow .12s steps(2);}
  .btn-solid .bx{font-size:18px;}
  .btn-solid:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark), 0 4px 12px rgba(111,191,90,0.35);}
  .btn-solid.done{background:var(--green-light);color:var(--green-dark);box-shadow:none;cursor:default;}
  .btn-solid.done:hover{transform:none;box-shadow:none;}
  .btn-outline{background:var(--card);border:1.5px solid var(--border);color:var(--ink);border-radius:12px;padding:9px 15px;min-height:42px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:6px;transition:transform .12s steps(2), box-shadow .12s steps(2), border-color .15s ease;}
  .btn-outline:hover{transform:translate(-2px,-2px);border-color:var(--green-dark);box-shadow:2px 2px 0 var(--green-dark);}
  .btn-outline.quiet{color:var(--muted);}
  .btn-outline.quiet:hover{color:var(--red);border-color:var(--red);box-shadow:2px 2px 0 var(--red);}
  .btn-new{font-size:14px;padding:11px 18px;}

  .empty-state{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:44px 20px;text-align:center;color:var(--muted);font-size:14px;font-weight:600;}
  .empty-state .bx{font-size:34px;color:var(--green);margin-bottom:8px;display:block;}
  .empty-state b{color:var(--ink);display:block;font-size:16px;margin-bottom:4px;}

  .tip .tip-text{font-size:13px;line-height:1.55;}
  .tip .tip-title{font-size:14.5px;}

  /* ---- Modal (write a recommendation) ---- */
  .modal-head{margin-bottom:16px;padding-right:30px;}
  .modal-head h2{font-size:18px;}
  .modal-head .sub{font-size:13px;color:var(--muted);font-weight:700;}
  .modal .form-row label{font-size:13.5px;font-weight:800;}
  .modal .form-row input,.modal .form-row select,.modal .form-row textarea{font-size:14px;}
  .modal-actions{display:flex;gap:10px;margin-top:8px;}
  .modal-actions .btn-solid{flex:1;justify-content:center;}

  @media (max-width:700px){
    .rec-card{padding:16px 16px;}
    .rec-actions{width:100%;}
    .rec-actions .btn-solid{flex:1;justify-content:center;}
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
        <a class="nav-item" href="index.php"><i class="bx bxs-dashboard"></i><span class="label">Dashboard</span></a>
        <a class="nav-item" href="students.php"><i class="bx bx-group"></i><span class="label">Sections</span></a>
        <a class="nav-item" href="sessions.php"><i class="bx bx-calendar"></i><span class="label">Sessions</span></a>
        <a class="nav-item" href="reports.php"><i class="bx bx-file"></i><span class="label">Reports</span></a>
        <a class="nav-item" href="struggle-map.php"><i class="bx bx-target-lock"></i><span class="label">Struggle Map</span></a>
        <a class="nav-item active" href="recommendations.php"><i class="bx bx-bulb"></i><span class="label">Recommendations</span></a>
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
        <h1>Recommendations</h1>
        <div class="greet">Practice ideas for your students. You decide what gets assigned.</div>
      </div>
      <div class="topbar-actions">
        <button class="btn-new" id="openRecModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
          Write My Own
        </button>
      </div>
    </div>

    <!-- ================= WAITING-FOR-YOU BANNER ================= -->
    <div class="wait-banner all-clear" id="waitBanner" style="display:none;">
      <div class="wb-icon"><i class='bx bx-bell'></i></div>
      <div class="wb-text" id="waitText"></div>
    </div>
    <div class="progress-snapshot" id="progressSnapshot" role="status" aria-live="polite">
      <i class='bx bx-bar-chart-alt-2'></i>
      <span>Loading student support count from Progress…</span>
    </div>

    <!-- ================= FILTERS ================= -->
    <div class="simple-controls">
      <div class="simple-tabs tabs" id="filterTabs">
        <div class="tab active" data-filter="all">All</div>
        <div class="tab" data-filter="pending">Waiting for you</div>
        <div class="tab" data-filter="approved">Assigned</div>
        <div class="tab" data-filter="applied">Done</div>
        <div class="tab" data-filter="dismissed">Removed</div>
      </div>
      <div class="sort-wrap">
        <span>Show:</span>
        <select class="sort-select" id="sortSelect" aria-label="Order of recommendations">
          <option value="recent">Newest first</option>
          <option value="name">By student name</option>
          <option value="status">Waiting ones first</option>
        </select>
      </div>
    </div>

    <!-- ================= RECOMMENDATION LIST ================= -->
    <div class="rec-list" id="recList">
      <div class="empty-state" id="emptyState" style="display:none;">
        <i class='bx bx-bulb'></i>
        <b>Nothing to show here</b>
        Try another filter above, or tap “Write My Own” to add one.
      </div>
    </div>

    <div class="tip">
      <div class="tip-icon">
        <i class='bx bx-bot' style="font-size:22px;color:#8b6bd1;"></i>
      </div>
      <div>
        <div class="tip-title">How suggestions work</div>
        <div class="tip-text">After each reading session, ReadPilot looks at which words were hard and suggests some practice. Nothing is given to a student until you say yes.</div>
      </div>
      <div class="tip-close" id="tipClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </div>
    </div>
  </main>

  <!-- ================= MODAL: Write My Own ================= -->
  <div class="overlay" id="recOverlay">
    <div class="modal">
      <button class="modal-close" id="closeRecModal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      <div class="modal-head">
        <h2>Write My Own Recommendation</h2>
        <div class="sub">Add your own practice idea for a student</div>
      </div>
      <div class="form-row">
        <label for="formStudent">Which student?</label>
        <select id="formStudent"></select>
      </div>
      <div class="form-row">
        <label for="formSkill">What do they need help with?</label>
        <input type="text" id="formSkill" placeholder="e.g. Blending consonant clusters">
      </div>
      <div class="form-row">
        <label for="formNotes">What should they practice?</label>
        <textarea id="formNotes" placeholder="Describe the practice activities. Put each idea on its own line."></textarea>
      </div>
      <div class="form-row">
        <label for="formBook">Book (optional)</label>
        <input type="text" id="formBook" placeholder="e.g. The Three Little Pigs">
      </div>
      <div class="modal-actions">
        <button class="btn-outline" id="cancelRecModal">Cancel</button>
        <button class="btn-solid" id="saveRecModal"><i class='bx bx-check'></i>Save</button>
      </div>
    </div>
  </div>

  <!-- ================= TOAST ================= -->
  <div class="toast" id="toast"><i class='bx bx-check-circle'></i><span id="toastMsg">Saved</span></div>

  <script>
    // ---- Hamburger: collapse sidebar ----
    document.getElementById('sidebarToggle').addEventListener('click', function(){
      var html = document.documentElement;
      var isCollapsed = html.getAttribute('data-sidebar') === 'collapsed';

      if (isCollapsed) {
        html.removeAttribute('data-sidebar');
      } else {
        html.setAttribute('data-sidebar', 'collapsed');
      }

      try {
        localStorage.setItem('readpilot-sidebar', isCollapsed ? 'expanded' : 'collapsed');
      } catch (e) {
        /* localStorage unavailable — toggle still works for this page load */
      }
    });

    // ---- Tip banner close ----
    document.getElementById('tipClose').addEventListener('click', function(){
      this.closest('.tip').style.display = 'none';
    });

    // ---- Toast helper ----
    var toast = document.getElementById('toast');
    var toastMsg = document.getElementById('toastMsg');
    var toastTimer;
    function showToast(msg){
      toastMsg.textContent = msg;
      toast.classList.add('show');
      clearTimeout(toastTimer);
      toastTimer = setTimeout(function(){ toast.classList.remove('show'); }, 3000);
    }

    // ---- State ----
    var tabs = document.querySelectorAll('.tab');
    var cards = [];
    var emptyState = document.getElementById('emptyState');
    var recList = document.getElementById('recList');
    var waitBanner = document.getElementById('waitBanner');
    var waitText = document.getElementById('waitText');
    var recommendations = [];
    var progressSnapshot = document.querySelector('#progressSnapshot span');
    var activeFilter = 'all';
    var activeSort = 'recent';

    function escapeHtml(value){
      return String(value == null ? '' : value).replace(/[&<>"']/g, function(character){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[character];
      });
    }
    function firstName(name){ return String(name || 'this student').split(' ')[0]; }
    function niceDate(value){
      var t = Date.parse(String(value || '').replace(' ', 'T'));
      if(isNaN(t)) return '';
      return new Date(t).toLocaleDateString(undefined, {month:'short', day:'numeric'});
    }

    var STATUS = {
      pending:  {label:'Waiting for you', icon:'bx-time-five'},
      approved: {label:'Assigned',        icon:'bx-send'},
      applied:  {label:'Done',            icon:'bx-check-circle'},
      dismissed:{label:'Removed',         icon:'bx-x-circle'}
    };

    // ---- Build one card ----
    function renderCard(rec){
      var st = STATUS[rec.status] || STATUS.pending;
      var initial = escapeHtml((rec.studentName || '?').charAt(0).toUpperCase());
      var who = firstName(rec.studentName);
      var sourceNote = rec.source === 'ai' ? 'Suggested by ReadPilot'
                     : rec.source === 'specialist' ? 'Written by the reading specialist'
                     : 'Written by a teacher';

      var metaParts = [];
      if(rec.relatedBook) metaParts.push('Book: ' + rec.relatedBook);
      var d = niceDate(rec.createdAt);
      if(d) metaParts.push(d);
      var meta = metaParts.length ? escapeHtml(metaParts.join(' • ')) : '&nbsp;';

      var steps = String(rec.practiceNotes || '').split(/\r?\n/).map(function(s){ return s.trim(); }).filter(Boolean);
      if(!steps.length) steps = ['No practice notes were added.'];
      var stepsHtml = steps.map(function(s){
        return '<li><i class="bx bx-check-circle"></i><span>'+escapeHtml(s)+'</span></li>';
      }).join('');

      var ask = '', action = '';
      if(rec.status === 'pending'){
        ask = '<div class="rec-ask">Would you like to give this practice to '+escapeHtml(who)+'?</div>';
        action = '<button class="btn-outline quiet js-dismiss"><i class="bx bx-x"></i>No thanks</button>'
               + '<button class="btn-solid js-approve"><i class="bx bx-check"></i>Yes, assign this</button>';
      } else if(rec.status === 'approved'){
        ask = '<div class="rec-ask">This practice has been assigned to '+escapeHtml(who)+'. Tap “Mark as done” once '+escapeHtml(who)+' has finished.</div>';
        action = '<button class="btn-outline quiet js-dismiss"><i class="bx bx-x"></i>Remove</button>'
               + '<button class="btn-solid js-apply"><i class="bx bx-check-double"></i>Mark as done</button>';
      } else if(rec.status === 'applied'){
        action = '<button class="btn-solid done" disabled><i class="bx bx-check-double"></i>All done</button>';
      } else {
        action = '<button class="btn-outline quiet" disabled><i class="bx bx-x-circle"></i>Removed</button>';
      }

      return '<div class="rec-card" data-id="'+escapeHtml(rec.id)+'" data-source="'+escapeHtml(rec.source)+'" data-status="'+escapeHtml(rec.status)+'" data-name="'+escapeHtml(rec.studentName)+'">'
        + '<div class="rec-top">'
          + '<div class="rec-who"><div class="rec-avatar" style="background:'+escapeHtml(rec.color || '#6fbf5a')+'">'+initial+'</div>'
          + '<div><div class="rec-name">'+escapeHtml(rec.studentName)+'</div><div class="rec-meta">'+meta+'</div></div></div>'
          + '<span class="status-pill '+escapeHtml(rec.status)+'"><i class="bx '+st.icon+'"></i>'+st.label+'</span>'
        + '</div>'
        + ask
        + '<div class="rec-block"><div class="rec-label"><i class="bx bx-target-lock"></i>Needs help with</div>'
          + '<div class="skill-focus"><i class="bx bx-bulb"></i><span>'+escapeHtml(rec.skillFocus)+'</span></div></div>'
        + '<div class="rec-block"><div class="rec-label"><i class="bx bx-list-check"></i>What to practice</div>'
          + '<ul class="practice-list">'+stepsHtml+'</ul></div>'
        + '<details class="rec-more"><summary>More details</summary><p>'+escapeHtml(sourceNote)+'.</p></details>'
        + '<div class="rec-foot"><div></div><div class="rec-actions">'+action+'</div></div>'
        + '</div>';
    }

    // ---- Banner at the top ----
    function updateBanner(){
      var waiting = recommendations.filter(function(r){ return r.status === 'pending'; }).length;
      waitBanner.style.display = 'flex';
      if(waiting > 0){
        waitBanner.classList.remove('all-clear');
        waitText.innerHTML = 'You have <b>'+waiting+'</b> suggestion'+(waiting === 1 ? '' : 's')+' waiting for you<small>Read each one and choose Yes or No.</small>';
      } else {
        waitBanner.classList.add('all-clear');
        waitText.innerHTML = 'You are all caught up<small>Nothing is waiting for your decision right now.</small>';
      }
    }

    function renderProgressSnapshot(summary){
      var assessedCount = Number(summary && summary.assessedCount) || 0;
      var needsSupportCount = Number(summary && summary.needsSupportCount) || 0;
      if(assessedCount === 0){
        progressSnapshot.textContent = 'No students have a saved AI assessment in Progress yet.';
        return;
      }
      progressSnapshot.textContent = 'Progress shows '+needsSupportCount+' student'+(needsSupportCount === 1 ? '' : 's')+' marked “Needs Support” in the latest AI assessment ('+assessedCount+' student'+(assessedCount === 1 ? '' : 's')+' assessed).';
    }

    // ---- Render everything ----
    function renderRecommendations(){
      Array.prototype.slice.call(recList.children).forEach(function(child){
        if(child.classList.contains('rec-section-group') || child.classList.contains('rec-card')) child.remove();
      });
      var grouped = new Map();
      recommendations.forEach(function(rec){
        var sectionName = rec.sectionName || 'Unassigned';
        if(!grouped.has(sectionName)) grouped.set(sectionName, []);
        grouped.get(sectionName).push(rec);
      });
      var statusRank = {pending:0, approved:1, applied:2, dismissed:3};
      Array.from(grouped.entries()).sort(function(a,b){ return a[0].localeCompare(b[0], undefined, {numeric:true, sensitivity:'base'}); }).forEach(function(entry){
        var sectionName = entry[0];
        var list = entry[1];
        if(activeSort === 'name'){
          list.sort(function(a,b){ return a.studentName.localeCompare(b.studentName); });
        } else if(activeSort === 'status'){
          list.sort(function(a,b){ return statusRank[a.status] - statusRank[b.status] || a.studentName.localeCompare(b.studentName); });
        } else {
          list.sort(function(a,b){ return String(b.createdAt).localeCompare(String(a.createdAt)); });
        }
        var group = document.createElement('section');
        group.className = 'rec-section-group';
        group.innerHTML = '<div class="rec-section-heading">'+escapeHtml(sectionName)+' <span class="count">'+list.length+' recommendation'+(list.length === 1 ? '' : 's')+'</span></div>';
        list.forEach(function(rec){ group.insertAdjacentHTML('beforeend', renderCard(rec)); });
        recList.insertBefore(group, emptyState);
      });
      cards = Array.prototype.slice.call(recList.querySelectorAll('.rec-card'));
      applyFilter(activeFilter);
      updateBanner();
      bindRecommendationActions();
    }

    function applyFilter(filter){
      var visible = 0;
      cards.forEach(function(card){
        var match = filter === 'all' || card.dataset.status === filter;
        card.classList.toggle('is-hidden', !match);
        if (match) visible++;
      });
      recList.querySelectorAll('.rec-section-group').forEach(function(group){
        group.classList.toggle('is-hidden', !group.querySelector('.rec-card:not(.is-hidden)'));
      });
      emptyState.style.display = visible === 0 ? 'block' : 'none';
    }

    function setFilter(filter){
      activeFilter = filter;
      tabs.forEach(function(t){ t.classList.toggle('active', t.dataset.filter === filter); });
      applyFilter(filter);
      updateBanner();
      window.scrollTo({top:0, behavior:'smooth'});
    }
    tabs.forEach(function(tab){
      tab.addEventListener('click', function(){ setFilter(this.dataset.filter); });
    });

    // ---- Sorting ----
    document.getElementById('sortSelect').addEventListener('change', function(){
      activeSort = this.value;
      renderRecommendations();
    });

    // ---- Approve / Done / Dismiss actions ----
    function bindRecommendationActions(){
      recList.querySelectorAll('.js-approve, .js-apply, .js-dismiss').forEach(function(button){
        button.addEventListener('click', function(){
          var card = this.closest('.rec-card');
          var action = this.classList.contains('js-approve') ? 'approve' : this.classList.contains('js-apply') ? 'apply' : 'dismiss';
          if(action === 'dismiss' && !confirm('Remove this recommendation? You will not see it again.')) return;
          var btn = this;
          btn.disabled = true;
          fetch('recommendation-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:action, id:card.dataset.id})})
            .then(function(response){ return response.json().then(function(data){ return {ok:response.ok, data:data}; }); })
            .then(function(result){ if(!result.ok) throw new Error(result.data.error || 'Something went wrong. Please try again.'); return loadRecommendations(); })
            .then(function(){ showToast(action === 'approve' ? 'Done! It has been assigned to the student.' : action === 'apply' ? 'Marked as done.' : 'Recommendation removed.'); })
            .catch(function(error){ btn.disabled = false; showToast(error.message); });
        });
      });
    }

    // ---- Modal ----
    var overlay = document.getElementById('recOverlay');
    function openModal(){ overlay.classList.add('open'); }
    function closeModal(){ overlay.classList.remove('open'); }
    document.getElementById('openRecModal').addEventListener('click', openModal);
    document.getElementById('closeRecModal').addEventListener('click', closeModal);
    document.getElementById('cancelRecModal').addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e){ if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeModal(); });

    document.getElementById('saveRecModal').addEventListener('click', function(){
      var studentId = document.getElementById('formStudent').value;
      var skill = document.getElementById('formSkill').value.trim();
      var notes = document.getElementById('formNotes').value.trim();
      if(!studentId || !skill || !notes){ showToast('Please choose a student and fill in the two main boxes.'); return; }
      fetch('recommendation-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'save', student_id:studentId, related_book:document.getElementById('formBook').value.trim(), skill_focus:skill, practice_notes:notes})})
        .then(function(response){ return response.json().then(function(data){ return {ok:response.ok, data:data}; }); })
        .then(function(result){ if(!result.ok) throw new Error(result.data.error || 'Unable to save. Please try again.'); closeModal(); document.getElementById('formSkill').value=''; document.getElementById('formNotes').value=''; document.getElementById('formBook').value=''; return loadRecommendations(); })
        .then(function(){ showToast('Saved. Your recommendation was added.'); })
        .catch(function(error){ showToast(error.message); });
    });

    // ---- Load data ----
    function loadRecommendations(){
      return fetch('recommendation-api.php').then(function(response){ return response.json().then(function(data){ if(!response.ok) throw new Error(data.error || 'Unable to load recommendations'); return data; }); }).then(function(data){
        recommendations = data.recommendations || [];
        renderProgressSnapshot(data.progressSummary);
        renderRecommendations();
      }).catch(function(error){
        progressSnapshot.textContent = 'Progress student count is unavailable right now.';
        showToast(error.message);
      });
    }
    fetch('student-api.php').then(function(response){ return response.json(); }).then(function(data){
      var select = document.getElementById('formStudent');
      select.innerHTML = (data.students || []).map(function(student){ return '<option value="'+student.id+'">'+escapeHtml(student.name)+'</option>'; }).join('');
    }).then(loadRecommendations);
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>