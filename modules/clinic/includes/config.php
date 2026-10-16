<?php
// CLINIC_BASE = URL path of the clinic module folder (e.g. /modules/clinic),
// computed from this file's location (includes/ -> parent folder).
if (!defined('CLINIC_BASE')) {
    $docRoot   = rtrim(str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $clinicDir = str_replace('\\', '/', (string) realpath(dirname(__DIR__)));
    $base = ($docRoot !== '' && strpos($clinicDir, $docRoot) === 0)
        ? substr($clinicDir, strlen($docRoot))
        : '';
    define('CLINIC_BASE', rtrim($base, '/'));
}

// SITE_BASE = site root (CLINIC_BASE without the /modules/clinic part).
if (!defined('SITE_BASE')) {
    define('SITE_BASE', rtrim(preg_replace('#/modules/clinic$#i', '', CLINIC_BASE), '/'));
}
