<?php
declare(strict_types=1);

/**
 * SDM Central File API
 *
 * POST actions:
 *   action=upload   + file=<PDF>
 *   action=download + file_id=<ID>
 *   action=delete   + file_id=<ID>
 *
 * Authentication:
 *   Authorization: Bearer <API_SECRET>
 */

header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function respond(int $status, array $data): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

function requestMethod(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function bearerToken(): string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if ($header === '' && function_exists('getallheaders')) {
        $headers = getallheaders();
        $header = $headers['Authorization']
            ?? $headers['authorization']
            ?? '';
    }

    if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)) {
        return trim($matches[1]);
    }

    return '';
}

// Load secrets from outside public_html.
$configFile = '/home/sdm.bcp-sms.com/api-secrets.php';

if (!is_file($configFile)) {
    respond(500, ['error' => 'API configuration unavailable.']);
}

$config = require $configFile;

if (
    !is_array($config) ||
    empty($config['api_secret']) ||
    empty($config['storage_path'])
) {
    respond(500, ['error' => 'API configuration is invalid.']);
}

$expectedSecret = (string) $config['api_secret'];
$providedSecret = bearerToken();

if (
    $providedSecret === '' ||
    !hash_equals($expectedSecret, $providedSecret)
) {
    respond(401, ['error' => 'Unauthorized.']);
}

$storageRoot = rtrim(
    str_replace('\\', '/', (string) $config['storage_path']),
    '/'
);

$filesDir = $storageRoot . '/api-files';
$metadataDir = $storageRoot . '/.api-metadata';

foreach ([$filesDir, $metadataDir] as $directory) {
    if (
        !is_dir($directory) &&
        !mkdir($directory, 0750, true) &&
        !is_dir($directory)
    ) {
        respond(500, ['error' => 'Private storage is unavailable.']);
    }

    if (!is_writable($directory)) {
        respond(500, ['error' => 'Private storage is not writable.']);
    }
}

$action = (string) ($_POST['action'] ?? '');

if (requestMethod() !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'Use POST for API requests.']);
}

if ($action === 'upload') {
    if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
        respond(400, ['error' => 'A PDF file is required.']);
    }

    $upload = $_FILES['file'];

    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        respond(400, ['error' => 'Upload failed. Check the file and size.']);
    }

    $tmp = (string) ($upload['tmp_name'] ?? '');
    $size = (int) ($upload['size'] ?? 0);

    if (!is_uploaded_file($tmp)) {
        respond(400, ['error' => 'Invalid uploaded file.']);
    }

    if ($size < 1 || $size > 10 * 1024 * 1024) {
        respond(413, ['error' => 'PDF must be no larger than 10 MB.']);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    $handle = fopen($tmp, 'rb');
    $signature = $handle ? fread($handle, 5) : false;

    if ($handle) {
        fclose($handle);
    }

    if ($mime !== 'application/pdf' || $signature !== '%PDF-') {
        respond(415, ['error' => 'Only valid PDF files are accepted.']);
    }

    // Never use a caller-supplied filename as the storage path.
    $fileId = bin2hex(random_bytes(16));
    $filePath = $filesDir . '/' . $fileId . '.pdf';
    $metadataPath = $metadataDir . '/' . $fileId . '.json';

    if (!move_uploaded_file($tmp, $filePath)) {
        respond(500, ['error' => 'Could not save the PDF.']);
    }

    chmod($filePath, 0640);

    $metadata = [
        'file_id' => $fileId,
        'original_name' => basename(
            str_replace('\\', '/', (string) ($upload['name'] ?? 'document.pdf'))
        ),
        'mime' => 'application/pdf',
        'size' => $size,
        'created_at' => gmdate('c'),
    ];

    $written = file_put_contents(
        $metadataPath,
        json_encode($metadata, JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );

    if ($written === false) {
        @unlink($filePath);
        respond(500, ['error' => 'Could not save file metadata.']);
    }

    chmod($metadataPath, 0640);

    respond(201, [
        'success' => true,
        'file_id' => $fileId,
        'filename' => $metadata['original_name'],
        'size' => $size,
    ]);
}

$fileId = (string) ($_POST['file_id'] ?? '');

if (!preg_match('/^[a-f0-9]{32}$/', $fileId)) {
    respond(400, ['error' => 'A valid file_id is required.']);
}

$filePath = $filesDir . '/' . $fileId . '.pdf';
$metadataPath = $metadataDir . '/' . $fileId . '.json';

if ($action === 'download') {
    if (!is_file($filePath) || !is_file($metadataPath)) {
    error_log('SDM FILE DEBUG: ' . json_encode([
        'file_id' => $fileId,
        'files_dir' => $filesDir,
        'metadata_dir' => $metadataDir,
        'file_path' => $filePath,
        'metadata_path' => $metadataPath,
        'pdf_exists' => is_file($filePath),
        'metadata_exists' => is_file($metadataPath),
    ]));
    respond(404, ['error' => 'File not found.']);
    }

    $metadata = json_decode(
        (string) file_get_contents($metadataPath),
        true
    );

    if (!is_array($metadata)) {
        respond(500, ['error' => 'File metadata is unavailable.']);
    }

    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($filePath));
    header('Content-Disposition: attachment; filename="'
        . $fileId . '.pdf"');

    readfile($filePath);
    exit;
}

if ($action === 'delete') {
    if (!is_file($filePath) || !is_file($metadataPath)) {
        respond(404, ['error' => 'File not found.']);
    }

    if (!unlink($filePath) || !unlink($metadataPath)) {
        respond(500, ['error' => 'Could not delete the file.']);
    }

    respond(200, ['success' => true, 'file_id' => $fileId]);
}

respond(400, ['error' => 'Unsupported action.']);
