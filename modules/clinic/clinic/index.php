<?php
require_once('../../auth/guard.php');
require_once 'classes/Page.php';

$pageController = new Page();
$currentPage = $pageController->getPage();

// header.php must come first: it opens <html>, <head> and <body>
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
    <div class="container">
        <?php $pageController->render(); ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
