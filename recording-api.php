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
