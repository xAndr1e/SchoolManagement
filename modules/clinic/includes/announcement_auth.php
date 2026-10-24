<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Sino ang pwedeng mag-manage ng announcements?
 * Default: kahit sinong naka-login (chine-check ang mga karaniwang session key).
 * Kapag alam mo na ang tamang key/role, palitan ang laman nito, halimbawa:
 *   return in_array($_SESSION['role'] ?? '', ['admin', 'nurse'], true);
 */
function announcement_can_manage(): bool {
    $keys = ['user_id', 'id', 'userid', 'uid', 'admin_id', 'staff_id', 'nurse_id',
             'username', 'user', 'email', 'role', 'logged_in', 'loggedin', 'is_logged_in'];
    foreach ($keys as $k) {
        if (!empty($_SESSION[$k])) return true;
    }
    return false;
}

function announcement_poster_name(): ?string {
    foreach (['full_name', 'fullname', 'name', 'username', 'email'] as $k) {
        if (!empty($_SESSION[$k]) && is_string($_SESSION[$k])) return $_SESSION[$k];
    }
    return null;
}