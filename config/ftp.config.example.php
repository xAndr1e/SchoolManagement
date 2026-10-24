<?php
/**
 * Copy to config/ftp_storage.php (gitignored) or, on production, to a folder
 * ABOVE public_html and point the SDM_FTP_CONFIG env var / loader at it.
 * Never commit the real file.
 */
return [
        'host'     => 'ftp.example.com',
        'port'     => 21,
        'username' => 'sdmbcpsm_storage',
        'password' => 'ChangeMe123!',
        'base_dir' => '/storage',
        'ssl'      => true,

    // Path as the FTP account sees it (often "/" or "/central-storage"
    // because the account is chrooted). Do NOT use the physical server path.
    'base_dir' => '/storage',

    // Explicit FTPS (AUTH TLS) on port 21. Leave true; the class fails closed
    // if TLS is unavailable.
    'allow_plain_ftp' => false, // only ever set true for a local XAMPP test server

    'timeout'          => 20,               // seconds
    'max_upload_bytes' => 10 * 1024 * 1024, // 10 MB
    'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
];