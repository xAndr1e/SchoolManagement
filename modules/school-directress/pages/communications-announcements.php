<?php
include_once __DIR__ . '/../../../auth/session.php';
include_once __DIR__ . '/../classes/Announcement.php';

// Keep in step with ANN_ARCHIVE_ROLES in AnnouncementArchiveController.php.
// The controller is the real gate; this only decides what the page shows.
$annArchiveRoles = ['School Directress'];
$annCanArchive   = in_array((string) ($_SESSION['role_name'] ?? ''), $annArchiveRoles, true);

if ($annCanArchive && empty($_SESSION['ann_csrf'])) {
    $_SESSION['ann_csrf'] = bin2hex(random_bytes(32));
}

$announcement = new Announcement();
$announcements = $announcement->getAnnouncement();

$annImageBase = '/SMS/modules/school-directress/uploads/announcements/';
?>

<div class="module-header">
    <h1>Communications & Announcements</h1>
    <p>Manage and view announcements for the school community.</p>
</div>

<div class="module-content" id="annRoot"
     data-can-archive="<?= $annCanArchive ? '1' : '0' ?>"
     data-csrf="<?= htmlspecialchars((string) ($_SESSION['ann_csrf'] ?? '')) ?>"
     data-image-base="<?= htmlspecialchars($annImageBase) ?>">

    <?php if ($annCanArchive): ?>
    <div class="ann-tabs" role="tablist" aria-label="Announcement views">
        <button type="button" class="ann-tab is-active" role="tab" id="annTabActive" aria-selected="true"
                aria-controls="annActivePanel" data-ann-action="tab" data-tab="active">Active</button>
        <button type="button" class="ann-tab" role="tab" id="annTabArchived" aria-selected="false"
                aria-controls="annArchivedPanel" data-ann-action="tab" data-tab="archived" tabindex="-1">Archived</button>
    </div>
    <?php endif; ?>

    <div class="announcements-list" id="annActivePanel" role="tabpanel" aria-labelledby="annTabActive">
        <div class="comm-list-header">
            <h2>Announcements</h2>
            <button type="button" id="comm-open-modal" class="comm-add-btn">+ Post Announcement</button>
        </div>

        <?php if (empty($announcements)): ?>
            <p class="muted">No announcements have been posted yet.</p>
        <?php else: ?>
            <?php foreach ($announcements as $ann):
                $annTitle = trim((string) ($ann['title'] ?? ''));
                $annDate  = date('F j, Y', strtotime($ann['publish_date']));
                $annLabel = $annTitle !== '' ? $annTitle : 'The announcement for ' . $annDate;
            ?>
                <div class="announcement-card">
                    <?php if (!empty($ann['image_file'])): ?>
                        <div class="announcement-image">
                            <img src="<?= htmlspecialchars($annImageBase . $ann['image_file']) ?>" alt="<?= htmlspecialchars($annTitle) ?>">
                        </div>
                    <?php endif; ?>
                    <div class="announcement-content">
                        <div class="announcement-header">
                            <span class="announcement-meta">
                                <span class="meta-date">&#128197; <?= $annDate ?></span>
                            </span>
                            <?php if ($annCanArchive): ?>
                            <button type="button" class="ann-card-btn"
                                    data-ann-action="archive"
                                    data-id="<?= (int) $ann['announcement_id'] ?>"
                                    data-label="<?= htmlspecialchars($annLabel) ?>">Archive</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($annCanArchive): ?>
    <!-- Archived announcements (loaded by AJAX when the Archived tab is opened) -->
    <div class="ann-archive-panel" id="annArchivedPanel" role="tabpanel" aria-labelledby="annTabArchived" hidden>
        <div class="comm-list-header ann-archive-header">
            <h2>Archived announcements</h2>
            <label class="ann-search">
                <span class="ann-sr-only">Search archived announcements</span>
                <input type="search" id="annSearch" placeholder="Search title, message or author…" autocomplete="off">
            </label>
        </div>

        <p class="muted" id="annArchiveState" role="status" aria-live="polite">Loading archived announcements…</p>
        <div id="annArchiveList"></div>

        <div class="ann-footer">
            <span class="ann-showing" id="annShowing"></span>
            <div class="ann-page-nav" id="annPageNav"></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Post Announcement Modal -->
    <div class="comm-modal-overlay" id="comm-modal-overlay">
        <div class="comm-modal" role="dialog" aria-modal="true" aria-labelledby="comm-modal-title">
            <div class="comm-modal-header">
                <h3 id="comm-modal-title">Post Announcement</h3>
                <button type="button" class="comm-modal-close" id="comm-modal-close" aria-label="Close">&times;</button>
            </div>

            <div class="comm-modal-body">
                <div class="comm-form">
                    <form id="announcement-form" method="POST" action="controllers/AnnouncementController.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="ann-image">Image</label>
                            <input class="ann-image" id="ann-image" name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp">
                        </div>
                        <div class="form-row">
                            <div class="form-group small">
                                <label for="ann-date">Publish Date</label>
                                <input id="ann-date" name="publish_date" type="date">
                            </div>
                            <div class="form-group small">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn-save">Post Announcement</button>
                                    <button type="reset" class="btn-cancel">Clear</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($annCanArchive): ?>
    <!-- Confirm archive / restore -->
    <div class="comm-modal-overlay" id="annConfirmOverlay">
        <div class="comm-modal ann-confirm-modal" role="alertdialog" aria-modal="true"
             aria-labelledby="annConfirmTitle" aria-describedby="annConfirmText">
            <div class="comm-modal-header">
                <h3 id="annConfirmTitle">Archive announcement</h3>
                <button type="button" class="comm-modal-close" data-ann-action="confirm-cancel" aria-label="Close">&times;</button>
            </div>
            <div class="comm-modal-body">
                <p class="ann-confirm-text" id="annConfirmText"></p>
                <p class="ann-form-message" id="annConfirmMsg" role="alert"></p>
                <div class="ann-confirm-actions">
                    <button type="button" class="btn-cancel ann-cancel" data-ann-action="confirm-cancel">Cancel</button>
                    <button type="button" class="btn-save" id="annConfirmBtn" data-ann-action="confirm-run">Confirm archive</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="ann-toast" id="annToast" role="status" aria-live="polite" hidden></div>
</div>