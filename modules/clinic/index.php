
<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/classes/Page.php';

$pageController = new Page();
$currentPage = $pageController->getPage();
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<main class="main-content">
    <div class="container">
        <?php $pageController->render(); ?>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
