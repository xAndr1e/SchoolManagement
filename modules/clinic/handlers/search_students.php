<?php
// DUMMY VERSION: walang DB.
// GET ?search=...&page=N -> {students, currentPage, totalPages, start, end, totalStudents}

// Ibalik ito kapag may DB na (at tanggalin ang fallback sa ibaba):
// require_once __DIR__ . '/_bootstrap.php';
if (!function_exists('jsonOut')) {
    function jsonOut($data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
require_once __DIR__ . '/../classes/Student.php';

try {
    $search = trim((string) ($_GET['search'] ?? ''));
    $page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;

    $all = (new Student())->getAllStudents();

    if ($search !== '') {
        $q = strtolower($search);
        $all = array_values(array_filter($all, function ($s) use ($q) {
            foreach (['student_number', 'first_name', 'last_name', 'course', 'email'] as $k) {
                if (strpos(strtolower((string) ($s[$k] ?? '')), $q) !== false) {
                    return true;
                }
            }
            return false;
        }));
    }

    $perPage    = 10;
    $total      = count($all);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = max(1, min($page, $totalPages));
    $offset     = ($page - 1) * $perPage;

    jsonOut([
        'students'      => array_slice($all, $offset, $perPage),
        'currentPage'   => $page,
        'totalPages'    => $totalPages,
        'start'         => $total > 0 ? $offset + 1 : 0,
        'end'           => min($offset + $perPage, $total),
        'totalStudents' => $total,
    ]);
} catch (Throwable $e) {
    error_log('search_students: ' . $e->getMessage());
    jsonOut(['error' => 'Could not search students.'], 500);
}