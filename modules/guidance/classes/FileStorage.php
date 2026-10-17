<?php
declare(strict_types=1);

require_once __DIR__ . '/SdmFileApi.php';

class FileStorage
{
    public const MAX_BYTES = 10485760; // 10 MB

    public static $allowed = [
        'pdf'  => ['application/pdf'],
        'doc'  => [
            'application/msword', 'application/cdfv2',
            'application/x-ole-storage', 'application/octet-stream',
        ],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', 'application/octet-stream',
        ],
        'xls'  => [
            'application/vnd.ms-excel', 'application/cdfv2',
            'application/x-ole-storage', 'application/octet-stream',
        ],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip', 'application/octet-stream',
        ],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
    ];

    public static $mimeByExt = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
    ];

    
    private static function remoteEnabled(): bool
    {
        $path = getenv('SDM_API_CONFIG_FILE');

        if (!is_string($path) || $path === '') {
            $path = '/home/gms.bcp-sms.com/sdm-api-config.php';
        }

        return is_file($path) && is_readable($path);
    }

    private static function api(): SdmFileApi
    {
        return new SdmFileApi();
    }

    public static function root(): string
    {
        return str_replace('\\', '/', dirname(__DIR__, 3));
    }

    public static function base(): string
    {
        if (defined('SMS_STORAGE_PATH') && SMS_STORAGE_PATH) {
            return rtrim(str_replace('\\', '/', SMS_STORAGE_PATH), '/');
        }

        $env = getenv('SMS_STORAGE_PATH');

        if ($env) {
            return rtrim(str_replace('\\', '/', $env), '/');
        }

        // Local development fallback only. Do not use the SDM host's path
        // as though it were a local directory on the Guidance server.
        return self::root() . '/storage';
    }

    public static function ensureDir($dir): bool
    {
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        return is_writable($dir);
    }

    public static function hasUpload($file): bool
    {
        return is_array($file)
            && (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);
    }

    public static function validateUpload($file): ?string
    {
        if (!is_array($file)) {
            return 'No valid attachment was provided.';
        }

        switch ($file['error'] ?? UPLOAD_ERR_NO_FILE) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'The attachment is too large (maximum 10 MB).';
            case UPLOAD_ERR_PARTIAL:
                return 'The attachment was only partly uploaded. Please try again.';
            default:
                return 'The attachment could not be uploaded (server error code '
                    . (int) ($file['error'] ?? -1) . ').';
        }

        $tmp = (string) ($file['tmp_name'] ?? '');

        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return 'Invalid upload.';
        }

        $size = (int) ($file['size'] ?? 0);

        if ($size < 1 || $size > self::MAX_BYTES) {
            return 'The attachment must be between 1 byte and 10 MB.';
        }

        $ext = self::extensionOf($file['name'] ?? '');

        if (!isset(self::$allowed[$ext])) {
            return 'This file type is not allowed. Use PDF, Word, Excel, JPG or PNG.';
        }

        $mime = strtolower(self::detectMime($tmp));

        $allowed = array_map('strtolower', self::$allowed[$ext]);

        if ($mime !== '' && !in_array($mime, $allowed, true)) {
            return 'The file content does not match its file type.';
        }

        return null;
    }

    /**
     * Store a PHP-uploaded file.
     * Returns either a remote reference ("sdm:<32-hex-id>.<ext>")
     * or a local relative path when remote API configuration is absent.
     */
    public static function store($file, $subDir, $prefix): string
    {
        $error = self::validateUpload($file);

        if ($error !== null) {
            throw new RuntimeException($error);
        }

        $ext = self::extensionOf($file['name']);
        $name = self::safePrefix($prefix)
            . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        if (self::remoteEnabled()) {
            $result = self::api()->upload($file['tmp_name'], $name);
            $id = (string) ($result['file_id'] ?? '');

            if (!preg_match('/^[a-f0-9]{32}$/', $id)) {
                throw new RuntimeException(
                    'The SDM API did not return a valid file ID.'
                );
            }

            return 'sdm:' . $id . '.' . $ext;
        }

        // Local fallback for development.
        $subDir = self::cleanSubDir($subDir);
        $dir = self::base() . '/' . $subDir;

        if (!self::ensureDir($dir)) {
            throw new RuntimeException(
                'Local storage folder is missing or not writable: ' . $dir
            );
        }

        $path = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new RuntimeException('Could not move the uploaded file into storage.');
        }

        @chmod($path, 0640);

        return $subDir . '/' . $name;
    }

    /**
     * Store generated file bytes, such as a PDF.
     */
    public static function storeBytes($bytes, $subDir, $fileName): string
    {
        if (!is_string($bytes) || $bytes === '' || strlen($bytes) > self::MAX_BYTES) {
            throw new RuntimeException('Generated file must be between 1 byte and 10 MB.');
        }

        $fileName = basename(str_replace('\\', '/', (string) $fileName));
        $ext = self::extensionOf($fileName);

        if (!isset(self::$allowed[$ext])) {
            throw new RuntimeException('This generated file type is not allowed.');
        }

        if (self::remoteEnabled()) {
            $result = self::api()->uploadBytes($bytes, $fileName);
            $id = (string) ($result['file_id'] ?? '');

            if (!preg_match('/^[a-f0-9]{32}$/', $id)) {
                throw new RuntimeException(
                    'The SDM API did not return a valid file ID.'
                );
            }

            return 'sdm:' . $id . '.' . $ext;
        }

        // Local fallback for development.
        $subDir = self::cleanSubDir($subDir);
        $dir = self::base() . '/' . $subDir;

        if (!self::ensureDir($dir)) {
            throw new RuntimeException(
                'Local storage folder is missing or not writable: ' . $dir
            );
        }

        $path = $dir . '/' . $fileName;

        if (file_put_contents($path, $bytes, LOCK_EX) === false) {
            throw new RuntimeException('Could not write file to local storage.');
        }

        @chmod($path, 0640);

        return $subDir . '/' . $fileName;
    }

    /**
     * Resolve only local files. Remote references must use streamStored().
     */
    public static function resolve($relative): ?string
    {
        $relative = ltrim(str_replace('\\', '/', (string) $relative), '/');

        if ($relative === '' || strpos($relative, '..') !== false
            || self::isRemoteReference($relative)) {
            return null;
        }

        $base = self::base();
        $candidates = [$base . '/' . $relative];

        if (strpos($relative, 'uploads/') === 0) {
            $candidates[] = self::root() . '/' . $relative;
        }

        $allowedRoots = array_filter([
            realpath($base),
            realpath(self::root() . '/uploads'),
        ]);

        foreach ($candidates as $path) {
            if (!is_file($path)) {
                continue;
            }

            $real = realpath($path);

            if (!$real) {
                continue;
            }

            foreach ($allowedRoots as $root) {
                if (strpos($real, rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) === 0) {
                    return $real;
                }
            }
        }

        return null;
    }

    /**
     * Stream a stored file after the caller has authorized access.
     * Supports both local paths and remote SDM references.
     */
    public static function streamStored(
        $reference,
        string $downloadName,
        string $mime,
        bool $download = false
    ): void {
        $reference = (string) $reference;

        if (self::isRemoteReference($reference)) {
            if (!self::remoteEnabled()) {
                throw new RuntimeException('Remote SDM storage is not configured.');
            }

            $fileId = self::remoteFileId($reference);
            $downloaded = self::api()->downloadToTemp($fileId);

            try {
                self::streamLocalFile(
                    $downloaded['path'],
                    $downloadName ?: $downloaded['filename'],
                    $mime ?: $downloaded['mime'],
                    $download
                );
            } finally {
                if (is_file($downloaded['path'])) {
                    @unlink($downloaded['path']);
                }
            }

            exit;
        }

        $absolute = self::resolve($reference);

        if (!$absolute) {
            throw new RuntimeException('The stored file could not be found.');
        }

        self::streamLocalFile($absolute, $downloadName, $mime, $download);
        exit;
    }

    /**
     * Backward-compatible local stream method.
     */
    public static function stream($absPath, $downloadName, $mime, $download = false): void
    {
        self::streamLocalFile($absPath, $downloadName, $mime, (bool) $download);
        exit;
    }

    private static function streamLocalFile(
        string $path,
        string $downloadName,
        string $mime,
        bool $download
    ): void {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('The stored file is unavailable.');
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $safeName = preg_replace(
            '/[^A-Za-z0-9._ -]/',
            '_',
            basename(str_replace('\\', '/', $downloadName))
        );

        if (!headers_sent()) {
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . (string) filesize($path));
            header(
                'Content-Disposition: '
                . ($download ? 'attachment' : 'inline')
                . '; filename="' . $safeName . '"'
            );
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('Cache-Control: private, no-store, max-age=0');
        }

        readfile($path);
    }

    public static function delete($reference): void
    {
        $reference = (string) $reference;

        if (self::isRemoteReference($reference)) {
            if (!self::remoteEnabled()) {
                error_log('[FileStorage] Cannot delete remote file: SDM API is not configured.');
                return;
            }

            try {
                self::api()->delete(self::remoteFileId($reference));
            } catch (Throwable $e) {
                error_log('[FileStorage] Remote delete failed: ' . $e->getMessage());
            }

            return;
        }

        $absolute = self::resolve($reference);

        if ($absolute) {
            @unlink($absolute);
        }
    }

    public static function extensionOf($name): string
    {
        return strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
    }

    public static function mimeFor($name): string
    {
        return self::$mimeByExt[self::extensionOf((string) $name)]
            ?? 'application/octet-stream';
    }

    public static function cleanName($name): string
    {
        $name = basename(str_replace('\\', '/', (string) $name));
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?? '';
        return function_exists('mb_substr') ? mb_substr($name, 0, 150) : substr($name, 0, 150);
    }

    private static function detectMime(string $path): string
    {
        if (!function_exists('finfo_open')) {
            return '';
        }

        $f = finfo_open(FILEINFO_MIME_TYPE);

        if (!$f) {
            return '';
        }

        $mime = finfo_file($f, $path);
        finfo_close($f);

        return is_string($mime) ? $mime : '';
    }

    private static function isRemoteReference(string $reference): bool
    {
        return (bool) preg_match(
            '/^sdm:[a-f0-9]{32}\.(pdf|doc|docx|xls|xlsx|jpg|jpeg|png)$/i',
            $reference
        );
    }

    private static function remoteFileId(string $reference): string
    {
        if (!preg_match(
            '/^sdm:([a-f0-9]{32})\.(pdf|doc|docx|xls|xlsx|jpg|jpeg|png)$/i',
            $reference,
            $matches
        )) {
            throw new InvalidArgumentException('Invalid remote SDM file reference.');
        }

        return strtolower($matches[1]);
    }

    private static function safePrefix(string $prefix): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_-]/', '', $prefix) ?? '';
        return $safe !== '' ? $safe : 'file';
    }

    private static function cleanSubDir(string $subDir): string
    {
        $subDir = trim(str_replace('\\', '/', $subDir), '/');

        if ($subDir === '' || strpos($subDir, '..') !== false) {
            throw new InvalidArgumentException('Invalid local storage directory.');
        }

        return $subDir;
    }
}
