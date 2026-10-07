<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Results</title>
<script src="theme-init.js"></script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  /* =====================================================================
     results.php reuses reading.php's exact palette and stage anatomy so
     that when a student finishes a story, this page reads as "the same
     reading screen, just paused." The whole stage (topbar / sentence /
     progress / bottom bar) is rebuilt from the finished session's data as
     an inert, non-interactive backdrop, then blurred — the results card
     floats on top of it, in sharp focus, like a modal over a frozen frame
     of the reading session.
     ===================================================================== */
  :root{
    --stage-bg-1: #163828;
    --stage-bg-2: #0c2016;
    --stage-card: #1c3d2b;
    --accent: #8fd67c;
    --accent-strong: #6fbf5a;
    --ink: #f3faf5;
    --muted: #a9c9b8;
    --line: rgba(255,255,255,0.10);
    --red: #ea5d5d;
    --chip-bg: rgba(255,255,255,0.07);
    --chip-bg-hover: rgba(255,255,255,0.14);
    --dot-bg: rgba(255,255,255,0.22);
    --on-accent: #0c2016;
  }
  :root.light-theme{
    --stage-bg-1: #f2f9ee;
    --stage-bg-2: #e3f1dd;
    --stage-card: #ffffff;
    --accent: #2f7a24;
    --accent-strong: #256019;
    --ink: #16281c;
    --muted: #5c7266;
    --line: rgba(22,40,29,0.12);
    --red: #c94336;
    --chip-bg: rgba(22,40,29,0.05);
    --chip-bg-hover: rgba(22,40,29,0.10);
    --dot-bg: rgba(22,40,29,0.18);
    --on-accent: #ffffff;
  }
  *{box-sizing:border-box;}
  html,body{
    margin:0;height:100%;
    font-family:'Nunito', sans-serif;
    background:
      radial-gradient(1200px 600px at 15% -10%, rgba(143,214,124,0.10), transparent 60%),
      radial-gradient(900px 500px at 100% 110%, rgba(79,163,184,0.10), transparent 60%),
      linear-gradient(160deg, var(--stage-bg-1) 0%, var(--stage-bg-2) 100%);
    color:var(--ink);
    overflow:hidden;
    transition:background .3s ease, color .3s ease;
  }

  /* ---------- Frozen backdrop: a non-interactive replay of the reading
     stage in whatever state the student finished it in ---------- */
  .bg-replay{
    position:fixed;inset:0;
    display:flex;flex-direction:column;
    filter:blur(9px) saturate(1.05);
    transform:scale(1.03); /* avoids sharp un-blurred edges at the viewport border */
    pointer-events:none;
    user-select:none;
  }
  .bg-topbar{
    height:64px;flex-shrink:0;
    display:flex;align-items:center;justify-content:space-between;
    padding:0 28px;
    border-bottom:2px solid var(--accent-strong);
  }
  .bg-brand{display:flex;align-items:center;gap:10px;}
  .bg-brand .bx{font-size:22px;color:var(--accent);}
  .bg-brand span{font-family:'Poppins',sans-serif;font-weight:700;font-size:17px;color:var(--ink);}
  .bg-timer{
    display:flex;align-items:center;gap:7px;
    background:var(--chip-bg);border:1px solid var(--line);
    padding:8px 14px;border-radius:20px;font-size:12.5px;font-weight:800;color:var(--ink);
  }
  .bg-timer .dot{width:7px;height:7px;border-radius:50%;background:var(--accent);}

  .bg-stage{
    flex-grow:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:20px 24px;gap:22px;min-height:0;
  }
  .bg-meta{
    font-size:12px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--muted);
  }
  .bg-sentence{
    max-width:900px;width:100%;
    display:flex;flex-wrap:wrap;justify-content:center;gap:8px 0.42em;
  }
  .bg-word{
    font-family:'Poppins',sans-serif;font-weight:700;font-size:34px;line-height:1.5;
    color:var(--muted);
  }
  .bg-progress-wrap{width:100%;max-width:640px;}
  .bg-progress-track{height:6px;background:var(--line);border-radius:99px;overflow:hidden;}
  .bg-progress-fill{height:100%;width:100%;background:var(--accent);border-radius:99px;}
  .bg-dots{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:center;max-width:640px;}
  .bg-dot{width:9px;height:9px;border-radius:50%;background:var(--muted);}

  .bg-bottombar{
    flex-shrink:0;
    display:flex;align-items:center;justify-content:space-between;gap:16px;
    padding:14px 26px;
    background:linear-gradient(90deg, #cbe98f, #b1db65);
    color:#16281d;
  }
  .bg-line-count{font-size:12.5px;font-weight:800;white-space:nowrap;}
  .bg-progress-track2{flex-grow:1;height:8px;border-radius:6px;background:rgba(22,40,29,0.18);overflow:hidden;margin:0 20px;}
  .bg-progress-fill2{height:100%;width:100%;background:#16281d;border-radius:6px;}
  .bg-pct{font-size:12.5px;font-weight:800;white-space:nowrap;}

  /* ---------- Crisp foreground layer ---------- */
  .top-glass{
    position:fixed;top:0;left:0;right:0;
    height:64px;z-index:20;
    display:flex;align-items:center;justify-content:space-between;
    padding:0 28px;
  }
  .brand{display:flex;align-items:center;gap:10px;}
  .brand .bx{font-size:22px;color:var(--accent);}
  .brand span{font-family:'Poppins',sans-serif;font-weight:700;font-size:17px;color:var(--ink);}
  .top-actions{display:flex;align-items:center;gap:12px;}
  .theme-toggle, .exit-btn{
    display:flex;align-items:center;justify-content:center;gap:6px;
    background:var(--stage-card);border:1px solid var(--line);color:var(--ink);
    cursor:pointer;font-family:'Nunito',sans-serif;font-weight:800;font-size:12.5px;
    box-shadow:0 6px 18px rgba(0,0,0,0.18);
  }
  .theme-toggle{width:38px;height:38px;border-radius:50%;}
  .theme-toggle .bx{font-size:18px;}
  .theme-toggle:hover{background:var(--chip-bg-hover);}
  .exit-btn{padding:8px 16px;border-radius:20px;text-decoration:none;}
  .exit-btn:hover{background:var(--chip-bg-hover);}
  .exit-btn .bx{font-size:14px;}

  .results-overlay{
    position:fixed;inset:0;z-index:10;
    background:rgba(6,14,9,0.42);
    display:flex;align-items:center;justify-content:center;
    padding:24px;
  }
  :root.light-theme .results-overlay{background:rgba(22,40,29,0.28);}

  .results-card{
    position:relative;
    width:100%;max-width:440px;
    background:var(--stage-card);
    border:1px solid var(--line);
    border-radius:26px;
    padding:38px 30px 28px;
    text-align:center;
    box-shadow:0 30px 70px rgba(0,0,0,0.45);
    animation:cardIn .45s cubic-bezier(.2,.9,.25,1.15);
  }
  @keyframes cardIn{
    from{opacity:0;transform:translateY(20px) scale(.96);}
    to{opacity:1;transform:translateY(0) scale(1);}
  }

  .results-badge{
    width:64px;height:64px;border-radius:50%;
    background:linear-gradient(135deg, var(--accent) 0%, var(--accent-strong) 100%);
    color:var(--on-accent);
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 16px;font-size:28px;
    box-shadow:0 12px 28px rgba(111,191,90,0.4);
  }
  .results-title{font-family:'Poppins',sans-serif;font-weight:800;font-size:22px;margin:0 0 4px;color:var(--ink);}
  .results-sub{font-size:13.5px;font-weight:700;color:var(--muted);margin:0 0 24px;}

  .stats-grid{
    display:grid;grid-template-columns:repeat(3,1fr);gap:10px;
    margin-bottom:18px;
  }
  .stat-box{
    background:var(--chip-bg);border:1px solid var(--line);
    border-radius:16px;padding:15px 8px;
  }
  .stat-icon{font-size:19px;margin-bottom:5px;}
  .stat-value{font-family:'Poppins',sans-serif;font-weight:800;font-size:23px;color:var(--ink);line-height:1;}
  .stat-value.accent{color:var(--accent);}
  .stat-label{
    display:block;font-size:10px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;
    color:var(--muted);margin-top:5px;
  }

  .accuracy-wrap{margin-bottom:18px;}
  .accuracy-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:7px;}
  .accuracy-head span{font-size:11.5px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);}
  .accuracy-head strong{font-family:'Poppins',sans-serif;font-size:14px;color:var(--ink);}
  .accuracy-track{height:9px;background:var(--line);border-radius:99px;overflow:hidden;}
  .accuracy-fill{height:100%;border-radius:99px;background:linear-gradient(90deg, var(--accent-strong), var(--accent));width:0%;transition:width .8s cubic-bezier(.2,.9,.25,1) .2s;}

  .grade-row{display:flex;justify-content:center;margin-bottom:16px;}
  .grade-pill{
    padding:9px 20px;border-radius:99px;
    font-family:'Poppins',sans-serif;font-size:13.5px;font-weight:800;letter-spacing:.02em;
  }
  .grade-pill.excellent{background:rgba(143,214,124,0.18);color:var(--accent);border:1px solid rgba(143,214,124,.35);}
  .grade-pill.good{background:rgba(111,191,90,0.18);color:var(--accent-strong);border:1px solid rgba(111,191,90,.35);}
  .grade-pill.fair{background:rgba(255,184,119,0.18);color:#ffb877;border:1px solid rgba(255,184,119,.35);}
  .grade-pill.needs-work{background:rgba(234,93,93,0.16);color:var(--red);border:1px solid rgba(234,93,93,.35);}

  .tricky-box{
    display:none;
    font-size:12.5px;font-weight:700;color:var(--muted);line-height:1.55;
    background:var(--chip-bg);border:1px solid var(--line);
    border-radius:14px;padding:12px 16px;margin-bottom:20px;
    text-align:left;
  }
  .tricky-box .bx{color:#ffb877;font-size:14px;margin-right:5px;vertical-align:-2px;}

  .results-actions{display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;}
  @media (max-width:560px){ .results-actions{grid-template-columns:1fr;} }

  .ai-eval-box{
    display:none;
    margin-bottom:20px;
    background:rgba(111,191,90,0.08);
    border:1.5px solid rgba(111,191,90,0.28);
    border-radius:16px;
    padding:16px 18px;
    text-align:left;
  }
  .ai-eval-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;gap:8px;flex-wrap:wrap;}
  .ai-eval-title{display:flex;align-items:center;gap:7px;font-family:'Poppins',sans-serif;font-size:12.5px;font-weight:700;color:var(--accent-strong);}
  .ai-eval-title .bx{font-size:16px;}
  .ai-eval-badge{
    font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;
    padding:3px 9px;border-radius:12px;
  }
  .ai-eval-badge.rapid_growth{background:rgba(111,191,90,0.22);color:var(--accent-strong);}
  .ai-eval-badge.on_track{background:rgba(111,191,90,0.18);color:var(--accent-strong);}
  .ai-eval-badge.steady{background:rgba(79,163,184,0.18);color:var(--teal);}
  .ai-eval-badge.needs_intervention{background:rgba(234,93,93,0.18);color:var(--red);}
  .ai-eval-text{font-size:12.5px;line-height:1.55;color:var(--ink);margin:0 0 10px 0;}
  .ai-eval-tags{display:flex;gap:6px;flex-wrap:wrap;}
  .ai-eval-tag{font-size:11px;font-weight:700;background:var(--chip-bg);border:1px solid var(--line);border-radius:8px;padding:3px 8px;color:var(--muted);}

  .res-btn{
    display:flex;align-items:center;justify-content:center;gap:7px;
    font-family:'Poppins',sans-serif;font-weight:700;font-size:13.5px;
    padding:13px 10px;border-radius:14px;cursor:pointer;
    text-decoration:none;border:1px solid var(--line);
  }
  .res-btn.secondary{background:var(--chip-bg);color:var(--ink);}
  .res-btn.secondary:hover{background:var(--chip-bg-hover);}
  .res-btn.primary{background:var(--accent-strong);border-color:var(--accent-strong);color:var(--on-accent);}
  .res-btn.primary:hover{background:var(--accent);}

  @media (max-width:640px){
    .bg-word{font-size:24px;}
    .top-glass{padding:0 16px;}
    .results-card{padding:30px 22px 24px;}
    .stats-grid{gap:8px;}
    .stat-value{font-size:19px;}
  }
</style>
</head>
<body>

  <!-- Frozen, blurred replay of the finished reading stage -->
  <div class="bg-replay" id="bgReplay" aria-hidden="true">
    <div class="bg-topbar">
      <div class="bg-brand"><i class='bx bxs-paper-plane'></i><span>ReadPilot</span></div>
      <div class="bg-timer"><span class="dot"></span><span id="bgTimer">0:00</span></div>
    </div>
    <div class="bg-stage">
      <div class="bg-meta" id="bgMeta">STORY \u00B7 COMPLETE</div>
      <div class="bg-sentence" id="bgSentence"></div>
      <div class="bg-progress-wrap">
        <div class="bg-progress-track"><div class="bg-progress-fill"></div></div>
      </div>
      <div class="bg-dots" id="bgDots"></div>
    </div>
    <div class="bg-bottombar">
      <div class="bg-line-count" id="bgLineCount">Line 1 / 1</div>
      <div class="bg-progress-track2"><div class="bg-progress-fill2"></div></div>
      <div class="bg-pct">100%</div>
    </div>
  </div>

  <!-- Crisp foreground -->
  <div class="top-glass">
    <div class="brand"><i class='bx bxs-paper-plane'></i><span>ReadPilot</span></div>
    <div class="top-actions">
      <button class="theme-toggle" id="themeToggle" aria-label="Switch to light mode">
        <i class='bx bx-sun' id="themeIcon"></i>
      </button>
      <a class="exit-btn" href="resources.php"><i class='bx bx-x'></i> Exit</a>
    </div>
  </div>

  <div class="results-overlay">
    <div class="results-card">
      <div class="results-badge"><i class='bx bxs-trophy'></i></div>
      <h1 class="results-title">Story Complete!</h1>
      <p class="results-sub" id="resultsSub">Here's how the reading went</p>

      <div class="stats-grid">
        <div class="stat-box">
          <div class="stat-icon">⚡</div>
          <div class="stat-value accent" id="statWPM">0</div>
          <span class="stat-label">Words / Min</span>
        </div>
        <div class="stat-box">
          <div class="stat-icon">⏱</div>
          <div class="stat-value" id="statTime">0:00</div>
          <span class="stat-label">Time</span>
        </div>
        <div class="stat-box">
          <div class="stat-icon">📖</div>
          <div class="stat-value" id="statWords">0</div>
          <span class="stat-label">Words Read</span>
        </div>
      </div>

      <div class="accuracy-wrap" id="accuracyWrap">
        <div class="accuracy-head">
          <span>Voice Accuracy</span>
          <strong id="accuracyValue">100%</strong>
        </div>
        <div class="accuracy-track"><div class="accuracy-fill" id="accuracyFill"></div></div>
      </div>

      <div class="grade-row">
        <div class="grade-pill" id="gradePill">—</div>
      </div>

      <div class="tricky-box" id="trickyBox"></div>

      <div class="ai-eval-box" id="aiEvalBox">
        <div class="ai-eval-head">
          <div class="ai-eval-title"><i class='bx bxs-brain'></i> AI Session Assessment</div>
          <div class="ai-eval-badge on_track" id="aiEvalBadge">Evaluating…</div>
        </div>
        <p class="ai-eval-text" id="aiEvalNarrative">Analyzing oral fluency and pronunciation patterns…</p>
        <div class="ai-eval-tags" id="aiEvalTags"></div>
      </div>

      <div class="results-actions">
        <button class="res-btn secondary" id="readAgainBtn"><i class='bx bx-refresh'></i> Read Again</button>
        <a class="res-btn secondary" id="quizBtn" href="quiz.php"><i class='bx bx-brain'></i> Take Quiz</a>
        <a class="res-btn primary" id="progressBtn" href="reports.php"><i class='bx bx-bar-chart-alt-2'></i> Reports</a>
      </div>
    </div>
  </div>


<script>
  /* =====================================================================
     Theme — shares the same 'readpilot-theme' key as reading.php so the
     two pages always render in sync, light or dark.
  ===================================================================== */
  const THEME_KEY = 'readpilot-theme';
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');
  let currentTheme = 'dark';
  try { currentTheme = localStorage.getItem(THEME_KEY) || 'dark'; } catch (e) { /* storage unavailable */ }

  function applyTheme(theme){
    document.documentElement.classList.toggle('light-theme', theme === 'light');
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    } else {
      document.documentElement.removeAttribute('data-theme');
    }
    themeIcon.className = theme === 'light' ? 'bx bx-moon' : 'bx bx-sun';
    themeToggle.setAttribute('aria-label', theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
    try { localStorage.setItem(THEME_KEY, theme); } catch (e) { /* storage unavailable */ }
  }
  applyTheme(currentTheme);
  themeToggle.addEventListener('click', () => {
    currentTheme = currentTheme === 'light' ? 'dark' : 'light';
    applyTheme(currentTheme);
  });

  /* =====================================================================
     Load this student's performance, handed off by reading.php. Falls
     back to a small demo result so the page still renders sensibly if
     opened directly (e.g. while testing).
  ===================================================================== */
  const DEMO_RESULTS = {
    title: "The Lion and the Mouse",
    level: "Grade 3",
    genre: "Fable",
    lines: ["A lion was asleep in the forest one quiet afternoon."],
    finishedLine: "With his sharp little teeth, the mouse chewed through the ropes and set the lion free.",
    lineCount: 12,
    seconds: 96,
    totalWords: 108,
    wpm: 68,
    everUsedManual: true,
    accuracy: 91,
    trickyWords: ["squeaked", "tangled"]
  };

  let results = DEMO_RESULTS;
  try {
    const raw = sessionStorage.getItem('readpilot-results');
    if (raw) {
      const parsed = JSON.parse(raw);
      if (parsed && typeof parsed.wpm === 'number') results = parsed;
    }
  } catch (e) { /* sessionStorage unavailable */ }

  document.title = 'ReadPilot — Results \u00B7 ' + (results.title || 'Story');

  /* ---------- Populate the frozen backdrop ---------- */
  const m0 = Math.floor((results.seconds || 0) / 60), s0 = (results.seconds || 0) % 60;
  document.getElementById('bgTimer').textContent = m0 + ':' + String(s0).padStart(2, '0');
  document.getElementById('bgMeta').textContent =
    (results.genre ? results.genre.toUpperCase() + ' \u00B7 ' : '') + (results.level ? results.level.toUpperCase() : '') + ' \u00B7 COMPLETE';

  const bgSentence = document.getElementById('bgSentence');
  const finishedWords = (results.finishedLine || '').split(/\s+/).filter(Boolean);
  finishedWords.forEach(w => {
    const span = document.createElement('span');
    span.className = 'bg-word';
    span.textContent = w;
    bgSentence.appendChild(span);
  });

  const bgDots = document.getElementById('bgDots');
  const lineCount = results.lineCount || (results.lines ? results.lines.length : 1);
  for (let i = 0; i < lineCount; i++){
    const d = document.createElement('div');
    d.className = 'bg-dot';
    bgDots.appendChild(d);
  }
  document.getElementById('bgLineCount').textContent = `Line ${lineCount} / ${lineCount}`;

  /* ---------- Populate the results card ---------- */
  document.getElementById('resultsSub').textContent = results.title
    ? `You finished "${results.title}"`
    : "Here's how the reading went";
  document.getElementById('statWPM').textContent = results.wpm;
  const m = Math.floor((results.seconds || 0) / 60), s = (results.seconds || 0) % 60;
  document.getElementById('statTime').textContent = m + ':' + String(s).padStart(2, '0');
  document.getElementById('statWords').textContent = results.totalWords;

  const accuracyWrap = document.getElementById('accuracyWrap');
  const trickyBox = document.getElementById('trickyBox');
  if (results.everUsedManual){
    const accuracy = typeof results.accuracy === 'number' ? results.accuracy : 100;
    document.getElementById('accuracyValue').textContent = accuracy + '%';
    requestAnimationFrame(() => {
      document.getElementById('accuracyFill').style.width = accuracy + '%';
    });
    accuracyWrap.style.display = 'block';

    if (results.trickyWords && results.trickyWords.length){
      trickyBox.innerHTML = '<i class="bx bx-flag"></i>Tricky words this time: ' +
        results.trickyWords.map(w => `<strong>${escapeHtml(w)}</strong>`).join(', ');
      trickyBox.style.display = 'block';
    }
  } else {
    accuracyWrap.style.display = 'none';
  }

  // Performance grade, same thresholds ReadPilot has always used.
  const wpm = results.wpm || 0;
  let gradeText, gradeClass;
  if (wpm >= 90)      { gradeText = '⭐ Excellent Reader'; gradeClass = 'excellent'; }
  else if (wpm >= 70)  { gradeText = '👍 Good Reader';      gradeClass = 'good'; }
  else if (wpm >= 50)  { gradeText = '📘 Fair Progress';    gradeClass = 'fair'; }
  else                 { gradeText = '💪 Keep Practicing';  gradeClass = 'needs-work'; }
  const gradePill = document.getElementById('gradePill');
  gradePill.textContent = gradeText;
  gradePill.classList.add(gradeClass);

  function escapeHtml(str){
    return String(str).replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]));
  }

  // Active session id & button routing
  const params = new URLSearchParams(window.location.search);
  const currentSessionId = params.get('session_id') || results.sessionId || '';

  const quizBtn = document.getElementById('quizBtn');
  if (quizBtn) {
    quizBtn.href = 'quiz.php' + (currentSessionId ? '?session_id=' + encodeURIComponent(currentSessionId) : '');
  }

  const progressBtn = document.getElementById('progressBtn');
  if (progressBtn && results.studentId) {
    progressBtn.href = 'progress.php?id=' + encodeURIComponent(results.studentId);
  }

  // Fetch AI Evaluation for current session
  if (currentSessionId) {
    const aiBox = document.getElementById('aiEvalBox');
    const aiBadge = document.getElementById('aiEvalBadge');
    const aiNarrative = document.getElementById('aiEvalNarrative');
    const aiTags = document.getElementById('aiEvalTags');
    aiBox.style.display = 'block';

    const renderEval = (ev) => {
      const statusLabels = {
        rapid_growth: 'Rapid Growth 🚀',
        on_track: 'On Track ✨',
        steady: 'Steady 📈',
        needs_intervention: 'Needs Support 🎯'
      };
      aiBadge.className = 'ai-eval-badge ' + (ev.overall_progress_status || 'on_track');
      aiBadge.textContent = statusLabels[ev.overall_progress_status] || ev.overall_progress_status;
      aiNarrative.textContent = ev.progress_narrative || 'Evaluation complete.';
      
      let tagsHtml = '';
      if (ev.fluency_rating) tagsHtml += `<span class="ai-eval-tag"><i class='bx bx-tachometer'></i> Fluency: ${escapeHtml(ev.fluency_rating)}</span>`;
      if (ev.comprehension_rating) tagsHtml += `<span class="ai-eval-tag"><i class='bx bx-book-reader'></i> Comprehension: ${escapeHtml(ev.comprehension_rating)}</span>`;
      if (ev.phonics_insight) tagsHtml += `<span class="ai-eval-tag"><i class='bx bx-bulb'></i> ${escapeHtml(ev.phonics_insight)}</span>`;
      aiTags.innerHTML = tagsHtml;
    };

    fetch('ai-api.php?action=get_evaluation&session_id=' + encodeURIComponent(currentSessionId))
      .then(r => r.json())
      .then(data => {
        if (data && data.evaluation) {
          renderEval(data.evaluation);
        } else {
          // If not evaluated yet, trigger on-the-fly evaluation
          fetch('ai-api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ action: 'evaluate_session', session_id: currentSessionId })
          })
          .then(r => r.json())
          .then(evalRes => {
            if (evalRes && evalRes.evaluation) renderEval(evalRes.evaluation);
          }).catch(() => {});
        }
      })
      .catch(() => {});
  }

  /* ---------- Read Again: send the student back into the same story ---------- */
  document.getElementById('readAgainBtn').addEventListener('click', () => {
    window.location.href = 'reading.php';
  });
</script>

</body>
</html>