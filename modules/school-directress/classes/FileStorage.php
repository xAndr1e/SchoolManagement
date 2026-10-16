
<?php
/**
 * FileStorage — local and central SDM file storage.
 *
 * Local references:
 *   reports/pdf/report_12.pdf
 *
 * Central SDM references:
 *   sdm:cf7b2ecdb7b988419572617985026b04.pdf
 *
 * Local files use resolve()/stream().
 * Central files use SdmFileApi and are streamed through the
 * existing controller after its session and authorization checks.
 */
class FileStorage
{
    const MAX_BYTES = 10485760; // 10 MB

    public static $allowed = [
        'pdf' => ['application/pdf'],
        'doc' => [
            'application/msword',
            'application/x-ole-storage',
            'application/octet-stream',
            'application/vnd.ms-office',
        ],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream',
        ],
        'xls' => [
            'application/vnd.ms-excel',
            'application/msword',
            'application/x-ole-storage',
            'application/octet-stream',
            'application/vnd.ms-office',
        ],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'application/octet-stream',
        ],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ];

    public static $mimeByExt = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
    ];

    // ---------------------------------------------------------
    // Locations
    // ---------------------------------------------------------

    public static function root()
    {
        return str_replace('\\', '/', dirname(__DIR__, 3));
    }

    public static function base()
    {
        if (defined('SMS_STORAGE_PATH') && SMS_STORAGE_PATH) {
            return rtrim(
                str_replace('\\', '/', SMS_STORAGE_PATH),
                '/'
            );
        }

        $env = getenv('SMS_STORAGE_PATH');

        if ($env) {
            return rtrim(str_replace('\\', '/', $env), '/');
        }

        return '/home/sdm.bcp-sms.com/storage';
    }

    public static function ensureDir($dir)
    {
        if (
            !is_dir($dir) &&
            !@mkdir($dir, 0775, true) &&
            !is_dir($dir)
        ) {
            return false;
        }

        return is_writable($dir);
    }

    private static function protectBase()
    {
        $base = self::base();

        if (!is_dir($base)) {
            return;
        }

        $ht = $base . '/.htaccess';

        if (!file_exists($ht)) {
            @file_put_contents(
                $ht,
                "Require all denied\n" .
                "<IfModule !mod_authz_core.c>\n" .
                "Order allow,deny\nDeny from all\n" .
                "</IfModule>\n"
            );
        }

        $idx = $base . '/index.html';

        if (!file_exists($idx)) {
            @file_put_contents($idx, '');
        }
    }

    /**
     * Resolve only local relative paths.
     * Remote sdm: references are handled by streamStored().
     */
    public static function resolve($relative)
    {
        $relative = (string) $relative;

        if (strpos($relative, 'sdm:') === 0) {
            return null;
        }

        $relative = ltrim(
            str_replace('\\', '/', $relative),
            '/'
        );

        if (
            $relative === '' ||
            strpos($relative, '..') !== false
        ) {
            return null;
        }

        $candidates = [
            self::base() . '/' . $relative,
        ];

        // Legacy uploads stored under the project root.
        if (strpos($relative, 'uploads/') === 0) {
            $candidates[] = self::root() . '/' . $relative;
        }

        $allowedRoots = array_filter([
            realpath(self::base()),
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
                $prefix = rtrim($root, DIRECTORY_SEPARATOR)
                    . DIRECTORY_SEPARATOR;

                if (strpos($real, $prefix) === 0) {
                    return $real;
                }
            }
        }

        return null;
    }

    // ---------------------------------------------------------
    // Upload validation and storage
    // ---------------------------------------------------------

    public static function hasUpload($file)
    {
        return is_array($file) &&
            (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);
    }

    public static function validateUpload($file)
    {
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

        if (!is_uploaded_file($file['tmp_name'])) {
            return 'Invalid upload.';
        }

        if ((int) $file['size'] > self::MAX_BYTES) {
            return 'The attachment is too large (maximum 10 MB).';
        }

        $ext = self::extensionOf($file['name']);

        if (!isset(self::$allowed[$ext])) {
            return 'This file type is not allowed. Use PDF, Word, Excel, JPG or PNG.';
        }

        $mime = self::detectMime($file['tmp_name']);

        if (
            $mime !== '' &&
            !in_array($mime, self::$allowed[$ext], true)
        ) {
            return 'The file content does not match its file type.';
        }

        return null;
    }

    public static function store($file, $subDir, $prefix)
    {
        $subDir = trim(str_replace('\\', '/', $subDir), '/');
        $dir = self::base() . '/' . $subDir;

        if (!self::ensureDir($dir)) {
            throw new RuntimeException(
                'Storage folder is missing or not writable: ' . $dir
            );
        }

        self::protectBase();

        $ext = self::extensionOf($file['name']);
        $safePrefix = preg_replace('/[^A-Za-z0-9_-]/', '', $prefix);
        $name = $safePrefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            throw new RuntimeException(
                'Could not move the uploaded file into storage.'
            );
        }

        @chmod($dir . '/' . $name, 0640);

        return $subDir . '/' . $name;
    }

    public static function storeBytes($bytes, $subDir, $fileName)
    {
        $subDir = trim(str_replace('\\', '/', $subDir), '/');
        $dir = self::base() . '/' . $subDir;

        if (!self::ensureDir($dir)) {
            throw new RuntimeException(
                'Storage folder is missing or not writable: ' . $dir
            );
        }

        self::protectBase();

        // Do not permit a caller to write outside the requested directory.
        $fileName = basename(str_replace('\\', '/', $fileName));

        if (
            $fileName === '' ||
            $fileName === '.' ||
            $fileName === '..'
        ) {
            throw new RuntimeException('Invalid storage filename.');
        }

        if (file_put_contents($dir . '/' . $fileName, $bytes) === false) {
            throw new RuntimeException('Could not write file to storage.');
        }

        @chmod($dir . '/' . $fileName, 0640);

        return $subDir . '/' . $fileName;
    }

    public static function delete($relative)
    {
        // Only delete local files here. Remote deletion should be an
        // explicit API operation with its own authorization checks.
        $abs = self::resolve($relative);

        if ($abs) {
            @unlink($abs);
        }
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public static function extensionOf($name)
    {
        // Support extensions from either local paths or sdm: references.
        $name = (string) $name;

        if (preg_match(
            '/^sdm:[a-f0-9]{32}\.([a-z0-9]+)$/i',
            $name,
            $matches
        )) {
            return strtolower($matches[1]);
        }

        return strtolower(pathinfo($name, PATHINFO_EXTENSION));
    }

    public static function mimeFor($name)
    {
        return self::$mimeByExt[self::extensionOf($name)]
            ?? 'application/octet-stream';
    }

    public static function cleanName($name)
    {
        $name = basename(str_replace('\\', '/', (string) $name));
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name);

        return function_exists('mb_substr')
            ? mb_substr($name, 0, 150)
            : substr($name, 0, 150);
    }

    private static function detectMime($path)
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

        return $mime ?: '';
    }

    // ---------------------------------------------------------
    // Central SDM API client
    // ---------------------------------------------------------

    private static function api(): SdmFileApi
    {
        static $client = null;

        if ($client === null) {
            $clientFile = __DIR__ . '/SdmFileApi.php';

            if (!is_file($clientFile)) {
                throw new RuntimeException(
                    'SdmFileApi.php is missing from the FileStorage directory.'
                );
            }

            require_once $clientFile;

            if (!class_exists('SdmFileApi')) {
                throw new RuntimeException(
                    'SdmFileApi class was not loaded.'
                );
            }

            $client = new SdmFileApi();
        }

        return $client;
    }

    /**
     * Stream either a central SDM reference or a local stored path.
     *
     * Call only after the controller has checked the user's session,
     * approval record, and permission to access that record.
     */
    public static function streamStored(
        $reference,
        $downloadName,
        $mime,
        $download = false
    ) {
        $reference = trim((string) $reference);

        if ($reference === '') {
            throw new RuntimeException('The file reference is empty.');
        }

        // Central reference format: sdm:<32-hex-id>.<extension>
        if (preg_match(
            '/^sdm:([a-f0-9]{32})\.([a-z0-9]+)$/i',
            $reference,
            $matches
        )) {
            $fileId = $matches[1];
            $tempPath = null;

            try {
                $result = self::api()->downloadToTemp($fileId);

                // Expected interface: ['path' => '/temporary/file.pdf']
                $tempPath = is_array($result)
                    ? ($result['path'] ?? null)
                    : null;

                if (
                    !$tempPath ||
                    !is_file($tempPath) ||
                    !is_readable($tempPath)
                ) {
                    throw new RuntimeException(
                        'Central storage did not return a readable temporary file.'
                    );
                }

                $actualMime = self::mimeFor($reference);

                self::streamLocalFile(
                    $tempPath,
                    $downloadName,
                    $actualMime !== 'application/octet-stream'
                        ? $actualMime
                        : $mime,
                    $download
                );
            } finally {
                // The temporary copy is removed after readfile() completes.
                if ($tempPath && is_file($tempPath)) {
                    @unlink($tempPath);
                }
            }

            exit;
        }

        // Existing local file path.
        $absolutePath = self::resolve($reference);

        if (!$absolutePath || !is_readable($absolutePath)) {
            throw new RuntimeException(
                'The requested local file could not be found or read.'
            );
        }

        self::streamLocalFile(
            $absolutePath,
            $downloadName,
            $mime,
            $download
        );

        exit;
    }

    private static function streamLocalFile(
        $path,
        $downloadName,
        $mime,
        $download = false
    ) {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('The file is not readable.');
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $safeName = preg_replace(
            '/[^A-Za-z0-9._ -]/',
            '_',
            basename((string) $downloadName)
        );

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header(
            'Content-Disposition: ' .
            ($download ? 'attachment' : 'inline') .
            '; filename="' . $safeName . '"'
        );
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Cache-Control: private, no-store');

        readfile($path);
    }

    // Keep the existing local-only streaming method for other callers.
    public static function stream(
        $absPath,
        $downloadName,
        $mime,
        $download = false
    ) {
        self::streamLocalFile(
            $absPath,
            $downloadName,
            $mime,
            $download
        );

        exit;
    }
}
