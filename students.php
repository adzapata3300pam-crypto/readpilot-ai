<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Sections</title>
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
  /* Editable student name in the profile pop-up */
  .name-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
  .name-row h2{margin:0;}
  .name-edit-btn{
    display:inline-flex;align-items:center;gap:5px;border:1px solid var(--border);background:var(--bg);color:var(--green-dark);
    border-radius:20px;padding:4px 11px;font-size:12px;font-weight:800;cursor:pointer;font-family:inherit;
  }
  .name-edit-btn .bx{font-size:14px;}
  .name-edit-btn:hover{background:var(--green-light);}
  .name-edit-form{display:flex;align-items:center;gap:6px;flex-wrap:wrap;width:100%;}
  .name-edit-form input{
    flex:1 1 180px;min-width:0;border:1.5px solid var(--green);border-radius:10px;padding:8px 11px;
    font-family:inherit;font-size:15px;font-weight:700;color:var(--ink);background:var(--card);outline:none;
  }
  .name-edit-form button{
    border:none;border-radius:10px;padding:8px 12px;font-family:inherit;font-size:12.5px;font-weight:800;cursor:pointer;
    display:inline-flex;align-items:center;gap:4px;
  }
  .name-edit-form .name-save{background:var(--green);color:#fff;}
  .name-edit-form .name-save:disabled{opacity:.6;cursor:wait;}
  .name-edit-form .name-cancel{background:var(--bg);color:var(--muted);}
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
        <a class="nav-item active" href="students.php"><i class="bx bx-group"></i><span class="label">Sections</span></a>
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
        <a class="teacher-logout-btn" href="logout.php" title="Log out" aria-label="Log out"><i class='bx bx-log-out'></i></a>
      </div>
      <div class="quote">"Every page a child reads today is a step toward a brighter tomorrow." <span class="heart">♥</span></div>
    </div>
  </aside>

  <!-- ================= MAIN ================= -->
  <main class="main">
    <div class="topbar">
      <div class="title-block">
        <h1>Sections</h1>
        <div class="greet" id="greetLine">Choose a section to manage its students and reading records.</div>
      </div>
      <div class="topbar-actions">
        <div class="search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" id="searchInput" placeholder="Search by name or book...">
        </div>
        <button class="btn-new" id="addStudentBtn" disabled>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
          Add Student
        </button>
      </div>
    </div>

    <div class="controls">
      <div class="tabs" id="filterTabs">
        <div class="tab active" data-filter="all">All</div>
        <div class="tab" data-filter="ontrack">On Track</div>
        <div class="tab" data-filter="support">Needs Support</div>
        <div class="tab" data-filter="new">New</div>
      </div>
      <div style="display:flex;align-items:center;gap:14px;">
        <span class="results-count" id="resultsCount"></span>
        <select class="sort-select" id="sectionSelect" aria-label="Filter by section"></select>
        <button class="btn-secondary section-edit-btn" id="backToSectionsBtn" type="button" title="Back to sections" aria-label="Back to sections" style="display:none;"><i class='bx bx-grid-alt'></i></button>
        <button class="btn-secondary section-edit-btn" id="editSectionsBtn" type="button" title="Edit section names" aria-label="Edit section names"><i class='bx bx-edit-alt'></i></button>
        <button class="btn-secondary section-edit-btn" id="addSectionBtn" type="button" title="Add a section" aria-label="Add a section"><i class='bx bx-plus'></i></button>
      </div>
    </div>

    <div class="students-grid" id="studentsGrid"></div>

    <div class="tip">
      <div class="tip-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
      </div>
      <div>
        <div class="tip-title">Tip of the day</div>
        <div class="tip-text">Tap any student card to log a session, leave a note, or check their reading history.</div>
      </div>
      <div class="tip-close" id="tipClose">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </div>
    </div>
  </main>

  <!-- ================= EDIT SECTIONS MODAL ================= -->
  <div class="overlay" id="sectionsOverlay">
    <div class="modal">
      <button class="modal-close" id="sectionsCloseBtn" type="button" aria-label="Close section editor">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
      <div class="modal-head">
        <div class="modal-avatar" style="background:var(--teal);"><i class='bx bx-list-ul'></i></div>
        <div>
          <h2>Edit Sections</h2>
          <div class="sub">Rename the three sections for your class</div>
        </div>
      </div>
      <form id="sectionsForm">
        <div class="form-row">
          <label for="sectionName0">Section 1</label>
          <input type="text" id="sectionName0" required>
        </div>
        <div class="form-row">
          <label for="sectionName1">Section 2</label>
          <input type="text" id="sectionName1" required>
        </div>
        <div class="form-row">
          <label for="sectionName2">Section 3</label>
          <input type="text" id="sectionName2" required>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-secondary" id="sectionsCancelBtn">Cancel</button>
          <button type="submit" class="btn-primary"><i class='bx bx-check'></i>Save Sections</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= STUDENT DETAIL MODAL ================= -->
  <div class="overlay" id="detailOverlay">
    <div class="modal" id="detailModal"></div>
  </div>

  <!-- ================= ADD STUDENT MODAL ================= -->
  <div class="overlay" id="addOverlay">
    <div class="modal">
      <button class="modal-close" id="addCloseBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
      <div class="modal-head">
        <div class="modal-avatar" style="background:var(--green);">
          <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M17 11a4 4 0 1 0-3.2-6.4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/><path d="M15 15c3.5 0 6 2 6 6"/></svg>
        </div>
        <div>
          <h2>Add a Student</h2>
          <div class="sub">They'll show up on your dashboard right away</div>
        </div>
      </div>
      <form id="addForm">
        <div class="form-row">
          <label for="newName">Full name</label>
          <input type="text" id="newName" placeholder="Enter the student's full name" required>
        </div>
        <div class="form-row">
          <label for="newSection">Section</label>
          <select class="sort-select" id="newSection" required></select>
        </div>
        <div class="form-row">
          <label for="newBook">Currently reading (optional)</label>
          <select class="sort-select" id="newBook">
            <option value="">No book assigned</option>
          </select>
        </div>
        <div class="form-row">
          <label>Avatar color</label>
          <div class="swatches" id="colorSwatches"></div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-secondary" id="addCancelBtn">Cancel</button>
          <button type="submit" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Add Student
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= ADD SECTION MODAL ================= -->
  <div class="overlay" id="sectionAddOverlay">
    <div class="modal section-add-modal">
      <button class="modal-close" id="sectionAddCloseBtn" type="button" aria-label="Close add section dialog">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
      <div class="modal-head">
        <div class="modal-avatar" style="background:var(--teal);"><i class='bx bx-layer-plus'></i></div>
        <div>
          <h2>Add a Section</h2>
          <div class="sub">Create a new class group for your students</div>
        </div>
      </div>
      <form id="sectionAddForm">
        <div class="form-row">
          <label for="newSectionName">Section name</label>
          <input type="text" id="newSectionName" maxlength="80" placeholder="e.g. Section D" autocomplete="off" required>
          <div class="field-hint"><span>Choose a name your students will recognize.</span><span id="sectionNameCount">0/80</span></div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-secondary" id="sectionAddCancelBtn">Cancel</button>
          <button type="submit" class="btn-primary" id="sectionAddSubmitBtn"><i class='bx bx-plus'></i>Add Section</button>
        </div>
      </form>
    </div>
  </div>

  <div class="toast" id="toast"><i class='bx bx-check-circle'></i><span id="toastMsg"></span></div>

  <script>
    // ============================================================
    // Data
    // ============================================================
    const COLORS = [
      {name:'green', hex:'#6fbf5a'},
      {name:'purple', hex:'#8b6bd1'},
      {name:'orange', hex:'#f2a13a'},
      {name:'red', hex:'#ea5d5d'},
      {name:'teal', hex:'#4fa3b8'},
      {name:'tan', hex:'#c9924d'},
    ];

    // Every "currently reading" book below is pulled straight from the
    // Resources library (resources.php's LIBRARY array) so a student's
    // book is always something that's actually assignable/clickable
    // there — no titles invented just for this page.
    const LIBRARY_TITLES = [
      "The Lion and the Mouse",
      "Journey to the Stars",
      "The Three Little Pigs",
      "A Rainy Day Surprise",
      "How Butterflies Are Born",
      "The Kind Knight",
      "Our Solar System",
      "The Grumpy Garden Gnome"
    ];

    let sectionNames = [];
    let sectionIds = {};
    let students = [];
    let activeFilter = 'all';
    let searchTerm = '';
    let sectionFilter = 'all';
    let selectedColor = COLORS[0].hex;

    // ============================================================
    // Helpers
    // ============================================================
    function initials(name){
      return name.split(' ').filter(Boolean).map(p=>p[0]).slice(0,2).join('').toUpperCase();
    }
    function escapeHtml(value){
      return String(value).replace(/[&<>"']/g, character=>({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[character]));
    }
    function statusLabel(s){
      if(s.status==='support') return 'Needs Support';
      if(s.status==='new') return 'New';
      if(s.status==='inactive') return 'Deactivated';
      return 'On Track';
    }
    function ringColor(accuracy){
      if(accuracy>=90) return 'var(--green-dark)';
      if(accuracy>=80) return 'var(--orange)';
      return 'var(--red)';
    }
    function showToast(msg){
      const t = document.getElementById('toast');
      document.getElementById('toastMsg').textContent = msg;
      t.classList.add('show');
      clearTimeout(window.__toastTimer);
      window.__toastTimer = setTimeout(()=>t.classList.remove('show'), 2800);
    }

    function saveStudents(){
      // Roster mutations are persisted through student-api.php.
    }

    async function databaseRequest(data){
      const response = await fetch('student-api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
      });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'Unable to save student data');
      return result;
    }

    async function loadDatabaseRoster(){
      document.getElementById('addStudentBtn').disabled = true;
      const response = await fetch('student-api.php');
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'Unable to load students');
      if (!Array.isArray(result.sections) || !Array.isArray(result.students)) throw new Error('Student data is incomplete');
      sectionIds = {};
      result.sections.forEach(section => { sectionIds[section.name] = section.id; });
      sectionNames = result.sections.map(section => section.name);
      students = result.students;
      document.getElementById('addStudentBtn').disabled = sectionNames.length === 0;
      renderSectionOptions();
      renderAll();
    }

    function renderSectionOptions(){
      const sectionSelect = document.getElementById('sectionSelect');
      const newSection = document.getElementById('newSection');
      sectionSelect.innerHTML = '';
      newSection.innerHTML = '';

      const allOption = document.createElement('option');
      allOption.value = 'all';
      allOption.textContent = 'All Sections';
      sectionSelect.appendChild(allOption);

      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = 'Choose a section';
      newSection.appendChild(placeholder);

      sectionNames.forEach(name=>{
        const filterOption = document.createElement('option');
        filterOption.value = name;
        filterOption.textContent = name;
        sectionSelect.appendChild(filterOption);

        const newStudentOption = document.createElement('option');
        newStudentOption.value = sectionIds[name] || '';
        newStudentOption.textContent = name;
        newSection.appendChild(newStudentOption);
      });
      sectionSelect.value = sectionFilter;
      sectionSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function openSectionsModal(){
      sectionNames.forEach((name,index)=>{
        document.getElementById('sectionName'+index).value = name;
      });
      document.getElementById('sectionsOverlay').classList.add('open');
    }
    function closeSectionsModal(){
      document.getElementById('sectionsOverlay').classList.remove('open');
    }

    // ============================================================
    // Grid rendering
    // ============================================================
    function getFilteredSorted(){
      let list = students.filter(s=>{
        if(activeFilter!=='all' && s.status!==activeFilter) return false;
        if(sectionFilter!=='all' && s.section!==sectionFilter) return false;
        if(searchTerm){
          const q = searchTerm.toLowerCase();
          if(!s.name.toLowerCase().includes(q) && !s.book.toLowerCase().includes(q)) return false;
        }
        return true;
      });
      list.sort((a,b)=>a.name.localeCompare(b.name));
      return list;
    }

    function renderGrid(){
      const grid = document.getElementById('studentsGrid');
      if(sectionFilter === 'all' && !searchTerm){
        document.getElementById('filterTabs').style.display = 'none';
        document.getElementById('sectionSelect').style.display = 'none';
        document.getElementById('sectionSelect').dataset.sectionOverview = 'true';
        grid.innerHTML = sectionNames.map(name=>{
          const members = students.filter(student=>student.section === name);
          const active = members.filter(student=>student.status !== 'inactive').length;
          const support = members.filter(student=>student.status === 'support').length;
          const activePercent = members.length ? Math.round((active / members.length) * 100) : 0;
          return `<div class="section-card" data-section="${escapeHtml(name)}">
            <div class="section-card-icon"><i class='bx bx-group'></i></div>
            <h2>${escapeHtml(name)}</h2>
            <p>${members.length} ${members.length === 1 ? 'student' : 'students'} · ${active} active</p>
            <div class="section-card-stats"><div class="section-card-stat"><strong>${active}</strong><span>Active</span></div><div class="section-card-stat"><strong>${support}</strong><span>Support</span></div></div>
            <div class="section-card-progress"><span style="width:${activePercent}%"></span></div>
            <div class="section-card-foot"><span>${support ? support + ' need support' : 'All on track'}</span><span>Open section <i class='bx bx-right-arrow-alt'></i></span></div>
          </div>`;
        }).join('');
        document.getElementById('resultsCount').textContent = sectionNames.length + (sectionNames.length === 1 ? ' section' : ' sections');
        document.getElementById('backToSectionsBtn').style.display = 'none';
        grid.querySelectorAll('.section-card').forEach(card=>card.addEventListener('click', ()=>{
          sectionFilter = card.dataset.section;
          document.getElementById('sectionSelect').value = sectionFilter;
          document.getElementById('backToSectionsBtn').style.display = 'inline-flex';
          history.pushState({section: sectionFilter}, '', '#section=' + encodeURIComponent(sectionFilter));
          renderGrid();
        }));
        return;
      }
      document.getElementById('filterTabs').style.display = 'flex';
      document.getElementById('sectionSelect').style.display = '';
      delete document.getElementById('sectionSelect').dataset.sectionOverview;
      document.getElementById('backToSectionsBtn').style.display = 'inline-flex';
      const list = getFilteredSorted();
      document.getElementById('resultsCount').textContent = list.length + (list.length===1 ? ' student' : ' students');

      if(list.length===0){
        grid.innerHTML = `
          <div class="empty-state">
            <i class='bx bx-search-alt bx'></i>
            <b>No students match</b>
            Try a different search term or filter.
          </div>`;
        return;
      }

      grid.innerHTML = list.map(s=>`
        <div class="student-card" data-id="${s.id}">
          <div class="sc-top">
            <div class="sc-id">
              <div class="sc-avatar" style="background:${s.color}">${initials(s.name)}</div>
              <div>
                <div class="sc-name">${escapeHtml(s.name)}</div>
                <div class="sc-grade">${s.section}</div>
              </div>
            </div>
            <div class="sc-status ${s.status}">${statusLabel(s)}</div>
          </div>
          <div class="sc-book">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2Z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7Z"/></svg>
            <span class="b">${s.book || 'No book assigned'}</span>
          </div>
          <div class="sc-stats">
            <div class="sc-stat">
              <div class="v">${s.sessions>0 ? s.wpm : '—'}</div>
              <div class="l">WPM</div>
            </div>
            <div class="sc-stat">
              <div class="v">${s.booksCompleted}</div>
              <div class="l">Books</div>
            </div>
            <div class="sc-stat">
              <div class="v">${s.sessions}</div>
              <div class="l">Sessions</div>
            </div>
            <div class="acc-ring" style="--pct:${s.sessions>0?s.accuracy:0}; --ring-color:${ringColor(s.accuracy)};">
              <div class="acc-ring-inner">${s.sessions>0 ? s.accuracy+'%' : '—'}</div>
            </div>
          </div>
          <div class="sc-foot">
            <div class="sc-last">${s.sessions>0 ? s.lastActive : 'No sessions yet'}</div>
            <div class="sc-view">
              View Profile
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
            </div>
          </div>
        </div>
      `).join('');

      grid.querySelectorAll('.student-card').forEach(card=>{
        card.addEventListener('click', ()=>openDetail(Number(card.dataset.id)));
      });
    }

    function renderAll(){
      renderGrid();
    }

    // ============================================================
    // Detail modal
    // ============================================================
    function renderDetail(s){
      const modal = document.getElementById('detailModal');
      modal.innerHTML = `
        <button class="modal-close" id="detailCloseBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
        <div class="modal-head">
          <div class="modal-avatar" style="background:${s.color}">${initials(s.name)}</div>
          <div>
            <div class="name-row" id="nameRow">
              <h2 id="detailName">${escapeHtml(s.name)}</h2>
              <button type="button" class="name-edit-btn" id="editNameBtn" aria-label="Edit student name"><i class='bx bx-edit-alt'></i> Edit name</button>
            </div>
            <div class="sub">${s.section} • <span class="sc-status ${s.status}" style="padding:3px 8px;">${statusLabel(s)}</span></div>
          </div>
        </div>

        <div class="modal-stats">
          <div class="m-stat"><div class="v">${s.sessions>0 ? s.wpm : '—'}</div><div class="l">Avg WPM</div></div>
          <div class="m-stat"><div class="v">${s.sessions>0 ? s.accuracy+'%' : '—'}</div><div class="l">Accuracy</div></div>
          <div class="m-stat"><div class="v">${s.sessions}</div><div class="l">Sessions</div></div>
          <div class="m-stat"><div class="v">${s.booksCompleted}</div><div class="l">Books Read</div></div>
        </div>

        <div class="sc-book" style="margin-bottom:4px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2Z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7Z"/></svg>
          <span class="b">Currently reading: ${s.book || 'Not set'}</span>
        </div>

        <div class="modal-section-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg>
          Recent Sessions
        </div>
        <div id="logList">
          ${s.log.length===0
            ? `<div class="no-log">No sessions logged yet. Tap "Log a Session" to add one.</div>`
            : s.log.slice(0,5).map(l=>`
              <div class="log-row">
                <div>
                  <div class="log-book">${l.book}</div>
                  <div class="log-date">${l.date}</div>
                </div>
                <div class="log-wpm">${l.wpm} WPM • ${l.accuracy}%</div>
              </div>`).join('')
          }
        </div>

        <div class="modal-section-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
          Teacher Notes
        </div>
        <textarea class="notes" id="notesArea" placeholder="Add a private note about this student...">${s.notes}</textarea>

        <div class="modal-actions">
          <button class="btn-primary" id="logSessionBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Log a Session
          </button>
          <button class="btn-secondary" id="saveNoteBtn">Save Note</button>
        </div>
          <button class="btn-danger-text" id="removeBtn">Deactivate Student</button>
      `;

      document.getElementById('detailCloseBtn').addEventListener('click', closeDetail);
      document.getElementById('logSessionBtn').addEventListener('click', ()=>simulateSession(s.id));
      document.getElementById('saveNoteBtn').addEventListener('click', ()=>{
        s.notes = document.getElementById('notesArea').value;
        databaseRequest({action:'save_note', id:s.id, notes:s.notes})
          .then(()=>showToast(`Note saved for ${s.name}`)).catch(error=>showToast(error.message));
      });
      document.getElementById('removeBtn').addEventListener('click', ()=>deactivateStudent(s.id));
      document.getElementById('editNameBtn').addEventListener('click', ()=>startNameEdit(s));
    }

    // ------------------------------------------------------------
    // Edit a student's name (saved through student-api.php,
    // action "update_student")
    // ------------------------------------------------------------
    function startNameEdit(s){
      const row = document.getElementById('nameRow');
      row.innerHTML = `
        <form class="name-edit-form" id="nameEditForm">
          <input type="text" id="nameEditInput" maxlength="120" value="${escapeHtml(s.name)}" aria-label="Student full name" required>
          <button type="submit" class="name-save" id="nameSaveBtn"><i class='bx bx-check'></i> Save</button>
          <button type="button" class="name-cancel" id="nameCancelBtn">Cancel</button>
        </form>`;
      const input = document.getElementById('nameEditInput');
      input.focus();
      input.select();

      const cancel = ()=>{
        const notes = document.getElementById('notesArea');
        if(notes) s.notes = notes.value;   // keep any unsaved note text
        renderDetail(s);
      };
      document.getElementById('nameCancelBtn').addEventListener('click', cancel);
      input.addEventListener('keydown', e=>{
        if(e.key==='Escape'){ e.stopPropagation(); cancel(); }   // don't close the whole pop-up
      });

      document.getElementById('nameEditForm').addEventListener('submit', e=>{
        e.preventDefault();
        const newName = input.value.replace(/\s+/g,' ').trim();
        if(!newName){ showToast('Please enter a name'); return; }
        if(newName === s.name){ cancel(); return; }
        // The server rejects duplicate names across the teacher's whole roster, so check the same way here.
        const clash = students.some(x=>x.id!==s.id && x.name.toLowerCase()===newName.toLowerCase());
        if(clash){ showToast(`A student named ${newName} already exists`); return; }

        const saveBtn = document.getElementById('nameSaveBtn');
        saveBtn.disabled = true;
        databaseRequest({action:'update_student', id:s.id, name:newName})
          .then(result=>{
            const notes = document.getElementById('notesArea');
            if(notes) s.notes = notes.value;
            s.name = result && result.name ? result.name : newName;   // use the name exactly as the server saved it
            renderDetail(s);
            renderAll();
            showToast('Name updated to ' + s.name);
          })
          .catch(error=>{ saveBtn.disabled = false; showToast(error.message); });
      });
    }

    function openDetail(id){
      const s = students.find(x=>x.id===id);
      if(!s) return;
      renderDetail(s);
      document.getElementById('detailOverlay').classList.add('open');
    }
    function closeDetail(){
      document.getElementById('detailOverlay').classList.remove('open');
    }
    document.getElementById('detailOverlay').addEventListener('click', e=>{
      if(e.target.id==='detailOverlay') closeDetail();
    });

    function simulateSession(id){
      const s = students.find(x=>x.id===id);
      if(!s) return;
      const baseWpm = s.wpm>0 ? s.wpm : 180;
      const baseAcc = s.accuracy>0 ? s.accuracy : 82;
      const newWpm = Math.max(90, Math.round(baseWpm + (Math.random()*30-15)));
      const newAcc = Math.min(100, Math.max(60, Math.round(baseAcc + (Math.random()*8-4))));

      s.log.unshift({book:s.book || 'Free Reading', date:'Just now', wpm:newWpm, accuracy:newAcc});
      s.sessions += 1;
      s.sessionsThisWeek += 1;
      s.wpm = Math.round(((s.wpm||newWpm) * (s.sessions-1) + newWpm) / s.sessions);
      s.accuracy = Math.round(((s.accuracy||newAcc) * (s.sessions-1) + newAcc) / s.sessions);
      if(s.sessions % 3 === 0) s.booksCompleted += 1;
      s.lastActive = 'Just now';
      s.status = s.accuracy < 82 ? 'support' : 'ontrack';

      renderDetail(s);
      renderAll();
      databaseRequest({action:'record_session', student_id:s.id, book:s.book || 'Free Reading', wpm:newWpm, accuracy:newAcc})
        .then(()=>showToast(`Session logged for ${s.name}: ${newWpm} WPM, ${newAcc}% accuracy`))
        .catch(error=>showToast(error.message));
    }

    function deactivateStudent(id){
      const s = students.find(x=>x.id===id);
      if(!s) return;
      const reason = prompt(`Why are you deactivating ${s.name}? This record and all history will be preserved.`);
      if (reason === null || !reason.trim()) {
        showToast('A reason is required');
        return;
      }
      databaseRequest({action:'deactivate_student', id:id, reason:reason.trim()})
        .then(()=>loadDatabaseRoster())
        .then(()=>{ closeDetail(); showToast(`${s.name} was deactivated and preserved`); })
        .catch(error=>showToast(error.message));
    }

    // ============================================================
    // Add student modal
    // ============================================================
    function renderSwatches(){
      const wrap = document.getElementById('colorSwatches');
      wrap.innerHTML = COLORS.map(c=>`
        <div class="swatch ${c.hex===selectedColor?'selected':''}" style="background:${c.hex}" data-hex="${c.hex}"></div>
      `).join('');
      wrap.querySelectorAll('.swatch').forEach(sw=>{
        sw.addEventListener('click', ()=>{
          selectedColor = sw.dataset.hex;
          renderSwatches();
        });
      });
    }

    // Populates the "Currently reading" dropdown in the Add Student modal
    // from the same LIBRARY_TITLES list used to validate every student's
    // book above, so a newly added student can only be set to read
    // something that actually exists in the Resources library.
    function buildNewBookSelect(){
      const sel = document.getElementById('newBook');
      sel.innerHTML = `<option value="">No book assigned</option>` +
        LIBRARY_TITLES.map(t => `<option value="${t}">${t}</option>`).join('');
    }

    function openAddModal(){
      document.getElementById('addForm').reset();
      selectedColor = COLORS[0].hex;
      renderSwatches();
      buildNewBookSelect();
      document.getElementById('addOverlay').classList.add('open');
    }
    function closeAddModal(){
      document.getElementById('addOverlay').classList.remove('open');
    }
    document.getElementById('addStudentBtn').addEventListener('click', openAddModal);
    document.getElementById('addCloseBtn').addEventListener('click', closeAddModal);
    document.getElementById('addCancelBtn').addEventListener('click', closeAddModal);
    document.getElementById('addOverlay').addEventListener('click', e=>{
      if(e.target.id==='addOverlay') closeAddModal();
    });

    document.getElementById('addForm').addEventListener('submit', e=>{
      e.preventDefault();
      const name = document.getElementById('newName').value.trim();
      if(!name) return;
      const sectionId = Number(document.getElementById('newSection').value);
      if(!Number.isInteger(sectionId) || sectionId < 1){
        showToast('Choose an available section');
        return;
      }
      const book = document.getElementById('newBook').value;

      databaseRequest({action:'save_student', name:name, section_id:sectionId, color:selectedColor, book:book})
        .then(()=>loadDatabaseRoster())
        .then(()=>{ closeAddModal(); showToast(`${name} was added to your class`); })
        .catch(error=>showToast(error.message));
    });

    // ============================================================
    // Controls: search, filter tabs, sort
    // ============================================================
    document.getElementById('searchInput').addEventListener('input', e=>{
      searchTerm = e.target.value;
      renderGrid();
    });

    document.getElementById('filterTabs').addEventListener('click', e=>{
      const tab = e.target.closest('.tab');
      if(!tab) return;
      document.querySelectorAll('#filterTabs .tab').forEach(t=>t.classList.remove('active'));
      tab.classList.add('active');
      activeFilter = tab.dataset.filter;
      renderGrid();
    });

    document.getElementById('sectionSelect').addEventListener('change', e=>{
      sectionFilter = e.target.value;
      if(sectionFilter === 'all') document.getElementById('backToSectionsBtn').style.display = 'none';
      renderGrid();
    });
    document.getElementById('backToSectionsBtn').addEventListener('click', ()=>{
      sectionFilter = 'all';
      document.getElementById('sectionSelect').value = 'all';
      document.getElementById('backToSectionsBtn').style.display = 'none';
      history.pushState({section: null}, '', window.location.pathname);
      renderGrid();
    });

    window.addEventListener('popstate', event=>{
      sectionFilter = event.state && event.state.section ? event.state.section : 'all';
      document.getElementById('sectionSelect').value = sectionFilter;
      document.getElementById('backToSectionsBtn').style.display = sectionFilter === 'all' ? 'none' : 'inline-flex';
      renderGrid();
    });
    document.getElementById('editSectionsBtn').addEventListener('click', openSectionsModal);
    function openAddSectionModal(){
      document.getElementById('sectionAddForm').reset();
      document.getElementById('sectionNameCount').textContent = '0/80';
      document.getElementById('sectionAddSubmitBtn').disabled = false;
      document.getElementById('sectionAddOverlay').classList.add('open');
      document.getElementById('newSectionName').focus();
    }
    function closeAddSectionModal(){
      document.getElementById('sectionAddOverlay').classList.remove('open');
    }
    document.getElementById('addSectionBtn').addEventListener('click', openAddSectionModal);
    document.getElementById('sectionAddCloseBtn').addEventListener('click', closeAddSectionModal);
    document.getElementById('sectionAddCancelBtn').addEventListener('click', closeAddSectionModal);
    document.getElementById('sectionAddOverlay').addEventListener('click', e=>{
      if(e.target.id==='sectionAddOverlay') closeAddSectionModal();
    });
    document.getElementById('newSectionName').addEventListener('input', e=>{
      document.getElementById('sectionNameCount').textContent = `${e.target.value.length}/80`;
    });
    document.getElementById('sectionAddForm').addEventListener('submit', e=>{
      e.preventDefault();
      const name = document.getElementById('newSectionName').value.trim();
      if(!name) return;
      const submitButton = document.getElementById('sectionAddSubmitBtn');
      submitButton.disabled = true;
      databaseRequest({action:'add_section', name:name})
        .then(()=>loadDatabaseRoster())
        .then(()=>{ closeAddSectionModal(); showToast(`${name} was added`); })
        .catch(error=>showToast(error.message))
        .finally(()=>{ submitButton.disabled = false; });
    });
    document.getElementById('sectionsCloseBtn').addEventListener('click', closeSectionsModal);
    document.getElementById('sectionsCancelBtn').addEventListener('click', closeSectionsModal);
    document.getElementById('sectionsOverlay').addEventListener('click', e=>{
      if(e.target.id==='sectionsOverlay') closeSectionsModal();
    });
    document.getElementById('sectionsForm').addEventListener('submit', e=>{
      e.preventDefault();
      const updatedNames = [0,1,2].map(index=>document.getElementById('sectionName'+index).value.trim());
      if(updatedNames.some(name=>!name) || new Set(updatedNames.map(name=>name.toLowerCase())).size!==updatedNames.length){
        showToast('Section names must be unique');
        return;
      }

      const previousNames = sectionNames;
      students.forEach(student=>{
        const sectionIndex = previousNames.indexOf(student.section);
        if(sectionIndex!==-1) student.section = updatedNames[sectionIndex];
      });
      if(sectionFilter!=='all'){
        const selectedIndex = previousNames.indexOf(sectionFilter);
        if(selectedIndex!==-1) sectionFilter = updatedNames[selectedIndex];
      }
      sectionNames = updatedNames;
      databaseRequest({action:'update_sections', names:JSON.stringify(updatedNames)})
        .then(()=>{ sectionNames = updatedNames; renderSectionOptions(); renderGrid(); closeSectionsModal(); showToast('Section names updated'); })
        .catch(error=>showToast(error.message));
    });

    // ============================================================
    // Sidebar toggle, bell, tip banner
    // ============================================================
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


    document.getElementById('tipClose').addEventListener('click', function(){
      this.closest('.tip').style.display = 'none';
    });

    // Keyboard: Escape closes modals
    document.addEventListener('keydown', e=>{
      if(e.key==='Escape'){ closeDetail(); closeAddModal(); closeSectionsModal(); }
    });

    // ============================================================
    // Init
    // ============================================================
    renderSectionOptions();
    renderAll();
    history.replaceState({section: null}, '', window.location.pathname);
    loadDatabaseRoster().catch(error=>showToast(error.message));
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>