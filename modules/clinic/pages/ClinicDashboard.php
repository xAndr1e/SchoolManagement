<?php
require_once __DIR__ . '/../classes/ClinicDashboard.php';

$dashboard    = new ClinicDashboard();
$patientCount = $dashboard->getPatientCount();
$medSum       = $dashboard->getMedicationSummary();
$alerts       = $dashboard->getMedicationAlerts(50); // kinuha lahat (hanggang 50) para kumpleto ang alert list

// ---- Medication chart data (PHP side) ----
$medProblems = $medSum['low'] + $medSum['out_of_stock'] + $medSum['expiring'] + $medSum['expired'];
$medOk       = max(0, $medSum['total'] - $medProblems);
$stockHealth = $medSum['total'] > 0 ? round(($medOk / $medSum['total']) * 100) : null;

$medDonut = [
    ['label' => 'Healthy',      'value' => (int) $medOk,                  'color' => '#38b26d'],
    ['label' => 'Low',          'value' => (int) $medSum['low'],          'color' => '#f6ad55'],
    ['label' => 'Out of stock', 'value' => (int) $medSum['out_of_stock'], 'color' => '#e53e3e'],
    ['label' => 'Expiring',     'value' => (int) $medSum['expiring'],     'color' => '#ecc94b'],
    ['label' => 'Expired',      'value' => (int) $medSum['expired'],      'color' => '#9b2c2c'],
];

// Helper: "Expired 3 days ago" / "Expires in 12 days" (only shown when within 90 days)
$expiryNote = function ($date) {
    if (!$date) return '';
    $days = (int) floor((strtotime($date) - strtotime('today')) / 86400);
    if ($days < 0)   return 'Expired ' . abs($days) . ' day' . (abs($days) === 1 ? '' : 's') . ' ago';
    if ($days === 0) return 'Expires today';
    if ($days <= 90) return 'Expires in ' . $days . ' day' . ($days === 1 ? '' : 's');
    return '';
};

// Prepare alert rows once (used by banner, bar chart AND table)
$alertRows = [];
foreach ($alerts as $row) {
    [$label, $class] = Medication::getStatus($row);
    $color = '#a0aec0';
    $sev   = 0;
    if (stripos($class, 'danger') !== false)       { $color = '#e53e3e'; $sev = 2; }
    elseif (stripos($class, 'warn') !== false)     { $color = '#f6ad55'; $sev = 1; }
    elseif (stripos($class, 'success') !== false)  { $color = '#38b26d'; $sev = 0; }
    $alertRows[] = ['row' => $row, 'label' => $label, 'class' => $class, 'color' => $color, 'sev' => $sev];
}
// Pinaka-urgent muna (expired / out of stock), tapos pinakamababang quantity
usort($alertRows, function ($a, $b) {
    if ($a['sev'] !== $b['sev']) return $b['sev'] <=> $a['sev'];
    return (int) $a['row']['quantity'] <=> (int) $b['row']['quantity'];
});

$topRows  = array_slice($alertRows, 0, 6);   // para sa chart at table
$alertMax = 1;
foreach ($topRows as $a) { $alertMax = max($alertMax, (int) $a['row']['quantity']); }

// ---- Alert banner data ----
$cExpired  = (int) $medSum['expired'];
$cOut      = (int) $medSum['out_of_stock'];
$cLow      = (int) $medSum['low'];
$cExpiring = (int) $medSum['expiring'];
$hasCritical = ($cExpired + $cOut) > 0;
$hasAlert    = ($cExpired + $cOut + $cLow + $cExpiring) > 0;

$alertParts = [];
if ($cExpired)  $alertParts[] = $cExpired  . ' expired';
if ($cOut)      $alertParts[] = $cOut      . ' out of stock';
if ($cLow)      $alertParts[] = $cLow      . ' low stock';
if ($cExpiring) $alertParts[] = $cExpiring . ' expiring soon';
$alertSignature = "e{$cExpired}-o{$cOut}-l{$cLow}-x{$cExpiring}";
?>

