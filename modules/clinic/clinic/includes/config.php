<?php
/**
 * Auto-detect URL base paths so the module works on any host/folder
 * (e.g. scm.bcp-sms.com/modules/clinic OR localhost/SMS/modules/clinic).
 */
if (!defined('CLINIC_BASE')) {
    // e.g. "/modules/clinic" or "/SMS/modules/clinic"
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    define('CLINIC_BASE', rtrim($scriptDir, '/'));
    // e.g. "" or "/SMS"  (site root, where /auth, /modules etc. live)
    define('SITE_BASE', preg_replace('#/modules/[^/]+$#', '', CLINIC_BASE));
}
