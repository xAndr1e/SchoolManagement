<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Clinic CSS -->
    <link rel="stylesheet" href="<?= CLINIC_BASE ?>/css/styles.css">

    <!-- Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <title><?= htmlspecialchars($pageTitle ?? 'School Management System', ENT_QUOTES, 'UTF-8') ?></title>

    <script>
        window.SITE_BASE = <?= json_encode(SITE_BASE) ?>;
        window.CLINIC_BASE = <?= json_encode(CLINIC_BASE) ?>;
    </script>
    <script type="module" src="<?= CLINIC_BASE ?>/js/script.js"></script>
</head>
<body>
<header>
    <div class="hamburger" aria-label="Toggle navigation">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="realtime" id="realtimeClock" aria-live="polite">--:--</div>
</header>
