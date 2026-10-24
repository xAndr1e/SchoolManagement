<?php
require_once __DIR__ . '/announcement_auth.php';
if (!class_exists('Announcement')) {
    require_once __DIR__ . '/../classes/Announcement.php'; // ayusin ang path kung iba
}

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$csrf      = $_SESSION['csrf'];
$canManage = announcement_can_manage();

$annModel      = new Announcement();
$announcements = $annModel->getActive();

$annStyles = [
    'Urgent'          => ['bg' => '#fff5f5', 'border' => '#e53e3e', 'text' => '#9b2c2c'],
    'Health Advisory' => ['bg' => '#f0fff4', 'border' => '#38a169', 'text' => '#276749'],
    'Schedule'        => ['bg' => '#ebf8ff', 'border' => '#3182ce', 'text' => '#2c5282'],
    'General'         => ['bg' => '#fffff0', 'border' => '#d69e2e', 'text' => '#744210'],
];
?>

<style>
    .ann-wrap        { margin-bottom:20px; position:relative; z-index:5; }
    .ann-top         { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; gap:10px; flex-wrap:wrap; }
    .ann-top h2      { margin:0; font-size:18px; }
    .ann-add-btn     { background:#3182ce; color:#fff; border:none; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600;
                       cursor:pointer; position:relative; z-index:10; pointer-events:auto; }
    .ann-add-btn:hover { background:#2b6cb0; }

    .ann-form        { display:none; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:14px; }
    .ann-form.open   { display:block; }
    .ann-form label  { display:block; font-size:13px; font-weight:600; margin:10px 0 4px; color:#2d3748; }
    .ann-form input[type=text], .ann-form input[type=date], .ann-form select, .ann-form textarea {
        width:100%; padding:8px 10px; border:1px solid #cbd5e0; border-radius:8px; font-size:14px; box-sizing:border-box; font-family:inherit; }
    .ann-form textarea { min-height:90px; resize:vertical; }
    .ann-row         { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .ann-check       { display:flex !important; align-items:center; gap:6px; margin-top:12px; font-size:13px; }
    .ann-actions     { display:flex; gap:8px; margin-top:14px; }
    .ann-save        { background:#38a169; color:#fff; border:none; padding:8px 16px; border-radius:8px; font-weight:600; cursor:pointer; }
    .ann-cancel      { background:#edf2f7; color:#2d3748; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; }
    .ann-msg-box     { margin-top:10px; font-size:13px; }
    .ann-msg-box.err { color:#c53030; }

    .ann-card        { padding:14px 16px; border-radius:12px; margin-bottom:12px; border:1px solid; border-left-width:6px; box-shadow:0 2px 8px rgba(0,0,0,.07); }
    .ann-card.pinned { box-shadow:0 4px 14px rgba(0,0,0,.14); }
    .ann-head        { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .ann-badge       { color:#fff; font-size:12px; font-weight:700; padding:3px 11px; border-radius:20px; }
    .ann-pin         { font-size:12px; font-weight:700; }
    .ann-tools       { margin-left:auto; display:flex; gap:6px; }
    .ann-tools button{ background:rgba(255,255,255,.8); border:1px solid rgba(0,0,0,.15); border-radius:6px; font-size:12px; padding:3px 9px; cursor:pointer; }
    .ann-tools button:hover { background:#fff; }
    .ann-title       { margin:8px 0 4px; font-size:16px; font-weight:700; }
    .ann-msg         { margin:0 0 8px; font-size:14px; line-height:1.5; color:#2d3748; }
    .ann-meta        { font-size:12px; color:#718096; }
    .ann-none        { color:#a0aec0; font-size:14px; padding:6px 0; }
</style>

<div class="ann-wrap" id="annWrap"
     data-endpoint="<?= htmlspecialchars('handlers/announcement_action.php') ?>"
     data-csrf="<?= htmlspecialchars($csrf) ?>">

    <div class="ann-top">
        <h2>📢 Announcements</h2>
        <?php if ($canManage): ?>
            <button type="button" class="ann-add-btn" id="annToggle"
                    onclick="document.getElementById('annForm').classList.toggle('open');">
                + New announcement
            </button>
        <?php endif; ?>
    </div>

    <?php if ($canManage): ?>
    <form class="ann-form" id="annForm" autocomplete="off" onsubmit="return false;">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

        <label for="annTitle">Title</label>
        <input type="text" id="annTitle" name="title" maxlength="150" required>

        <label for="annMessage">Message</label>
        <textarea id="annMessage" name="message" required></textarea>

        <div class="ann-row">
            <div>
                <label for="annCategory">Category</label>
                <select id="annCategory" name="category">
                    <?php foreach (Announcement::CATEGORIES as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="annExpires">Expires on (optional)</label>
                <input type="date" id="annExpires" name="expires_at" min="<?= date('Y-m-d') ?>">
            </div>
        </div>

        <label class="ann-check"><input type="checkbox" name="is_pinned" value="1"> 📌 Pin to top</label>

        <div class="ann-actions">
            <button type="submit" class="ann-save">Post announcement</button>
            <button type="button" class="ann-cancel" id="annCancel"
                    onclick="var f=document.getElementById('annForm'); f.classList.remove('open'); f.reset(); document.getElementById('annFormMsg').textContent='';">
                Cancel
            </button>
        </div>
        <div class="ann-msg-box" id="annFormMsg"></div>
    </form>
    <?php endif; ?>

    <?php if (empty($announcements)): ?>
        <div class="ann-none">No active announcements.</div>
    <?php endif; ?>

    <?php foreach ($announcements as $a):
        $s = $annStyles[$a['category']] ?? $annStyles['General'];
    ?>
        <div class="ann-card <?= !empty($a['is_pinned']) ? 'pinned' : '' ?>"
             style="background:<?= $s['bg'] ?>; border-color:<?= $s['border'] ?>; border-left-color:<?= $s['border'] ?>;">
            <div class="ann-head">
                <span class="ann-badge" style="background:<?= $s['border'] ?>;"><?= htmlspecialchars($a['category']) ?></span>
                <?php if (!empty($a['is_pinned'])): ?><span class="ann-pin" style="color:<?= $s['text'] ?>;">📌 Pinned</span><?php endif; ?>

                <?php if ($canManage): ?>
                <span class="ann-tools">
                    <button type="button" data-ann-act="pin" data-id="<?= (int) $a['announcement_id'] ?>">
                        <?= !empty($a['is_pinned']) ? 'Unpin' : 'Pin' ?>
                    </button>
                    <button type="button" data-ann-act="archive" data-id="<?= (int) $a['announcement_id'] ?>">Archive</button>
                </span>
                <?php endif; ?>
            </div>
            <p class="ann-title" style="color:<?= $s['text'] ?>;"><?= htmlspecialchars($a['title']) ?></p>
            <p class="ann-msg"><?= nl2br(htmlspecialchars($a['message'])) ?></p>
            <span class="ann-meta">
                <?= date('M d, Y', strtotime($a['created_at'])) ?>
                <?php if (!empty($a['posted_by'])): ?> • <?= htmlspecialchars($a['posted_by']) ?><?php endif; ?>
                <?php if (!empty($a['expires_at'])): ?> • Until <?= date('M d, Y', strtotime($a['expires_at'])) ?><?php endif; ?>
            </span>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($canManage): ?>
<script>
(function () {
    // Para hindi madoble ang listener kung dalawang beses tumakbo ang script
    if (window.__annBound) return;
    window.__annBound = true;

    function cfg() {
        const w = document.getElementById('annWrap');
        if (!w) return null;
        const base = (window.CLINIC_BASE || '.').replace(/\/$/, '');
        const ep = w.dataset.endpoint;
        return { endpoint: base + '/' + ep, csrf: w.dataset.csrf };
    }

    async function send(data) {
        const c = cfg();
        const res = await fetch(c.endpoint, { method: 'POST', body: data });
        let json;
        try { json = await res.json(); }
        catch (e) { throw new Error('Server error (' + res.status + '). Check endpoint path: ' + c.endpoint); }
        if (!res.ok || !json.success) throw new Error(json.message || 'Failed');
        return json;
    }

    // Post new announcement
    document.addEventListener('submit', async function (e) {
        const form = e.target;
        if (!form || form.id !== 'annForm') return;
        e.preventDefault();
        const msgBox = document.getElementById('annFormMsg');
        msgBox.className = 'ann-msg-box';
        msgBox.textContent = 'Saving…';
        try {
            await send(new FormData(form));
            window.location.reload();
        } catch (err) {
            msgBox.className = 'ann-msg-box err';
            msgBox.textContent = err.message;
        }
    });

    // Pin / Archive
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest ? e.target.closest('[data-ann-act]') : null;
        if (!btn) return;
        const act = btn.dataset.annAct;
        if (act === 'archive' && !confirm('Archive this announcement?')) return;
        const fd = new FormData();
        fd.append('action', act);
        fd.append('id', btn.dataset.id);
        fd.append('csrf', cfg().csrf);
        try {
            await send(fd);
            window.location.reload();
        } catch (err) {
            alert(err.message);
        }
    });
})();
</script>
<?php endif; ?>