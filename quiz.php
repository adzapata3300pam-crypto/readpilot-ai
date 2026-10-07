<?php require_once __DIR__ . '/auth-guard.php'; require_teacher(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Quiz</title>
<script src="theme-init.js"></script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  /* =====================================================================
     Same palette + stage anatomy as reading.php / results.php, so the
     quiz feels like a continuation of the same product rather than a
     bolted-on feature.
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
  .page{display:flex;flex-direction:column;height:100vh;}

  /* ---------- Top bar ---------- */
  .topbar{
    display:flex;align-items:center;justify-content:space-between;
    padding:16px 28px;
    border-bottom:2px solid var(--accent-strong);
    flex-shrink:0;
  }
  .brand{display:flex;align-items:center;gap:10px;}
  .brand .bx{font-size:22px;color:var(--accent);}
  .brand-text{font-family:'Poppins',sans-serif;font-weight:700;font-size:17px;color:var(--ink);}
  .top-right{display:flex;align-items:center;gap:12px;}

  .theme-toggle{
    display:flex;align-items:center;justify-content:center;
    width:38px;height:38px;border-radius:50%;
    background:var(--chip-bg);border:1px solid var(--line);color:var(--ink);
    cursor:pointer;font-family:inherit;flex-shrink:0;
    transition:background .15s ease, transform .25s ease;
  }
  .theme-toggle:hover{background:var(--chip-bg-hover);transform:rotate(18deg);}
  .theme-toggle .bx{font-size:18px;}

  .teacher-chip, .exit-btn{
    display:flex;align-items:center;gap:6px;
    background:var(--chip-bg);border:1px solid var(--line);color:var(--ink);
    padding:8px 16px;border-radius:20px;font-size:12.5px;font-weight:800;
    cursor:pointer;text-decoration:none;font-family:inherit;
  }
  .teacher-chip:hover, .exit-btn:hover{background:var(--chip-bg-hover);}
  .teacher-chip .bx{font-size:14px;color:#ffb877;}
  .exit-btn .bx{font-size:14px;}

  /* ---------- Stage ---------- */
  .stage{
    flex-grow:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:24px;gap:20px;min-height:0;overflow-y:auto;
  }
  .stage-head{
    display:flex;flex-direction:column;align-items:center;gap:8px;
    width:100%;max-width:640px;
  }
  .meta-label{
    font-size:12px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--muted);
    text-align:center;
  }
  .source-chip{
    display:inline-flex;align-items:center;gap:6px;
    background:var(--chip-bg);border:1px solid var(--line);
    padding:5px 14px;border-radius:20px;font-size:11px;font-weight:800;color:var(--muted);
  }
  .source-chip.teacher{color:#ffb877;border-color:rgba(255,184,119,.35);background:rgba(255,184,119,.08);}
  .source-chip .bx{font-size:13px;}

  .quiz-area{width:100%;max-width:640px;display:flex;justify-content:center;}

  /* ---------- Question card ---------- */
  .q-card{
    width:100%;
    background:var(--stage-card);
    border:1px solid var(--line);
    border-radius:24px;
    padding:30px 26px;
    animation:cardIn .35s cubic-bezier(.2,.9,.25,1.15);
  }
  @keyframes cardIn{
    from{opacity:0;transform:translateY(14px) scale(.98);}
    to{opacity:1;transform:translateY(0) scale(1);}
  }
  .q-prompt{
    font-family:'Poppins',sans-serif;font-weight:700;font-size:18px;
    line-height:1.5;color:var(--ink);margin-bottom:22px;
  }
  .q-options{display:flex;flex-direction:column;gap:10px;}
  .q-option{
    display:flex;align-items:center;gap:12px;
    background:var(--chip-bg);border:1.5px solid var(--line);
    border-radius:14px;padding:13px 16px;
    cursor:pointer;text-align:left;font-family:'Nunito',sans-serif;
    font-size:14.5px;font-weight:700;color:var(--ink);
    transition:background .15s ease, border-color .15s ease, transform .1s ease;
  }
  .q-option:hover:not(:disabled){background:var(--chip-bg-hover);border-color:var(--accent);}
  .q-option:active:not(:disabled){transform:scale(.99);}
  .q-option:disabled{cursor:default;}
  .q-option .opt-badge{
    flex-shrink:0;width:26px;height:26px;border-radius:50%;
    background:rgba(255,255,255,0.08);
    display:flex;align-items:center;justify-content:center;
    font-family:'Poppins',sans-serif;font-weight:800;font-size:12px;color:var(--muted);
  }
  .q-option.correct{background:rgba(143,214,124,0.16);border-color:var(--accent);}
  .q-option.correct .opt-badge{background:var(--accent-strong);color:var(--on-accent);}
  .q-option.incorrect{background:rgba(234,93,93,0.14);border-color:var(--red);}
  .q-option.incorrect .opt-badge{background:var(--red);color:#fff;}

  .q-feedback{
    display:none;margin-top:18px;padding:14px 16px;border-radius:14px;
    font-size:13px;font-weight:700;line-height:1.55;
    background:var(--chip-bg);border:1px solid var(--line);color:var(--muted);
  }
  .q-feedback.show{display:block;}
  .q-feedback .verdict{
    display:block;font-family:'Poppins',sans-serif;font-weight:800;font-size:14px;margin-bottom:4px;
  }
  .q-feedback.right .verdict{color:var(--accent);}
  .q-feedback.wrong .verdict{color:var(--red);}

  .q-next{
    display:none;margin-top:18px;width:100%;
    background:var(--accent-strong);border:none;color:var(--on-accent);
    font-family:'Poppins',sans-serif;font-weight:800;font-size:14px;
    padding:14px;border-radius:14px;cursor:pointer;
  }
  .q-next.show{display:block;}
  .q-next:hover{background:var(--accent);}

  /* ---------- Quiz results card (mirrors results.php) ---------- */
  .quiz-results-card{
    width:100%;
    background:var(--stage-card);border:1px solid var(--line);border-radius:26px;
    padding:36px 28px 26px;text-align:center;
    animation:cardIn .4s cubic-bezier(.2,.9,.25,1.15);
  }
  .qr-badge{
    width:64px;height:64px;border-radius:50%;
    background:linear-gradient(135deg, var(--accent) 0%, var(--accent-strong) 100%);
    color:var(--on-accent);display:flex;align-items:center;justify-content:center;
    margin:0 auto 16px;font-size:28px;box-shadow:0 12px 28px rgba(111,191,90,0.4);
  }
  .qr-title{font-family:'Poppins',sans-serif;font-weight:800;font-size:21px;margin:0 0 4px;}
  .qr-sub{font-size:13px;font-weight:700;color:var(--muted);margin:0 0 22px;}
  .qr-score{
    font-family:'Poppins',sans-serif;font-weight:800;font-size:40px;color:var(--accent);
    margin-bottom:4px;
  }
  .qr-score span{font-size:18px;color:var(--muted);font-weight:700;}
  .qr-track{height:9px;background:var(--line);border-radius:99px;overflow:hidden;max-width:320px;margin:0 auto 18px;}
  .qr-fill{height:100%;border-radius:99px;background:linear-gradient(90deg, var(--accent-strong), var(--accent));width:0%;transition:width .8s cubic-bezier(.2,.9,.25,1) .2s;}
  .qr-grade{
    display:inline-flex;padding:9px 20px;border-radius:99px;margin-bottom:22px;
    font-family:'Poppins',sans-serif;font-size:13.5px;font-weight:800;
  }
  .qr-grade.excellent{background:rgba(143,214,124,0.18);color:var(--accent);border:1px solid rgba(143,214,124,.35);}
  .qr-grade.good{background:rgba(111,191,90,0.18);color:var(--accent-strong);border:1px solid rgba(111,191,90,.35);}
  .qr-grade.fair{background:rgba(255,184,119,0.18);color:#ffb877;border:1px solid rgba(255,184,119,.35);}
  .qr-grade.needs-work{background:rgba(234,93,93,0.16);color:var(--red);border:1px solid rgba(234,93,93,.35);}
  .qr-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
  .qr-actions .full{grid-column:1 / -1;}
  .qr-btn{
    display:flex;align-items:center;justify-content:center;gap:7px;
    font-family:'Poppins',sans-serif;font-weight:700;font-size:13.5px;
    padding:13px 10px;border-radius:14px;cursor:pointer;
    text-decoration:none;border:1px solid var(--line);
  }
  .qr-btn.secondary{background:var(--chip-bg);color:var(--ink);}
  .qr-btn.secondary:hover{background:var(--chip-bg-hover);}
  .qr-btn.primary{background:var(--accent-strong);border-color:var(--accent-strong);color:var(--on-accent);}
  .qr-btn.primary:hover{background:var(--accent);}

  /* ---------- Bottom bar ---------- */
  .bottombar{
    flex-shrink:0;
    display:flex;align-items:center;justify-content:space-between;gap:16px;
    padding:14px 26px;
    background:linear-gradient(90deg, #cbe98f, #b1db65);
    color:#16281d;
  }
  .q-count{font-size:12.5px;font-weight:800;white-space:nowrap;}
  .progress-track{
    position:relative;flex-grow:1;height:8px;border-radius:6px;
    background:rgba(22,40,29,0.18);overflow:hidden;
  }
  .progress-fill{height:100%;background:#16281d;border-radius:6px;width:0%;transition:width .3s ease;}
  .score-chip{font-size:12.5px;font-weight:800;white-space:nowrap;}

  /* ---------- Toast ---------- */
  .toast{
    position:fixed;bottom:24px;right:24px;background:#16281d;color:#fff;
    padding:13px 20px;border-radius:12px;font-size:13px;font-weight:700;
    box-shadow:0 10px 30px rgba(0,0,0,0.35);z-index:500;
    display:flex;align-items:center;gap:10px;
    opacity:0;transform:translateY(10px);pointer-events:none;
    transition:opacity .25s ease, transform .25s ease;
  }
  .toast.show{opacity:1;transform:translateY(0);}
  .toast .bx{color:var(--accent);font-size:16px;}

  /* ══════════════════════════════════════════════════════
     TEACHER QUIZ BUILDER PANEL
     ══════════════════════════════════════════════════════ */
  .builder-panel{
    position:fixed;top:0;right:0;height:100vh;width:400px;max-width:94vw;
    background:var(--stage-card);border-left:1px solid var(--line);
    box-shadow:-14px 0 40px rgba(0,0,0,0.35);
    display:flex;flex-direction:column;
    transform:translateX(100%);transition:transform .25s ease, background .3s ease;
    z-index:300;
  }
  .builder-panel.show{transform:translateX(0);}
  .builder-head{
    display:flex;align-items:center;justify-content:space-between;
    padding:18px 20px;border-bottom:1px solid var(--line);flex-shrink:0;
  }
  .builder-head h3{font-family:'Poppins',sans-serif;font-weight:700;font-size:15px;color:var(--ink);}
  .builder-head button{
    background:var(--chip-bg);border:none;color:var(--ink);
    width:30px;height:30px;border-radius:50%;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
  }
  .builder-head button:hover{background:var(--chip-bg-hover);}

  .builder-body{flex-grow:1;overflow-y:auto;padding:16px 18px;display:flex;flex-direction:column;gap:10px;}
  .builder-hint{font-size:12px;font-weight:600;color:var(--muted);line-height:1.5;margin-bottom:2px;}

  .bq-item{
    background:var(--chip-bg);border:1px solid var(--line);border-radius:14px;padding:12px 14px;
  }
  .bq-prompt{font-family:'Poppins',sans-serif;font-weight:700;font-size:13px;color:var(--ink);margin-bottom:8px;line-height:1.4;}
  .bq-opts{display:flex;flex-direction:column;gap:4px;margin-bottom:8px;}
  .bq-opt{font-size:12px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:6px;}
  .bq-opt.is-correct{color:var(--accent);font-weight:800;}
  .bq-opt .bx{font-size:13px;}
  .bq-actions{display:flex;gap:8px;}
  .bq-actions button{
    flex:1;display:flex;align-items:center;justify-content:center;gap:5px;
    background:transparent;border:1px solid var(--line);color:var(--muted);
    font-family:inherit;font-weight:700;font-size:11.5px;
    padding:7px;border-radius:10px;cursor:pointer;
  }
  .bq-actions button:hover{background:var(--chip-bg-hover);color:var(--ink);}
  .bq-actions button.danger:hover{color:var(--red);border-color:var(--red);}

  .builder-empty{font-size:12.5px;font-weight:700;color:var(--muted);text-align:center;padding:24px 8px;}

  .qb-add-btn{
    width:100%;background:var(--chip-bg);border:1.5px dashed var(--line);color:var(--ink);
    font-family:'Poppins',sans-serif;font-weight:700;font-size:13px;
    padding:12px;border-radius:14px;cursor:pointer;
    display:flex;align-items:center;justify-content:center;gap:7px;
  }
  .qb-add-btn:hover{background:var(--chip-bg-hover);border-color:var(--accent);}

  .qb-form{
    background:var(--chip-bg);border:1px solid var(--line);border-radius:14px;padding:14px;
    display:flex;flex-direction:column;gap:10px;
  }
  .qb-form label{font-size:11px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);}
  .qb-form textarea, .qb-form input[type="text"]{
    width:100%;background:var(--stage-card);border:1.5px solid var(--line);border-radius:10px;
    padding:9px 11px;color:var(--ink);font-family:'Nunito',sans-serif;font-size:13px;font-weight:600;
    resize:vertical;
  }
  .qb-form textarea:focus, .qb-form input:focus{outline:none;border-color:var(--accent);}
  .qb-opt-row{display:flex;align-items:center;gap:8px;}
  .qb-opt-row input[type="radio"]{accent-color:var(--accent-strong);width:16px;height:16px;flex-shrink:0;}
  .qb-remove-opt{
    flex-shrink:0;background:transparent;border:none;color:var(--muted);cursor:pointer;font-size:16px;
    display:flex;align-items:center;justify-content:center;width:22px;height:22px;
  }
  .qb-remove-opt:hover{color:var(--red);}
  .qb-add-opt{
    align-self:flex-start;background:none;border:none;color:var(--accent);font-weight:800;
    font-size:12px;cursor:pointer;padding:2px 0;
  }
  .qb-form-actions{display:flex;gap:8px;margin-top:2px;}
  .qb-form-actions button{
    flex:1;font-family:'Poppins',sans-serif;font-weight:800;font-size:12.5px;
    padding:10px;border-radius:10px;cursor:pointer;border:1px solid var(--line);
  }
  .qb-form-actions .qb-cancel{background:transparent;color:var(--muted);}
  .qb-form-actions .qb-cancel:hover{background:var(--chip-bg-hover);}
  .qb-form-actions .qb-save{background:var(--accent-strong);border-color:var(--accent-strong);color:var(--on-accent);}
  .qb-form-actions .qb-save:hover{background:var(--accent);}

  .builder-foot{padding:14px 18px;border-top:1px solid var(--line);display:flex;flex-direction:column;gap:8px;flex-shrink:0;}
  .builder-foot button, .builder-foot a{
    display:flex;align-items:center;justify-content:center;gap:7px;
    font-family:'Poppins',sans-serif;font-weight:800;font-size:13px;
    padding:12px;border-radius:12px;cursor:pointer;border:1px solid var(--line);
    text-decoration:none;
  }
  .bf-save{background:var(--accent-strong);border-color:var(--accent-strong);color:var(--on-accent);}
  .bf-save:hover{background:var(--accent);}
  .bf-reset{background:transparent;color:var(--ink);}
  .bf-reset:hover{background:var(--chip-bg-hover);}
  .bf-clear{background:transparent;color:var(--red);border-color:rgba(234,93,93,.35);}
  .bf-clear:hover{background:rgba(234,93,93,.1);}

  @media (max-width:640px){
    .topbar{padding:14px 18px;}
    .stage{padding:16px;}
    .q-card{padding:24px 20px;}
    .q-prompt{font-size:16px;}
  }
</style>
</head>
<body>
  <div class="page">
    <div class="topbar">
      <div class="brand">
        <i class='bx bxs-paper-plane'></i>
        <span class="brand-text">ReadPilot</span>
      </div>
      <div class="top-right">
        <button class="theme-toggle" id="themeToggle" aria-label="Switch to light mode">
          <i class='bx bx-sun' id="themeIcon"></i>
        </button>
        <button class="teacher-chip" id="teacherBtn">
          <i class='bx bx-edit-alt'></i> Build Your Own Quiz
        </button>
        <a class="exit-btn" href="resources.php">
          <i class='bx bx-x'></i> Exit
        </a>
      </div>
    </div>

    <div class="stage">
      <div class="stage-head">
        <div class="meta-label" id="metaLabel">Loading story...</div>
        <div class="source-chip" id="sourceChip"><i class='bx bx-magic-wand'></i> Auto-generated quiz</div>
      </div>

      <div class="quiz-area" id="quizArea"></div>
    </div>

    <div class="bottombar">
      <div class="q-count" id="qCount">Question 1 / 1</div>
      <div class="progress-track"><div class="progress-fill" id="progressFill"></div></div>
      <div class="score-chip" id="scoreChip">Score: 0 / 0</div>
    </div>
  </div>

  <div class="toast" id="toast"><i class='bx bxs-info-circle'></i><span id="toastMsg">Done</span></div>

  <!-- ══ TEACHER QUIZ BUILDER ══ -->
  <div class="builder-panel" id="builderPanel">
    <div class="builder-head">
      <h3>Quiz Builder</h3>
      <button id="closeBuilder" aria-label="Close"><i class='bx bx-x'></i></button>
    </div>
    <div class="builder-body" id="builderBody">
      <div class="builder-hint">
        Edit the auto-generated questions below, delete ones you don't want, or add your own. Save when you're ready — students will see exactly this quiz.
      </div>
      <div id="builderList"></div>
      <button class="qb-add-btn" id="qbAddBtn"><i class='bx bx-plus'></i> Add a question</button>

      <div class="qb-form" id="qbForm" style="display:none;">
        <label>Question</label>
        <textarea id="qbPrompt" rows="2" placeholder="e.g. Why did the mouse help the lion?"></textarea>
        <label>Options — select the correct one</label>
        <div id="qbOptions"></div>
        <button type="button" class="qb-add-opt" id="qbAddOpt">+ Add another option</button>
        <label>Explanation (optional)</label>
        <input type="text" id="qbExplanation" placeholder="Shown to students after they answer">
        <div class="qb-form-actions">
          <button type="button" class="qb-cancel" id="qbCancel">Cancel</button>
          <button type="button" class="qb-save" id="qbSave">Add Question</button>
        </div>
      </div>
    </div>
    <div class="builder-foot">
      <button class="bf-save" id="bfSave"><i class='bx bx-save'></i> Save &amp; Use This Quiz</button>
      <button class="bf-reset" id="bfReset"><i class='bx bx-refresh'></i> Reset to Auto-Generated</button>
      <button class="bf-clear" id="bfClear"><i class='bx bx-trash'></i> Clear Saved Quiz</button>
    </div>
  </div>

<script>
  /* =====================================================================
     Theme — shared with reading.php / results.php
  ===================================================================== */
  const THEME_KEY = 'readpilot-theme';
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');
  let currentTheme = 'dark';
  try { currentTheme = localStorage.getItem(THEME_KEY) || 'dark'; } catch (e) {}
  function applyTheme(theme){
    document.documentElement.classList.toggle('light-theme', theme === 'light');
    themeIcon.className = theme === 'light' ? 'bx bx-moon' : 'bx bx-sun';
    themeToggle.setAttribute('aria-label', theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
    try { localStorage.setItem(THEME_KEY, theme); } catch (e) {}
  }
  applyTheme(currentTheme);
  themeToggle.addEventListener('click', () => {
    currentTheme = currentTheme === 'light' ? 'dark' : 'light';
    applyTheme(currentTheme);
  });

  /* =====================================================================
     Toast helper
  ===================================================================== */
  let toastTimer;
  function showToast(message){
    const toast = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = message;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
  }

  /* =====================================================================
     Load the story this quiz is about. Priority:
     1. 'readpilot-quiz-story'    — handed off from results.php ("Take the Quiz")
     2. 'readpilot-reading-session' — the story currently loaded in reading.php
     3. A small demo story, so this page still works if opened directly.
  ===================================================================== */
  const DEMO_STORY = {
    title: "The Lion and the Mouse",
    level: "Grade 3",
    genre: "Fable",
    lines: [
      "A lion was asleep in the forest one quiet afternoon.",
      "A tiny mouse scurried across his paw and woke him up.",
      "The lion opened one eye and let out an angry roar.",
      "He grabbed the mouse in his big paw, ready to eat him.",
      "Please let me go, squeaked the mouse. One day I might help you too.",
      "The lion laughed at the idea, but he let the little mouse go free.",
      "A few days later, the lion got tangled in a hunters net.",
      "He roared and roared, but the ropes only pulled tighter.",
      "The tiny mouse heard him and came running as fast as he could.",
      "With his sharp little teeth, the mouse chewed through the ropes and set the lion free.",
      "You laughed when I said I could help you, said the mouse with a smile.",
      "Now you know that even the smallest friend can make the biggest difference."
    ]
  };

  let story = DEMO_STORY;
  try {
    const fromResults = sessionStorage.getItem('readpilot-quiz-story');
    const fromReading = sessionStorage.getItem('readpilot-reading-session');
    const raw = fromResults || fromReading;
    if (raw) {
      const parsed = JSON.parse(raw);
      if (parsed && Array.isArray(parsed.lines) && parsed.lines.length) story = parsed;
    }
  } catch (e) { /* storage unavailable — use demo story */ }

  document.title = 'ReadPilot — Quiz \u00B7 ' + story.title;
  document.getElementById('metaLabel').textContent =
    story.title + (story.genre ? ' \u00B7 ' + story.genre : '') + (story.level ? ' \u00B7 ' + story.level : '');

  function slugify(str){
    return (str || 'story').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '') || 'story';
  }
  const storySlug = slugify(story.title);
  const QUIZ_KEY = 'readpilot-quiz-' + storySlug;

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]));
  }

  /* =====================================================================
     AUTO QUIZ GENERATOR
     Rule-based, entirely client-side — no external calls. It builds a mix
     of question types straight from the story's own sentences:
       - fill-in-the-blank (a content word removed from a real line)
       - true/false recall (a real line, sometimes with one word swapped)
       - "which happens first" ordering
       - "what happens next" comprehension
  ===================================================================== */
  const STOPWORDS = new Set(['a','an','the','and','but','or','nor','in','on','at','to','of','was','were','is','are',
    'he','she','it','they','his','her','their','with','for','that','this','as','had','has','have','one','two',
    'big','little','up','down','out','near','from','by','so','too','then','when','while','before','after','than',
    'into','over','under','about','again','once','more','most','some','such','no','not','only','own','same','very',
    'just','now','all','be','been','being','you','your','yours','them','him','i','me','my']);

  function cleanWord(w){ return (w || '').replace(/[^a-zA-Z']/g, ''); }
  function contentWords(line){
    return line.split(/\s+/).map(cleanWord).filter(w => w.length > 3 && !STOPWORDS.has(w.toLowerCase()));
  }
  function shuffle(arr){
    const a = arr.slice();
    for (let i = a.length - 1; i > 0; i--){
      const j = Math.floor(Math.random() * (i + 1));
      [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
  }
  function pick(arr, n){ return shuffle(arr).slice(0, n); }

  function replaceWordInLine(line, targetWord){
    const tokens = line.split(/\s+/);
    for (let i = 0; i < tokens.length; i++){
      if (cleanWord(tokens[i]) === targetWord){ tokens[i] = '_____'; return tokens.join(' '); }
    }
    return line;
  }
  function swapWordInLine(line, targetWord, replacement){
    const tokens = line.split(/\s+/);
    for (let i = 0; i < tokens.length; i++){
      if (cleanWord(tokens[i]) === targetWord){
        const trailing = (tokens[i].match(/[.,!?;:"')]+$/) || [''])[0];
        tokens[i] = replacement + trailing;
        return tokens.join(' ');
      }
    }
    return line;
  }
  function makeOptionSet(correctText, distractorTexts){
    const options = shuffle([correctText, ...distractorTexts]).map((text, i) => ({ id: String.fromCharCode(97 + i), text }));
    const correctOptionId = options.find(o => o.text === correctText).id;
    return { options, correctOptionId };
  }

  function generateQuizFromStory(lines){
    const qs = [];
    if (!lines || !lines.length) return qs;

    const wordPool = [];
    lines.forEach((line, li) => contentWords(line).forEach(w => wordPool.push({ word: w, line: li })));

    // ---- Fill in the blank ----
    const blankLines = shuffle(lines.map((l, i) => i)).filter(i => contentWords(lines[i]).length > 0);
    blankLines.slice(0, 3).forEach(li => {
      const words = contentWords(lines[li]);
      const answer = words[Math.floor(Math.random() * words.length)];
      const distractorPool = [...new Set(wordPool.map(w => w.word))].filter(w => w.toLowerCase() !== answer.toLowerCase());
      if (distractorPool.length < 3) return;
      const distractors = pick(distractorPool, 3);
      const { options, correctOptionId } = makeOptionSet(answer, distractors);
      qs.push({
        id: 'fill-' + li,
        prompt: 'Fill in the blank: "' + replaceWordInLine(lines[li], answer) + '"',
        options, correctOptionId,
        explanation: 'The full sentence is: "' + lines[li] + '"'
      });
    });

    // ---- True / False recall ----
    shuffle(lines.map((l, i) => i)).slice(0, 3).forEach((li, k) => {
      let statement = lines[li];
      let isTrue = true;
      if (Math.random() < 0.5 && lines.length > 1){
        const myWords = contentWords(lines[li]);
        const otherWords = wordPool.filter(w => w.line !== li).map(w => w.word);
        if (myWords.length && otherWords.length){
          const swapWord = myWords[Math.floor(Math.random() * myWords.length)];
          const replacement = otherWords[Math.floor(Math.random() * otherWords.length)];
          const swapped = swapWordInLine(lines[li], swapWord, replacement);
          if (swapped !== lines[li]){ statement = swapped; isTrue = false; }
        }
      }
      qs.push({
        id: 'tf-' + li + '-' + k,
        prompt: 'True or False: "' + statement + '" is a sentence from the story.',
        options: [{ id: 't', text: 'True' }, { id: 'f', text: 'False' }],
        correctOptionId: isTrue ? 't' : 'f',
        explanation: isTrue
          ? 'Correct \u2014 that sentence appears in the story as written.'
          : 'Not quite \u2014 the story actually says: "' + lines[li] + '"'
      });
    });

    // ---- Which happens first ----
    if (lines.length >= 4){
      const used = new Set();
      let added = 0, attempts = 0;
      while (added < 2 && attempts < 30){
        attempts++;
        const i = Math.floor(Math.random() * lines.length);
        const j = Math.floor(Math.random() * lines.length);
        if (i === j || Math.abs(i - j) < 2) continue;
        const first = Math.min(i, j), second = Math.max(i, j);
        const key = first + '-' + second;
        if (used.has(key)) continue;
        used.add(key);
        const { options, correctOptionId } = makeOptionSet(lines[first], [lines[second]]);
        qs.push({
          id: 'order-' + key,
          prompt: 'Which of these happens first in the story?',
          options, correctOptionId,
          explanation: '"' + lines[first] + '" comes before "' + lines[second] + '" in the story.'
        });
        added++;
      }
    }

    // ---- What happens next ----
    if (lines.length >= 3){
      shuffle(lines.map((l, i) => i).filter(i => i < lines.length - 1)).slice(0, 2).forEach(li => {
        const correct = lines[li + 1];
        const distractorIdx = shuffle(lines.map((l, i) => i).filter(i => i !== li && i !== li + 1)).slice(0, 2);
        if (distractorIdx.length < 2) return;
        const { options, correctOptionId } = makeOptionSet(correct, distractorIdx.map(i => lines[i]));
        qs.push({
          id: 'next-' + li,
          prompt: 'What happens right after: "' + lines[li] + '"',
          options, correctOptionId,
          explanation: 'Right \u2014 the next line in the story is: "' + correct + '"'
        });
      });
    }

    return shuffle(qs);
  }

  /* =====================================================================
     Custom (teacher-made) quiz persistence — keyed per story, so a saved
     quiz reappears automatically every time a student opens this story.
  ===================================================================== */
  function loadCustomQuiz(){
    try {
      const raw = localStorage.getItem(QUIZ_KEY);
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (parsed && Array.isArray(parsed.questions) && parsed.questions.length) return parsed;
    } catch (e) {}
    return null;
  }
  function saveCustomQuiz(questions){
    try {
      localStorage.setItem(QUIZ_KEY, JSON.stringify({ questions, updatedAt: new Date().toISOString() }));
      return true;
    } catch (e) { return false; }
  }
  function clearCustomQuiz(){
    try { localStorage.removeItem(QUIZ_KEY); } catch (e) {}
  }

  /* =====================================================================
     Active quiz state (what the student is currently taking)
  ===================================================================== */
  let activeQuestions = [];
  let quizSource = 'auto';
  let qIndex = 0;
  let score = 0;
  let answered = false;

  const sourceChip = document.getElementById('sourceChip');
  function updateSourceChip(){
    if (quizSource === 'teacher'){
      sourceChip.className = 'source-chip teacher';
      sourceChip.innerHTML = "<i class='bx bx-chalkboard'></i> Teacher's quiz";
    } else {
      sourceChip.className = 'source-chip';
      sourceChip.innerHTML = "<i class='bx bx-magic-wand'></i> Auto-generated quiz";
    }
  }

  function loadActiveQuiz(){
    const custom = loadCustomQuiz();
    if (custom){
      activeQuestions = custom.questions;
      quizSource = 'teacher';
    } else {
      activeQuestions = generateQuizFromStory(story.lines);
      quizSource = 'auto';
    }
    updateSourceChip();
  }

  const quizArea = document.getElementById('quizArea');
  const qCountEl = document.getElementById('qCount');
  const progressFillEl = document.getElementById('progressFill');
  const scoreChipEl = document.getElementById('scoreChip');

  function startQuiz(){
    qIndex = 0;
    score = 0;
    answered = false;
    if (!activeQuestions.length){
      quizArea.innerHTML = '<div class="q-card"><div class="q-prompt">This story is too short to build a quiz from yet. Try Teacher Mode to add your own questions.</div></div>';
      qCountEl.textContent = 'Question 0 / 0';
      progressFillEl.style.width = '0%';
      scoreChipEl.textContent = 'Score: 0 / 0';
      return;
    }
    renderQuestion();
  }

  function renderQuestion(){
    answered = false;
    const total = activeQuestions.length;
    const q = activeQuestions[qIndex];
    qCountEl.textContent = `Question ${qIndex + 1} / ${total}`;
    progressFillEl.style.width = Math.round((qIndex / total) * 100) + '%';
    scoreChipEl.textContent = `Score: ${score} / ${qIndex}`;

    const optionsHtml = q.options.map(opt => `
      <button class="q-option" data-id="${escapeHtml(opt.id)}">
        <span class="opt-badge">${escapeHtml(opt.id.toUpperCase())}</span>
        <span>${escapeHtml(opt.text)}</span>
      </button>
    `).join('');

    quizArea.innerHTML = `
      <div class="q-card">
        <div class="q-prompt">${escapeHtml(q.prompt)}</div>
        <div class="q-options">${optionsHtml}</div>
        <div class="q-feedback" id="qFeedback"></div>
        <button class="q-next" id="qNext">${qIndex === total - 1 ? 'See Results' : 'Next Question'} <i class='bx bx-right-arrow-alt'></i></button>
      </div>
    `;

    quizArea.querySelectorAll('.q-option').forEach(btn => {
      btn.addEventListener('click', () => selectAnswer(btn.dataset.id));
    });
  }

  function selectAnswer(chosenId){
    if (answered) return;
    answered = true;
    const q = activeQuestions[qIndex];
    const correct = chosenId === q.correctOptionId;
    if (correct) score++;

    quizArea.querySelectorAll('.q-option').forEach(btn => {
      btn.disabled = true;
      if (btn.dataset.id === q.correctOptionId) btn.classList.add('correct');
      else if (btn.dataset.id === chosenId) btn.classList.add('incorrect');
    });

    const fb = document.getElementById('qFeedback');
    fb.classList.add('show', correct ? 'right' : 'wrong');
    fb.innerHTML = `<span class="verdict">${correct ? '\u2713 Correct!' : '\u2717 Not quite'}</span>${escapeHtml(q.explanation || '')}`;

    scoreChipEl.textContent = `Score: ${score} / ${qIndex + 1}`;
    document.getElementById('qNext').classList.add('show');
    document.getElementById('qNext').addEventListener('click', () => {
      if (qIndex < activeQuestions.length - 1){
        qIndex++;
        renderQuestion();
      } else {
        finishQuiz();
      }
    }, { once: true });
  }

  function finishQuiz(){
    const total = activeQuestions.length;
    progressFillEl.style.width = '100%';
    qCountEl.textContent = `Question ${total} / ${total}`;
    scoreChipEl.textContent = `Score: ${score} / ${total}`;

    const pct = total > 0 ? Math.round((score / total) * 100) : 0;
    let gradeText, gradeClass;
    if (pct >= 90)      { gradeText = '\u2B50 Excellent!';       gradeClass = 'excellent'; }
    else if (pct >= 70)  { gradeText = '\uD83D\uDC4D Good Job';    gradeClass = 'good'; }
    else if (pct >= 50)  { gradeText = '\uD83D\uDCD8 Fair Effort'; gradeClass = 'fair'; }
    else                 { gradeText = '\uD83D\uDCAA Keep Practicing'; gradeClass = 'needs-work'; }

    quizArea.innerHTML = `
      <div class="quiz-results-card">
        <div class="qr-badge"><i class='bx bxs-trophy'></i></div>
        <h2 class="qr-title">Quiz Complete!</h2>
        <p class="qr-sub">${escapeHtml(story.title)}</p>
        <div class="qr-score">${score}<span> / ${total}</span></div>
        <div class="qr-track"><div class="qr-fill" id="qrFill"></div></div>
        <div class="qr-grade ${gradeClass}">${gradeText}</div>
        <div class="qr-actions">
          <button class="qr-btn secondary" id="retakeBtn"><i class='bx bx-refresh'></i> Retake Quiz</button>
          <button class="qr-btn secondary" id="readAgainBtn"><i class='bx bx-book-open'></i> Read Again</button>
          <a class="qr-btn primary full" href="resources.php"><i class='bx bx-library'></i> Back to Library</a>
        </div>
      </div>
    `;
    requestAnimationFrame(() => { document.getElementById('qrFill').style.width = pct + '%'; });

    document.getElementById('retakeBtn').addEventListener('click', () => {
      activeQuestions = shuffle(activeQuestions);
      startQuiz();
    });
    document.getElementById('readAgainBtn').addEventListener('click', () => {
      try { sessionStorage.setItem('readpilot-reading-session', JSON.stringify(story)); } catch (e) {}
      window.location.href = 'reading.php';
    });
    if (story.studentId) {
      fetch('student-api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({action:'record_quiz', student_id:story.studentId, title:story.title, score:score, total_questions:total})}).catch(() => {});
    }
  }

  loadActiveQuiz();
  startQuiz();

  /* ══════════════════════════════════════════════════════
     TEACHER QUIZ BUILDER
     ══════════════════════════════════════════════════════ */
  const builderPanel = document.getElementById('builderPanel');
  const builderList = document.getElementById('builderList');
  const qbForm = document.getElementById('qbForm');
  const qbPrompt = document.getElementById('qbPrompt');
  const qbOptions = document.getElementById('qbOptions');
  const qbExplanation = document.getElementById('qbExplanation');

  let builderQuestions = []; // working copy while the panel is open

  function openBuilder(){
    // Start the builder from whatever quiz is currently active, so the
    // teacher edits/extends it rather than starting from nothing.
    builderQuestions = JSON.parse(JSON.stringify(activeQuestions));
    renderBuilderList();
    hideQbForm();
    builderPanel.classList.add('show');
  }
  function closeBuilder(){ builderPanel.classList.remove('show'); }

  document.getElementById('teacherBtn').addEventListener('click', openBuilder);
  document.getElementById('closeBuilder').addEventListener('click', closeBuilder);

  function renderBuilderList(){
    if (!builderQuestions.length){
      builderList.innerHTML = '<div class="builder-empty">No questions yet \u2014 add one below.</div>';
      return;
    }
    builderList.innerHTML = builderQuestions.map((q, i) => `
      <div class="bq-item" data-idx="${i}">
        <div class="bq-prompt">${i + 1}. ${escapeHtml(q.prompt)}</div>
        <div class="bq-opts">
          ${q.options.map(o => `
            <div class="bq-opt ${o.id === q.correctOptionId ? 'is-correct' : ''}">
              <i class='bx ${o.id === q.correctOptionId ? "bxs-check-circle" : "bx-circle"}'></i> ${escapeHtml(o.text)}
            </div>
          `).join('')}
        </div>
        <div class="bq-actions">
          <button type="button" class="bq-edit" data-idx="${i}"><i class='bx bx-edit-alt'></i> Edit</button>
          <button type="button" class="bq-delete danger" data-idx="${i}"><i class='bx bx-trash'></i> Delete</button>
        </div>
      </div>
    `).join('');

    builderList.querySelectorAll('.bq-delete').forEach(btn => {
      btn.addEventListener('click', () => {
        builderQuestions.splice(Number(btn.dataset.idx), 1);
        renderBuilderList();
      });
    });
    builderList.querySelectorAll('.bq-edit').forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = Number(btn.dataset.idx);
        loadQuestionIntoForm(builderQuestions[idx]);
        builderQuestions.splice(idx, 1);
        renderBuilderList();
      });
    });
  }

  function makeOptionRow(id, text, checked){
    const row = document.createElement('div');
    row.className = 'qb-opt-row';
    row.innerHTML = `
      <input type="radio" name="qbCorrect" value="${id}" ${checked ? 'checked' : ''}>
      <input type="text" class="qb-opt-text" placeholder="Option text" value="${escapeHtml(text || '')}">
      <button type="button" class="qb-remove-opt" aria-label="Remove option"><i class='bx bx-x'></i></button>
    `;
    row.querySelector('.qb-remove-opt').addEventListener('click', () => {
      if (qbOptions.querySelectorAll('.qb-opt-row').length > 2) row.remove();
      else showToast('A question needs at least 2 options');
    });
    return row;
  }

  function resetQbOptions(){
    qbOptions.innerHTML = '';
    ['a','b','c','d'].forEach((id, i) => qbOptions.appendChild(makeOptionRow(id, '', i === 0)));
  }

  document.getElementById('qbAddOpt').addEventListener('click', () => {
    const rows = qbOptions.querySelectorAll('.qb-opt-row');
    if (rows.length >= 6){ showToast('Up to 6 options per question'); return; }
    const nextId = String.fromCharCode(97 + rows.length);
    qbOptions.appendChild(makeOptionRow(nextId, '', false));
  });

  function showQbForm(){ qbForm.style.display = 'flex'; document.getElementById('qbAddBtn').style.display = 'none'; }
  function hideQbForm(){
    qbForm.style.display = 'none';
    document.getElementById('qbAddBtn').style.display = 'flex';
    qbPrompt.value = '';
    qbExplanation.value = '';
    resetQbOptions();
  }

  function loadQuestionIntoForm(q){
    showQbForm();
    qbPrompt.value = q.prompt;
    qbExplanation.value = q.explanation || '';
    qbOptions.innerHTML = '';
    q.options.forEach((o, i) => {
      qbOptions.appendChild(makeOptionRow(String.fromCharCode(97 + i), o.text, o.id === q.correctOptionId));
    });
  }

  document.getElementById('qbAddBtn').addEventListener('click', () => { resetQbOptions(); showQbForm(); });
  document.getElementById('qbCancel').addEventListener('click', hideQbForm);

  document.getElementById('qbSave').addEventListener('click', () => {
    const prompt = qbPrompt.value.trim();
    if (!prompt){ showToast('Add a question first'); return; }

    const rows = Array.from(qbOptions.querySelectorAll('.qb-opt-row'));
    const options = [];
    let correctOptionId = null;
    rows.forEach((row, i) => {
      const id = String.fromCharCode(97 + i);
      const text = row.querySelector('.qb-opt-text').value.trim();
      const isChecked = row.querySelector('input[type="radio"]').checked;
      if (text){ options.push({ id, text }); if (isChecked) correctOptionId = id; }
    });

    if (options.length < 2){ showToast('Add at least 2 options'); return; }
    if (!correctOptionId){ showToast('Mark which option is correct'); return; }

    builderQuestions.push({
      id: 'custom-' + Date.now(),
      prompt,
      options,
      correctOptionId,
      explanation: qbExplanation.value.trim()
    });
    renderBuilderList();
    hideQbForm();
    showToast('Question added');
  });

  document.getElementById('bfSave').addEventListener('click', () => {
    if (!builderQuestions.length){ showToast('Add at least one question before saving'); return; }
    saveCustomQuiz(builderQuestions);
    activeQuestions = shuffle(JSON.parse(JSON.stringify(builderQuestions)));
    quizSource = 'teacher';
    updateSourceChip();
    closeBuilder();
    startQuiz();
    showToast('Saved \u2014 students will now see this quiz');
  });

  document.getElementById('bfReset').addEventListener('click', () => {
    builderQuestions = generateQuizFromStory(story.lines);
    renderBuilderList();
    hideQbForm();
    showToast('Reset to the auto-generated questions');
  });

  document.getElementById('bfClear').addEventListener('click', () => {
    clearCustomQuiz();
    loadActiveQuiz();
    startQuiz();
    closeBuilder();
    showToast('Saved quiz cleared \u2014 back to auto-generated');
  });

  resetQbOptions();
</script>
<script src="shared-ui.js"></script>
</body>
</html>