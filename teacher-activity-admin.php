<?php
require_once __DIR__ . '/auth-guard.php';
require_admin();
$adminName = htmlspecialchars(current_user()['full_name'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot Admin — Teacher Activity</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin-style.css">
<script>
(function () {
  try {
    if (localStorage.getItem('readpilot-admin-theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    if (localStorage.getItem('readpilot-admin-sidebar') === 'collapsed') document.documentElement.setAttribute('data-sidebar', 'collapsed');
  } catch (error) {}
})();
</script>
<style>
.activity-controls{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:18px;}
.activity-filters{display:flex;gap:6px;flex-wrap:wrap;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:5px;box-shadow:var(--shadow);}
.activity-filter{border:0;background:transparent;border-radius:9px;padding:8px 13px;color:var(--muted);font:700 13px 'Nunito',sans-serif;cursor:pointer;}
.activity-filter:hover{color:var(--ink);}
.activity-filter.active{background:var(--green-light);color:var(--green-dark);}
.activity-count{font-size:12.5px;font-weight:700;color:var(--muted);}
.activity-row{display:flex;align-items:center;gap:14px;padding:15px 4px;border-bottom:1px solid var(--border);}
.activity-row:last-child{border-bottom:0;}
.activity-avatar{width:40px;height:40px;display:grid;place-items:center;flex:0 0 auto;border-radius:50%;background:var(--purple-light);color:var(--purple);font-size:14px;font-weight:800;}
.activity-info{flex:1;min-width:180px;}
.activity-teacher{font-size:14px;font-weight:800;color:var(--ink);}
.activity-email{margin-left:7px;color:var(--muted);font-size:11.5px;font-weight:600;}
.activity-summary{margin-top:3px;color:var(--muted);font-size:12.5px;font-weight:600;line-height:1.45;}
.activity-date{flex:0 0 auto;color:var(--muted);font-size:12px;font-weight:600;white-space:nowrap;}
.activity-type{flex:0 0 auto;min-width:94px;text-align:center;padding:5px 9px;border-radius:20px;background:var(--green-light);color:var(--green-dark);font-size:11.5px;font-weight:800;}
.activity-type.resource{background:var(--purple-light);color:var(--purple);}
.activity-type.quiz{background:var(--orange-light);color:var(--orange);}
.activity-empty{padding:42px 20px;text-align:center;color:var(--muted);font-size:13px;font-weight:700;}
.activity-empty .bx{display:block;margin-bottom:8px;color:var(--green-dark);font-size:32px;}
.activity-error{color:var(--red);}

/* Refresh button — matches the date pill / dropdown triggers */
.activity-refresh{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--card);border:1px solid var(--border);border-radius:12px;
  padding:9px 16px;box-shadow:var(--shadow);
  font:700 13px 'Nunito',sans-serif;color:var(--ink);cursor:pointer;
  transition:transform .12s steps(2), box-shadow .12s steps(2), border-color .15s ease, background-color .2s ease;
}
.activity-refresh .bx{font-size:17px;color:var(--green-dark);}
.activity-refresh:hover:not(:disabled){transform:translate(-2px,-2px);box-shadow:2px 2px 0 var(--green-dark), var(--shadow);border-color:var(--green-dark);}
.activity-refresh:active:not(:disabled){transform:translate(0,0);box-shadow:var(--shadow);}
.activity-refresh:focus-visible{outline:2px solid var(--green);outline-offset:2px;}
.activity-refresh:disabled{opacity:.7;cursor:wait;}
.activity-empty .activity-refresh{margin-top:14px;}

/* Pagination */
.activity-pagination{display:flex;flex-direction:row;flex-grow:0;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;padding-top:18px;margin-top:6px;border-top:1px solid var(--border);}
.activity-pagination[hidden]{display:none;}
.page-btn{
  min-width:36px;height:36px;padding:0 10px;
  display:inline-flex;align-items:center;justify-content:center;
  background:var(--card);border:1px solid var(--border);border-radius:10px;
  font:800 13px 'Nunito',sans-serif;color:var(--ink);cursor:pointer;
  transition:transform .12s steps(2), box-shadow .12s steps(2), background-color .15s ease, color .15s ease, border-color .15s ease;
}
.page-btn .bx{font-size:18px;line-height:1;}
.page-btn:hover:not(:disabled):not(.active){background:var(--green-light);color:var(--green-dark);border-color:var(--green-dark);transform:translate(-1px,-1px);}
.page-btn:focus-visible{outline:2px solid var(--green);outline-offset:2px;}
.page-btn.active{background:var(--green);border-color:var(--green);color:#fff;cursor:default;}
.page-btn:disabled{opacity:.4;cursor:not-allowed;}
.page-ellipsis{min-width:24px;text-align:center;color:var(--muted);font-weight:800;user-select:none;}
@media(max-width:720px){.activity-row{align-items:flex-start;flex-wrap:wrap;gap:9px;}.activity-info{min-width:calc(100% - 54px);}.activity-date{margin-left:49px;}.activity-type{margin-left:auto;}}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="pixel-plane" style="--y:14%; --dur:13s; --delay:0s;" aria-hidden="true">
    <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
      <rect x="0" y="0" width="2" height="1" fill="#c3cdf5"/><rect x="0" y="1" width="4" height="1" fill="#c3cdf5"/>
      <rect x="0" y="2" width="6" height="1" fill="#c3cdf5"/><rect x="0" y="3" width="9" height="1" fill="#a6b3ef"/>
      <rect x="0" y="4" width="13" height="1" fill="#8497e9"/><rect x="0" y="5" width="9" height="1" fill="#5f74d6"/>
      <rect x="0" y="6" width="6" height="1" fill="#5f74d6"/><rect x="0" y="7" width="4" height="1" fill="#5f74d6"/>
      <rect x="0" y="8" width="2" height="1" fill="#5f74d6"/>
    </svg>
  </div>
  <div class="pixel-plane" style="--y:74%; --dur:17s; --delay:6s;" aria-hidden="true">
    <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
      <rect x="0" y="0" width="2" height="1" fill="#c3cdf5"/><rect x="0" y="1" width="4" height="1" fill="#c3cdf5"/>
      <rect x="0" y="2" width="6" height="1" fill="#c3cdf5"/><rect x="0" y="3" width="9" height="1" fill="#a6b3ef"/>
      <rect x="0" y="4" width="13" height="1" fill="#8497e9"/><rect x="0" y="5" width="9" height="1" fill="#5f74d6"/>
      <rect x="0" y="6" width="6" height="1" fill="#5f74d6"/><rect x="0" y="7" width="4" height="1" fill="#5f74d6"/>
      <rect x="0" y="8" width="2" height="1" fill="#5f74d6"/>
    </svg>
  </div>
  <div>
    <div class="logo-row">
      <div class="logo-left">
        <div class="logo-icon"><i class="bx bxs-shield-alt-2"></i></div>
        <div class="logo-text"><span class="brand">ReadPilot</span><span class="tagline">Admin Console</span></div>
      </div>
      <button class="hamburger" id="sidebarToggle" aria-label="Toggle navigation"><span class="bar"></span></button>
    </div>
    <nav>
      <a class="nav-item" href="admin.php"><i class="bx bxs-dashboard"></i><span class="label">Dashboard</span></a>
      <a class="nav-item" href="users-admin.php"><i class="bx bx-user-circle"></i><span class="label">User Management</span></a>
      <a class="nav-item" href="gradesec-admin.php"><i class="bx bx-layer"></i><span class="label">Student Records</span></a>
      <a class="nav-item active" href="teacher-activity-admin.php" aria-current="page"><i class="bx bx-pulse"></i><span class="label">Teacher Activity</span></a>
      <a class="nav-item" href="audit-trail-admin.php"><i class="bx bx-history"></i><span class="label">Audit Log</span></a>
      <a class="nav-item" href="settings-admin.php"><i class="bx bx-cog"></i><span class="label">Settings</span></a>
    </nav>
  </div>
  <div class="teacher-card">
    <div class="teacher-row">
      <div class="teacher-row-info">
        <div class="avatar"><?php include __DIR__ . '/profile-avatar.php'; ?></div>
        <div><div class="teacher-name"><?= $adminName ?></div><div class="teacher-role">System Administrator</div></div>
      </div>
      <button class="teacher-logout-btn" id="sidebarLogoutBtn" title="Log out" aria-label="Log out"><i class="bx bx-log-out"></i></button>
    </div>
    <div class="quote">"Good access control is what lets every teacher trust the system." <span class="heart">♥</span></div>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <div class="title-block">
      <h1>Teacher Activity</h1>
      <div class="greet">Recent classroom work recorded across teacher accounts.</div>
    </div>
    <div class="topbar-actions">
      <div class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" id="activitySearch" placeholder="Search teachers or activity..." aria-label="Search teacher activity">
      </div>
      <div class="date-pill" id="todayDate"></div>
    </div>
  </div>

  <div class="activity-controls">
    <div class="activity-filters" role="group" aria-label="Filter teacher activity">
      <button type="button" class="activity-filter active" data-filter="all">All activity</button>
      <button type="button" class="activity-filter" data-filter="roster">Students</button>
      <button type="button" class="activity-filter" data-filter="resource">Resources</button>
      <button type="button" class="activity-filter" data-filter="quiz">Quizzes</button>
      <button type="button" class="activity-filter" data-filter="reading">Reading</button>
    </div>
    <span class="activity-count" id="activityCount" aria-live="polite"></span>
  </div>

  <section class="panel" id="activityPanel" aria-label="Recent teacher activity">
    <div class="panel-head">
      <div class="panel-title"><i class="bx bx-pulse"></i> Activity feed</div>
      <button type="button" class="activity-refresh" id="refreshActivity"><i class="bx bx-refresh"></i> Refresh</button>
    </div>
    <div id="activityList"><div class="activity-empty"><i class="bx bx-loader-alt bx-spin"></i>Loading teacher activity…</div></div>
    <div class="activity-pagination" id="activityPagination" role="navigation" aria-label="Activity pages" hidden></div>
  </section>
</main>

<script>
(function () {
  var activities = [];
  var activeFilter = 'all';
  var PAGE_SIZE = 5;
  var currentPage = 1;
  var searchInput = document.getElementById('activitySearch');
  var list = document.getElementById('activityList');
  var count = document.getElementById('activityCount');
  var todayDate = document.getElementById('todayDate');
  var refreshButton = document.getElementById('refreshActivity');
  var pagination = document.getElementById('activityPagination');
  var panel = document.getElementById('activityPanel');
  var loading = false;
  todayDate.textContent = new Date().toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'});

  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
      return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[character];
    });
  }

  function initials(name) {
    return String(name || 'Teacher').trim().split(/\s+/).slice(0, 2).map(function (part) {
      return part.charAt(0);
    }).join('').toUpperCase();
  }

  function formatDate(value) {
    var parsed = new Date(String(value || '').replace(' ', 'T'));
    return isNaN(parsed.getTime()) ? String(value || '') : parsed.toLocaleString(undefined, {
      month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'2-digit'
    });
  }

  /* Returns e.g. [1, '…', 4, 5, 6, '…', 12] */
  function pageList(current, total) {
    if (total <= 7) {
      var all = [];
      for (var i = 1; i <= total; i++) all.push(i);
      return all;
    }
    var pages = [1];
    var start = Math.max(2, current - 1);
    var end = Math.min(total - 1, current + 1);
    if (current <= 3) { start = 2; end = 4; }
    if (current >= total - 2) { start = total - 3; end = total - 1; }
    if (start > 2) pages.push('…');
    for (var p = start; p <= end; p++) pages.push(p);
    if (end < total - 1) pages.push('…');
    pages.push(total);
    return pages;
  }

  function renderPagination(totalPages) {
    if (totalPages <= 1) {
      pagination.hidden = true;
      pagination.innerHTML = '';
      return;
    }
    var html = '<button type="button" class="page-btn" data-page="' + (currentPage - 1) + '" aria-label="Previous page"' +
      (currentPage === 1 ? ' disabled' : '') + '><i class="bx bx-chevron-left"></i></button>';
    pageList(currentPage, totalPages).forEach(function (page) {
      if (page === '…') {
        html += '<span class="page-ellipsis" aria-hidden="true">…</span>';
      } else {
        html += '<button type="button" class="page-btn' + (page === currentPage ? ' active' : '') + '" data-page="' + page + '"' +
          (page === currentPage ? ' aria-current="page"' : '') + ' aria-label="Page ' + page + '">' + page + '</button>';
      }
    });
    html += '<button type="button" class="page-btn" data-page="' + (currentPage + 1) + '" aria-label="Next page"' +
      (currentPage === totalPages ? ' disabled' : '') + '><i class="bx bx-chevron-right"></i></button>';
    pagination.innerHTML = html;
    pagination.hidden = false;
  }

  function render() {
    var query = searchInput ? searchInput.value.trim().toLowerCase() : '';
    var visible = activities.filter(function (item) {
      var categoryMatch = activeFilter === 'all' || item.kind === activeFilter;
      var text = (item.teacherName + ' ' + item.teacherEmail + ' ' + item.summary).toLowerCase();
      return categoryMatch && (!query || text.indexOf(query) !== -1);
    });
    var totalPages = Math.max(1, Math.ceil(visible.length / PAGE_SIZE));
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    if (!visible.length) {
      count.textContent = '0 activities';
      pagination.hidden = true;
      list.innerHTML = '<div class="activity-empty"><i class="bx bx-search-alt"></i>No activity matches this filter.</div>';
      return;
    }

    var startIndex = (currentPage - 1) * PAGE_SIZE;
    var shown = visible.slice(startIndex, startIndex + PAGE_SIZE);
    count.textContent = 'Showing ' + (startIndex + 1) + '–' + (startIndex + shown.length) + ' of ' + visible.length + ' activities';
    renderPagination(totalPages);

    list.innerHTML = shown.map(function (item) {
      var kindLabel = item.kind === 'roster' ? 'Student' : item.kind === 'resource' ? 'Resource' : item.kind === 'quiz' ? 'Quiz' : 'Reading';
      return '<article class="activity-row">' +
        '<div class="activity-avatar">' + escapeHtml(initials(item.teacherName)) + '</div>' +
        '<div class="activity-info"><div class="activity-teacher">' + escapeHtml(item.teacherName) +
          '<span class="activity-email">' + escapeHtml(item.teacherEmail) + '</span></div>' +
          '<div class="activity-summary">' + escapeHtml(item.summary) + '</div></div>' +
        '<time class="activity-date">' + escapeHtml(formatDate(item.occurredAt)) + '</time>' +
        '<span class="activity-type ' + escapeHtml(item.kind) + '">' + kindLabel + '</span></article>';
    }).join('');
  }

  function loadActivity() {
    if (loading) return;
    loading = true;
    refreshButton.disabled = true;
    refreshButton.innerHTML = '<i class="bx bx-refresh bx-spin"></i> Refreshing…';
    pagination.hidden = true;
    list.innerHTML = '<div class="activity-empty"><i class="bx bx-loader-alt bx-spin"></i>Loading teacher activity…</div>';
    fetch('admin-api.php?view=teacher_activity', {cache:'no-store'})
      .then(function (response) {
        return response.json().then(function (data) {
          if (!response.ok) throw new Error(data.error || 'Unable to load teacher activity.');
          return data;
        });
      })
      .then(function (data) {
        if (!Array.isArray(data.activities)) throw new Error('The activity response was invalid.');
        activities = data.activities;
        currentPage = 1;
        render();
      })
      .catch(function (error) {
        count.textContent = '';
        pagination.hidden = true;
        list.innerHTML = '<div class="activity-empty activity-error"><i class="bx bx-error-circle"></i>' +
          escapeHtml(error.message || 'Unable to load teacher activity.') +
          '<div><button type="button" class="activity-refresh" id="retryActivity"><i class="bx bx-refresh"></i> Try again</button></div></div>';
        document.getElementById('retryActivity').addEventListener('click', loadActivity);
      })
      .then(function () {
        loading = false;
        refreshButton.disabled = false;
        refreshButton.innerHTML = '<i class="bx bx-refresh"></i> Refresh';
      });
  }

  document.querySelectorAll('.activity-filter').forEach(function (button) {
    button.addEventListener('click', function () {
      document.querySelectorAll('.activity-filter').forEach(function (filter) { filter.classList.remove('active'); });
      button.classList.add('active');
      activeFilter = button.dataset.filter;
      currentPage = 1;
      render();
    });
  });
  if (searchInput) searchInput.addEventListener('input', function () { currentPage = 1; render(); });
  pagination.addEventListener('click', function (event) {
    var button = event.target.closest('.page-btn');
    if (!button || button.disabled) return;
    var page = parseInt(button.dataset.page, 10);
    if (isNaN(page) || page === currentPage) return;
    currentPage = page;
    render();
    panel.scrollIntoView({behavior:'smooth', block:'start'});
  });
  refreshButton.addEventListener('click', loadActivity);
  loadActivity();
})();
</script>
<script src="admin-shared-ui.js"></script>
</body>
</html>