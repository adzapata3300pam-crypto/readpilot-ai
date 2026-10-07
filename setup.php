<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pdo = db();
echo "ReadPilot database connection is working. Import database.sql to create and seed accounts.\n";
