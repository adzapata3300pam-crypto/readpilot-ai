<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Cloud Recordings</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script>
(function () {
  try {
    if (localStorage.getItem('readpilot-theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
    if (localStorage.getItem('readpilot-sidebar') === 'collapsed') document.documentElement.setAttribute('data-sidebar', 'collapsed');
  } catch (e) {}
})();
</script>
<style>
  .recordings-intro{display:flex;align-items:center;gap:14px;margin-bottom:22px;padding:18px 20px;background:var(--green-light);border-radius:14px;color:var(--green-dark);}
  .recordings-intro-icon{width:44px;height:44px;display:grid;place-items:center;flex:0 0 auto;background:var(--card);border-radius:12px;font-size:23px;}
  .recordings-intro-copy{min-width:0;flex:1;}
  .recordings-intro-title{font-family:'Poppins',sans-serif;font-size:14px;font-weight:700;color:var(--ink);}
  .recordings-intro-text{margin-top:2px;font-size:12.5px;font-weight:600;color:var(--muted);}
  .recordings-state{display:flex;align-items:center;gap:7px;margin-top:5px;font-size:12px;font-weight:700;color:var(--muted);}
  .recordings-state::before{content:'';width:7px;height:7px;border-radius:50%;background:var(--muted);}
  .recordings-state.connected{color:var(--green-dark);}
  .recordings-state.connected::before{background:var(--green);}
  .recordings-state.failed{color:var(--red);}
  .recordings-state.failed::before{background:var(--red);}
  .recording-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:22px;}
  .recording-stat{display:flex;align-items:center;gap:13px;min-width:0;padding:16px 18px;background:var(--card);border-radius:14px;box-shadow:var(--shadow);}
  .recording-stat-icon{width:38px;height:38px;display:grid;place-items:center;flex:0 0 auto;border-radius:11px;background:var(--green-light);color:var(--green-dark);font-size:20px;}
  .recording-stat-label{font-size:11.5px;font-weight:700;color:var(--muted);}
  .recording-stat-value{margin-top:1px;font-family:'Poppins',sans-serif;font-size:19px;font-weight:700;color:var(--ink);}
  .recordings-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;}
  .recordings-toolbar h2{margin:0;font-family:'Poppins',sans-serif;font-size:16px;color:var(--ink);}
  .recordings-count{font-size:12px;font-weight:700;color:var(--muted);}
  .recordings-table-wrap{overflow-x:auto;background:var(--card);border-radius:14px;box-shadow:var(--shadow);}
  .recordings-table{width:100%;border-collapse:collapse;font-size:12.5px;}
  .recordings-table th,.recordings-table td{padding:13px 14px;text-align:left;border-bottom:1px solid var(--border);vertical-align:middle;}
  .recordings-table th{background:var(--card);color:var(--muted);font-size:10.5px;font-weight:800;text-transform:uppercase;}
  .recordings-table tr:last-child td{border-bottom:0;}
  .recording-student{font-weight:800;color:var(--ink);}
  .recording-book{margin-top:2px;color:var(--muted);font-size:11.5px;font-weight:600;}
  .recording-path,.recording-etag{display:block;max-width:240px;overflow-wrap:anywhere;color:var(--muted);font-family:monospace;font-size:10.5px;}
  .recording-actions{display:flex;align-items:center;gap:6px;white-space:nowrap;}
  .recording-actions button{display:inline-flex;align-items:center;gap:4px;padding:7px 10px;border:0;border-radius:9px;font:inherit;font-size:11.5px;font-weight:800;cursor:pointer;}
  .recording-verify{background:var(--green-light);color:var(--green-dark);}
  .recording-play{background:var(--bg);color:var(--ink);}
  .recording-delete{background:var(--red-light);color:var(--red);}
  .recording-actions button:disabled{opacity:.55;cursor:wait;}
  .recording-delete-dialog{width:min(420px,calc(100% - 32px));padding:22px;border:1px solid var(--border);border-radius:12px;background:var(--card);color:var(--ink);box-shadow:var(--shadow);}
  .recording-delete-dialog::backdrop{background:rgba(0,0,0,.55);}
  .recording-delete-dialog h2{margin:0 0 8px;font-family:'Poppins',sans-serif;font-size:17px;}
  .recording-delete-dialog p{margin:0;color:var(--muted);font-size:13px;line-height:1.5;}
  .recording-delete-dialog .recording-delete-error{min-height:18px;margin-top:8px;color:var(--red);font-size:12px;}
  .recording-delete-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:16px;}
  .recording-delete-actions button{padding:8px 12px;border:0;border-radius:8px;font:700 12px 'Nunito',sans-serif;cursor:pointer;}
  .recording-delete-cancel{background:var(--bg);color:var(--ink);}
  .recording-delete-confirm{background:var(--red);color:#fff;}
  .recording-delete-confirm:disabled{opacity:.6;cursor:wait;}
  .recording-actions button:hover{filter:brightness(.96);}
  .recording-result{margin-top:5px;font-size:11px;font-weight:700;white-space:normal;}
  .recordings-notice .ok,.recording-result .ok{color:var(--green-dark);}
  .recordings-notice .bad,.recording-result .bad{color:var(--red);}
  .recordings-message{padding:48px 20px!important;text-align:center!important;color:var(--muted);}
  .recordings-message i{display:block;margin-bottom:8px;color:var(--green);font-size:34px;}
  .recordings-message strong{display:block;margin-bottom:3px;color:var(--ink);font-family:'Poppins',sans-serif;font-size:14px;}
  .recordings-message span{font-size:12px;font-weight:600;}
  .recordings-notice{display:none;margin:0 0 16px;padding:12px 15px;border-radius:11px;background:var(--green-light);color:var(--green-dark);font-size:12px;font-weight:700;overflow-wrap:anywhere;}
  .recordings-notice.show{display:block;}
  .recordings-notice.failed{background:var(--red-light);color:var(--red);}
  .recordings-footer{margin-top:14px;color:var(--muted);font-size:11.5px;font-weight:600;}
  @media(max-width:900px){.main{padding:28px 22px 40px;}.recording-stats{gap:10px;}.recording-stat{padding:14px;}}
  @media(max-width:640px){.main{padding:22px 15px 32px;}.recordings-intro{align-items:flex-start;padding:15px;}.recordings-intro .btn-primary{align-self:center;white-space:nowrap;}.recording-stats{grid-template-columns:1fr;gap:8px;}.recording-stat{padding:11px 14px;}.recording-stat-value{font-size:17px;}.recordings-table{min-width:780px;}}
</style>
</head>
<body>
<aside class="sidebar">
  <div>
    <div class="logo-row">
      <div class="logo-left"><div class="logo-icon"><i class="bx bxs-paper-plane"></i></div><div class="logo-text"><span class="brand">ReadPilot</span><span class="tagline">Guide. Read. Grow.</span></div></div>
      <button class="hamburger" id="sidebarToggle" aria-label="Toggle navigation"><span class="bar"></span></button>
    </div>
    <nav>
      <a class="nav-item" href="index.php"><i class="bx bxs-dashboard"></i><span class="label">Dashboard</span></a>
      <a class="nav-item" href="students.php"><i class="bx bx-group"></i><span class="label">Sections</span></a>
      <a class="nav-item" href="sessions.php"><i class="bx bx-calendar"></i><span class="label">Sessions</span></a>
      <a class="nav-item" href="reports.php"><i class="bx bx-file"></i><span class="label">Reports</span></a>
      <a class="nav-item" href="struggle-map.php"><i class="bx bx-target-lock"></i><span class="label">Struggle Map</span></a>
      <a class="nav-item" href="recommendations.php"><i class="bx bx-bulb"></i><span class="label">Recommendations</span></a>
      <a class="nav-item" href="resources.php"><i class="bx bx-book-open"></i><span class="label">Resources</span></a>
      <a class="nav-item active" href="recordings.php" aria-current="page"><i class="bx bx-cloud-upload"></i><span class="label">Cloud Recordings</span></a>
      <a class="nav-item" href="settings.php"><i class="bx bx-cog"></i><span class="label">Settings</span></a>
    </nav>
  </div>
  <div class="teacher-card">
    <div class="teacher-row"><div class="teacher-row-info"><div class="avatar"><?php include __DIR__ . '/profile-avatar.php'; ?></div><div><div class="teacher-name"><?= htmlspecialchars(current_user()['full_name'], ENT_QUOTES, 'UTF-8') ?></div><div class="teacher-role">Grade 3 Teacher</div></div></div><a class="teacher-logout-btn" href="logout.php" title="Log out" aria-label="Log out"><i class="bx bx-log-out"></i></a></div>
  </div>
</aside>
<main class="main">
  <div class="topbar">
    <div class="title-block"><h1>Cloud recordings</h1><div class="greet">Review student reading audio stored securely in the cloud.</div></div>
    <div class="topbar-actions"><a class="btn-secondary" href="resources.php" style="text-decoration:none"><i class="bx bx-book-open"></i> Resources</a></div>
  </div>

  <section class="recordings-intro" aria-label="Cloud storage status">
    <div class="recordings-intro-icon"><i class="bx bx-cloud"></i></div>
    <div class="recordings-intro-copy"><div class="recordings-intro-title">Private cloud storage</div><div class="recordings-intro-text">Recordings are saved in ReadPilot's private Supabase Storage bucket.</div><div id="cloudStatus" class="recordings-state">Checking storage connection status</div></div>
    <button class="btn-primary" id="selftest" type="button"><i class="bx bx-check-shield"></i> Test connection</button>
  </section>
  <div id="testout" class="recordings-notice" role="status" aria-live="polite"></div>

  <section class="recording-stats" aria-label="Recording summary">
    <div class="recording-stat"><div class="recording-stat-icon"><i class="bx bx-microphone"></i></div><div><div class="recording-stat-label">Saved recordings</div><div class="recording-stat-value" id="totalCount">--</div></div></div>
    <div class="recording-stat"><div class="recording-stat-icon"><i class="bx bx-hdd"></i></div><div><div class="recording-stat-label">Total storage used</div><div class="recording-stat-value" id="totalSize">--</div></div></div>
    <div class="recording-stat"><div class="recording-stat-icon"><i class="bx bx-time-five"></i></div><div><div class="recording-stat-label">Most recent</div><div class="recording-stat-value" id="latestSaved">--</div></div></div>
  </section>

  <section>
    <div class="recordings-toolbar"><h2>Recording library</h2><span class="recordings-count" id="recordingCount">Loading recordings...</span></div>
    <div class="recordings-table-wrap"><table class="recordings-table"><thead><tr><th>Student / Book</th><th>Date Saved</th><th>Cloud path</th><th>Size</th><th>ETag</th><th>Actions</th></tr></thead><tbody id="rows"><tr><td colspan="6" class="recordings-message"><i class="bx bx-loader-alt bx-spin"></i><strong>Loading recordings</strong><span>Connecting to your recording library...</span></td></tr></tbody></table></div>
    <div class="recordings-footer">Cloud paths and file identifiers are visible only to authorized teachers.</div>
  </section>
</main>
<dialog class="recording-delete-dialog" id="deleteDialog" aria-labelledby="deleteDialogTitle">
  <h2 id="deleteDialogTitle">Delete recording?</h2>
  <p>Permanently delete this recording for <strong id="deleteStudentName"></strong> from cloud storage?</p>
  <div class="recording-delete-error" id="deleteError" role="alert"></div>
  <div class="recording-delete-actions">
    <button class="recording-delete-cancel" id="cancelDelete" type="button">Cancel</button>
    <button class="recording-delete-confirm" id="confirmDelete" type="button"><i class="bx bx-trash"></i> Delete recording</button>
  </div>
</dialog>
<script>
const csrfToken = <?= json_encode(csrf_token()) ?>;
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const kb = n => n >= 1048576 ? (n/1048576).toFixed(2)+' MB' : n >= 1024 ? Math.round(n/1024)+' KB' : n+' B';
const deleteDialog = document.getElementById('deleteDialog');
let pendingDeleteId = null;
async function api(q, options = {}){ const r = await fetch('recording-api.php?'+q, options); let d={}; try{d=await r.json();}catch(e){} return d; }
async function load(){
  const d = await api('view=list'); const tb = document.getElementById('rows');
  if(!d.ok){ document.getElementById('recordingCount').textContent='Unable to load'; tb.innerHTML='<tr><td colspan="6" class="recordings-message"><i class="bx bx-error-circle"></i><strong>Could not load recordings</strong><span>Check your connection and try refreshing the page.</span></td></tr>'; return; }
  document.getElementById('totalCount').textContent = d.recordings.length;
  document.getElementById('totalSize').textContent = kb(d.recordings.reduce((sum, r) => sum + (+r.size_bytes || 0), 0));
  document.getElementById('recordingCount').textContent = d.recordings.length + (d.recordings.length === 1 ? ' recording' : ' recordings');
  if(!d.recordings.length){ document.getElementById('latestSaved').textContent='None yet'; tb.innerHTML='<tr><td colspan="6" class="recordings-message"><i class="bx bx-microphone"></i><strong>No recordings yet</strong><span>Student reading recordings will appear here after they are saved.</span></td></tr>'; return; }
  document.getElementById('latestSaved').textContent = String(d.recordings[0].created_at || 'Saved').slice(0, 10);
  tb.innerHTML = d.recordings.map(r => `<tr>
    <td><div class="recording-student">${esc(r.student)}</div><div class="recording-book">${esc(r.book)}</div></td><td>${esc(r.created_at)}</td>
    <td><code class="recording-path">${esc(r.bucket)}/${esc(r.storage_key)}</code></td><td>${kb(+r.size_bytes)}</td><td><code class="recording-etag">${esc(r.etag)}</code></td>
    <td><div class="recording-actions"><button class="recording-verify" data-v="${r.id}" type="button"><i class="bx bx-check-circle"></i> Verify</button><button class="recording-play" data-p="${r.id}" type="button"><i class="bx bx-play"></i> Play</button><button class="recording-delete" data-remove="${Number(r.id)}" type="button" title="Delete recording"><i class="bx bx-trash"></i> Delete</button></div><div id="v${r.id}" class="recording-result"></div></td></tr>`).join('');
}
document.getElementById('rows').addEventListener('click', async e => {
  const button = e.target.closest('button'); if (!button) return;
  const v = button.dataset.v, p = button.dataset.p, removeId = button.dataset.remove;
  if(v){ const box = document.getElementById('v'+v); box.textContent='Asking Google…';
    const d = await api('view=verify&id='+v);
    box.innerHTML = d.exists && d.size_match ? `<span class="ok">Found in bucket</span> · ${kb(d.size)} · modified ${esc(d.modified)}` : `<span class="bad">${esc(d.error || 'Missing or size mismatch')}</span>`; }
  if(p){
    const player = window.open('about:blank', '_blank');
    const d = await api('view=play&id='+p);
    if(d.ok && player) player.location = d.url;
    else { if(player) player.close(); alert(d.error || 'Could not open recording'); }
  }
  if(removeId){
    const row = button.closest('tr');
    const student = row.querySelector('.recording-student')?.textContent || 'this student';
    pendingDeleteId = removeId;
    document.getElementById('deleteStudentName').textContent = student;
    document.getElementById('deleteError').textContent = '';
    deleteDialog.showModal();
  }
});
document.getElementById('cancelDelete').addEventListener('click', () => deleteDialog.close());
deleteDialog.addEventListener('close', () => { pendingDeleteId = null; });
document.getElementById('confirmDelete').addEventListener('click', async () => {
  if(!pendingDeleteId) return;
  const button = document.getElementById('confirmDelete');
  const id = pendingDeleteId;
  button.disabled = true;
  document.getElementById('deleteError').textContent = '';
  try{
    const d = await api('view=delete', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({csrf_token:csrfToken, id})});
    if(!d.ok) throw new Error(d.error || 'Could not delete this recording.');
    deleteDialog.close();
    await load();
  }catch(error){
    document.getElementById('deleteError').textContent = error.message || 'Could not delete this recording.';
  }finally{
    button.disabled = false;
  }
});
document.getElementById('selftest').addEventListener('click', async () => {
  const o = document.getElementById('testout'); const status = document.getElementById('cloudStatus');
  o.classList.add('show'); o.classList.remove('failed'); o.textContent='Writing a test file to the bucket…'; status.className='recordings-state'; status.textContent='Checking the Supabase S3 endpoint, credentials, and bucket…';
  const d = await api('view=selftest', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({csrf_token:csrfToken})});
  o.innerHTML = d.ok ? `<span class="ok">Connected.</span> Wrote, read back and deleted <code>${esc(d.key)}</code> in bucket <code>${esc(d.bucket)}</code> (${d.size} bytes, ETag ${esc(d.etag)}).` : `<span class="bad">Failed:</span> ${esc(d.error)}`;
  if (!d.ok) o.classList.add('failed'); status.className = 'recordings-state ' + (d.ok ? 'connected' : 'failed'); status.textContent = d.ok ? 'Supabase Storage is connected and verified.' : 'Supabase Storage needs configuration or permissions.';
});
document.getElementById('sidebarToggle').addEventListener('click', function(){
  const html = document.documentElement;
  const isCollapsed = html.getAttribute('data-sidebar') === 'collapsed';
  if (isCollapsed) html.removeAttribute('data-sidebar'); else html.setAttribute('data-sidebar', 'collapsed');
  try { localStorage.setItem('readpilot-sidebar', isCollapsed ? 'expanded' : 'collapsed'); } catch (e) {}
});
load();
</script>
</body>
</html>