<?php
require_once __DIR__ . '/auth-guard.php';
require_teacher();

$apiRequest = $_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['view']);
if ($apiRequest) {
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  $respond = static function (array $payload, int $status = 200): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
  };
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_token_is_valid($_POST['csrf_token'] ?? null)) {
    $respond(['ok' => false, 'error' => 'Your session expired. Reload the page and try again.'], 403);
  }

  try {
    if (!is_file(__DIR__ . '/vendor/autoload.php')) {
      $respond(['ok' => false, 'error' => 'Cloud storage packages are not installed. Run Composer install in the ReadPilot project folder.'], 503);
    }
    require_once __DIR__ . '/storage.php';
    if (storage_setting('SUPABASE_BUCKET') === '') {
      $respond(['ok' => false, 'error' => 'Supabase Storage bucket is not configured. Set SUPABASE_BUCKET in the secrets file outside htdocs.'], 503);
    }
    foreach (['SUPABASE_S3_ENDPOINT', 'SUPABASE_S3_REGION', 'SUPABASE_S3_ACCESS_KEY', 'SUPABASE_S3_SECRET_KEY'] as $setting) {
      if (storage_setting($setting) === '') {
        $respond(['ok' => false, 'error' => 'Supabase Storage is not configured. Set the project S3 endpoint, region, access key, and secret in the secrets file outside htdocs.'], 503);
      }
    }
    $pdo = db();
    $teacherId = (int) current_user()['id'];
    $view = (string) ($_GET['view'] ?? 'upload');

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $view === 'list') {
      $statement = $pdo->prepare(
        'SELECT sr.id, sr.student_id, sr.session_id, sr.bucket, sr.storage_key, sr.size_bytes, sr.etag, sr.created_at, s.name AS student, rs.book '
        . 'FROM session_recordings sr JOIN reading_sessions rs ON rs.id = sr.session_id AND rs.teacher_id = sr.teacher_id '
        . 'JOIN students s ON s.id = sr.student_id AND s.teacher_id = sr.teacher_id '
        . 'WHERE sr.teacher_id = ? ORDER BY sr.created_at DESC'
      );
      $statement->execute([$teacherId]);
      $respond(['ok' => true, 'recordings' => $statement->fetchAll()]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $view === 'selftest') {
      $respond(['ok' => true] + storage_selftest('teacher-' . $teacherId));
    }

    if (in_array($view, ['verify', 'play'], true) && $_SERVER['REQUEST_METHOD'] === 'GET') {
      $recordingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
      $statement = $pdo->prepare('SELECT storage_key, size_bytes, etag FROM session_recordings WHERE id = ? AND teacher_id = ?');
      $statement->execute([(int) $recordingId, $teacherId]);
      $recording = $statement->fetch();
      if (!$recording) $respond(['ok' => false, 'error' => 'Recording not found.'], 404);

      if ($view === 'play') {
        $respond(['ok' => true, 'url' => recording_presigned_url((string) $recording['storage_key'])]);
      }

      $info = recording_object_info((string) $recording['storage_key']);
      $respond([
        'ok' => true,
        'exists' => true,
        'size_match' => $info['size'] === (int) $recording['size_bytes'],
        'size' => $info['size'],
        'etag' => $info['etag'],
        'modified' => $info['modified'],
      ]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $view === 'upload') {
      $sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
      $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
      $session = $pdo->prepare('SELECT id FROM reading_sessions WHERE id = ? AND student_id = ? AND teacher_id = ?');
      $session->execute([(int) $sessionId, (int) $studentId, $teacherId]);
      if (!$session->fetchColumn()) $respond(['ok' => false, 'error' => 'Reading session not found.'], 404);
      if (($_POST['consent_confirmed'] ?? '') !== '1') {
        $respond(['ok' => false, 'error' => 'Recording consent must be confirmed before upload.'], 403);
      }

      $file = $_FILES['audio'] ?? null;
      if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        $respond(['ok' => false, 'error' => 'No valid audio recording was received.'], 422);
      }
      if ((int) $file['size'] < 1 || (int) $file['size'] > 25 * 1024 * 1024) {
        $respond(['ok' => false, 'error' => 'Recording must be smaller than 25 MB.'], 413);
      }

      $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
      $extensions = ['audio/webm' => 'webm', 'video/webm' => 'webm', 'audio/ogg' => 'ogg', 'application/ogg' => 'ogg', 'audio/mp4' => 'm4a', 'video/mp4' => 'm4a', 'audio/mpeg' => 'mp3', 'audio/wav' => 'wav', 'audio/x-wav' => 'wav'];
      if (!is_string($mime) || !isset($extensions[$mime])) {
        $respond(['ok' => false, 'error' => 'Unsupported audio format.'], 415);
      }

      $existing = $pdo->prepare('SELECT storage_key FROM session_recordings WHERE session_id = ? AND teacher_id = ?');
      $existing->execute([(int) $sessionId, $teacherId]);
      $oldStorageKey = $existing->fetchColumn();
      $storageKey = sprintf('recordings/%d/%d/%d-%s.%s', $teacherId, (int) $studentId, (int) $sessionId, bin2hex(random_bytes(12)), $extensions[$mime]);
      upload_recording_object($file['tmp_name'], $storageKey, $mime);
      try {
        $info = recording_object_info($storageKey);
        $pdo->beginTransaction();
        $insert = $pdo->prepare(
          'INSERT INTO session_recordings (teacher_id, student_id, session_id, bucket, storage_key, mime_type, size_bytes, etag, duration_seconds, consent_confirmed_at) '
          . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE bucket = VALUES(bucket), storage_key = VALUES(storage_key), '
          . 'mime_type = VALUES(mime_type), size_bytes = VALUES(size_bytes), etag = VALUES(etag), duration_seconds = VALUES(duration_seconds), consent_confirmed_at = CURRENT_TIMESTAMP, created_at = CURRENT_TIMESTAMP'
        );
        $insert->execute([$teacherId, (int) $studentId, (int) $sessionId, recording_bucket(), $storageKey, $mime, $info['size'], $info['etag'], max(0, (int) ($_POST['duration_seconds'] ?? 0))]);
        $pdo->commit();
      } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        delete_recording_object($storageKey);
        throw $exception;
      }
      if ($oldStorageKey && $oldStorageKey !== $storageKey) {
        try { delete_recording_object((string) $oldStorageKey); } catch (Throwable $exception) { error_log('Could not remove replaced recording: ' . $exception->getMessage()); }
      }
      $respond(['ok' => true, 'size' => $info['size'], 'etag' => $info['etag']]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $view === 'delete') {
      $recordingId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
      $statement = $pdo->prepare('SELECT storage_key FROM session_recordings WHERE id = ? AND teacher_id = ?');
      $statement->execute([(int) $recordingId, $teacherId]);
      $recording = $statement->fetch();
      if (!$recording) $respond(['ok' => false, 'error' => 'Recording not found.'], 404);
      delete_recording_object((string) $recording['storage_key']);
      $pdo->prepare('DELETE FROM session_recordings WHERE id = ? AND teacher_id = ?')->execute([(int) $recordingId, $teacherId]);
      $respond(['ok' => true]);
    }

    $respond(['ok' => false, 'error' => 'Unknown recording request.'], 400);
  } catch (Throwable $exception) {
    error_log('Recording API failure: ' . $exception->getMessage());
    $respond(['ok' => false, 'error' => 'Supabase Storage could not complete that request. Check the S3 credentials, bucket access, and Apache error log.'], 500);
  }
}
header('Location: reading.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ReadPilot — Reading Session</title>
<script src="theme-init.js"></script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  
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
  .timer-pill{
    display:flex;align-items:center;gap:7px;
    background:var(--chip-bg);border:1px solid var(--line);
    padding:8px 14px;border-radius:20px;font-size:12.5px;font-weight:800;color:var(--ink);
  }
  .timer-dot{width:7px;height:7px;border-radius:50%;background:var(--accent);animation:pulseDot 1.6s ease-in-out infinite;}
  @keyframes pulseDot{0%,100%{opacity:1;}50%{opacity:.35;}}

  .theme-toggle{
    display:flex;align-items:center;justify-content:center;
    width:38px;height:38px;border-radius:50%;
    background:var(--chip-bg);border:1px solid var(--line);color:var(--ink);
    cursor:pointer;font-family:inherit;flex-shrink:0;
    transition:background .15s ease, transform .25s ease;
  }
  .theme-toggle:hover{background:var(--chip-bg-hover);transform:rotate(18deg);}
  .theme-toggle .bx{font-size:18px;}

  .exit-btn{
    display:flex;align-items:center;gap:6px;
    background:var(--chip-bg);border:1px solid var(--line);color:var(--ink);
    padding:8px 16px;border-radius:20px;font-size:12.5px;font-weight:800;
    cursor:pointer;text-decoration:none;font-family:inherit;
  }
  .exit-btn:hover{background:var(--chip-bg-hover);}
  .exit-btn .bx{font-size:14px;}

  .struggle-chip{
    position:relative;
    display:flex;align-items:center;gap:6px;
    background:var(--chip-bg);border:1px solid var(--line);color:var(--ink);
    padding:8px 16px;border-radius:20px;font-size:12.5px;font-weight:800;
    cursor:pointer;font-family:inherit;
  }
  .struggle-chip:hover{background:var(--chip-bg-hover);}
  .struggle-chip .bx{font-size:14px;color:#ffb877;}
  .struggle-badge{
    display:none;align-items:center;justify-content:center;
    min-width:16px;height:16px;padding:0 4px;border-radius:9px;
    background:var(--red);color:#fff;font-size:10px;font-weight:800;
  }

  /* ---------- Stage ---------- */
  .stage{
    flex-grow:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:20px 24px;gap:26px;min-height:0;
  }
  .meta-label{
    font-size:12px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--muted);
    display:flex;align-items:center;gap:8px;
  }
  .meta-dot{width:4px;height:4px;border-radius:50%;background:var(--muted);}

  .stage-head{display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;}
  .mode-toggle{
    display:flex;background:var(--chip-bg);border:1px solid var(--line);
    border-radius:20px;padding:3px;gap:2px;
  }
  .mode-btn{
    border:none;background:transparent;color:var(--muted);
    font-family:'Poppins',sans-serif;font-weight:700;font-size:11.5px;
    padding:7px 15px;border-radius:16px;cursor:pointer;
    transition:background .15s ease,color .15s ease;
  }
  .mode-btn:hover:not(.active){color:var(--ink);}
  .mode-btn.active{background:var(--accent-strong);color:var(--on-accent);}

  .sentence-wrap{
    position:relative;
    max-width:900px;width:100%;
    display:flex;flex-wrap:wrap;justify-content:center;
    /* Row-gap reserves room for the pointer + trail line beneath the active
       word, so wrapped text on the next row never overlaps them — the
       max() keeps that room even at the smallest font size, and lets it
       grow with larger font sizes. Column-gap stays fixed and small. */
    gap: max(58px, calc(var(--read-size, 34px) * 1.5)) 0.42em;
    padding-bottom:44px;
  }
  .word{
    font-family:'Poppins',sans-serif;
    font-weight:700;
    font-size:var(--read-size, 34px);
    line-height:1.5;
    color:var(--ink);
    cursor:pointer;
    padding:2px 3px;
    border-radius:8px;
    transition:color .15s ease, background .15s ease, transform .15s ease;
  }
  .word:hover{color:var(--accent);}
  .word.active{color:var(--accent);transform:translateY(-1px);}
  .word.struggled{color:#c94336 !important;background:rgba(234,93,93,0.18) !important;box-shadow:0 2px 0 rgba(201,67,54,0.35);}
  .word.active.struggled{color:#c94336 !important;background:rgba(234,93,93,0.18) !important;}
  .word.correct-flash{background:rgba(143,214,124,0.3);animation:correctFlash .6s ease;}
  @keyframes correctFlash{
    0%{background:rgba(143,214,124,0.45);}
    100%{background:transparent;}
  }

  .word-pointer{
    position:absolute;
    top:0;left:0;
    width:26px;height:26px;
    display:flex;align-items:center;justify-content:center;
    color:var(--accent-strong);
    /* Sits a comfortable distance under the active word rather than
       hugging it, so it never crowds text wrapping onto the next line. */
    transform:translate(-50%, 14px) rotate(90deg);
    transition:left .28s cubic-bezier(.3,.9,.3,1.15), top .28s cubic-bezier(.3,.9,.3,1.15), opacity .2s ease;
    opacity:0;
    pointer-events:none;
    filter:drop-shadow(0 2px 5px rgba(0,0,0,0.35));
    z-index:2;
  }
  .word-pointer.show{opacity:1;}
  .word-pointer .bx{font-size:20px;}
  .word-pointer.bob{animation:pointerBob 1.4s ease-in-out infinite;}
  @keyframes pointerBob{0%,100%{margin-top:0;}50%{margin-top:4px;}}

  /* The contrail: a thin line that grows behind the plane as it moves
     across the current row, from wherever that row started up to the
     plane's current position. It resets (shrinks back down) whenever the
     plane lands on a new row, so it always reads as "ground covered on
     this line so far" rather than a path across the whole story. */
  .word-trail{
    position:absolute;
    top:0;left:0;
    height:3px;width:0;
    border-radius:3px;
    background:linear-gradient(90deg, transparent, rgba(111,191,90,0.55) 35%, var(--accent) 100%);
    transform:translateY(-50%);
    opacity:0;
    transition:width .28s cubic-bezier(.3,.9,.3,1.15), left .28s cubic-bezier(.3,.9,.3,1.15), top .28s cubic-bezier(.3,.9,.3,1.15), opacity .2s ease;
    pointer-events:none;
    z-index:1;
  }
  .word-trail.show{opacity:0.9;}

  /* ---------- Per-line word progress bar + "currently reading" word ----------
     Sits directly under the sentence, and mirrors the story-wide progress
     bar in the bottom bar but tracks just the current line/sentence: it
     fills from the first word to the last word of *this* sentence, and
     resets each time a new line loads. The label to its left echoes back
     whichever word is currently active (cleaned of punctuation) so it
     doubles as a "here's the word to say" cue for Manual/voice mode. */
  .word-progress-wrap{
    width:100%;
    max-width:640px;
  }
  .word-progress-track{
    height:6px;
    background:var(--line);
    border-radius:99px;
    overflow:hidden;
  }
  .word-progress-fill{
    height:100%;
    width:0%;
    background:var(--accent);
    border-radius:99px;
    transition:width .25s ease;
  }
  .current-word-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    width:100%;
    max-width:640px;
  }
  .current-word-label{
    font-family:'Poppins',sans-serif;
    font-weight:800;
    font-size:22px;
    color:var(--accent);
    letter-spacing:.02em;
    min-height:28px;
  }
  .current-word-hint{
    font-size:11.5px;
    font-weight:700;
    color:var(--muted);
    white-space:nowrap;
  }

  .divider{width:100%;max-width:640px;height:2px;background:var(--line);border-radius:2px;}

  .hint{
    display:flex;align-items:center;gap:8px;
    font-size:12.5px;font-weight:700;color:var(--muted);
  }
  .hint .bx{font-size:15px;color:var(--accent);}

  .dots-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:center;max-width:640px;}
  .dot{
    width:9px;height:9px;border-radius:50%;background:var(--dot-bg);
    cursor:pointer;transition:background .15s ease, transform .15s ease;
  }
  .dot:hover{transform:scale(1.3);}
  .dot.done{background:var(--muted);}
  .dot.active{background:var(--accent);transform:scale(1.35);}

  /* ---------- Controls row ---------- */
  .controls-row{display:flex;align-items:center;justify-content:center;gap:26px;}
  .nav-btn{
    width:46px;height:46px;border-radius:50%;
    background:var(--chip-bg);border:1.5px solid var(--line);color:var(--ink);
    display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;
  }
  .nav-btn .bx{font-size:20px;}
  .nav-btn:hover{background:var(--chip-bg-hover);}
  .nav-btn:disabled{opacity:.35;cursor:not-allowed;}
  .nav-btn:disabled:hover{background:var(--chip-bg);}

  .mic-wrap{display:flex;flex-direction:column;align-items:center;gap:9px;}
  .mic-btn{
    width:74px;height:74px;border-radius:50%;
    background:var(--accent-strong);
    border:none;color:var(--on-accent);
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;box-shadow:0 8px 22px rgba(111,191,90,0.4);
    position:relative;flex-shrink:0;
    transition:transform .15s ease, box-shadow .15s ease, background .15s ease;
  }
  .mic-btn .bx{font-size:30px;}
  .mic-btn:hover{transform:translateY(-2px);box-shadow:0 12px 26px rgba(111,191,90,0.5);}
  .mic-btn:active{transform:translateY(0);}
  .mic-btn.recording{background:var(--red);box-shadow:0 8px 22px rgba(234,93,93,0.45);}
  .mic-ring{
    position:absolute;inset:-8px;border-radius:50%;
    border:2.5px solid var(--red);
    opacity:0;
  }
  .mic-btn.recording .mic-ring{opacity:1;animation:micPulse 1.4s ease-out infinite;}
  @keyframes micPulse{
    0%{transform:scale(.9);opacity:.8;}
    100%{transform:scale(1.5);opacity:0;}
  }
  .mic-label{font-size:12.5px;font-weight:800;color:var(--ink);}
  .mic-sub{font-size:11px;font-weight:700;color:var(--muted);}
  .listen-status{font-size:11px;font-weight:800;color:var(--accent);min-height:14px;text-align:center;}
  .rec-actions{display:flex;align-items:center;gap:10px;margin-top:2px;}
  .autoplay-btn{background:var(--accent-strong);border-color:var(--accent-strong);color:var(--on-accent);}
  .autoplay-btn:hover{background:#7fce6b;}
  .autoplay-btn.playing{background:var(--red);border-color:var(--red);color:#fff;}
  .rec-chip{
    display:none;align-items:center;gap:6px;
    background:var(--chip-bg);border:1px solid var(--line);
    color:var(--ink);font-size:11.5px;font-weight:800;
    padding:6px 12px;border-radius:20px;cursor:pointer;
  }
  .rec-chip.show{display:flex;}
  .rec-chip:hover{background:var(--chip-bg-hover);}
  .rec-chip .bx{font-size:13px;}

  /* ---------- Bottom bar ---------- */
  .bottombar{
    flex-shrink:0;
    display:flex;align-items:center;justify-content:space-between;gap:16px;
    padding:14px 26px;
    background:linear-gradient(90deg, #cbe98f, #b1db65);
    color:#16281d;
  }
  .line-count{font-size:12.5px;font-weight:800;white-space:nowrap;}
  .progress-track{
    position:relative;
    flex-grow:1;height:8px;border-radius:6px;background:rgba(22,40,29,0.18);overflow:hidden;
  }
  .progress-fill{height:100%;background:#16281d;border-radius:6px;width:0%;transition:width .3s ease;}
  .pct{font-size:12.5px;font-weight:800;white-space:nowrap;}
  .font-controls{display:flex;align-items:center;gap:6px;flex-shrink:0;}
  .font-btn{
    width:32px;height:32px;border-radius:9px;border:1.5px solid rgba(22,40,29,0.3);
    background:rgba(255,255,255,0.35);color:#16281d;font-family:'Poppins',sans-serif;
    font-weight:800;font-size:12px;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
  }
  .font-btn:hover{background:rgba(255,255,255,0.6);}

  /* ---------- Toast (local copy, same look as the rest of the site) ---------- */
  .toast{
    position:fixed;bottom:88px;right:24px;background:#16281d;color:#fff;
    padding:13px 20px;border-radius:12px;font-size:13px;font-weight:700;
    box-shadow:0 10px 30px rgba(0,0,0,0.35);z-index:200;
    display:flex;align-items:center;gap:10px;
    opacity:0;transform:translateY(10px);pointer-events:none;
    transition:opacity .25s ease, transform .25s ease;
  }
  .toast.show{opacity:1;transform:translateY(0);}
  .toast .bx{color:var(--accent);font-size:16px;}

  .struggle-panel{
    position:fixed;top:0;right:0;height:100vh;width:320px;max-width:90vw;
    background:var(--stage-card);border-left:1px solid var(--line);
    box-shadow:-14px 0 40px rgba(0,0,0,0.35);
    display:flex;flex-direction:column;
    transform:translateX(100%);transition:transform .25s ease, background .3s ease;
    z-index:300;
  }
  .struggle-panel.show{transform:translateX(0);}
  .struggle-panel-head{
    display:flex;align-items:center;justify-content:space-between;
    padding:18px 20px;border-bottom:1px solid var(--line);
    font-family:'Poppins',sans-serif;font-weight:700;font-size:15px;
    color:var(--ink);
  }
  .struggle-panel-head button{
    background:var(--chip-bg);border:none;color:var(--ink);
    width:30px;height:30px;border-radius:50%;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
  }
  .struggle-panel-head button:hover{background:var(--chip-bg-hover);}
  .struggle-list{flex-grow:1;overflow-y:auto;padding:14px 16px;display:flex;flex-direction:column;gap:8px;}
  .struggle-item{
    background:var(--chip-bg);border:1px solid var(--line);border-radius:12px;
    padding:11px 14px;
  }
  .struggle-word{font-family:'Poppins',sans-serif;font-weight:800;font-size:14.5px;color:#ffb877;display:block;}
  .struggle-meta{font-size:11px;font-weight:700;color:var(--muted);}
  .struggle-empty{font-size:12.5px;font-weight:700;color:var(--muted);text-align:center;padding:30px 10px;}
  .struggle-panel-foot{padding:14px 16px;border-top:1px solid var(--line);}
  .clear-struggle-btn{
    width:100%;background:rgba(234,93,93,0.12);border:1px solid rgba(234,93,93,0.4);
    color:#ffb2b2;font-family:inherit;font-weight:800;font-size:12px;
    padding:10px;border-radius:12px;cursor:pointer;
  }
  .clear-struggle-btn:hover{background:rgba(234,93,93,0.2);}

  audio{display:none;}

  @media (max-width:640px){
    .word{font-size:calc(var(--read-size, 34px) * 0.72);}
    .topbar{padding:14px 18px;}
    .stage{padding:16px;gap:18px;}
    .bottombar{padding:12px 16px;flex-wrap:wrap;}
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
        <div class="timer-pill"><span class="timer-dot"></span><span id="sessionTimer">0:00</span></div>
        <button class="theme-toggle" id="themeToggle" aria-label="Switch to light mode">
          <i class='bx bx-sun' id="themeIcon"></i>
        </button>
        <button class="struggle-chip" id="struggleBtn">
          <i class='bx bx-flag'></i> Struggle Map
          <span class="struggle-badge" id="struggleBadge">0</span>
        </button>
        <a class="exit-btn" href="resources.php">
          <i class='bx bx-x'></i> Exit
        </a>
      </div>
    </div>

    <div class="stage">
      <div class="stage-head">
        <div class="meta-label" id="metaLabel">Loading story<span class="meta-dot"></span>...</div>
        <div class="mode-toggle" id="modeToggle">
          <button class="mode-btn active" data-mode="automatic" type="button">Automatic</button>
          <button class="mode-btn" data-mode="manual" type="button">Manual</button>
        </div>
      </div>

      <div class="sentence-wrap" id="sentenceWrap">
        <div class="word-trail" id="wordTrail"></div>
        <div class="word-pointer" id="wordPointer"><i class='bx bxs-paper-plane'></i></div>
      </div>

      <div class="word-progress-wrap">
        <div class="word-progress-track"><div class="word-progress-fill" id="wordProgressFill"></div></div>
      </div>

      <div class="current-word-row">
        <div class="current-word-label" id="currentWordLabel"></div>
        <div class="current-word-hint">Tap a word or use the arrows</div>
      </div>

      <div class="dots-row" id="dotsRow"></div>

      <div class="controls-row">
        <button class="nav-btn" id="prevLineBtn" aria-label="Previous line"><i class='bx bx-chevron-left'></i></button>

        <div class="mic-wrap">
          <button class="mic-btn" id="micBtn" aria-label="Record my reading">
            <span class="mic-ring"></span>
            <i class='bx bx-microphone' id="micIcon"></i>
          </button>
          <div class="mic-label" id="micLabel">Record My Reading</div>
          <div class="mic-sub" id="micSub">Tap to start</div>
          <div class="listen-status" id="listenStatus"></div>
          <div class="rec-actions">
            <button class="rec-chip" id="rerecordChip"><i class='bx bx-refresh'></i> Re-record</button>
          </div>
        </div>

        <button class="nav-btn" id="nextLineBtn" aria-label="Next line"><i class='bx bx-chevron-right'></i></button>
      </div>
    </div>

    <div class="bottombar">
      <div class="line-count" id="lineCount">Line 1 / 1</div>
      <div class="progress-track"><div class="progress-fill" id="progressFill"></div></div>
      <div class="pct" id="pct">0%</div>
      <div class="font-controls">
        <button class="font-btn" id="fontMinus">A-</button>
        <button class="font-btn" id="fontPlus">A+</button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"><i class='bx bxs-info-circle'></i><span id="toastMsg">Done</span></div>

  <div class="struggle-panel" id="strugglePanel">
    <div class="struggle-panel-head">
      <span>Struggle Map</span>
      <button id="closeStrugglePanel" aria-label="Close"><i class='bx bx-x'></i></button>
    </div>
    <div class="struggle-list" id="struggleList"></div>
    <div class="struggle-panel-foot">
      <button class="clear-struggle-btn" id="clearStruggleMap">Clear struggle map</button>
    </div>
  </div>

  <script>
    /* =====================================================================
       Load the story handed off from resources.php (sessionStorage).
       Falls back to a short demo story so this page still works if opened
       directly, e.g. while testing.
    ===================================================================== */
    const DEMO_SESSION = {
      title: "The Lion and the Mouse",
      author: "Aesop (retold)",
      level: "Grade 3",
      genre: "Fable",
      color: "#3f7d4a",
      lines: [
        "A lion was asleep in the forest one quiet afternoon.",
        "A tiny mouse scurried across his paw and woke him up.",
        "The lion opened one eye and let out an angry roar.",
        "He grabbed the mouse in his big paw, ready to eat him.",
        "\u201CPlease let me go,\u201D squeaked the mouse. \u201COne day I might help you too.\u201D",
        "The lion laughed at the idea, but he let the little mouse go free.",
        "A few days later, the lion got tangled in a hunter's net.",
        "He roared and roared, but the ropes only pulled tighter.",
        "The tiny mouse heard him and came running as fast as he could.",
        "With his sharp little teeth, the mouse chewed through the ropes and set the lion free.",
        "\u201CYou laughed when I said I could help you,\u201D said the mouse with a smile.",
        "\u201CNow you know that even the smallest friend can make the biggest difference.\u201D"
      ]
    };

    let session = DEMO_SESSION;
    try {
      const raw = sessionStorage.getItem('readpilot-reading-session');
      if (raw) {
        const parsed = JSON.parse(raw);
        if (parsed && Array.isArray(parsed.lines) && parsed.lines.length) session = parsed;
      }
    } catch (e) {
      /* sessionStorage unavailable (e.g. private browsing) — use demo story */
    }

    document.getElementById('metaLabel').textContent =
      (session.genre ? session.genre.toUpperCase() + ' \u00B7 ' : '') + (session.level ? session.level.toUpperCase() : 'GRADE 3');
    document.title = 'ReadPilot — ' + session.title;

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
       Light / dark mode toggle. Preference is remembered per-device (same
       pattern the struggle map already uses), and the icon shown is always
       the mode a click will switch TO — a sun invites you into light mode,
       a moon invites you back into dark mode.
    ===================================================================== */
    const THEME_KEY = 'readpilot-theme';
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    let currentTheme = 'dark';
    try { currentTheme = localStorage.getItem(THEME_KEY) || 'dark'; } catch (e) { /* storage unavailable */ }

    function applyTheme(theme){
      document.documentElement.classList.toggle('light-theme', theme === 'light');
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
       Session timer
    ===================================================================== */
    let seconds = 0;
    setInterval(() => {
      seconds++;
      const m = Math.floor(seconds / 60);
      const s = seconds % 60;
      document.getElementById('sessionTimer').textContent = m + ':' + String(s).padStart(2, '0');
    }, 1000);

    /* =====================================================================
       Follow-along reader: one line/sentence at a time, split into tappable
       words with a little paper-plane pointer that glides beneath whichever
       word is currently active — the "follow along" guide.
    ===================================================================== */
    const lines = session.lines;
    let lineIndex = 0;
    let wordIndex = 0;

    // Word-level progress bookkeeping — the progress bar fills by total
    // words read across the whole story, not just by which line you're on,
    // so it moves smoothly as you go rather than jumping only at line
    // breaks. wordCounts[i] = how many words are in line i; lineStartWord[i]
    // = how many words come before line i starts (its offset into the
    // whole story) — used both for the fill % and for the tick marks.
    const wordCounts = lines.map(l => l.trim().split(/\s+/).filter(Boolean).length);
    const totalWords = wordCounts.reduce((a, b) => a + b, 0) || 1;
    const lineStartWord = [];
    wordCounts.reduce((acc, count, i) => { lineStartWord[i] = acc; return acc + count; }, 0);

    // 'automatic' = the progress moves on its own so the student can follow along.
    // 'manual' = the progress only moves once the student says the word correctly;
    // if they get stuck on a word, it's flagged and logged to the struggle map.
    let mode = 'automatic';
    let everUsedManual = false; // drives whether the end-of-story report shows an accuracy stat
    let sessionStruggles = []; // tricky words logged during THIS reading (reset on "Read Again")
    let autoPlaying = false;
    let autoTimer = null;
    let recognition = null;
    let listening = false;
    let stuckTimer = null;
    let finishing = false; // guards against finishStory() running twice
    const STUCK_MS = 6000; // how long a student can be stuck on a word before it's flagged

    const sentenceWrap = document.getElementById('sentenceWrap');
    const wordPointer = document.getElementById('wordPointer');
    const wordTrail = document.getElementById('wordTrail');
    const dotsRow = document.getElementById('dotsRow');
    const lineCountEl = document.getElementById('lineCount');
    const progressFillEl = document.getElementById('progressFill');
    const pctEl = document.getElementById('pct');
    const prevLineBtn = document.getElementById('prevLineBtn');
    const nextLineBtn = document.getElementById('nextLineBtn');
    const wordProgressFillEl = document.getElementById('wordProgressFill');
    const currentWordLabelEl = document.getElementById('currentWordLabel');

    function buildDots(){
      dotsRow.innerHTML = '';
      lines.forEach((_, i) => {
        const d = document.createElement('div');
        d.className = 'dot';
        d.addEventListener('click', () => { stopAutoplay(); goToLine(i); });
        dotsRow.appendChild(d);
      });
    }

    function renderLine(){
      // Rebuild the word spans for the current line
      sentenceWrap.querySelectorAll('.word').forEach(w => w.remove());
      const words = lines[lineIndex].split(/\s+/);
      words.forEach((w, i) => {
        const span = document.createElement('span');
        span.className = 'word';
        span.textContent = w;
        span.dataset.idx = i;
        span.addEventListener('click', () => { stopAutoplay(); setWord(i); });
        sentenceWrap.appendChild(span);
      });
      wordIndex = 0;
      updateWordHighlight();
      updateProgressUI();
    }

    function updateWordHighlight(){
      const words = sentenceWrap.querySelectorAll('.word');
      words.forEach(w => w.classList.remove('active'));
      const active = words[wordIndex];
      if (!active) return;
      active.classList.add('active');
      positionPointer(active);
      updateLineWordProgress(active, words.length);
    }

    // Fills the thin bar under the sentence based on how far through THIS
    // line/sentence the reader currently is (word 1 of N ... word N of N),
    // resetting to 0% whenever a new line loads. The label to its left
    // always mirrors the word currently highlighted, punctuation stripped,
    // so it reads clearly as "the word to say right now."
    function updateLineWordProgress(activeEl, wordCount){
      if (!wordProgressFillEl || !currentWordLabelEl) return;
      const pct = wordCount > 1 ? Math.round((wordIndex / (wordCount - 1)) * 100) : 100;
      wordProgressFillEl.style.width = pct + '%';
      currentWordLabelEl.textContent = activeEl
        ? activeEl.textContent.replace(/[\u201C\u201D\u2018\u2019".,!?;:\-\u2014()]/g, '')
        : '';
    }

    function positionPointer(activeEl){
      // Position the little paper-plane pointer beneath the active word,
      // relative to the sentence container, with enough clearance that it
      // never sits on top of text wrapping onto the next line (the row-gap
      // on .sentence-wrap reserves that same space). Recomputed on every
      // word change / line change / font-size change / resize so it still
      // lines up if the text wraps differently.
      const wrapRect = sentenceWrap.getBoundingClientRect();
      const wordRect = activeEl.getBoundingClientRect();
      const left = (wordRect.left - wrapRect.left) + wordRect.width / 2;
      const top = (wordRect.top - wrapRect.top) + wordRect.height + 6;
      wordPointer.style.left = left + 'px';
      wordPointer.style.top = top + 'px';
      wordPointer.classList.add('show', 'bob');

      // Trail: a thin contrail that grows behind the plane as it crosses
      // the current visual row, from wherever that row starts up to the
      // plane's position. It's keyed off which words currently share the
      // active word's top offset (its row), so it naturally resets when
      // the plane lands on a new row — a new line, or a wrap partway
      // through a long sentence.
      const rowWords = Array.from(sentenceWrap.querySelectorAll('.word'))
        .filter(w => Math.abs(w.getBoundingClientRect().top - wordRect.top) < 4);
      const rowStart = rowWords.reduce(
        (min, w) => Math.min(min, w.getBoundingClientRect().left),
        wordRect.left
      );
      const trailLeft = rowStart - wrapRect.left;
      const trailWidth = Math.max(0, left - trailLeft);
      // Center the trail vertically on the plane icon itself: the pointer
      // box's own transform (translate(-50%, 14px)) puts its center at
      // top+14 plus half its 26px height.
      const trailTop = top + 14 + 13;
      wordTrail.style.left = trailLeft + 'px';
      wordTrail.style.top = trailTop + 'px';
      wordTrail.style.width = trailWidth + 'px';
      wordTrail.classList.add('show');
    }

    function setWord(i){
      const words = sentenceWrap.querySelectorAll('.word');
      if (i < 0){
        if (lineIndex > 0){ goToLine(lineIndex - 1, true); }
        return;
      }
      if (i >= words.length){
        if (lineIndex < lines.length - 1){
          goToLine(lineIndex + 1);
        } else {
          finishStory();
        }
        return;
      }
      wordIndex = i;
      updateWordHighlight();
      updateProgressUI();
      if (mode === 'manual' && listening) resetStuckTimer();
    }

    // CHANGED: now async — it waits for the recorder to finish writing its
    // last chunk so the audio blob is complete before we upload it.
    async function finishStory(){
      if (finishing) return;
      finishing = true;
      stopAutoplay();
      if (listening) stopListening();
      if (isRecording) await stopRecordingAndWait();
      progressFillEl.style.width = '100%';
      pctEl.textContent = '100%';
      if (wordProgressFillEl) wordProgressFillEl.style.width = '100%';
      showToast('🎉 Great job — you finished the story!');
      // Hand off performance data to the results page. A short delay lets
      // the "finished" toast register before the page navigates away.
      setTimeout(goToResults, 900);
    }

    // Bundles this reading's performance into sessionStorage, then saves the
    // session (and the cloud recording, if there is one) and sends the student
    // to results.php, which renders a frozen, blurred replay of this stage
    // behind the results card — so it still visually reads as "the reading
    // page," just paused and out of focus.
    function goToResults(){
      const minutes = seconds / 60;
      const wpm = minutes > 0 ? Math.round(totalWords / minutes) : totalWords;
      const trickyCount = sessionStruggles.length;
      const accuracy = totalWords > 0
        ? Math.max(0, Math.min(100, Math.round(((totalWords - trickyCount) / totalWords) * 100)))
        : 100;
      const uniqueTricky = [...new Set(sessionStruggles.map(w => w.word))];

      const results = {
        title: session.title,
        author: session.author || '',
        level: session.level || '',
        genre: session.genre || '',
        lines: lines,
        finishedLine: lines[lines.length - 1],
        lineCount: lines.length,
        seconds: seconds,
        totalWords: totalWords,
        wpm: wpm,
        everUsedManual: everUsedManual,
        accuracy: accuracy,
        trickyWords: uniqueTricky
      };

      try { sessionStorage.setItem('readpilot-results', JSON.stringify(results)); } catch (e) { /* storage unavailable */ }
      uploadAndLeave(wpm, accuracy, uniqueTricky);
    }

    // NEW: saves the reading session, then uploads the recording (if any)
    // linked to the new session id, then goes to the results page. Any
    // failure is swallowed so the student is never stuck on this page.
    async function uploadAndLeave(wpm, accuracy, uniqueTricky){
      try {
        if (session.studentId) {
          const res = await fetch('student-api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
              action: 'record_session',
              student_id: session.studentId,
              book: session.title,
              wpm: wpm,
              accuracy: accuracy,
              duration_seconds: Math.round(seconds),
              tricky_words: uniqueTricky.join(', ')
            })
          });
          const data = await res.json();
          if (data.ok && data.session_id && recordingBlob) {
            showToast('Saving recording…');
            const fd = new FormData();
            fd.append('session_id', data.session_id);
            fd.append('student_id', session.studentId);
            fd.append('duration_seconds', Math.round(seconds));
            fd.append('audio', recordingBlob, 'reading.webm');
            await fetch('recording-api.php', { method: 'POST', body: fd });
          }
        }
      } catch (e) { /* don't block the student from seeing results */ }
      window.location.href = 'results.php';
    }

    function goToLine(i, landOnLastWord){
      if (i < 0 || i >= lines.length) return;
      lineIndex = i;
      renderLine();
      if (landOnLastWord){
        const words = sentenceWrap.querySelectorAll('.word');
        wordIndex = words.length - 1;
        updateWordHighlight();
        updateProgressUI();
      }
      if (mode === 'manual' && listening) resetStuckTimer();
    }

    function updateProgressUI(){
      lineCountEl.textContent = `Line ${lineIndex + 1} / ${lines.length}`;
      // Word-level fill: how many words have been reached across the whole
      // story so far, not just which line we're on — this is what makes
      // the bar move smoothly within a long line instead of only jumping
      // when a new line starts.
      const wordsDone = lineStartWord[lineIndex] + wordIndex;
      const pct = Math.round((wordsDone / totalWords) * 100);
      progressFillEl.style.width = pct + '%';
      pctEl.textContent = pct + '%';
      const dots = dotsRow.querySelectorAll('.dot');
      dots.forEach((d, i) => {
        d.classList.toggle('active', i === lineIndex);
        d.classList.toggle('done', i < lineIndex);
      });
      prevLineBtn.disabled = lineIndex === 0;
      nextLineBtn.disabled = lineIndex === lines.length - 1;
    }

    prevLineBtn.addEventListener('click', () => { stopAutoplay(); goToLine(lineIndex - 1); });
    nextLineBtn.addEventListener('click', () => {
      stopAutoplay();
      if (lineIndex === lines.length - 1){
        finishStory();
        return;
      }
      goToLine(lineIndex + 1);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight'){ e.preventDefault(); stopAutoplay(); setWord(wordIndex + 1); }
      else if (e.key === 'ArrowLeft'){ e.preventDefault(); stopAutoplay(); setWord(wordIndex - 1); }
    });

    window.addEventListener('resize', () => {
      const words = sentenceWrap.querySelectorAll('.word');
      if (words[wordIndex]) positionPointer(words[wordIndex]);
    });

    buildDots();
    renderLine();

    /* =====================================================================
       Font size controls (bottom bar A- / A+)
    ===================================================================== */
    let fontSize = 34;
    function applyFontSize(){
      document.documentElement.style.setProperty('--read-size', fontSize + 'px');
      // Re-run on next frame so layout has updated before repositioning the pointer
      requestAnimationFrame(() => {
        const words = sentenceWrap.querySelectorAll('.word');
        if (words[wordIndex]) positionPointer(words[wordIndex]);
      });
    }
    document.getElementById('fontMinus').addEventListener('click', () => {
      fontSize = Math.max(22, fontSize - 4);
      applyFontSize();
    });
    document.getElementById('fontPlus').addEventListener('click', () => {
      fontSize = Math.min(50, fontSize + 4);
      applyFontSize();
    });

    /* =====================================================================
       Audio recorder — records the student reading aloud using the mic.
       Uses MediaRecorder + getUserMedia. Falls back gracefully with a
       toast if the browser/device doesn't support it or permission is
       denied (no crash, no silent failure).
       The finished clip is kept in `recordingBlob` and uploaded to cloud
       storage (via recording-api.php) when the story is finished.
    ===================================================================== */
    const micBtn = document.getElementById('micBtn');
    const micIcon = document.getElementById('micIcon');
    const micLabel = document.getElementById('micLabel');
    const micSub = document.getElementById('micSub');
    const rerecordChip = document.getElementById('rerecordChip');

    let mediaRecorder = null;
    let recordedChunks = [];
    let recordingBlob = null; // NEW: the finished clip, uploaded on finish
    let recordingStream = null;
    let isRecording = false;
    let recSeconds = 0;
    let recTimer = null;

    function setMicIdle(){
      micBtn.classList.remove('recording');
      micIcon.className = 'bx bx-microphone';
      micLabel.textContent = 'Record My Reading';
      micSub.textContent = 'Tap to start';
    }
    function setMicRecording(){
      micBtn.classList.add('recording');
      micIcon.className = 'bx bx-stop';
      micLabel.textContent = 'Recording\u2026';
      micSub.textContent = 'Tap to stop';
    }
    function setMicHasClip(){
      micBtn.classList.remove('recording');
      micIcon.className = 'bx bx-microphone';
      micLabel.textContent = 'Recording Complete';
      micSub.textContent = 'Tap to record again';
      rerecordChip.classList.add('show');
    }

    async function startRecording(){
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || typeof MediaRecorder === 'undefined'){
        showToast("This browser can't record audio here — try Chrome or Edge.");
        return;
      }
      try {
        recordingStream = await navigator.mediaDevices.getUserMedia({ audio: true });
      } catch (err) {
        showToast('Microphone access was blocked. Please allow it to record.');
        return;
      }
      recordedChunks = [];
      recordingBlob = null; // a new take replaces any earlier one
      try {
        mediaRecorder = new MediaRecorder(recordingStream);
      } catch (err) {
        showToast("This browser can't record audio here.");
        recordingStream.getTracks().forEach(t => t.stop());
        return;
      }
      mediaRecorder.ondataavailable = (e) => { if (e.data && e.data.size > 0) recordedChunks.push(e.data); };
      mediaRecorder.onstop = () => {
        // CHANGED: build the final blob so it can be uploaded later
        recordingBlob = new Blob(recordedChunks, { type: (mediaRecorder && mediaRecorder.mimeType) || 'audio/webm' });
        setMicHasClip();
        showToast('Recording saved for this session');
      };
      mediaRecorder.start();
      isRecording = true;
      recSeconds = 0;
      setMicRecording();
      if (mode === 'automatic') startAutoplay();
      recTimer = setInterval(() => {
        recSeconds++;
        micSub.textContent = `Tap to stop \u00B7 ${recSeconds}s`;
      }, 1000);
    }

    function stopRecording(){
      isRecording = false;
      clearInterval(recTimer);
      if (mode === 'automatic') stopAutoplay();
      if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
      if (recordingStream) recordingStream.getTracks().forEach(t => t.stop());
    }

    // NEW: stops the recorder and resolves only after `onstop` has fired,
    // i.e. after recordingBlob has been built.
    function stopRecordingAndWait(){
      return new Promise(resolve => {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') return resolve();
        const previousOnStop = mediaRecorder.onstop;
        mediaRecorder.onstop = (e) => {
          if (previousOnStop) previousOnStop(e);
          resolve();
        };
        stopRecording();
      });
    }

    micBtn.addEventListener('click', () => {
      if (mode === 'manual'){
        if (listening){ stopListening(); } else { startListening(); }
        return;
      }
      if (isRecording){
        stopRecording();
      } else {
        startRecording();
      }
    });

    // CHANGED: re-record now also throws away the old clip
    rerecordChip.addEventListener('click', () => {
      recordingBlob = null;
      recordedChunks = [];
      rerecordChip.classList.remove('show');
      setMicIdle();
    });

    setMicIdle();
    applyFontSize();

    /* =====================================================================
       Reading mode: Automatic vs Manual
       - Automatic advances the highlighted word (and line) on its own,
         at a pace based on word length, so the student
         can follow along with their eyes.
       - Manual: the mic listens for the student's voice using the Web
         Speech API. The progress only moves forward once the spoken word
         matches the highlighted word. If the student is stuck on a word
         for too long, it gets flagged on the page and logged to the
         struggle map, then reading moves on to the next word.
    ===================================================================== */
    const modeToggle = document.getElementById('modeToggle');
    const listenStatusEl = document.getElementById('listenStatus');
    const hintTextEl = document.getElementById('hintText');

    const HINTS = {
      automatic: 'Follow the plane as it moves through the story, or use the arrows',
      manual: 'Tap the mic and read the highlighted word out loud'
    };

    function setMode(newMode){
      mode = newMode;
      if (newMode === 'manual') everUsedManual = true;
      stopAutoplay();
      if (listening) stopListening();
      if (isRecording) stopRecording();

      document.querySelectorAll('.mode-btn').forEach(b => b.classList.toggle('active', b.dataset.mode === mode));
      if (hintTextEl) hintTextEl.textContent = HINTS[mode];
      listenStatusEl.textContent = '';

      if (mode === 'manual'){
        micLabel.textContent = 'Read It Out Loud';
        micSub.textContent = 'Tap to start listening';
      } else {
        setMicIdle();
      }
    }

    modeToggle.addEventListener('click', (e) => {
      const btn = e.target.closest('.mode-btn');
      if (btn) setMode(btn.dataset.mode);
    });

    /* ---------- Automatic follow-along ---------- */
    function startAutoplay(){
      if (mode !== 'automatic') return;
      autoPlaying = true;
      scheduleAutoAdvance();
    }
    function stopAutoplay(){
      autoPlaying = false;
      if (autoTimer){ clearTimeout(autoTimer); autoTimer = null; }
    }
    function scheduleAutoAdvance(){
      if (!autoPlaying) return;
      const words = sentenceWrap.querySelectorAll('.word');
      const current = words[wordIndex];
      const wordLen = current ? current.textContent.length : 5;
      // Roughly pace by word length so short words don't linger and long words aren't rushed
      const delay = Math.min(5000, Math.max(3000, wordLen * 180 + 2500));
      autoTimer = setTimeout(() => {
        if (!autoPlaying) return;
        const isLastWordOfBook = (wordIndex + 1 >= sentenceWrap.querySelectorAll('.word').length) && (lineIndex === lines.length - 1);
        if (isLastWordOfBook){
          stopAutoplay();
          finishStory();
          return;
        }
        setWord(wordIndex + 1);
        scheduleAutoAdvance();
      }, delay);
    }
    /* ---------- Manual mode: listen for pronunciation ---------- */
    function normalizeWord(w){
      return (w || '').toLowerCase().replace(/[^a-z']/g, '');
    }
    function soundsLikeMatch(spoken, target){
      if (!spoken || !target) return false;
      if (spoken === target) return true;
      // Tolerant match for partial recognitions / minor mis-hearings
      if (spoken.length > 2 && target.length > 2 && (target.startsWith(spoken) || spoken.startsWith(target))) return true;
      return false;
    }

    async function startListening(){
      const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SpeechRec){
        showToast("This browser can't listen for pronunciation — try Chrome or Edge, or switch to Automatic mode.");
        return;
      }
      try {
        recognition = new SpeechRec();
      } catch (err) {
        showToast("This browser can't listen for pronunciation here.");
        return;
      }
      recognition.lang = 'en-US';
      recognition.continuous = true;
      recognition.interimResults = true;

      recognition.onresult = handleRecognitionResult;
      recognition.onerror = (e) => {
        if (e.error === 'no-speech' || e.error === 'aborted') return;
        if (e.error === 'not-allowed' || e.error === 'service-not-allowed'){
          showToast('Microphone access was blocked. Please allow it to listen.');
          stopListening();
        }
      };
      recognition.onend = () => {
        // Some browsers stop recognition periodically on their own; restart
        // seamlessly as long as the student is still in listening mode.
        if (listening){
          try { recognition.start(); } catch (e) { /* ignore transient restart errors */ }
        }
      };

      try {
        recognition.start();
      } catch (err) {
        showToast("Couldn't start listening. Please try again.");
        return;
      }

      listening = true;
      micBtn.classList.add('recording');
      micIcon.className = 'bx bx-stop';
      micLabel.textContent = 'Listening\u2026';
      micSub.textContent = 'Tap to stop';
      resetStuckTimer();
    }

    function stopListening(){
      listening = false;
      clearStuckTimer();
      if (recognition){
        recognition.onend = null;
        try { recognition.stop(); } catch (e) { /* already stopped */ }
        recognition = null;
      }
      micBtn.classList.remove('recording');
      micIcon.className = 'bx bx-microphone';
      micLabel.textContent = 'Read It Out Loud';
      micSub.textContent = 'Tap to start listening';
      listenStatusEl.textContent = '';
    }

    function handleRecognitionResult(event){
      const words = sentenceWrap.querySelectorAll('.word');
      const targetEl = words[wordIndex];
      if (!targetEl) return;
      const targetWord = normalizeWord(targetEl.textContent);

      let transcript = '';
      for (let i = event.resultIndex; i < event.results.length; i++){
        transcript += ' ' + event.results[i][0].transcript;
      }
      const spokenWords = transcript.trim().toLowerCase().split(/\s+/).map(normalizeWord).filter(Boolean);
      if (spokenWords.length){
        listenStatusEl.textContent = 'Heard: "' + spokenWords[spokenWords.length - 1] + '"';
      }

      const gotIt = spokenWords.some(w => soundsLikeMatch(w, targetWord));
      if (gotIt){
        targetEl.classList.add('correct-flash');
        setTimeout(() => targetEl.classList.remove('correct-flash'), 600);
        setWord(wordIndex + 1);
      }
    }

    function resetStuckTimer(){
      clearStuckTimer();
      stuckTimer = setTimeout(flagStuckWord, STUCK_MS);
    }
    function clearStuckTimer(){
      if (stuckTimer){ clearTimeout(stuckTimer); stuckTimer = null; }
    }
    function flagStuckWord(){
      const words = sentenceWrap.querySelectorAll('.word');
      const targetEl = words[wordIndex];
      if (targetEl){
        targetEl.classList.add('struggled');
        const cleanWord = targetEl.textContent.replace(/[^a-zA-Z']/g, '');
        addStruggleWord(targetEl.textContent);
        sessionStruggles.push({ word: cleanWord, line: lineIndex + 1 });
        showToast('Marked "' + cleanWord + '" as tricky \u2014 added to the struggle map');
      }
      setWord(wordIndex + 1);
    }

    /* ---------- Struggle map (persists across sessions on this device) ---------- */
    const STRUGGLE_KEY = 'readpilot-struggle-map';

    function loadStruggleMap(){
      try {
        const raw = localStorage.getItem(STRUGGLE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed) ? parsed : [];
      } catch (e) {
        return [];
      }
    }
    function addStruggleWord(word){
      const map = loadStruggleMap();
      map.push({
        word: word.replace(/[^a-zA-Z']/g, ''),
        story: session.title || 'Untitled story',
        line: lineIndex + 1,
        at: new Date().toISOString()
      });
      try { localStorage.setItem(STRUGGLE_KEY, JSON.stringify(map)); } catch (e) { /* storage unavailable */ }
      updateStruggleBadge();
    }
    function updateStruggleBadge(){
      const count = loadStruggleMap().length;
      const badge = document.getElementById('struggleBadge');
      badge.textContent = count > 99 ? '99+' : String(count);
      badge.style.display = count > 0 ? 'flex' : 'none';
    }
    function escapeHtml(s){
      return String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]));
    }
    function renderStruggleList(){
      const list = document.getElementById('struggleList');
      const map = loadStruggleMap();
      if (!map.length){
        list.innerHTML = '<div class="struggle-empty">No tricky words yet \u2014 keep reading in Manual mode and they\u2019ll show up here.</div>';
        return;
      }
      list.innerHTML = map.slice().reverse().map(item => `
        <div class="struggle-item">
          <span class="struggle-word">${escapeHtml(item.word)}</span>
          <span class="struggle-meta">${escapeHtml(item.story)} \u00B7 Line ${item.line}</span>
        </div>
      `).join('');
    }

    const strugglePanel = document.getElementById('strugglePanel');
    document.getElementById('struggleBtn').addEventListener('click', () => {
      renderStruggleList();
      strugglePanel.classList.add('show');
    });
    document.getElementById('closeStrugglePanel').addEventListener('click', () => {
      strugglePanel.classList.remove('show');
    });
    document.getElementById('clearStruggleMap').addEventListener('click', () => {
      try { localStorage.removeItem(STRUGGLE_KEY); } catch (e) { /* storage unavailable */ }
      renderStruggleList();
      updateStruggleBadge();
      showToast('Struggle map cleared');
    });

    updateStruggleBadge();
    setMode('automatic');

  </script>
  <script src="shared-ui.js"></script>
</body>
</html>