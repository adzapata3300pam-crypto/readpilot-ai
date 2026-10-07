<?php
declare(strict_types=1);

// Supabase Storage S3 helper. S3 keys are server-only and must stay outside
// the web root; requests use AWS Signature Version 4.

require_once __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;

function storage_setting(string $name): string
{
    $v = getenv($name);
    if ($v !== false && $v !== '') return (string) $v;
    static $file = null;
    if ($file === null) {
        $path = dirname(__DIR__, 2) . '/readpilot-secrets.php';
        $file = is_file($path) ? (array) require $path : [];
    }
    return (string) ($file[$name] ?? '');
}

function supabase_s3_client(): S3Client
{
    static $client = null;
    if ($client instanceof S3Client) return $client;

    $endpoint = rtrim(storage_setting('SUPABASE_S3_ENDPOINT'), '/');
    $region = storage_setting('SUPABASE_S3_REGION');
    $key = storage_setting('SUPABASE_S3_ACCESS_KEY');
    $secret = storage_setting('SUPABASE_S3_SECRET_KEY');
    if ($endpoint === '' || $region === '' || $key === '' || $secret === '') {
        throw new RuntimeException('Supabase Storage S3 endpoint, region, access key, and secret are not configured.');
    }

    return $client = new S3Client([
        'version' => 'latest',
        'region' => $region,
        'endpoint' => $endpoint,
        'use_path_style_endpoint' => true,
        'signature_version' => 'v4',
        'credentials' => ['key' => $key, 'secret' => $secret],
        'request_checksum_calculation' => 'when_required',
        'response_checksum_validation' => 'when_required',
    ]);
}

function recording_bucket(): string
{
    $bucket = storage_setting('SUPABASE_BUCKET');
    if ($bucket === '') throw new RuntimeException('Supabase Storage bucket is not configured.');
    return $bucket;
}

/** Asks the bucket itself (not our database) what it holds for $key. Throws if the object doesn't exist. */
function recording_object_info(string $key): array
{
    $info = supabase_s3_client()->headObject(['Bucket' => recording_bucket(), 'Key' => $key]);
    return [
        'size' => (int) ($info['ContentLength'] ?? 0),
        'etag' => trim((string) ($info['ETag'] ?? ''), '"'),
        'modified' => $info['LastModified'] instanceof DateTimeInterface ? $info['LastModified']->format(DATE_ATOM) : '',
    ];
}

function upload_recording_object(string $localPath, string $key, string $mime): void
{
    $stream = fopen($localPath, 'rb');
    if ($stream === false) throw new RuntimeException('Unable to read the uploaded recording.');
    try {
        supabase_s3_client()->putObject([
            'Bucket' => recording_bucket(),
            'Key' => $key,
            'Body' => $stream,
            'ContentType' => $mime,
        ]);
    } finally {
        fclose($stream);
    }
}

function recording_presigned_url(string $key, int $minutes = 10): string
{
    $command = supabase_s3_client()->getCommand('GetObject', ['Bucket' => recording_bucket(), 'Key' => $key]);
    return (string) supabase_s3_client()->createPresignedRequest($command, '+' . $minutes . ' minutes')->getUri();
}

function delete_recording_object(string $key): void
{
    supabase_s3_client()->deleteObject(['Bucket' => recording_bucket(), 'Key' => $key]);
}

/** Writes a tiny object, reads its metadata back from the bucket, deletes it. */
function storage_selftest(string $prefix): array
{
    $key = 'selftest/' . $prefix . '-' . time() . '.txt';
    $body = 'ReadPilot storage self-test ' . date('c');
    supabase_s3_client()->putObject(['Bucket' => recording_bucket(), 'Key' => $key, 'Body' => $body, 'ContentType' => 'text/plain']);
    try {
        $info = recording_object_info($key);
    } finally {
        delete_recording_object($key);
    }
    return ['bucket' => recording_bucket(), 'key' => $key] + $info;
}