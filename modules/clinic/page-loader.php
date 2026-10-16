<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/classes/Page.php';

$pageController = new Page();

header('Content-Type: text/html; charset=utf-8');
header('X-Rendered-Page: ' . $pageController->getPage());

$pageController->render();
exit;