<?php

declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'readpilot';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $pdo;
}

function access_id_storage_directory(): string
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'readpilot-private' . DIRECTORY_SEPARATOR . 'access-ids';
}
