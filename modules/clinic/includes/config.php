<?php
if (!defined('CLINIC_BASE')) {
    $docRoot   = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $clinicDir = str_replace('\\', '/', realpath(__DIR__));
    $base = ($docRoot !== '' && strpos($clinicDir, $docRoot) === 0)
        ? substr($clinicDir, strlen($docRoot))
        : '';
    define('CLINIC_BASE', rtrim($base, '/'));
}

if (!defined('SITE_BASE')) {
    define('SITE_BASE', rtrim(preg_replace('#/modules/clinic$#i', '', CLINIC_BASE), '/'));
}