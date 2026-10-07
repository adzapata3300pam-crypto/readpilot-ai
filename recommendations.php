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
  /* ================= Recommendations page (extends style.css) =================
     Dark-mode variables, .controls/.tabs/.sort-select, .overlay/.modal/.form-row,
     and .toast now live in style.css. Only recommendation-card-specific rules
     (and their dark-mode tweaks) stay in this file. */

  /* ---- Recommendation card ---- */
  .rec-list{display:flex;flex-direction:column;gap:16px;margin-bottom:26px;}
  .rec-card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:22px 24px;transition:box-shadow .15s ease;}
  .rec-card.is-hidden{display:none;}

  .rec-top{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:16px;}
  .rec-who{display:flex;align-items:center;gap:12px;}
  .rec-avatar{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;color:#fff;flex-shrink:0;}
  .rec-name{font-size:14.5px;font-weight:700;color:var(--ink);}
  .rec-meta{display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--muted);font-weight:600;flex-wrap:wrap;}
  .rec-meta .dot{width:3px;height:3px;border-radius:50%;background:var(--muted);}

  .rec-tags{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
  .src-pill{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:800;padding:5px 11px;border-radius:20px;white-space:nowrap;}
  .src-pill.ai{background:var(--purple-light);color:var(--purple);}
  .src-pill.specialist{background:var(--teal-light);color:var(--teal);}
  .src-pill .bx{font-size:13px;}
  .status-pill{font-size:11px;font-weight:800;padding:5px 11px;border-radius:20px;white-space:nowrap;}
  .status-pill.pending{background:var(--orange-light);color:var(--orange);}
  .status-pill.approved{background:var(--tan-light);color:var(--tan);}
  .status-pill.applied{background:var(--green-light);color:var(--green-dark);}

  .rec-body{display:grid;grid-template-columns:1.3fr 1fr;gap:22px;}
  @media (max-width:900px){.rec-body{grid-template-columns:1fr;}}

  .rec-section-label{font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:9px;display:flex;align-items:center;gap:6px;}
  .rec-section-label .bx{font-size:14px;color:var(--green-dark);}

  .word-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;}
  .word-chip{display:flex;align-items:center;gap:6px;background:var(--red-light);color:#b23f3f;font-size:12.5px;font-weight:700;padding:6px 12px;border-radius:10px;}
  .word-chip .miss-count{background:rgba(178,63,63,0.15);font-size:10.5px;font-weight:800;padding:1px 6px;border-radius:8px;}
  html[data-theme="dark"] .word-chip{color:#f0a3a3;}
  html[data-theme="dark"] .word-chip .miss-count{background:rgba(240,128,127,0.18);}

  .skill-focus{display:inline-flex;align-items:center;gap:7px;background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:9px 13px;font-size:12.5px;font-weight:700;color:var(--ink);}
  .skill-focus .bx{color:var(--purple);font-size:15px;}

  .practice-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:9px;}
  .practice-list li{display:flex;align-items:flex-start;gap:9px;font-size:13px;color:#3d5240;font-weight:600;line-height:1.45;}
  .practice-list li .bx{color:var(--green-dark);font-size:15px;margin-top:1px;flex-shrink:0;}
  html[data-theme="dark"] .practice-list li{color:#c3d6c9;}

  .specialist-note{background:var(--teal-light);border-radius:12px;padding:12px 14px;font-size:12.5px;color:#2c5764;font-weight:600;line-height:1.5;margin-top:14px;}
  .specialist-note b{color:#1f4048;}
  html[data-theme="dark"] .specialist-note{color:#bfe3ee;}
  html[data-theme="dark"] .specialist-note b{color:#eaf7fb;}

  .rec-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border-top:1px solid var(--border);margin-top:18px;padding-top:16px;}
  .rec-source-note{font-size:11.5px;color:var(--muted);font-weight:600;display:flex;align-items:center;gap:6px;}
  .rec-source-note .bx{font-size:14px;}
  .rec-actions{display:flex;gap:8px;flex-wrap:wrap;}
  .btn-outline{background:var(--card);border:1.5px solid var(--border);color:var(--ink);border-radius:10px;padding:8px 14px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:6px;transition:transform .12s steps(2), box-shadow .12s steps(2), border-color .15s ease;}
  .btn-outline:hover{transform:translate(-2px,-2px);border-color:var(--green-dark);box-shadow:2px 2px 0 var(--green-dark);}
  .btn-outline.danger{color:var(--red);}
  .btn-outline.danger:hover{border-color:var(--red);box-shadow:2px 2px 0 var(--red);}
  .btn-solid{background:var(--green);color:#fff;border:none;border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:6px;box-shadow:0 4px 10px rgba(111,191,90,0.3);transition:transform .12s steps(2), box-shadow .12s steps(2);}
  .btn-solid:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark), 0 4px 10px rgba(111,191,90,0.3);}
  .btn-solid.done{background:var(--muted);box-shadow:none;cursor:default;}
  .btn-solid.done:hover{transform:none;box-shadow:none;}

  .empty-state{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:50px 20px;text-align:center;color:var(--muted);}
  .empty-state .bx{font-size:34px;color:var(--green);margin-bottom:10px;display:block;}
  .empty-state b{color:var(--ink);display:block;font-size:15px;margin-bottom:4px;}

  /* ---- Modal (write a recommendation): base .overlay/.modal/.form-row in style.css ---- */
  .modal-head{margin-bottom:18px;padding-right:30px;}
  .modal-head .sub{font-size:12.5px;color:var(--muted);font-weight:700;}
  .modal-actions{display:flex;gap:10px;margin-top:6px;}
  .modal-actions .btn-solid{flex:1;justify-content:center;padding:11px 16px;}
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
        <div class="greet">🤖 AI-generated insights, ready for your review</div>
      </div>
      <div class="topbar-actions">
        <div class="search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          Search by student or skill...
        </div>
        <div class="date-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Aug 21, 2026
        </div>
        <button class="btn-new" id="openRecModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
          Write Recommendation
        </button>
        <div class="bell">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
          <span class="badge">3</span>
        </div>
      </div>
    </div>

    <!-- ================= CONTROLS ================= -->
    <div class="controls">
      <div class="tabs" id="filterTabs">
        <div class="tab active" data-filter="all">All</div>
        <div class="tab" data-filter="ai">AI Suggested</div>
        <div class="tab" data-filter="specialist">Specialist Written</div>
        <div class="tab" data-filter="pending">Pending Review</div>
        <div class="tab" data-filter="approved">Approved</div>
        <div class="tab" data-filter="applied">Applied</div>
      </div>
      <div class="controls-right">
        <span class="results-count" id="resultsCount">0 recommendations</span>
        <select class="sort-select" id="sortSelect">
          <option value="recent">Newest first</option>
          <option value="name">Student name (A–Z)</option>
          <option value="status">Status</option>
        </select>
      </div>
    </div>

    <!-- ================= RECOMMENDATION LIST ================= -->
    <div class="rec-list" id="recList">

      <!-- Card 1: AI, pending -->
      <div class="rec-card" data-source="ai" data-status="pending" data-name="Carmen Reyes">
        <div class="rec-top">
          <div class="rec-who">
            <div class="rec-avatar" style="background:#6fbf5a">C</div>
            <div>
              <div class="rec-name">Carmen Reyes</div>
              <div class="rec-meta">Grade 3 <span class="dot"></span> "A Rainy Day Surprise" <span class="dot"></span> Aug 20, 2026</div>
            </div>
          </div>
          <div class="rec-tags">
            <span class="src-pill ai"><i class='bx bx-bot'></i>AI Agent</span>
            <span class="status-pill pending">Pending Review</span>
          </div>
        </div>
        <div class="rec-body">
          <div>
            <div class="rec-section-label"><i class='bx bx-error-circle'></i>Words Carmen struggled with</div>
            <div class="word-chips">
              <span class="word-chip">surprise <span class="miss-count">×3</span></span>
              <span class="word-chip">umbrella <span class="miss-count">×2</span></span>
              <span class="word-chip">puddle <span class="miss-count">×2</span></span>
              <span class="word-chip">thunder <span class="miss-count">×1</span></span>
            </div>
            <div class="skill-focus"><i class='bx bx-target-lock'></i>Focus skill: blending consonant clusters (spr-, thr-, pl-)</div>
          </div>
          <div>
            <div class="rec-section-label"><i class='bx bx-bulb'></i>AI-recommended practice</div>
            <ul class="practice-list">
              <li><i class='bx bx-chevron-right'></i>5-minute daily drill on "spr", "thr", and "pl" blends using flashcards</li>
              <li><i class='bx bx-chevron-right'></i>Re-read "A Rainy Day Surprise" aloud, pausing on flagged words</li>
              <li><i class='bx bx-chevron-right'></i>Assign a decodable reader with weather vocabulary for reinforcement</li>
            </ul>
          </div>
        </div>
        <div class="rec-foot">
          <div class="rec-source-note"><i class='bx bx-bot'></i>Generated from session on Aug 20, 2026 · 88% accuracy</div>
          <div class="rec-actions">
            <button class="btn-outline danger"><i class='bx bx-x'></i>Dismiss</button>
            <button class="btn-outline"><i class='bx bx-edit-alt'></i>Edit</button>
            <button class="btn-solid js-approve"><i class='bx bx-check'></i>Approve &amp; Assign</button>
          </div>
        </div>
      </div>

      <!-- Card 2: AI, pending -->
      <div class="rec-card" data-source="ai" data-status="pending" data-name="Isabella Ramos">
        <div class="rec-top">
          <div class="rec-who">
            <div class="rec-avatar" style="background:#f2a13a">I</div>
            <div>
              <div class="rec-name">Isabella Ramos</div>
              <div class="rec-meta">Grade 3 <span class="dot"></span> "Journey to the Stars" <span class="dot"></span> Aug 19, 2026</div>
            </div>
          </div>
          <div class="rec-tags">
            <span class="src-pill ai"><i class='bx bx-bot'></i>AI Agent</span>
            <span class="status-pill pending">Pending Review</span>
          </div>
        </div>
        <div class="rec-body">
          <div>
            <div class="rec-section-label"><i class='bx bx-error-circle'></i>Words Isabella struggled with</div>
            <div class="word-chips">
              <span class="word-chip">galaxy <span class="miss-count">×4</span></span>
              <span class="word-chip">astronaut <span class="miss-count">×3</span></span>
              <span class="word-chip">mysterious <span class="miss-count">×2</span></span>
            </div>
            <div class="skill-focus"><i class='bx bx-target-lock'></i>Focus skill: decoding multisyllabic words</div>
          </div>
          <div>
            <div class="rec-section-label"><i class='bx bx-bulb'></i>AI-recommended practice</div>
            <ul class="practice-list">
              <li><i class='bx bx-chevron-right'></i>Practice syllable-clapping with "gal-ax-y" and "as-tro-naut"</li>
              <li><i class='bx bx-chevron-right'></i>Build a personal word wall of space vocabulary before next read</li>
              <li><i class='bx bx-chevron-right'></i>Pair with an easier space-themed book to build reading confidence</li>
            </ul>
          </div>
        </div>
        <div class="rec-foot">
          <div class="rec-source-note"><i class='bx bx-bot'></i>Generated from session on Aug 19, 2026 · 90% accuracy</div>
          <div class="rec-actions">
            <button class="btn-outline danger"><i class='bx bx-x'></i>Dismiss</button>
            <button class="btn-outline"><i class='bx bx-edit-alt'></i>Edit</button>
            <button class="btn-solid js-approve"><i class='bx bx-check'></i>Approve &amp; Assign</button>
          </div>
        </div>
      </div>

      <!-- Card 3: Specialist, approved -->
      <div class="rec-card" data-source="specialist" data-status="approved" data-name="Eva Mendoza">
        <div class="rec-top">
          <div class="rec-who">
            <div class="rec-avatar" style="background:#e7c58a">E</div>
            <div>
              <div class="rec-name">Eva Mendoza</div>
              <div class="rec-meta">Grade 3 <span class="dot"></span> "The Three Little Pigs" <span class="dot"></span> Aug 18, 2026</div>
            </div>
          </div>
          <div class="rec-tags">
            <span class="src-pill specialist"><i class='bx bx-user-voice'></i>Reading Specialist</span>
            <span class="status-pill approved">Approved</span>
          </div>
        </div>
        <div class="rec-body">
          <div>
            <div class="rec-section-label"><i class='bx bx-error-circle'></i>Flagged during reading</div>
            <div class="word-chips">
              <span class="word-chip">huffed <span class="miss-count">×2</span></span>
              <span class="word-chip">chimney <span class="miss-count">×2</span></span>
            </div>
            <div class="skill-focus"><i class='bx bx-target-lock'></i>Focus skill: expression &amp; pacing, not just accuracy</div>
          </div>
          <div>
            <div class="rec-section-label"><i class='bx bx-bulb'></i>Specialist-recommended practice</div>
            <ul class="practice-list">
              <li><i class='bx bx-chevron-right'></i>Echo-reading exercise focused on dialogue expression</li>
              <li><i class='bx bx-chevron-right'></i>Record Eva reading a page and play it back together</li>
            </ul>
            <div class="specialist-note"><b>Note from Mr. Aquino (Reading Specialist):</b> Eva's accuracy is strong — this is about building fluency and confidence reading aloud, not decoding.</div>
          </div>
        </div>
        <div class="rec-foot">
          <div class="rec-source-note"><i class='bx bx-user-voice'></i>Written by Mr. Aquino · Aug 18, 2026</div>
          <div class="rec-actions">
            <button class="btn-outline"><i class='bx bx-edit-alt'></i>Edit</button>
            <button class="btn-solid js-apply"><i class='bx bx-send'></i>Mark as Applied</button>
          </div>
        </div>
      </div>

      <!-- Card 4: AI, applied -->
      <div class="rec-card" data-source="ai" data-status="applied" data-name="Carmen Reyes">
        <div class="rec-top">
          <div class="rec-who">
            <div class="rec-avatar" style="background:#6fbf5a">C</div>
            <div>
              <div class="rec-name">Carmen Reyes</div>
              <div class="rec-meta">Grade 3 <span class="dot"></span> "The Lion and the Mouse" <span class="dot"></span> Aug 12, 2026</div>
            </div>
          </div>
          <div class="rec-tags">
            <span class="src-pill ai"><i class='bx bx-bot'></i>AI Agent</span>
            <span class="status-pill applied">Applied</span>
          </div>
        </div>
        <div class="rec-body">
          <div>
            <div class="rec-section-label"><i class='bx bx-error-circle'></i>Words Carmen struggled with</div>
            <div class="word-chips">
              <span class="word-chip">roared <span class="miss-count">×2</span></span>
              <span class="word-chip">tangled <span class="miss-count">×1</span></span>
            </div>
            <div class="skill-focus"><i class='bx bx-target-lock'></i>Focus skill: -ed past tense endings</div>
          </div>
          <div>
            <div class="rec-section-label"><i class='bx bx-bulb'></i>AI-recommended practice</div>
            <ul class="practice-list">
              <li><i class='bx bx-chevron-right'></i>Sort word cards by -ed sound: /t/, /d/, /ɪd/</li>
              <li><i class='bx bx-chevron-right'></i>Re-read passage aloud once fluently before moving on</li>
            </ul>
          </div>
        </div>
        <div class="rec-foot">
          <div class="rec-source-note"><i class='bx bx-check-circle'></i>Approved by Ms. Hernandez · Applied Aug 15, 2026</div>
          <div class="rec-actions">
            <button class="btn-solid done" disabled><i class='bx bx-check-double'></i>Completed</button>
          </div>
        </div>
      </div>

      <!-- Card 5: AI, pending -->
      <div class="rec-card" data-source="ai" data-status="pending" data-name="Diego Santos">
        <div class="rec-top">
          <div class="rec-who">
            <div class="rec-avatar" style="background:#8b6bd1">D</div>
            <div>
              <div class="rec-name">Diego Santos</div>
              <div class="rec-meta">Grade 3 <span class="dot"></span> "The Tortoise and the Hare" <span class="dot"></span> Aug 21, 2026</div>
            </div>
          </div>
          <div class="rec-tags">
            <span class="src-pill ai"><i class='bx bx-bot'></i>AI Agent</span>
            <span class="status-pill pending">Pending Review</span>
          </div>
        </div>
        <div class="rec-body">
          <div>
            <div class="rec-section-label"><i class='bx bx-error-circle'></i>Words Diego struggled with</div>
            <div class="word-chips">
              <span class="word-chip">confident <span class="miss-count">×3</span></span>
              <span class="word-chip">exhausted <span class="miss-count">×3</span></span>
              <span class="word-chip">steady <span class="miss-count">×2</span></span>
            </div>
            <div class="skill-focus"><i class='bx bx-target-lock'></i>Focus skill: vowel teams (ea, ou) &amp; comprehension pacing</div>
          </div>
          <div>
            <div class="rec-section-label"><i class='bx bx-bulb'></i>AI-recommended practice</div>
            <ul class="practice-list">
              <li><i class='bx bx-chevron-right'></i>Vowel-team sorting activity for "ea" and "ou" word families</li>
              <li><i class='bx bx-chevron-right'></i>Slow down oral reading rate slightly to improve comprehension checks</li>
              <li><i class='bx bx-chevron-right'></i>Discuss story moral together to reinforce comprehension over speed</li>
            </ul>
          </div>
        </div>
        <div class="rec-foot">
          <div class="rec-source-note"><i class='bx bx-bot'></i>Generated from session on Aug 21, 2026 · 82% accuracy</div>
          <div class="rec-actions">
            <button class="btn-outline danger"><i class='bx bx-x'></i>Dismiss</button>
            <button class="btn-outline"><i class='bx bx-edit-alt'></i>Edit</button>
            <button class="btn-solid js-approve"><i class='bx bx-check'></i>Approve &amp; Assign</button>
          </div>
        </div>
      </div>

      <!-- Card 6: Specialist, applied -->
      <div class="rec-card" data-source="specialist" data-status="applied" data-name="Isabella Ramos">
        <div class="rec-top">
          <div class="rec-who">
            <div class="rec-avatar" style="background:#f2a13a">I</div>
            <div>
              <div class="rec-name">Isabella Ramos</div>
              <div class="rec-meta">Grade 3 <span class="dot"></span> General fluency check-in <span class="dot"></span> Aug 10, 2026</div>
            </div>
          </div>
          <div class="rec-tags">
            <span class="src-pill specialist"><i class='bx bx-user-voice'></i>Reading Specialist</span>
            <span class="status-pill applied">Applied</span>
          </div>
        </div>
        <div class="rec-body">
          <div>
            <div class="rec-section-label"><i class='bx bx-error-circle'></i>Flagged during reading</div>
            <div class="word-chips">
              <span class="word-chip">whispered <span class="miss-count">×2</span></span>
            </div>
            <div class="skill-focus"><i class='bx bx-target-lock'></i>Focus skill: silent letters (wh-)</div>
          </div>
          <div>
            <div class="rec-section-label"><i class='bx bx-bulb'></i>Specialist-recommended practice</div>
            <ul class="practice-list">
              <li><i class='bx bx-chevron-right'></i>Silent-letter word hunt worksheet, focused on "wh" words</li>
            </ul>
            <div class="specialist-note"><b>Note from Mr. Aquino (Reading Specialist):</b> Quick, isolated fix — check in again after next session to confirm it stuck.</div>
          </div>
        </div>
        <div class="rec-foot">
          <div class="rec-source-note"><i class='bx bx-user-voice'></i>Written by Mr. Aquino · Applied Aug 13, 2026</div>
          <div class="rec-actions">
            <button class="btn-solid done" disabled><i class='bx bx-check-double'></i>Completed</button>
          </div>
        </div>
      </div>

      <div class="empty-state" id="emptyState" style="display:none;">
        <i class='bx bx-bulb'></i>
        <b>No recommendations here yet</b>
        Try a different filter, or write one manually for a student.
      </div>
    </div>

    <div class="tip">
      <div class="tip-icon">
        <i class='bx bx-bot' style="font-size:22px;color:#8b6bd1;"></i>
      </div>
      <div>
        <div class="tip-title">How AI recommendations work</div>
        <div class="tip-text">After each reading session, ReadPilot's AI reviews which words a student mispronounced, skipped, or paused on, then suggests targeted practice. Every suggestion waits for your approval before it's assigned.</div>
      </div>
      <div class="tip-close" id="tipClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </div>
    </div>
  </main>

  <!-- ================= MODAL: Write a Recommendation ================= -->
  <div class="overlay" id="recOverlay">
    <div class="modal">
      <button class="modal-close" id="closeRecModal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      <div class="modal-head">
        <h2>Write a Recommendation</h2>
        <div class="sub">Add your own guidance alongside the AI's suggestions</div>
      </div>
      <div class="form-row">
        <label>Student</label>
        <select id="formStudent"></select>
      </div>
      <div class="form-row">
        <label>Related session / book (optional)</label>
        <input type="text" id="formBook" placeholder="e.g. The Three Little Pigs">
      </div>
      <div class="form-row">
        <label>Skill focus</label>
        <input type="text" id="formSkill" placeholder="e.g. Blending consonant clusters">
      </div>
      <div class="form-row">
        <label>Recommended practice &amp; notes</label>
        <textarea id="formNotes" placeholder="Describe the practice activities and any context for this student..."></textarea>
      </div>
      <div class="modal-actions">
        <button class="btn-outline" id="cancelRecModal">Cancel</button>
        <button class="btn-solid" id="saveRecModal"><i class='bx bx-check'></i>Save Recommendation</button>
      </div>
    </div>
  </div>

  <!-- ================= TOAST ================= -->
  <div class="toast" id="toast"><i class='bx bx-check-circle'></i><span id="toastMsg">Saved</span></div>

  <script>
    // ---- Hamburger: collapse sidebar ----
    // Toggles the same data-sidebar="collapsed" attribute on <html> that the
    // shared stylesheet's collapsed rules key off of, and persists the choice
    // to localStorage so it stays collapsed/expanded across every page (the
    // inline <head> script above reads it back before paint on load).
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
        /* localStorage unavailable (e.g. private browsing) — toggle still
           works for this page load, it just won't persist across pages */
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
      toastTimer = setTimeout(function(){ toast.classList.remove('show'); }, 2400);
    }

    // ---- Filtering ----
    var tabs = document.querySelectorAll('.tab');
    var cards = [];
    var emptyState = document.getElementById('emptyState');
    var resultsCount = document.getElementById('resultsCount');
    var recList = document.getElementById('recList');
    var recommendations = [];
    var activeFilter = 'all';

    function escapeHtml(value){
      return String(value || '').replace(/[&<>"']/g, function(character){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[character];
      });
    }
    function statusLabel(status){ return status === 'pending' ? 'Pending Review' : status === 'approved' ? 'Approved' : 'Applied'; }
    function renderCard(rec){
      var initial = escapeHtml((rec.studentName || '?').charAt(0).toUpperCase());
      var sourceLabel = rec.source === 'ai' ? 'AI Agent' : rec.source === 'specialist' ? 'Reading Specialist' : 'Teacher Written';
      var icon = rec.source === 'ai' ? 'bx-bot' : rec.source === 'specialist' ? 'bx-user-voice' : 'bx-edit-alt';
      var action = rec.status === 'pending' ? '<button class="btn-solid js-approve"><i class="bx bx-check"></i>Approve &amp; Assign</button>' : rec.status === 'approved' ? '<button class="btn-solid js-apply"><i class="bx bx-send"></i>Mark as Applied</button>' : '<button class="btn-solid done" disabled><i class="bx bx-check-double"></i>Completed</button>';
      return '<div class="rec-card" data-id="'+rec.id+'" data-source="'+escapeHtml(rec.source)+'" data-status="'+escapeHtml(rec.status)+'" data-name="'+escapeHtml(rec.studentName)+'">'
        + '<div class="rec-top"><div class="rec-who"><div class="rec-avatar" style="background:'+escapeHtml(rec.color || '#6fbf5a')+'">'+initial+'</div><div><div class="rec-name">'+escapeHtml(rec.studentName)+'</div><div class="rec-meta">'+escapeHtml(rec.relatedBook || 'Teacher recommendation')+'</div></div></div><div class="rec-tags"><span class="src-pill '+escapeHtml(rec.source)+'"><i class="bx '+icon+'"></i>'+sourceLabel+'</span><span class="status-pill '+escapeHtml(rec.status)+'">'+statusLabel(rec.status)+'</span></div></div>'
        + '<div class="rec-body"><div><div class="rec-section-label"><i class="bx bx-target-lock"></i>Skill focus</div><div class="skill-focus">'+escapeHtml(rec.skillFocus)+'</div></div><div><div class="rec-section-label"><i class="bx bx-bulb"></i>Recommended practice</div><ul class="practice-list"><li><i class="bx bx-chevron-right"></i>'+escapeHtml(rec.practiceNotes)+'</li></ul></div></div>'
        + '<div class="rec-foot"><div class="rec-source-note"><i class="bx '+icon+'"></i>Saved recommendation</div><div class="rec-actions"><button class="btn-outline danger js-dismiss"><i class="bx bx-x"></i>Dismiss</button>'+action+'</div></div></div>';
    }
    function renderRecommendations(){
      recList.querySelectorAll('.rec-card').forEach(function(card){ card.remove(); });
      recommendations.slice().reverse().forEach(function(rec){ recList.insertAdjacentHTML('afterbegin', renderCard(rec)); });
      cards = Array.prototype.slice.call(recList.querySelectorAll('.rec-card'));
      applyFilter(activeFilter);
      bindRecommendationActions();
    }

    function applyFilter(filter){
      var visible = 0;
      cards.forEach(function(card){
        var match = filter === 'all'
          || card.dataset.source === filter
          || card.dataset.status === filter;
        card.classList.toggle('is-hidden', !match);
        if (match) visible++;
      });
      emptyState.style.display = visible === 0 ? 'block' : 'none';
      resultsCount.textContent = visible + (visible === 1 ? ' recommendation' : ' recommendations');
    }

    tabs.forEach(function(tab){
      tab.addEventListener('click', function(){
        tabs.forEach(function(t){ t.classList.remove('active'); });
        this.classList.add('active');
        activeFilter = this.dataset.filter;
        applyFilter(this.dataset.filter);
      });
    });

    // ---- Sorting ----
    document.getElementById('sortSelect').addEventListener('change', function(){
      var order = this.value;
      var list = Array.prototype.slice.call(cards);
      var statusRank = { pending: 0, approved: 1, applied: 2 };
      if (order === 'name'){
        list.sort(function(a,b){ return a.dataset.name.localeCompare(b.dataset.name); });
      } else if (order === 'status'){
        list.sort(function(a,b){ return statusRank[a.dataset.status] - statusRank[b.dataset.status]; });
      } else {
        list.sort(function(a,b){ return list.indexOf(a) - list.indexOf(b); }); // keep original / "newest" order
      }
      list.forEach(function(card){ recList.insertBefore(card, emptyState); });
    });

    // ---- Approve / Apply / Dismiss actions ----
    function bindRecommendationActions(){
      recList.querySelectorAll('.js-approve, .js-apply, .js-dismiss').forEach(function(button){
        button.addEventListener('click', function(){
          var card = this.closest('.rec-card');
          var action = this.classList.contains('js-approve') ? 'approve' : this.classList.contains('js-apply') ? 'apply' : 'dismiss';
          fetch('recommendation-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:action, id:card.dataset.id})})
            .then(function(response){ return response.json().then(function(data){ return {ok:response.ok, data:data}; }); })
            .then(function(result){ if(!result.ok) throw new Error(result.data.error || 'Unable to update recommendation'); return loadRecommendations(); })
            .then(function(){ showToast(action === 'approve' ? 'Recommendation approved and assigned' : action === 'apply' ? 'Marked as applied' : 'Recommendation dismissed'); })
            .catch(function(error){ showToast(error.message); });
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
    document.getElementById('saveRecModal').addEventListener('click', function(){
      var studentId = document.getElementById('formStudent').value;
      var skill = document.getElementById('formSkill').value.trim();
      var notes = document.getElementById('formNotes').value.trim();
      if(!studentId || !skill || !notes){ showToast('Student, skill focus, and notes are required'); return; }
      fetch('recommendation-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'save', student_id:studentId, related_book:document.getElementById('formBook').value.trim(), skill_focus:skill, practice_notes:notes})})
        .then(function(response){ return response.json().then(function(data){ return {ok:response.ok, data:data}; }); })
        .then(function(result){ if(!result.ok) throw new Error(result.data.error || 'Unable to save recommendation'); closeModal(); document.getElementById('formSkill').value=''; document.getElementById('formNotes').value=''; return loadRecommendations(); })
        .then(function(){ showToast('Recommendation saved'); })
        .catch(function(error){ showToast(error.message); });
    });

    function loadRecommendations(){
      return fetch('recommendation-api.php').then(function(response){ return response.json().then(function(data){ if(!response.ok) throw new Error(data.error || 'Unable to load recommendations'); return data; }); }).then(function(data){
        recommendations = data.recommendations || [];
        renderRecommendations();
      }).catch(function(error){ showToast(error.message); });
    }
    fetch('student-api.php').then(function(response){ return response.json(); }).then(function(data){
      var select = document.getElementById('formStudent');
      select.innerHTML = (data.students || []).map(function(student){ return '<option value="'+student.id+'">'+escapeHtml(student.name)+'</option>'; }).join('');
    }).then(loadRecommendations);
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>