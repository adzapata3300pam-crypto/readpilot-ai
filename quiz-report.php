<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-guard.php';
require_teacher();
require_once __DIR__ . '/quiz-report-helper.php';

$teacherId = (int) current_user()['id'];
$attemptId = filter_input(INPUT_GET, 'attempt_id', FILTER_VALIDATE_INT) ?: 0;
if ($attemptId < 1) {
    http_response_code(400);
    exit('A valid quiz attempt is required.');
}

$pdo = db();
$studentStatement = $pdo->prepare(
    'SELECT s.id, s.name, s.color, sec.name AS section
     FROM quiz_attempts qa
     INNER JOIN students s ON s.id = qa.student_id AND s.teacher_id = qa.teacher_id
     LEFT JOIN sections sec ON sec.id = s.section_id AND sec.teacher_id = s.teacher_id
     WHERE qa.id = ? AND qa.teacher_id = ? LIMIT 1'
);
$studentStatement->execute([$attemptId, $teacherId]);
$student = $studentStatement->fetch();
if (!$student) {
    http_response_code(404);
    exit('Quiz report not found.');
}

$attemptStatement = $pdo->prepare(
    'SELECT id, title, score, total_questions, created_at
     FROM quiz_attempts
     WHERE student_id = ? AND teacher_id = ?
     ORDER BY created_at DESC, id DESC'
);
$attemptStatement->execute([(int) $student['id'], $teacherId]);
$attempts = $attemptStatement->fetchAll();
foreach ($attempts as &$attempt) {
    $attempt['id'] = (int) $attempt['id'];
    $attempt['score'] = (int) $attempt['score'];
    $attempt['total_questions'] = (int) $attempt['total_questions'];
    $attempt['report'] = ensure_quiz_ai_report($pdo, $teacherId, $attempt['id']);
}
unset($attempt);

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

