<?php
require_once(__DIR__ . '/../classes/FacultyProfileManager.php');
require_once(__DIR__ . '/../../../database/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Database();
$conn = $database->getConnection();
$manager = new FacultyProfileManager($conn);

$engagementId = isset($_GET['engagement_id']) ? (int)$_GET['engagement_id'] : 0;
if ($engagementId <= 0) {
    echo '<p>Invalid engagement id.</p>';
    exit;
}

$engagement = $manager->getEngagementById($engagementId);
if (!$engagement) {
    echo '<p>Engagement record not found.</p>';
    exit;
}

$employee = $manager->getEmployeeById((int)$engagement['employee_id']);

$facultyName = $employee ? trim(($employee['first_name'] ?? '') . ' ' . ($employee['middle_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')) : 'N/A';
$title = htmlspecialchars($engagement['title'] ?? '');
$organization = htmlspecialchars($engagement['organization'] ?? '');
$startDate = htmlspecialchars($engagement['start_date'] ?? '');
$endDate = htmlspecialchars($engagement['end_date'] ?? '');
$engType = htmlspecialchars($engagement['engagement_type'] ?? '');
$dateIssued = $engagement['certificate_generated_at'] ?? null;

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Engagement Certificate Preview</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        .certificate { width: 900px; height: 600px; border: 12px solid #2c3e50; padding: 36px; position: relative; margin: 0 auto; }
        .certificate .header { text-align: center; margin-bottom: 24px; }
        .certificate h1 { margin: 0; font-size: 32px; }
        .certificate .sub { margin-top: 6px; color: #555; }
        .certificate .body { text-align: center; margin-top: 40px; }
        .certificate .recipient { font-size: 28px; font-weight: bold; margin: 20px 0; }
        .certificate .meta { margin-top: 12px; color: #333; }
        .controls { text-align: center; margin-top: 18px; }
        .btn { display:inline-block; padding:8px 12px; margin:4px; background:#2c3e50; color:#fff; border-radius:4px; text-decoration:none; }
        .btn.secondary { background:#6c757d; }
    </style>
</head>
<body>
    <div class="certificate" id="certificate">
        <div class="header">
            <h1>Certificate of Engagement</h1>
            <div class="sub">This certifies that</div>
        </div>

        <div class="body">
            <div class="recipient"><?php echo htmlspecialchars($facultyName); ?></div>
            <div class="meta">has been engaged as <strong><?php echo $engType; ?></strong> for the activity:</div>
            <div style="margin-top:14px; font-size:20px;"><em><?php echo $title; ?></em></div>
            <div class="meta" style="margin-top:14px;">Organized by: <?php echo $organization; ?></div>
            <div class="meta" style="margin-top:8px;">Period: <?php echo $startDate; ?> to <?php echo $endDate; ?></div>
            <div class="meta" style="margin-top:18px; color:#444;">Date Issued: <?php echo $dateIssued ? htmlspecialchars($dateIssued) : 'Not generated yet'; ?></div>
        </div>
    </div>

    <div class="controls">
        <a href="#" class="btn" id="printBtn">Print</a>
        <a href="#" class="btn secondary" id="markGeneratedBtn"><?php echo $dateIssued ? 'Re-mark Generated' : 'Mark Generated'; ?></a>
    </div>

    <script>
        document.getElementById('printBtn').addEventListener('click', function (e) {
            e.preventDefault();
            window.print();
        });

        document.getElementById('markGeneratedBtn').addEventListener('click', function (e) {
            e.preventDefault();
            if (!confirm('Mark this certificate as generated? This will save a timestamp.')) return;

            const payload = { action: 'generate_engagement_certificate', engagement_id: <?php echo (int)$engagementId; ?> };
            fetch('<?php echo htmlspecialchars((dirname($_SERVER['SCRIPT_NAME'])) . '/faculty-management.php'); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    alert(data.message || 'Timestamp saved');
                    location.reload();
                    return;
                }
                alert((data && data.message) || 'Unable to save timestamp');
            })
            .catch(() => {
                alert('Unable to save timestamp');
            });
        });
    </script>
</body>
</html>
