<?php
declare(strict_types=1);

/**
 * CentralFtpStorage
 *
 * Talks to the central FTP/FTPS account. It knows nothing about the database
 * or about who the user is: controllers do authentication, record-level
 * authorization and metadata. This class only moves bytes safely.
 *
 * Remote layout (relative to the configured base_dir):
 *   approvals/<32 hex>.<ext>
 *   reports/<32 hex>.<ext>
 *   concerns/<32 hex>.<ext>
 */

class CentralFtpException extends RuntimeException
{
    /** Message that is safe to show to end users. */
    private string $publicMessage;

    public function __construct(string $internalMessage, string $publicMessage = 'File storage is currently unavailable.', ?Throwable $previous = null)
    {
        parent::__construct($internalMessage, 0, $previous);
        $this->publicMessage = $publicMessage;
    }

    public function getPublicMessage(): string
    {
        return $this->publicMessage;
    }
}

class CentralFtpStorage
{
    /** module_type (DB value) => remote directory */
    public const MODULE_DIRS = [
        'approval' => 'approvals',
        'report'   => 'reports',
        'concern'  => 'concerns',
    ];

    /** Allowed extension => acceptable finfo MIME types. */
    private const MIME_MAP = [
        'pdf'  => ['application/pdf'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'doc'  => ['application/msword', 'application/CDFV2', 'application/x-cfb', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'xls'  => ['application/vnd.ms-excel', 'application/CDFV2', 'application/x-cfb', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
    ];

    private array $config;
    /** @var resource|\FTP\Connection|null */
    private $conn = null;

    public function __construct(array $config)
    {
        $required = ['host', 'port', 'username', 'password', 'base_dir', 'ssl', 'timeout', 'max_upload_bytes', 'allowed_extensions'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $config)) {
                throw new CentralFtpException("FTP config key missing: {$key}");
            }
        }
        $this->config = $config;
    }

    /**
     * Load config from (in order): $path argument, SDM_FTP_CONFIG env var,
     * then ftp_storage.php in the PARENT of the project root, the same place
     * database/db.php looks for db_config.php (outside the web root on production).
     */
    public static function fromConfigFile(?string $path = null): self
    {
        $path = $path ?: (getenv('SDM_FTP_CONFIG') ?: dirname(__DIR__, 4) . '/ftp_storage.php');
        if (!is_file($path)) {
            throw new CentralFtpException("FTP config file not found: {$path}");
        }
        $config = require $path;
        if (!is_array($config)) {
            throw new CentralFtpException('FTP config file must return an array.');
        }
        return new self($config);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    // ------------------------------------------------------------------
    // Connection
    // ------------------------------------------------------------------

    public function connect(): void
    {
        if ($this->conn !== null) {
            return;
        }
        if (!extension_loaded('ftp')) {
            throw new CentralFtpException('PHP ftp extension is not loaded.');
        }

        $host    = (string)$this->config['host'];
        $port    = (int)$this->config['port'];
        $timeout = (int)$this->config['timeout'];
        $useSsl  = (bool)$this->config['ssl'];

        if ($useSsl) {
            if (!function_exists('ftp_ssl_connect')) {
                throw new CentralFtpException('FTPS requested but ftp_ssl_connect() is unavailable (PHP built without OpenSSL).');
            }
            $conn = @ftp_ssl_connect($host, $port, $timeout);
        } else {
            if (empty($this->config['allow_plain_ftp'])) {
                throw new CentralFtpException('Plain FTP is disabled. Set ssl=true or allow_plain_ftp=true explicitly.');
            }
            $conn = @ftp_connect($host, $port, $timeout);
        }
        if ($conn === false) {
            throw new CentralFtpException("Cannot connect to FTP host {$host}:{$port} (ssl=" . ($useSsl ? '1' : '0') . ').');
        }

        if (!@ftp_login($conn, (string)$this->config['username'], (string)$this->config['password'])) {
            @ftp_close($conn);
            throw new CentralFtpException('FTP authentication failed for the configured account.');
        }

        @ftp_set_option($conn, FTP_TIMEOUT_SEC, $timeout);
        if (!@ftp_pasv($conn, true)) {
            @ftp_close($conn);
            throw new CentralFtpException('Could not enable FTP passive mode.');
        }

        $this->conn = $conn;
    }

    public function disconnect(): void
    {
        if ($this->conn !== null) {
            @ftp_close($this->conn);
            $this->conn = null;
        }
    }

    // ------------------------------------------------------------------
    // Public operations
    // ------------------------------------------------------------------

    /**
     * Validate a local file against extension, size and real MIME type.
     *
     * @return array{extension:string, mime_type:string, file_size:int}
     */
    public function validateFile(string $localPath, string $originalName): array
    {
        $public = 'This file cannot be uploaded.';

        if (!is_file($localPath) || !is_readable($localPath)) {
            throw new CentralFtpException('Upload source not readable.', $public);
        }

        $size = (int)filesize($localPath);
        if ($size <= 0) {
            throw new CentralFtpException('Empty upload.', 'The file is empty.');
        }
        if ($size > (int)$this->config['max_upload_bytes']) {
            $mb = round((int)$this->config['max_upload_bytes'] / 1048576, 1);
            throw new CentralFtpException("Upload too large ({$size} bytes).", "The file exceeds the {$mb} MB limit.");
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = array_map('strtolower', (array)$this->config['allowed_extensions']);
        if ($ext === '' || !in_array($ext, $allowed, true) || !isset(self::MIME_MAP[$ext])) {
            throw new CentralFtpException("Blocked extension: {$ext}", 'This file type is not allowed.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = (string)$finfo->file($localPath);
        if (!in_array($mime, self::MIME_MAP[$ext], true)) {
            throw new CentralFtpException("MIME mismatch: ext={$ext} detected={$mime}", 'The file content does not match its type.');
        }

        return ['extension' => $ext, 'mime_type' => $mime, 'file_size' => $size];
    }

    /**
     * Upload a local file. Returns data for the metadata row.
     *
     * @return array{stored_name:string, storage_path:string, mime_type:string, file_size:int, extension:string}
     */
    public function upload(string $localPath, string $originalName, string $moduleType): array
    {
        $dir  = $this->dirForModule($moduleType);
        $info = $this->validateFile($localPath, $originalName);

        $storedName  = bin2hex(random_bytes(16)) . '.' . $info['extension'];
        $storagePath = $dir . '/' . $storedName;

        $this->connect();
        $remote = $this->remotePath($storagePath);

        if (!@ftp_put($this->conn, $remote, $localPath, FTP_BINARY)) {
            // Best-effort cleanup of a partial file.
            @ftp_delete($this->conn, $remote);
            throw new CentralFtpException("ftp_put failed for {$storagePath}.", 'The file could not be saved to storage.');
        }

        // Verify the transfer where the server reports sizes (-1 = unsupported).
        $remoteSize = @ftp_size($this->conn, $remote);
        if ($remoteSize >= 0 && $remoteSize !== $info['file_size']) {
            @ftp_delete($this->conn, $remote);
            throw new CentralFtpException("Size mismatch after upload: local {$info['file_size']} remote {$remoteSize}.", 'The file could not be saved to storage.');
        }

        return [
            'stored_name'  => $storedName,
            'storage_path' => $storagePath,
            'mime_type'    => $info['mime_type'],
            'file_size'    => $info['file_size'],
            'extension'    => $info['extension'],
        ];
    }

    /**
     * Download to a temp stream (rewound, ready for fpassthru).
     * The caller must fclose() it. Nothing is sent to the browser here,
     * so a failure can still become a clean HTTP error.
     *
     * @return resource
     */
    public function fetchToTempStream(string $storagePath)
    {
        $this->assertValidStoragePath($storagePath);
        $this->connect();
        $remote = $this->remotePath($storagePath);

        $tmp = tmpfile();
        if ($tmp === false) {
            throw new CentralFtpException('Cannot create temp file for download.');
        }
        if (!@ftp_fget($this->conn, $tmp, $remote, FTP_BINARY)) {
            fclose($tmp);
            throw new CentralFtpException("ftp_fget failed for {$storagePath}.", 'The file is not available.');
        }
        rewind($tmp);
        return $tmp;
    }

    /**
     * Send a stored file to the browser with safe headers.
     * Call only after the controller has authorized the user.
     */
    public function streamDownload(string $storagePath, string $downloadName, string $mimeType, bool $inline = false): void
    {
        $stream = $this->fetchToTempStream($storagePath); // throws before any header is sent

        $safeName = self::sanitizeDownloadName($downloadName);
        $ascii    = preg_replace('/[^A-Za-z0-9._-]/', '_', $safeName) ?: 'download';
        $size     = (int)(fstat($stream)['size'] ?? 0);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: ' . ($mimeType ?: 'application/octet-stream'));
        header('Content-Length: ' . $size);
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment')
            . '; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($safeName));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');

        fpassthru($stream);
        fclose($stream);
    }

    /** Delete a stored file. Returns true if gone (including already missing). */
    public function delete(string $storagePath): bool
    {
        $this->assertValidStoragePath($storagePath);
        $this->connect();
        $remote = $this->remotePath($storagePath);

        if (@ftp_delete($this->conn, $remote)) {
            return true;
        }
        // Treat "already gone" as success; anything else is a failure.
        if (@ftp_size($this->conn, $remote) === -1 && !$this->existsInListing($storagePath)) {
            return true;
        }
        throw new CentralFtpException("ftp_delete failed for {$storagePath}.", 'The file could not be removed from storage.');
    }

    public function exists(string $storagePath): bool
    {
        $this->assertValidStoragePath($storagePath);
        $this->connect();
        return @ftp_size($this->conn, $this->remotePath($storagePath)) >= 0 || $this->existsInListing($storagePath);
    }

    /** Create approvals/, reports/, concerns/ under base_dir if missing. */
    public function ensureDirectories(): array
    {
        $this->connect();
        $result = [];
        $this->mkdirIfMissing(rtrim((string)$this->config['base_dir'], '/'));
        foreach (self::MODULE_DIRS as $dir) {
            $result[$dir] = $this->mkdirIfMissing($this->remotePath($dir));
        }
        return $result;
    }

    public static function isValidModuleType(string $moduleType): bool
    {
        return isset(self::MODULE_DIRS[$moduleType]);
    }

    public static function sanitizeDownloadName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F"]/u', '', $name) ?? '';
        $name = trim($name, ". \t");
        return $name === '' ? 'download' : mb_substr($name, 0, 150);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function dirForModule(string $moduleType): string
    {
        if (!self::isValidModuleType($moduleType)) {
            throw new CentralFtpException("Invalid module type: {$moduleType}", 'Invalid request.');
        }
        return self::MODULE_DIRS[$moduleType];
    }

    /** Only paths we generate ourselves are ever allowed on the wire. */
    private function assertValidStoragePath(string $storagePath): void
    {
        $dirs = implode('|', array_values(self::MODULE_DIRS));
        if (!preg_match('#^(' . $dirs . ')/[a-f0-9]{32}\.[a-z0-9]{2,5}$#', $storagePath)) {
            throw new CentralFtpException('Rejected storage path: ' . substr($storagePath, 0, 80), 'Invalid file reference.');
        }
    }

    private function remotePath(string $relative): string
    {
        return rtrim((string)$this->config['base_dir'], '/') . '/' . ltrim($relative, '/');
    }

    private function existsInListing(string $storagePath): bool
    {
        $list = @ftp_nlist($this->conn, $this->remotePath($storagePath));
        return is_array($list) && count($list) > 0;
    }

    private function mkdirIfMissing(string $remoteDir): string
    {
        $current = @ftp_pwd($this->conn);
        if (@ftp_chdir($this->conn, $remoteDir)) {
            @ftp_chdir($this->conn, $current !== false ? $current : '/');
            return 'exists';
        }
        if (@ftp_mkdir($this->conn, $remoteDir) !== false) {
            return 'created';
        }
        throw new CentralFtpException("Cannot create remote directory {$remoteDir}.");
    }
}