// Initials for the avatar
$initials = '';
foreach (array_slice(preg_split('/\s+/u', trim((string) $student['name'])) ?: [], 0, 2) as $part) {
    $initials .= function_exists('mb_substr') ? mb_substr($part, 0, 1) : substr($part, 0, 1);
}
$initials = strtoupper($initials);
$avatarColor = $student['color'] ?: '#6fbf5a';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Reports — <?= $escape($student['name']) ?></title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
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
    } catch (e) {}
    try {
      var savedSidebar = localStorage.getItem('readpilot-sidebar');
      if (savedSidebar === 'collapsed') {
        document.documentElement.setAttribute('data-sidebar', 'collapsed');
      }
    } catch (e) {}
  })();
  </script>
  <style>
    :root{
      --sidebar-bg:#163828;
      --sidebar-active:#dff0d8;
      --sidebar-text:#cfe3d7;
      --bg:#f6f8f2;
      --card:#ffffff;
      --ink:#1c2b23;
      --muted:#7c8d82;
      --green:#6fbf5a;
      --green-dark:#3f7d4a;
      --green-light:#e6f4e1;
      --orange:#f2a13a;
      --orange-light:#fdecd6;
      --red:#ea5d5d;
      --red-light:#fce3e3;
      --border:#ebefe6;
      --radius:18px;
      --shadow:0 4px 18px rgba(30,60,40,0.06);
    }
    *{box-sizing:border-box;}
    body{margin:0;font-family:'Nunito',sans-serif;background:var(--bg);color:var(--ink);display:flex;min-height:100vh;}

    /* ---------- Sidebar ---------- */
    .sidebar{
      width:250px;flex-shrink:0;
      background:linear-gradient(180deg,var(--sidebar-bg) 0%,#102c1f 100%);
      color:var(--sidebar-text);padding:28px 20px;
      display:flex;flex-direction:column;justify-content:space-between;
      position:fixed;top:0;left:0;bottom:0;overflow:hidden;
      transition:width .25s ease,padding .25s ease;
    }
    .logo-row{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:34px;padding:0 4px;}
    .logo-left{display:flex;align-items:center;gap:10px;}
    .logo-icon{width:30px;height:30px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .logo-icon .bx{font-size:26px;color:#b1db65;transition:transform .25s ease;}
    .logo-left:hover .logo-icon .bx{transform:translate(2px,-2px) rotate(-4deg);}
    .logo-text{display:flex;flex-direction:column;line-height:1.15;}
    .logo-text .brand{font-family:'Poppins',sans-serif;font-weight:700;font-size:19px;color:#fff;letter-spacing:.2px;}
    .logo-text .tagline{font-size:10.5px;color:#9fc3ac;font-weight:600;letter-spacing:.3px;}
    .hamburger{width:32px;height:32px;flex-shrink:0;background:transparent;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;border-radius:8px;transition:background .15s ease;}
    .hamburger:hover{background:rgba(255,255,255,0.08);}
    .hamburger .bar{display:block;width:18px;height:2px;background:#cfe3d7;border-radius:1px;position:relative;}
    .hamburger .bar::before,.hamburger .bar::after{content:"";position:absolute;left:0;width:18px;height:2px;background:#cfe3d7;border-radius:1px;}
    .hamburger .bar::before{top:-6px;}
    .hamburger .bar::after{top:6px;}

    html[data-sidebar="collapsed"] .sidebar{width:80px;padding-left:16px;padding-right:16px;}
    html[data-sidebar="collapsed"] .sidebar .logo-text,
    html[data-sidebar="collapsed"] .sidebar .nav-item span.label,
    html[data-sidebar="collapsed"] .sidebar .teacher-name,
    html[data-sidebar="collapsed"] .sidebar .teacher-role,
    html[data-sidebar="collapsed"] .sidebar .quote{display:none;}
    html[data-sidebar="collapsed"] .sidebar .logo-row{justify-content:center;flex-direction:column;gap:14px;}
    html[data-sidebar="collapsed"] .sidebar .nav-item{justify-content:center;padding:11px 0;}
    html[data-sidebar="collapsed"] .sidebar .teacher-row{width:36px;gap:0;justify-content:center;}
    html[data-sidebar="collapsed"] .sidebar .teacher-card{padding:10px 4px;display:flex;justify-content:center;}
    html[data-sidebar="collapsed"] .sidebar .avatar{margin-inline:auto;}

    nav{display:flex;flex-direction:column;gap:4px;flex-grow:1;}
    .nav-item{
      display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:12px;
      color:var(--sidebar-text);font-size:14.5px;font-weight:600;cursor:pointer;
      transition:background .15s ease,color .15s ease,transform .12s steps(2);text-decoration:none;
    }
    .nav-item .bx{width:18px;height:18px;font-size:18px;opacity:.9;flex-shrink:0;}
    .nav-item:hover{background:rgba(255,255,255,0.06);transform:translate(-2px,-2px);}
    .nav-item.active{background:var(--sidebar-active);color:#1c3d2b;}

    .teacher-card{background:rgba(255,255,255,0.06);border-radius:16px;padding:14px;}
    .teacher-row{display:flex;align-items:center;justify-content:space-between;gap:10px;}
    .teacher-row-info{display:flex;align-items:center;gap:10px;}
    .avatar{width:36px;height:36px;border-radius:50%;background:#e7c58a;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
    .teacher-name{font-size:13.5px;font-weight:700;color:#fff;}
    .teacher-role{font-size:11.5px;color:#9fc3ac;}
    .teacher-logout-btn{color:#cfe3d7;font-size:18px;text-decoration:none;display:flex;}
    .quote{margin-top:12px;font-size:11.5px;font-style:italic;color:#a9c9b8;line-height:1.5;border-top:1px solid rgba(255,255,255,0.08);padding-top:10px;}
    .quote .heart{color:#e88;}

    /* ---------- Main (left-aligned, fills the space like progress.php) ---------- */
    .main{flex-grow:1;width:calc(100% - 250px);min-width:0;margin:0 0 0 250px;padding:32px 40px 48px;}
    html[data-sidebar="collapsed"] .main{width:calc(100% - 80px);margin-left:80px;}

    .topbar{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:24px;}
    .topbar h1{font-family:'Poppins',sans-serif;font-size:28px;margin:0 0 4px;color:var(--ink);}
    .topbar .greet{font-size:14.5px;color:var(--muted);font-weight:600;}
    .topbar-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}

    .btn-new{
      display:inline-flex;align-items:center;gap:8px;background:var(--green);color:#fff;border:none;border-radius:12px;
      padding:11px 18px;font-size:13.5px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(111,191,90,0.35);
      font-family:inherit;text-decoration:none;transition:transform .12s steps(2),box-shadow .12s steps(2);
    }
    .btn-new:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark),0 4px 12px rgba(111,191,90,0.35);}
    .btn-outline{
      display:inline-flex;align-items:center;gap:8px;background:var(--card);color:var(--ink);border:1px solid var(--border);border-radius:12px;
      padding:11px 16px;font-size:13.5px;font-weight:700;cursor:pointer;box-shadow:var(--shadow);font-family:inherit;text-decoration:none;
      transition:transform .12s steps(2),box-shadow .12s steps(2);
    }
    .btn-outline:hover{transform:translate(-2px,-2px);box-shadow:3px 3px 0 var(--green-dark);}

    /* ---------- Cards ---------- */
    .card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:24px 26px;}
    .stack{display:flex;flex-direction:column;gap:20px;}
    .two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px;}

    /* Student + score header */
    .hero{display:flex;align-items:center;gap:18px;flex-wrap:wrap;}
    .hero-avatar{width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:#fff;flex-shrink:0;}
    .hero-info{flex:1 1 220px;min-width:0;}
    .hero-name{font-family:'Poppins',sans-serif;font-size:20px;font-weight:700;color:var(--ink);}
    .hero-sub{font-size:13px;color:var(--muted);font-weight:700;margin-top:2px;}
    .hero-score{text-align:right;}
    .hero-score-num{font-family:'Poppins',sans-serif;font-size:34px;font-weight:700;color:var(--green-dark);line-height:1;}
    .hero-score-num small{font-size:16px;color:var(--muted);font-weight:600;}
    .result-pill{display:inline-block;margin-top:8px;font-size:11.5px;font-weight:800;padding:5px 12px;border-radius:20px;}
    .result-pill.good{background:var(--green-light);color:var(--green-dark);}
    .result-pill.mid{background:var(--orange-light);color:var(--orange);}
    .result-pill.low{background:var(--red-light);color:var(--red);}

    .bar-wrap{margin-top:20px;}
    .bar-track{height:10px;background:var(--bg);border-radius:6px;overflow:hidden;}
    .bar-fill{height:100%;border-radius:6px;background:var(--green);}
    .bar-fill.mid{background:var(--orange);}
    .bar-fill.low{background:var(--red);}
    .bar-caption{font-size:12px;color:var(--muted);font-weight:700;margin-top:8px;}

    /* Section cards */
    .card h2{display:flex;align-items:center;gap:10px;font-family:'Poppins',sans-serif;font-size:15.5px;font-weight:700;color:var(--ink);margin:0 0 12px;}
    .card h2 .bx{width:30px;height:30px;border-radius:9px;background:var(--green-light);color:var(--green-dark);display:flex;align-items:center;justify-content:center;font-size:17px;}
    .card p{margin:0;font-size:14px;line-height:1.7;font-weight:600;color:var(--ink);}
    .card.action{background:var(--green-light);box-shadow:none;border:1.5px solid rgba(111,191,90,0.35);}
    .card.action h2 .bx{background:var(--card);}

    /* AI summary + teacher action live inside the main quiz card */
    .evidence{margin-top:20px;}
    .evidence-label{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.3px;margin-bottom:10px;}
    .evidence-label .bx{font-size:15px;color:var(--green-dark);}
    .stat-row.compact{margin-top:0;}
    .stat-row.compact .stat{padding:10px 14px;}
    .stat-row.compact .stat-value{font-size:18px;}
    .card-split{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;padding-top:20px;border-top:1px solid var(--border);}
    .split-block h2{margin-bottom:10px;}
    .split-block.action{background:var(--green-light);border:1.5px solid rgba(111,191,90,0.35);border-radius:14px;padding:16px 18px;}
    .split-block.action h2 .bx{background:var(--card);}
    .quiz-attempt-card{scroll-margin-top:24px;}
    .quiz-attempt-card:target{outline:2px solid var(--green);outline-offset:3px;}
    .report-list-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:4px 0 0;}
    .report-list-heading h2{font-family:'Poppins',sans-serif;font-size:18px;color:var(--ink);margin:0;}
    .report-list-count{padding:6px 11px;border-radius:20px;background:var(--green-light);color:var(--green-dark);font-size:11px;font-weight:800;}
    .attempt-label{display:inline-flex;align-items:center;gap:6px;margin-bottom:8px;color:var(--green-dark);font-size:10px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;}
    .attempt-label .bx{font-size:14px;}
    .quiz-report-empty{padding:30px;text-align:center;color:var(--muted);}
    .quiz-report-empty .bx{display:block;margin-bottom:8px;color:var(--green-dark);font-size:26px;}

    .stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:4px;}
    .stat{background:var(--bg);border-radius:14px;padding:14px 16px;}
    .stat-value{font-family:'Poppins',sans-serif;font-size:22px;font-weight:700;color:var(--green-dark);}
    .stat-label{font-size:11px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.3px;margin-top:2px;}

    .note{display:flex;gap:8px;align-items:flex-start;font-size:12px;line-height:1.5;color:var(--muted);font-weight:600;padding:0 4px;}
    .note .bx{font-size:15px;margin-top:1px;flex-shrink:0;}

    html[data-theme="dark"] .btn-outline{background:var(--card);color:var(--ink);border-color:var(--border);}

    @media(max-width:1000px){ .two-col,.card-split{grid-template-columns:1fr;} }
    @media(max-width:900px){ .main{padding:24px 18px 40px;} }
    @media(max-width:600px){
      .stat-row{grid-template-columns:1fr;}
      .hero-score{text-align:left;}
      .topbar h1{font-size:23px;}
      .report-list-heading h2{font-size:16px;}
    }
    @media print{
      .sidebar,.topbar-actions{display:none!important;}
      body{display:block;background:#fff;}
      .main,html[data-sidebar="collapsed"] .main{width:100%;margin:0;padding:0;}
      .card{box-shadow:none;border:1px solid #ddd;}
      .card,.two-col,.card-split{page-break-inside:avoid;}
    }
  </style>
</head>
<body>

  <!-- ================= SIDEBAR ================= -->
  <aside class="sidebar">
    <div class="pixel-plane" style="--y:14%; --dur:13s; --delay:0s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2" height="1" fill="#cbe98f"/><rect x="0" y="1" width="4" height="1" fill="#cbe98f"/><rect x="0" y="2" width="6" height="1" fill="#cbe98f"/><rect x="0" y="3" width="9" height="1" fill="#cbe98f"/><rect x="0" y="4" width="13" height="1" fill="#b1db65"/><rect x="0" y="5" width="9" height="1" fill="#7fae55"/><rect x="0" y="6" width="6" height="1" fill="#7fae55"/><rect x="0" y="7" width="4" height="1" fill="#7fae55"/><rect x="0" y="8" width="2" height="1" fill="#7fae55"/>
      </svg>
    </div>
    <div class="pixel-plane" style="--y:74%; --dur:17s; --delay:6s;" aria-hidden="true">
      <svg viewBox="0 0 13 9" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="2" height="1" fill="#cbe98f"/><rect x="0" y="1" width="4" height="1" fill="#cbe98f"/><rect x="0" y="2" width="6" height="1" fill="#cbe98f"/><rect x="0" y="3" width="9" height="1" fill="#cbe98f"/><rect x="0" y="4" width="13" height="1" fill="#b1db65"/><rect x="0" y="5" width="9" height="1" fill="#7fae55"/><rect x="0" y="6" width="6" height="1" fill="#7fae55"/><rect x="0" y="7" width="4" height="1" fill="#7fae55"/><rect x="0" y="8" width="2" height="1" fill="#7fae55"/>
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
        <a class="nav-item" href="index.php">
          <i class="bx bxs-dashboard"></i>
          <span class="label">Dashboard</span>
        </a>
        <a class="nav-item" href="students.php">
          <i class="bx bx-group"></i>
          <span class="label">Sections</span>
        </a>
        <a class="nav-item" href="sessions.php">
          <i class="bx bx-calendar"></i>
          <span class="label">Sessions</span>
        </a>
        <a class="nav-item active" href="reports.php">
          <i class="bx bx-file"></i>
          <span class="label">Reports</span>
        </a>
        <a class="nav-item" href="struggle-map.php">
          <i class="bx bx-target-lock"></i>
          <span class="label">Struggle Map</span>
        </a>
        <a class="nav-item" href="recommendations.php">
          <i class="bx bx-bulb"></i>
          <span class="label">Recommendations</span>
        </a>
        <a class="nav-item" href="resources.php">
          <i class="bx bx-book-open"></i>
          <span class="label">Resources</span>
        </a>
        <a class="nav-item" href="recordings.php">
          <i class="bx bx-cloud-upload"></i>
          <span class="label">Cloud Recordings</span>
        </a>
        <a class="nav-item" href="settings.php">
          <i class="bx bx-cog"></i>
          <span class="label">Settings</span>
        </a>
      </nav>
    </div>

    <div class="teacher-card">
      <div class="teacher-row">
        <div class="teacher-row-info">
          <div class="avatar"><?php include __DIR__ . '/profile-avatar.php'; ?></div>
          <div>
            <div class="teacher-name"><?= $escape(current_user()['full_name']) ?></div>
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
      <div>
        <h1>Student Quiz Reports</h1>
        <div class="greet">Complete quiz history and learning recommendations for this student.</div>
      </div>
      <div class="topbar-actions">
        <button class="btn-new" type="button" onclick="window.print()"><i class="bx bx-printer"></i> Print Reports</button>
      </div>
    </div>

    <div class="stack">

      <section class="card">
        <div class="hero">
          <div class="hero-avatar" style="background:<?= $escape($avatarColor) ?>"><?= $escape($initials) ?></div>
          <div class="hero-info">
            <div class="hero-name"><?= $escape($student['name']) ?></div>
            <div class="hero-sub"><?= $student['section'] ? $escape($student['section']) . ' · ' : '' ?>Full quiz history</div>
          </div>
          <span class="report-list-count"><?= count($attempts) ?> <?= count($attempts) === 1 ? 'quiz attempt' : 'quiz attempts' ?></span>
        </div>
      </section>

      <div class="report-list-heading">
        <h2>Reports for every quiz taken</h2>
        <span class="report-list-count"><?= count($attempts) ?> total</span>
      </div>

      <?php if (!$attempts): ?>
        <section class="card quiz-report-empty">
          <i class="bx bx-file-blank"></i>
          <strong>No quiz attempts found</strong>
        </section>
      <?php endif; ?>

      <?php foreach ($attempts as $attempt): ?>
        <?php
          $report = $attempt['report'];
          $score = $attempt['score'];
          $totalQuestions = $attempt['total_questions'];
          $scorePercent = $totalQuestions > 0 ? (int) round(($score / $totalQuestions) * 100) : 0;
          if ($scorePercent >= 80) {
              $resultClass = 'good';
              $resultLabel = 'Strong result';
          } elseif ($scorePercent >= 60) {
              $resultClass = 'mid';
              $resultLabel = 'Getting there';
          } else {
              $resultClass = 'low';
              $resultLabel = 'Needs support';
          }
          $readingCount = (int) $report['reading_session_count'];
        ?>
        <article class="quiz-attempt-card stack" id="quiz-attempt-<?= $attempt['id'] ?>">
          <section class="card quiz-main-card">
            <div class="attempt-label"><i class="bx bx-check-shield"></i> Quiz report</div>
            <div class="hero">
              <div class="hero-info">
                <div class="hero-name"><?= $escape($attempt['title']) ?></div>
                <div class="hero-sub"><?= $escape(date('M j, Y g:i A', strtotime($attempt['created_at']))) ?></div>
              </div>
              <div class="hero-score">
                <div class="hero-score-num"><?= $score ?><small> / <?= $totalQuestions ?></small></div>
                <span class="result-pill <?= $resultClass ?>"><?= $escape($resultLabel) ?></span>
              </div>
            </div>
            <div class="bar-wrap">
              <div class="bar-track"><div class="bar-fill <?= $resultClass === 'good' ? '' : $resultClass ?>" style="width:<?= $scorePercent ?>%"></div></div>
              <div class="bar-caption"><?= $scorePercent ?>% correct. The recommendation also considers reading fluency when available.</div>
            </div>

            <div class="evidence">
              <?php if ($readingCount > 0): ?>
                <div class="evidence-label"><i class="bx bx-line-chart"></i> Reading evidence · based on the <?= $readingCount === 1 ? 'most recent reading session' : $readingCount . ' most recent reading sessions' ?> before this quiz</div>
                <div class="stat-row compact">
                  <div class="stat"><div class="stat-value"><?= $readingCount ?></div><div class="stat-label">Session<?= $readingCount === 1 ? '' : 's' ?> reviewed</div></div>
                  <div class="stat"><div class="stat-value"><?= (int) $report['reading_wpm'] ?></div><div class="stat-label">Average WPM</div></div>
                  <div class="stat"><div class="stat-value"><?= (int) $report['reading_accuracy'] ?>%</div><div class="stat-label">Accuracy</div></div>
                </div>
              <?php else: ?>
                <div class="evidence-label"><i class="bx bx-line-chart"></i> Reading evidence · no saved reading sessions before this quiz, so this uses the quiz score only</div>
              <?php endif; ?>
            </div>

            <div class="card-split">
              <div class="split-block">
                <h2><i class="bx bx-brain"></i> AI Learning Summary</h2>
                <p><?= $escape($report['summary']) ?></p>
              </div>
              <div class="split-block action">
                <h2><i class="bx bx-bulb"></i> Suggested Teacher Action</h2>
                <p><?= $escape($report['recommendation']) ?></p>
              </div>
            </div>
          </section>
        </article>
      <?php endforeach; ?>

      <div class="note">
        <i class="bx bx-info-circle"></i>
        <span>This recommendation is based on the overall quiz score and recent reading speed/accuracy. Individual question answers are not currently saved, so the report does not identify specific missed questions.</span>
      </div>

    </div>
  </main>

  <script>
    window.addEventListener('load', function(){
      var selectedAttempt = document.getElementById('quiz-attempt-<?= $attemptId ?>');
      if (selectedAttempt) selectedAttempt.scrollIntoView({block:'start'});
    });

    document.getElementById('sidebarToggle').addEventListener('click', function(){
      var isCollapsed = document.documentElement.getAttribute('data-sidebar') === 'collapsed';
      try {
        if (isCollapsed){
          document.documentElement.removeAttribute('data-sidebar');
          localStorage.setItem('readpilot-sidebar', 'expanded');
        } else {
          document.documentElement.setAttribute('data-sidebar', 'collapsed');
          localStorage.setItem('readpilot-sidebar', 'collapsed');
        }
      } catch (e) {}
    });
  </script>
  <script src="shared-ui.js"></script>
</body>
</html>