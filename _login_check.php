<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=readpilot;charset=utf8mb4', 'root', '');
foreach (['teacher@readpilot.local', 'admin@readpilot.local'] as $email) {
    $hash = $pdo->query("SELECT password_hash FROM users WHERE email = '" . addslashes($email) . "'")->fetchColumn();
    $ok = $hash !== false && password_verify('ReadPilot2026!', $hash);
    echo $email . ' => ' . ($ok ? 'VALID' : 'INVALID') . PHP_EOL;
}