<style>
    /* ===== Medication alert banner ===== */
    .mal            { border-radius:12px; padding:14px 18px; margin-bottom:20px; border:1px solid; border-left-width:6px; }
    .mal-critical   { background:#fff5f5; border-color:#feb2b2; border-left-color:#e53e3e; }
    .mal-warning    { background:#fffaf0; border-color:#fbd38d; border-left-color:#ed8936; }
    .mal-top        { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
    .mal-icon       { width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:22px; color:#fff; flex:0 0 auto; }
    .mal-critical .mal-icon { background:#e53e3e; animation:malPulse 1.8s infinite; }
    .mal-warning  .mal-icon { background:#ed8936; }
    .mal-text       { flex:1; min-width:220px; }
    .mal-title      { margin:0; font-size:16px; font-weight:700; }
    .mal-critical .mal-title { color:#9b2c2c; }
    .mal-warning  .mal-title { color:#9c4221; }
    .mal-sub        { margin:2px 0 0; font-size:13px; color:#4a5568; }
    .mal-chips      { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
    .mal-chip       { font-size:12px; font-weight:600; padding:3px 10px; border-radius:20px; color:#fff; }
    .mal-actions    { display:flex; align-items:center; gap:10px; }
    .mal-btn        { text-decoration:none; font-size:13px; font-weight:600; padding:8px 14px; border-radius:8px; color:#fff; white-space:nowrap; }
    .mal-critical .mal-btn { background:#e53e3e; }
    .mal-warning  .mal-btn { background:#ed8936; }
    .mal-close      { background:none; border:none; font-size:22px; line-height:1; cursor:pointer; color:#718096; padding:2px 6px; }
    .mal-close:hover{ color:#2d3748; }
    .mal details    { margin-top:10px; }
    .mal summary    { cursor:pointer; font-size:13px; font-weight:600; color:#4a5568; }
    .mal-list       { list-style:none; margin:10px 0 0; padding:0; max-height:260px; overflow-y:auto; }
    .mal-list li    { display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:8px 10px; background:#fff; border:1px solid #edf2f7; border-radius:8px; margin-bottom:6px; font-size:13px; }
    .mal-dot        { width:10px; height:10px; border-radius:50%; flex:0 0 auto; }
    .mal-name       { font-weight:600; color:#2d3748; flex:1; min-width:140px; }
    .mal-meta       { color:#718096; }

    /* ===== Toast popup ===== */
    .mal-toast      { position:fixed; top:16px; right:16px; z-index:9999; max-width:340px; background:#fff; border-radius:12px; padding:14px 16px;
                      box-shadow:0 10px 30px rgba(0,0,0,.18); border-left:6px solid #ed8936; cursor:pointer; animation:malSlide .4s ease-out; }
    .mal-toast.crit { border-left-color:#e53e3e; }
    .mal-toast b    { display:block; font-size:14px; color:#2d3748; margin-bottom:3px; }
    .mal-toast span { font-size:13px; color:#4a5568; }
    .mal-toast.hide { opacity:0; transform:translateX(30px); transition:all .4s; }

    @keyframes malPulse { 0% { box-shadow:0 0 0 0 rgba(229,62,62,.55); } 70% { box-shadow:0 0 0 14px rgba(229,62,62,0); } 100% { box-shadow:0 0 0 0 rgba(229,62,62,0); } }
    @keyframes malSlide { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }

    /* ===== Chart styles (prefixed with "ch-" so they won't clash with your CSS) ===== */
    .ch-grid   { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:18px; margin-top:12px; }
    .ch-wide   { grid-column:1 / -1; }
    .ch-card   { background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:16px 18px; box-shadow:0 1px 3px rgba(0,0,0,.04); }
    .ch-card h3{ margin:0 0 2px; font-size:15px; color:#343a40; }
    .ch-card p { margin:0 0 12px; font-size:12px; color:#868e96; }
    .ch-empty  { padding:30px 0; text-align:center; color:#adb5bd; font-size:13px; }

    /* KPI strip */
    .ch-kpis   { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px; }
    .ch-kpi    { border-radius:12px; padding:14px 16px; color:#fff; }
    .ch-kpi strong { display:block; font-size:28px; line-height:1.1; }
    .ch-kpi span   { font-size:13px; opacity:.95; }
    .ch-kpi small  { display:block; margin-top:4px; font-size:12px; opacity:.85; }
    .ch-k1 { background:linear-gradient(135deg,#667eea,#764ba2); }
    .ch-k2 { background:linear-gradient(135deg,#38b2ac,#2c7a7b); }
    .ch-k3 { background:linear-gradient(135deg,#f6ad55,#dd6b20); }
    .ch-k4 { background:linear-gradient(135deg,#48bb78,#2f855a); }

    /* Donut */
    .ch-donut-wrap { display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
    .ch-donut-wrap svg { width:150px; height:150px; flex:0 0 auto; }
    .ch-legend { flex:1; min-width:130px; }
    .ch-legend div { display:flex; align-items:center; justify-content:space-between; font-size:13px; color:#495057; padding:3px 0; }
    .ch-legend i   { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:8px; }
    .ch-legend b   { margin-left:10px; }

    /* Horizontal bars */
    .ch-hrow   { margin-bottom:11px; }
    .ch-hhead  { display:flex; justify-content:space-between; font-size:13px; color:#495057; margin-bottom:4px; gap:8px; }
    .ch-hhead span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .ch-htrack { background:#edf2f7; border-radius:6px; height:10px; overflow:hidden; }
    .ch-hfill  { height:10px; border-radius:6px; transform-origin:left; animation:chGrowX .8s ease-out; }

    /* Vertical bars */
    .ch-vbars  { display:flex; align-items:flex-end; gap:10px; height:170px; padding-top:6px; }
    .ch-vcol   { flex:1; height:100%; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; }
    .ch-vval   { font-size:12px; font-weight:600; color:#495057; margin-bottom:3px; }
    .ch-vbar   { width:100%; max-width:44px; border-radius:6px 6px 0 0; transform-origin:bottom; animation:chGrowY .8s ease-out; min-height:3px; }
    .ch-vlbl   { font-size:11px; color:#868e96; margin-top:6px; text-align:center; line-height:1.2; }

    /* Line / area */
    .ch-line svg { width:100%; height:auto; display:block; }

    @keyframes chGrowX { from { transform:scaleX(0); } to { transform:scaleX(1); } }
    @keyframes chGrowY { from { transform:scaleY(0); } to { transform:scaleY(1); } }
</style>

<div class="module-header">
    <h1>Clinic Dashboard Overview</h1>
</div>

<div class="module-content">

    <!-- ===================== MEDICATION ALERT BANNER (pinaka-taas) ===================== -->
    <?php if ($hasAlert): ?>
    <div class="mal <?= $hasCritical ? 'mal-critical' : 'mal-warning' ?>" id="medAlertBanner" role="alert">
        <div class="mal-top">
            <div class="mal-icon"><?= $hasCritical ? '🚨' : '⚠️' ?></div>
            <div class="mal-text">
                <p class="mal-title">
                    <?= $hasCritical ? 'Medication alert — immediate action needed' : 'Medication warning — please review stock' ?>
                </p>
                <p class="mal-sub">
                    <?= htmlspecialchars(implode(' • ', $alertParts)) ?>
                    <?= $hasCritical ? ' — expired or out-of-stock medicines should not be dispensed / need restocking.' : '' ?>
                </p>
                <div class="mal-chips">
                    <?php if ($cExpired):  ?><span class="mal-chip" style="background:#9b2c2c;"><?= $cExpired ?> Expired</span><?php endif; ?>
                    <?php if ($cOut):      ?><span class="mal-chip" style="background:#e53e3e;"><?= $cOut ?> Out of stock</span><?php endif; ?>
                    <?php if ($cLow):      ?><span class="mal-chip" style="background:#dd6b20;"><?= $cLow ?> Low stock</span><?php endif; ?>
                    <?php if ($cExpiring): ?><span class="mal-chip" style="background:#b7791f;"><?= $cExpiring ?> Expiring soon</span><?php endif; ?>
                </div>
            </div>
            <div class="mal-actions">
                <a class="mal-btn" href="?page=medicationinventory">Open inventory</a>
                <button type="button" class="mal-close" id="medAlertClose" title="Dismiss for this session" aria-label="Dismiss">&times;</button>
            </div>
        </div>

        <?php if ($alertRows): ?>
        <details>
            <summary>Show affected medicines (<?= count($alertRows) ?>)</summary>
            <ul class="mal-list">
                <?php foreach ($alertRows as $a): $r = $a['row']; $note = $expiryNote($r['expiry_date']); ?>
                <li>
                    <span class="mal-dot" style="background:<?= $a['color'] ?>;"></span>
                    <span class="mal-name">
                        <?= htmlspecialchars($r['medicine_name']) ?><?= $r['strength'] ? ' ' . htmlspecialchars($r['strength']) : '' ?>
                    </span>
                    <span class="mal-meta"><?= (int) $r['quantity'] ?> <?= htmlspecialchars($r['unit']) ?></span>
                    <?php if ($note): ?><span class="mal-meta">• <?= htmlspecialchars($note) ?></span><?php endif; ?>
                    <span class="badge <?= $a['class'] ?>"><?= $a['label'] ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </details>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <!-- =================== END MEDICATION ALERT BANNER =================== -->

    <!-- Module cards -->
    <div class="student-info">
        <h2>Modules</h2>
        <div class="stat-row" style="margin-bottom:0;">
            <a href="?page=patientinformationmanagement" style="text-decoration:none; flex:1; min-width:170px;">
                <div class="stat-card">
                    <strong><?= $patientCount === null ? '-' : $patientCount ?></strong>
                    <span>Patients on record</span>
                </div>
            </a>
            <a href="?page=clinicvisitandconsultation" style="text-decoration:none; flex:1; min-width:170px;">
                <div class="stat-card">
                    <strong id="visitsToday">-</strong>
                    <span>Consultations today</span><br>
                    <span id="visitsTotal">Loading…</span>
                </div>
            </a>
            <a href="?page=incidentandemergencymanagement" style="text-decoration:none; flex:1; min-width:170px;">
                <div class="stat-card danger">
                    <strong id="incOpen">-</strong>
                    <span>Open gate passes</span><br>
                    <span id="incTotal">Loading…</span>
                </div>
            </a>
            <a href="?page=medicationinventory" style="text-decoration:none; flex:1; min-width:170px;">
                <div class="stat-card warn">
                    <strong><?= $medSum['low'] + $medSum['out_of_stock'] ?></strong>
                    <span>Medicines to restock</span><br>
                    <span><?= $medSum['total'] ?> in inventory</span>
                </div>
            </a>
        </div>
    </div>

    <!-- KPI strip -->
    <div class="student-info">
        <h2>Key Performance Indicators</h2>
        <div class="ch-kpis">
            <div class="ch-kpi ch-k1">
                <strong id="kpiWeek">-</strong>
                <span>Consultations (7 days)</span>
                <small id="kpiWeekTrend">Loading…</small>
            </div>
            <div class="ch-kpi ch-k2">
                <strong id="kpiAvg">-</strong>
                <span>Avg. consultations / day</span>
                <small>Last 7 days</small>
            </div>
            <div class="ch-kpi ch-k3">
                <strong id="kpiResolve">-</strong>
                <span>Gate pass resolution rate</span>
                <small id="kpiResolveSub">Loading…</small>
            </div>
            <div class="ch-kpi ch-k4">
                <strong><?= $stockHealth === null ? '-' : $stockHealth . '%' ?></strong>
                <span>Medication stock health</span>
                <small><?= $medOk ?> of <?= $medSum['total'] ?> medicines OK</small>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/announcements.php'; ?>

    <!-- ===================== CONSULTATION ANALYTICS ===================== -->
    <div class="student-info">
        <h2>Clinic Visits &amp; Consultations — Analytics</h2>
        <div class="ch-grid">
            <div class="ch-card ch-wide">
                <h3>Consultation trend</h3>
                <p>Number of consultations per day, last 14 days</p>
                <div class="ch-line" id="chTrend"><div class="ch-empty">Loading…</div></div>
            </div>
            <div class="ch-card">
                <h3>Visits by type</h3>
                <p>Share of each visit type</p>
                <div id="chVisitTypes"><div class="ch-empty">Loading…</div></div>
            </div>
            <div class="ch-card">
                <h3>Top diagnoses</h3>
                <p>Most common diagnoses recorded</p>
                <div id="chDiagnosis"><div class="ch-empty">Loading…</div></div>
            </div>
            <div class="ch-card">
                <h3>Nurse workload</h3>
                <p>Consultations handled per nurse</p>
                <div id="chNurses"><div class="ch-empty">Loading…</div></div>
            </div>
        </div>
    </div>

    <!-- ===================== GATE PASS ANALYTICS ===================== -->
    <div class="student-info">
        <h2>Incident &amp; Emergency (Gate Pass) — Analytics</h2>
        <div class="ch-grid">
            <div class="ch-card">
                <h3>Gate pass status</h3>
                <p>Open vs in progress vs resolved</p>
                <div id="chGateStatus"><div class="ch-empty">Loading…</div></div>
            </div>
            <div class="ch-card">
                <h3>Reasons for gate pass</h3>
                <p>Most common incident types</p>
                <div id="chGateReasons"><div class="ch-empty">Loading…</div></div>
            </div>
            <div class="ch-card">
                <h3>Gate passes — last 7 days</h3>
                <p>Issued per day</p>
                <div id="chGateDaily"><div class="ch-empty">Loading…</div></div>
            </div>
        </div>
    </div>

    <!-- ===================== MEDICATION ANALYTICS ===================== -->
    <div class="student-info">
        <h2>Medication Inventory — Analytics</h2>
        <div class="stat-row" style="margin-bottom:0;">
            <div class="stat-card warn"><strong><?= $medSum['low'] ?></strong><span>Low</span></div>
            <div class="stat-card danger"><strong><?= $medSum['out_of_stock'] ?></strong><span>Out</span></div>
            <div class="stat-card warn"><strong><?= $medSum['expiring'] ?></strong><span>Expiring</span></div>
            <div class="stat-card danger"><strong><?= $medSum['expired'] ?></strong><span>Expired</span></div>
        </div>
        <div class="ch-grid">
            <div class="ch-card">
                <h3>Inventory health</h3>
                <p>Breakdown of all <?= (int) $medSum['total'] ?> medicines</p>
                <div id="chMedDonut"></div>
            </div>
            <div class="ch-card">
                <h3>Stock levels needing attention</h3>
                <p>Quantity on hand (color = status)</p>
                <?php if (!$topRows): ?>
                    <div class="ch-empty">All medicines are stocked and within expiry.</div>
                <?php else: foreach ($topRows as $a): $r = $a['row']; $pct = max(3, ((int) $r['quantity'] / $alertMax) * 100); ?>
                    <div class="ch-hrow">
                        <div class="ch-hhead">
                            <span><?= htmlspecialchars($r['medicine_name']) ?><?= $r['strength'] ? ' ' . htmlspecialchars($r['strength']) : '' ?></span>
                            <strong><?= (int) $r['quantity'] ?> <?= htmlspecialchars($r['unit']) ?></strong>
                        </div>
                        <div class="ch-htrack"><div class="ch-hfill" style="width:<?= $pct ?>%; background:<?= $a['color'] ?>;"></div></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent consultations -->
    <div class="table-info" style="margin-top:25px;">
        <span>Latest consultations</span>
        <a class="page-link" href="?page=clinicvisitandconsultation">Open module</a>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr><th>Patient</th><th>Visit type</th><th>Nurse</th><th>Date &amp; time</th><th>Diagnosis</th></tr>
            </thead>
            <tbody id="recentVisits">
                <tr><td colspan="5" class="no-data">Loading…</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Latest gate passes -->
    <div class="table-info" style="margin-top:25px;">
        <span>Latest gate passes</span>
        <a class="page-link" href="?page=incidentandemergencymanagement">Open module</a>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr><th>Student</th><th>Reason</th><th>Date</th><th>Guardian</th><th>Status</th></tr>
            </thead>
            <tbody id="recentIncidents">
                <tr><td colspan="5" class="no-data">Loading…</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Medicines needing attention -->
    <div class="table-info" style="margin-top:25px;">
        <span>Medicines that need attention</span>
        <a class="page-link" href="?page=medicationinventory">Open module</a>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr><th>Medicine</th><th>Stock</th><th>Expiry</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php if (!$topRows): ?>
                <tr><td colspan="4" class="no-data">All medicines are stocked and within expiry.</td></tr>
            <?php else: foreach ($topRows as $a): $row = $a['row']; ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['medicine_name']) ?></strong>
                        <?php if ($row['strength']): ?> <?= htmlspecialchars($row['strength']) ?><?php endif; ?></td>
                    <td><?= (int) $row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?></td>
                    <td><?= $row['expiry_date'] ? date('M d, Y', strtotime($row['expiry_date'])) : '-' ?></td>
                    <td><span class="badge <?= $a['class'] ?>"><?= $a['label'] ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    'use strict';

    // Same storage key used by the Incident & Emergency page
    const GATEPASS_KEY = 'bcp_gatepass_v3';
    const ALERT_KEY    = 'bcp_med_alert_dismissed';
    const PALETTE = ['#667eea', '#38b2ac', '#f6ad55', '#fc8181', '#9f7aea', '#4299e1', '#68d391', '#ed64a6'];

    /* ================= Medication alert: banner dismiss + toast ================= */
    (function medAlert() {
        const hasAlert    = <?= json_encode($hasAlert) ?>;
        const hasCritical = <?= json_encode($hasCritical) ?>;
        const signature   = <?= json_encode($alertSignature) ?>;
        const summary     = <?= json_encode(implode(' • ', $alertParts)) ?>;
        if (!hasAlert) return;

        const banner   = document.getElementById('medAlertBanner');
        const closeBtn = document.getElementById('medAlertClose');

        // Dismissed na ba sa session na ito? (lalabas ulit kung nagbago ang stock/expiry counts)
        let dismissed = false;
        try { dismissed = sessionStorage.getItem(ALERT_KEY) === signature; } catch (e) {}

        if (dismissed) {
            if (banner) banner.style.display = 'none';
            return;
        }

        if (closeBtn && banner) {
            closeBtn.addEventListener('click', function () {
                banner.style.display = 'none';
                try { sessionStorage.setItem(ALERT_KEY, signature); } catch (e) {}
            });
        }

        // Toast popup (kusang mawawala after 9 seconds, click para pumunta sa inventory)
        const toast = document.createElement('div');
        toast.className = 'mal-toast' + (hasCritical ? ' crit' : '');
        toast.innerHTML = '<b>' + (hasCritical ? '🚨 Medication alert' : '⚠️ Medication warning') + '</b><span></span>';
        toast.querySelector('span').textContent = summary;
        toast.addEventListener('click', function () { window.location.href = '?page=medicationinventory'; });
        document.body.appendChild(toast);
        setTimeout(function () {
            toast.classList.add('hide');
            setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 450);
        }, 9000);
    })();

    /* ================= Helpers ================= */
    function esc(v) {
        if (v === null || v === undefined) return '';
        const d = document.createElement('div');
        d.textContent = String(v);
        return d.innerHTML;
    }
    function fmtDate(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function daysAgo(n) {
        const d = new Date();
        d.setDate(d.getDate() - n);
        return fmtDate(d);
    }
    function localToday() { return daysAgo(0); }
    function shortLabel(dateStr) {
        const d = new Date(dateStr + 'T00:00:00');
        return (d.getMonth() + 1) + '/' + d.getDate();
    }
    function dayName(dateStr) {
        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][new Date(dateStr + 'T00:00:00').getDay()];
    }
    function statusBadge(status) {
        const cls = status === 'Resolved' ? 'badge-success' : (status === 'In Progress' ? 'badge-warning' : 'badge-danger');
        return '<span class="badge ' + cls + '">' + esc(status) + '</span>';
    }
    function countBy(list, fn) {
        const c = {};
        list.forEach(function (x) {
            const k = fn(x);
            if (k) c[k] = (c[k] || 0) + 1;
        });
        return c;
    }
    function sortedEntries(counts) {
        return Object.keys(counts)
            .map(function (k) { return { label: k, value: counts[k] }; })
            .sort(function (a, b) { return b.value - a.value; });
    }
    function empty(el, msg) { el.innerHTML = '<div class="ch-empty">' + esc(msg) + '</div>'; }

    /* ================= Chart builders (pure SVG / CSS) ================= */

    // Donut chart: items = [{label, value, color}]
    function donut(el, items, centerValue, centerLabel) {
        const total = items.reduce(function (s, i) { return s + i.value; }, 0);
        if (!total) { empty(el, 'No data yet'); return; }
        const r = 54, c = 2 * Math.PI * r;
        let offset = 0, segs = '';
        items.forEach(function (i) {
            if (!i.value) return;
            const len = i.value / total * c;
            segs += '<circle cx="70" cy="70" r="' + r + '" fill="none" stroke="' + i.color + '" stroke-width="18" ' +
                'stroke-dasharray="' + len + ' ' + (c - len) + '" stroke-dashoffset="' + (-offset) + '" ' +
                'transform="rotate(-90 70 70)"><title>' + esc(i.label) + ': ' + i.value + '</title></circle>';
            offset += len;
        });
        const legend = items.map(function (i) {
            const pct = Math.round(i.value / total * 100);
            return '<div><span><i style="background:' + i.color + '"></i>' + esc(i.label) + '</span>' +
                   '<span><b>' + i.value + '</b> <small style="color:#adb5bd">(' + pct + '%)</small></span></div>';
        }).join('');
        el.innerHTML =
            '<div class="ch-donut-wrap">' +
            '<svg viewBox="0 0 140 140">' +
            '<circle cx="70" cy="70" r="' + r + '" fill="none" stroke="#edf2f7" stroke-width="18"/>' + segs +
            '<text x="70" y="68" text-anchor="middle" font-size="24" font-weight="700" fill="#343a40">' + esc(centerValue) + '</text>' +
            '<text x="70" y="86" text-anchor="middle" font-size="10" fill="#868e96">' + esc(centerLabel) + '</text>' +
            '</svg><div class="ch-legend">' + legend + '</div></div>';
    }

    // Horizontal bars: items = [{label, value}], color optional
    function hbars(el, items, color) {
        if (!items.length) { empty(el, 'No data yet'); return; }
        const max = Math.max.apply(null, items.map(function (i) { return i.value; }).concat([1]));
        el.innerHTML = items.map(function (i, idx) {
            const col = i.color || color || PALETTE[idx % PALETTE.length];
            return '<div class="ch-hrow">' +
                '<div class="ch-hhead"><span title="' + esc(i.label) + '">' + esc(i.label) + '</span><strong>' + i.value + '</strong></div>' +
                '<div class="ch-htrack"><div class="ch-hfill" style="width:' + (i.value / max * 100) + '%;background:' + col + ';"></div></div>' +
                '</div>';
        }).join('');
    }

    // Vertical bars: items = [{label, value}]
    function vbars(el, items, color) {
        const max = Math.max.apply(null, items.map(function (i) { return i.value; }).concat([1]));
        el.innerHTML = '<div class="ch-vbars">' + items.map(function (i) {
            const h = Math.round(i.value / max * 120);
            return '<div class="ch-vcol"><div class="ch-vval">' + i.value + '</div>' +
                '<div class="ch-vbar" style="height:' + h + 'px;background:' + color + ';"></div>' +
                '<div class="ch-vlbl">' + i.label + '</div></div>';
        }).join('') + '</div>';
    }

    // Area / line chart: points = [{label, value}]
    function areaChart(el, points, color, gradId) {
        const W = 600, H = 190, padL = 30, padR = 14, padT = 14, padB = 28;
        const n = points.length;
        const maxV = Math.max.apply(null, points.map(function (p) { return p.value; }).concat([0]));
        const niceMax = Math.max(2, Math.ceil(maxV / 2) * 2);
        const innerW = W - padL - padR, innerH = H - padT - padB;
        const x = function (i) { return padL + (n === 1 ? innerW / 2 : i * innerW / (n - 1)); };
        const y = function (v) { return padT + innerH - (v / niceMax) * innerH; };

        let grid = '';
        [0, 0.5, 1].forEach(function (f) {
            const v = niceMax * f, yy = y(v);
            grid += '<line x1="' + padL + '" x2="' + (W - padR) + '" y1="' + yy + '" y2="' + yy + '" stroke="#e9ecef" stroke-dasharray="3 3"/>' +
                    '<text x="' + (padL - 6) + '" y="' + (yy + 4) + '" text-anchor="end" font-size="10" fill="#adb5bd">' + Math.round(v) + '</text>';
        });
        const line = points.map(function (p, i) { return (i ? 'L' : 'M') + x(i) + ' ' + y(p.value); }).join(' ');
        const area = line + ' L' + x(n - 1) + ' ' + y(0) + ' L' + x(0) + ' ' + y(0) + ' Z';
        const dots = points.map(function (p, i) {
            return '<circle cx="' + x(i) + '" cy="' + y(p.value) + '" r="4" fill="#fff" stroke="' + color + '" stroke-width="2">' +
                   '<title>' + esc(p.label) + ': ' + p.value + '</title></circle>';
        }).join('');
        const labels = points.map(function (p, i) {
            if (n > 8 && i % 2 === 1) return '';
            return '<text x="' + x(i) + '" y="' + (H - 8) + '" text-anchor="middle" font-size="10" fill="#868e96">' + esc(p.label) + '</text>';
        }).join('');

        el.innerHTML =
            '<svg viewBox="0 0 ' + W + ' ' + H + '" preserveAspectRatio="xMidYMid meet">' +
            '<defs><linearGradient id="' + gradId + '" x1="0" y1="0" x2="0" y2="1">' +
            '<stop offset="0%" stop-color="' + color + '" stop-opacity=".35"/>' +
            '<stop offset="100%" stop-color="' + color + '" stop-opacity="0"/></linearGradient></defs>' +
            grid + '<path d="' + area + '" fill="url(#' + gradId + ')"/>' +
            '<path d="' + line + '" fill="none" stroke="' + color + '" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>' +
            dots + labels + '</svg>';
    }

    /* ================= Medication donut (data from PHP) ================= */
    donut(document.getElementById('chMedDonut'),
          <?= json_encode($medDonut, JSON_HEX_TAG | JSON_HEX_AMP) ?>,
          <?= json_encode($stockHealth === null ? '-' : $stockHealth . '%') ?>,
          'healthy');

    /* ================= Clinic visits ================= */
    async function loadVisits() {
        const body = document.getElementById('recentVisits');
        try {
            const params = new URLSearchParams({ page: 1, search: '', limit: 50 });
            const res = await fetch((window.CLINIC_BASE || '.') + '/handlers/get_consultations.php?' + params);
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || 'Failed');

            const rows  = json.data || [];
            const today = localToday();

            document.getElementById('visitsToday').textContent =
                rows.filter(function (r) { return r.appointment_date === today; }).length;
            document.getElementById('visitsTotal').textContent = (json.total || 0) + ' total records';

            /* --- 14-day trend + KPIs --- */
            const days = [];
            for (let i = 13; i >= 0; i--) days.push(daysAgo(i));
            const perDay = {};
            days.forEach(function (d) { perDay[d] = 0; });
            rows.forEach(function (r) { if (perDay.hasOwnProperty(r.appointment_date)) perDay[r.appointment_date]++; });

            areaChart(document.getElementById('chTrend'),
                days.map(function (d) { return { label: shortLabel(d), value: perDay[d] }; }),
                '#667eea', 'gradTrend');

            const thisWeek = days.slice(7).reduce(function (s, d) { return s + perDay[d]; }, 0);
            const prevWeek = days.slice(0, 7).reduce(function (s, d) { return s + perDay[d]; }, 0);
            document.getElementById('kpiWeek').textContent = thisWeek;
            document.getElementById('kpiAvg').textContent  = (thisWeek / 7).toFixed(1);
            const trend = document.getElementById('kpiWeekTrend');
            if (prevWeek === 0) {
                trend.textContent = thisWeek > 0 ? '▲ New activity vs previous 7 days' : 'No data for the last 14 days';
            } else {
                const pct = Math.round((thisWeek - prevWeek) / prevWeek * 100);
                trend.textContent = (pct > 0 ? '▲ ' : pct < 0 ? '▼ ' : '■ ') + Math.abs(pct) + '% vs previous 7 days (' + prevWeek + ')';
            }

            /* --- Visit types donut (top 6 + Others) --- */
            const typeList = sortedEntries(countBy(rows, function (r) { return r.visit_type; }));
            let typeItems = typeList.slice(0, 6);
            const othersSum = typeList.slice(6).reduce(function (s, t) { return s + t.value; }, 0);
            if (othersSum) typeItems.push({ label: 'Others', value: othersSum });
            typeItems = typeItems.map(function (t, i) { t.color = PALETTE[i % PALETTE.length]; return t; });
            donut(document.getElementById('chVisitTypes'), typeItems, rows.length, 'visits');

            /* --- Top diagnoses --- */
            const diagCounts = {}, diagNames = {};
            rows.forEach(function (r) {
                if (!r.diagnosis) return;
                const raw = String(r.diagnosis).trim();
                if (!raw) return;
                const key = raw.toLowerCase();
                diagCounts[key] = (diagCounts[key] || 0) + 1;
                if (!diagNames[key]) diagNames[key] = raw.length > 30 ? raw.substring(0, 30) + '…' : raw;
            });
            hbars(document.getElementById('chDiagnosis'),
                sortedEntries(diagCounts).slice(0, 5).map(function (d) { return { label: diagNames[d.label], value: d.value }; }),
                '#9f7aea');

            /* --- Nurse workload --- */
            hbars(document.getElementById('chNurses'),
                sortedEntries(countBy(rows, function (r) { return r.nurse ? String(r.nurse).trim() : ''; })).slice(0, 5),
                '#38b2ac');

            /* --- Recent table --- */
            body.innerHTML = rows.length
                ? rows.slice(0, 5).map(function (r) {
                    return '<tr><td><strong>' + esc(r.patient_name) + '</strong></td>' +
                        '<td>' + esc(r.visit_type) + '</td>' +
                        '<td>' + esc(r.nurse) + '</td>' +
                        '<td>' + esc(r.appointment_date) + ' ' + esc(r.appointment_time || '') + '</td>' +
                        '<td>' + (r.diagnosis ? esc(String(r.diagnosis).substring(0, 50)) : '-') + '</td></tr>';
                  }).join('')
                : '<tr><td colspan="5" class="no-data">No consultations yet.</td></tr>';
        } catch (err) {
            document.getElementById('visitsTotal').textContent = 'Could not load';
            document.getElementById('kpiWeekTrend').textContent = 'Could not load';
            ['chTrend', 'chVisitTypes', 'chDiagnosis', 'chNurses'].forEach(function (id) {
                empty(document.getElementById(id), 'Could not load data');
            });
            body.innerHTML = '<tr><td colspan="5" class="no-data">Could not load consultations (' + esc(err.message) + ').</td></tr>';
        }
    }

    /* ================= Gate passes (localStorage) ================= */
    function loadIncidents() {
        const body = document.getElementById('recentIncidents');
        let list = [];
        try { list = JSON.parse(localStorage.getItem(GATEPASS_KEY)) || []; } catch (e) { list = []; }

        const resolved   = list.filter(function (i) { return i.status === 'Resolved'; }).length;
        const inProgress = list.filter(function (i) { return i.status === 'In Progress'; }).length;
        const open       = list.length - resolved - inProgress;
        const notResolved = list.length - resolved;

        document.getElementById('incOpen').textContent  = notResolved;
        document.getElementById('incTotal').textContent = list.length + ' total on this browser';

        // KPI
        document.getElementById('kpiResolve').textContent = list.length ? Math.round(resolved / list.length * 100) + '%' : '-';
        document.getElementById('kpiResolveSub').textContent = list.length ? resolved + ' resolved of ' + list.length : 'No gate passes yet';

        // Status donut
        donut(document.getElementById('chGateStatus'), [
            { label: 'Open',        value: open,       color: '#e53e3e' },
            { label: 'In Progress', value: inProgress, color: '#f6ad55' },
            { label: 'Resolved',    value: resolved,   color: '#38b26d' }
        ], list.length, 'gate passes');

        // Reasons
        hbars(document.getElementById('chGateReasons'),
            sortedEntries(countBy(list, function (i) { return i.incident_type; })).slice(0, 6),
            '#fc8181');

        // Last 7 days
        const days = [];
        for (let i = 6; i >= 0; i--) days.push(daysAgo(i));
        const perDay = {};
        days.forEach(function (d) { perDay[d] = 0; });
        list.forEach(function (i) { if (perDay.hasOwnProperty(i.visit_date)) perDay[i.visit_date]++; });
        if (list.length) {
            vbars(document.getElementById('chGateDaily'),
                days.map(function (d) { return { label: dayName(d) + '<br>' + shortLabel(d), value: perDay[d] }; }),
                '#ed8936');
        } else {
            empty(document.getElementById('chGateDaily'), 'No gate passes saved on this browser');
        }

        // Recent table
        list.sort(function (a, b) { return b.id - a.id; });
        body.innerHTML = list.length
            ? list.slice(0, 5).map(function (i) {
                return '<tr><td><strong>' + esc(i.student_name) + '</strong><br><small>' + esc(i.student_id) + '</small></td>' +
                    '<td>' + esc(i.incident_type) + '</td>' +
                    '<td>' + esc(i.visit_date || '-') + '</td>' +
                    '<td>' + esc(i.guardian || '-') + '</td>' +
                    '<td>' + statusBadge(i.status) + '</td></tr>';
              }).join('')
            : '<tr><td colspan="5" class="no-data">No gate passes saved on this browser.</td></tr>';
    }

    loadVisits();
    loadIncidents();
})();
</script>