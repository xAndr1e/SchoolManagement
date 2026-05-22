<?php
$host    = 'localhost';
$db      = 'sms';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$students = [];
$courses  = [];
$error    = null;
$stats    = ['total' => 0, 'enrolled' => 0, 'on_leave' => 0, 'graduated' => 0, 'dropped' => 0];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->query("
        SELECT
            s.id, s.student_number, s.year_level, s.enrollment_status, s.enrolled_at,
            a.surname, a.first_name, a.middle_name, a.suffix, a.sex,
            a.email AS applicant_email, a.contact_number, a.date_of_birth,
            a.civil_status, a.address_city, a.address_province,
            a.school_last_attended, a.year_graduated, a.application_number,
            u.username, u.email AS user_email,
            c.course_code, c.course_name, c.duration_years,
            sec.section_code, sec.section_name, sec.academic_year,
            sec.semester, sec.max_students,
            (SELECT COUNT(*) FROM enr_students s2
             WHERE s2.section_id = s.section_id AND s2.enrollment_status = 'enrolled'
            ) AS section_enrolled_count
        FROM      enr_students   s
        JOIN      enr_applicants a   ON s.applicant_id = a.id
        JOIN      enr_users      u   ON s.user_id      = u.id
        JOIN      enr_courses    c   ON s.course_id    = c.id
        LEFT JOIN enr_sections   sec ON s.section_id   = sec.id
        ORDER BY s.enrolled_at DESC
    ");
    $students = $stmt->fetchAll();

    $cStmt   = $pdo->query("SELECT id, course_code, course_name FROM enr_courses WHERE is_active = 1 ORDER BY course_code");
    $courses = $cStmt->fetchAll();

    $stats['total'] = count($students);
    foreach ($students as $r) {
        $key = $r['enrollment_status'];
        if (isset($stats[$key])) $stats[$key]++;
    }

} catch (PDOException $e) {
    $error = $e->getMessage();

    // Mock data for preview
    $students = [
        ['id'=>1,'student_number'=>'2024-00001','year_level'=>1,'enrollment_status'=>'enrolled','enrolled_at'=>'2024-08-15 09:00:00','surname'=>'Santos','first_name'=>'Maria','middle_name'=>'Cruz','suffix'=>null,'sex'=>'Female','applicant_email'=>'maria@uni.edu','contact_number'=>'09171234567','date_of_birth'=>'2005-03-12','civil_status'=>'Single','address_city'=>'Quezon City','address_province'=>'Metro Manila','school_last_attended'=>'QC Science High School','year_graduated'=>'2024','application_number'=>'APP-2024-001','username'=>'maria.santos','user_email'=>'maria@uni.edu','course_code'=>'BSCS','course_name'=>'Bachelor of Science in Computer Science','duration_years'=>4,'section_code'=>'CS-1A','section_name'=>'CS 1-A','academic_year'=>'2024-2025','semester'=>'1st Semester','max_students'=>40,'section_enrolled_count'=>32],
        ['id'=>2,'student_number'=>'2024-00002','year_level'=>2,'enrollment_status'=>'enrolled','enrolled_at'=>'2024-08-15 09:30:00','surname'=>'dela Cruz','first_name'=>'Juan','middle_name'=>null,'suffix'=>'Jr.','sex'=>'Male','applicant_email'=>'juan@uni.edu','contact_number'=>'09281234567','date_of_birth'=>'2004-07-22','civil_status'=>'Single','address_city'=>'Makati','address_province'=>'Metro Manila','school_last_attended'=>'Makati Science HS','year_graduated'=>'2023','application_number'=>'APP-2024-002','username'=>'juan.delacruz','user_email'=>'juan@uni.edu','course_code'=>'BSIT','course_name'=>'Bachelor of Science in Information Technology','duration_years'=>4,'section_code'=>'IT-2B','section_name'=>'IT 2-B','academic_year'=>'2024-2025','semester'=>'1st Semester','max_students'=>35,'section_enrolled_count'=>28],
        ['id'=>3,'student_number'=>'2023-00045','year_level'=>3,'enrollment_status'=>'on_leave','enrolled_at'=>'2023-08-10 10:00:00','surname'=>'Reyes','first_name'=>'Ana','middle_name'=>'Luz','suffix'=>null,'sex'=>'Female','applicant_email'=>'ana@uni.edu','contact_number'=>'09091234567','date_of_birth'=>'2003-11-05','civil_status'=>'Single','address_city'=>'Pasig','address_province'=>'Metro Manila','school_last_attended'=>'Pasig NHS','year_graduated'=>'2022','application_number'=>'APP-2023-045','username'=>'ana.reyes','user_email'=>'ana@uni.edu','course_code'=>'BSCS','course_name'=>'Bachelor of Science in Computer Science','duration_years'=>4,'section_code'=>null,'section_name'=>null,'academic_year'=>null,'semester'=>null,'max_students'=>null,'section_enrolled_count'=>0],
        ['id'=>4,'student_number'=>'2021-00012','year_level'=>4,'enrollment_status'=>'graduated','enrolled_at'=>'2021-08-12 08:00:00','surname'=>'Villanueva','first_name'=>'Pedro','middle_name'=>'Gomez','suffix'=>null,'sex'=>'Male','applicant_email'=>'pedro@uni.edu','contact_number'=>'09151234567','date_of_birth'=>'2001-04-18','civil_status'=>'Single','address_city'=>'Taguig','address_province'=>'Metro Manila','school_last_attended'=>'BGC Senior HS','year_graduated'=>'2020','application_number'=>'APP-2021-012','username'=>'pedro.villanueva','user_email'=>'pedro@uni.edu','course_code'=>'BSIT','course_name'=>'Bachelor of Science in Information Technology','duration_years'=>4,'section_code'=>'IT-4A','section_name'=>'IT 4-A','academic_year'=>'2024-2025','semester'=>'2nd Semester','max_students'=>40,'section_enrolled_count'=>38],
        ['id'=>5,'student_number'=>'2022-00033','year_level'=>2,'enrollment_status'=>'dropped','enrolled_at'=>'2022-08-11 11:00:00','surname'=>'Mercado','first_name'=>'Liza','middle_name'=>null,'suffix'=>null,'sex'=>'Female','applicant_email'=>'liza@uni.edu','contact_number'=>'09361234567','date_of_birth'=>'2003-09-29','civil_status'=>'Single','address_city'=>'Mandaluyong','address_province'=>'Metro Manila','school_last_attended'=>'Mandaluyong NHS','year_graduated'=>'2021','application_number'=>'APP-2022-033','username'=>'liza.mercado','user_email'=>'liza@uni.edu','course_code'=>'BSCS','course_name'=>'Bachelor of Science in Computer Science','duration_years'=>4,'section_code'=>'CS-2C','section_name'=>'CS 2-C','academic_year'=>'2024-2025','semester'=>'1st Semester','max_students'=>38,'section_enrolled_count'=>25],
        ['id'=>6,'student_number'=>'2024-00006','year_level'=>1,'enrollment_status'=>'enrolled','enrolled_at'=>'2024-08-16 08:45:00','surname'=>'Bautista','first_name'=>'Carlos','middle_name'=>'Ramos','suffix'=>null,'sex'=>'Male','applicant_email'=>'carlos@uni.edu','contact_number'=>'09221234567','date_of_birth'=>'2006-01-14','civil_status'=>'Single','address_city'=>'Las Pinas','address_province'=>'Metro Manila','school_last_attended'=>'Las Pinas NHS','year_graduated'=>'2024','application_number'=>'APP-2024-006','username'=>'carlos.bautista','user_email'=>'carlos@uni.edu','course_code'=>'BSCpE','course_name'=>'Bachelor of Science in Computer Engineering','duration_years'=>5,'section_code'=>'CPE-1B','section_name'=>'CPE 1-B','academic_year'=>'2024-2025','semester'=>'1st Semester','max_students'=>30,'section_enrolled_count'=>18],
    ];
    $courses = [
        ['id'=>1,'course_code'=>'BSCS','course_name'=>'BS Computer Science'],
        ['id'=>2,'course_code'=>'BSIT','course_name'=>'BS Information Technology'],
        ['id'=>3,'course_code'=>'BSCpE','course_name'=>'BS Computer Engineering'],
    ];
    $stats['total'] = count($students);
    foreach ($students as $r) {
        $key = $r['enrollment_status'];
        if (isset($stats[$key])) $stats[$key]++;
    }
}

/* ── HELPERS ─────────────────────────────────────────────────────────────── */
function ep_fullName(array $r): string {
    $n = trim($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'][0].'. ' : '') . $r['surname']);
    if (!empty($r['suffix'])) $n .= ', ' . $r['suffix'];
    return $n;
}
function ep_initials(array $r): string {
    return strtoupper(substr($r['first_name'],0,1) . substr($r['surname'],0,1));
}
function ep_age(string $dob): int {
    return (int) date_diff(date_create($dob), date_create('today'))->y;
}
function ep_statusMeta(string $s): array {
    $map = [
        'enrolled'  => ['label'=>'Enrolled',  'cls'=>'s-enrolled'],
        'on_leave'  => ['label'=>'On Leave',   'cls'=>'s-leave'],
        'graduated' => ['label'=>'Graduated',  'cls'=>'s-graduated'],
        'dropped'   => ['label'=>'Dropped',    'cls'=>'s-dropped'],
    ];
    return $map[$s] ?? ['label'=>ucfirst($s),'cls'=>'s-default'];
}
function ep_yearLabel(int $y): string {
    return (['','1st','2nd','3rd','4th','5th'][$y] ?? "{$y}th");
}
function ep_sectionFill(int $enrolled, int $max): int {
    return $max > 0 ? (int)min(100, round($enrolled / $max * 100)) : 0;
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

<style>
/* ── RESET & TOKENS ────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --color1:  rgba(32, 0, 130, 1);
    --color2:  rgba(51, 65, 85, 1);
    --color3:  rgba(255, 255, 255, 1);
    --color4:  rgba(186, 186, 186, 1);
    --color5:  rgba(95, 95, 95, 1);
    --color6:  rgba(51, 65, 85, 1);
    --color7:  rgb(81, 70, 183);
    --color8:  rgb(0, 0, 0);
    --color9:  rgba(240, 240, 240, 1);
    --color10: #e53e3e;
    --sidebar-width: 252px;
    --header-height: 60px;

    --c1-5:   rgba(32, 0, 130, .05);
    --c1-10:  rgba(32, 0, 130, .10);
    --c1-18:  rgba(32, 0, 130, .18);
    --c7-10:  rgba(81, 70, 183, .10);
    --c7-18:  rgba(81, 70, 183, .18);
    --green:  #22c55e;
    --amber:  #f59e0b;
    --violet: #818cf8;
    --red:    #f87171;

    --r-xs: 4px;
    --r-sm: 7px;
    --r-md: 12px;
    --r-lg: 18px;

    --sh-xs: 0 1px 3px rgba(0,0,0,.07);
    --sh-sm: 0 2px 8px rgba(0,0,0,.08);
    --sh-md: 0 4px 16px rgba(32,0,130,.10);

    --font: 'DM Sans', sans-serif;
    --mono: 'JetBrains Mono', monospace;
}

/* ── PAGE SHELL ─────────────────────────────────────────────────── */
.ep {
    font-family: var(--font);
    background: var(--color9);
    color: var(--color2);
    min-height: 100vh;
    font-size: 14px;
    line-height: 1.5;
}

/* ── HERO ───────────────────────────────────────────────────────── */
.ep-hero {
    background: var(--color1);
    position: relative;
    overflow: hidden;
    padding: 0 32px;
    border-radius: 12px;
}
.ep-hero::before {
    content: '';
    position: absolute;
    width: 380px; height: 380px;
    top: -140px; right: -80px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(81,70,183,.45) 0%, transparent 70%);
    pointer-events: none;
}
.ep-hero::after {
    content: '';
    position: absolute;
    width: 240px; height: 240px;
    bottom: -110px; left: 15%;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,.06) 0%, transparent 70%);
    pointer-events: none;
}
.ep-hero__inner {
    position: relative; z-index: 1;
    display: flex; align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap; gap: 16px;
    padding: 28px 0 0;
}
.ep-hero__text h1 {
    font-size: 23px; font-weight: 800;
    color: var(--color3);
    letter-spacing: -.5px;
    line-height: 1.15;
}
.ep-hero__text p {
    font-size: 13px;
    color: rgba(255,255,255,.5);
    margin-top: 4px;
}
.ep-hero__actions {
    display: flex; gap: 8px;
    padding-bottom: 4px;
}
.ep-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px;
    border-radius: var(--r-sm);
    font-family: var(--font);
    font-size: 12.5px; font-weight: 600;
    cursor: pointer; border: none;
    transition: all .15s;
}
.ep-btn--glass {
    background: rgba(255,255,255,.10);
    color: var(--color3);
    border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(6px);
}
.ep-btn--glass:hover { background: rgba(255,255,255,.20); }
.ep-btn svg { width: 14px; height: 14px; flex-shrink: 0; }

/* ── STAT STRIP ─────────────────────────────────────────────────── */
.ep-stats {
    position: relative; z-index: 1;
    display: flex;
    margin-top: 22px;
    border-top: 1px solid rgba(255,255,255,.10);
}
.ep-stat {
    flex: 1;
    padding: 15px 20px;
    border-right: 1px solid rgba(255,255,255,.10);
    position: relative;
    transition: background .15s;
}
.ep-stat:last-child { border-right: none; }
.ep-stat::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 2px;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform .2s ease;
}
.ep-stat:hover { background: rgba(255,255,255,.05); }
.ep-stat:hover::after { transform: scaleX(1); }
.ep-stat--all::after     { background: rgba(255,255,255,.7); }
.ep-stat--enrolled::after { background: var(--green); }
.ep-stat--leave::after   { background: var(--amber); }
.ep-stat--grad::after    { background: var(--violet); }
.ep-stat--dropped::after { background: var(--red); }
.ep-stat__num {
    font-size: 27px; font-weight: 800;
    color: var(--color3);
    letter-spacing: -1.5px; line-height: 1;
}
.ep-stat__lbl {
    font-size: 10.5px; font-weight: 500;
    color: rgba(255,255,255,.48);
    text-transform: uppercase; letter-spacing: .6px;
    margin-top: 3px;
}

/* ── TOOLBAR ────────────────────────────────────────────────────── */
.ep-toolbar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: auto auto;
    gap: 10px;
    padding: 20px 32px 0;
}
.ep-search {
    position: relative; flex: 1 1 220px;
}
.ep-search svg {
    position: absolute; left: 11px; top: 50%;
    transform: translateY(-50%);
    width: 15px; height: 15px;
    color: var(--color4); pointer-events: none;
}
.ep-search input {
    width: 100%;
    padding: 9px 12px 9px 34px;
    border: 1.5px solid var(--color4);
    border-radius: var(--r-sm);
    font-family: var(--font); font-size: 13px;
    color: var(--color2); background: var(--color3);
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.ep-search input:focus {
    border-color: var(--color1);
    box-shadow: 0 0 0 3px var(--c1-10);
}
.ep-sel {
    padding: 9px 30px 9px 11px;
    border: 1.5px solid var(--color4);
    border-radius: var(--r-sm);
    font-family: var(--font); font-size: 13px;
    color: var(--color2); background: var(--color3);
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23bababa' stroke-width='2.5'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 9px center;
    outline: none; cursor: pointer;
    transition: border-color .15s;
}
.ep-sel:focus { border-color: var(--color1); }
.ep-count {
    font-size: 12px; color: var(--color5);
    margin-left: auto; white-space: nowrap;
}
.ep-count strong { color: var(--color1); font-weight: 700; }

/* ── NOTICE ─────────────────────────────────────────────────────── */
.ep-notice {
    margin: 14px 32px 0;
    padding: 10px 14px;
    border-radius: var(--r-sm);
    font-size: 12px;
    display: flex; align-items: flex-start; gap: 8px;
    background: rgba(245,158,11,.08);
    border: 1px solid rgba(245,158,11,.22);
    color: #92400e;
}
.ep-notice svg { flex-shrink: 0; margin-top: 1px; }

/* ── TABLE WRAPPER ──────────────────────────────────────────────── */
.ep-table-wrap {
    margin: 18px 32px 32px;
    background: var(--color3);
    border-radius: var(--r-lg);
    box-shadow: var(--sh-sm);
    border: 1px solid rgba(0,0,0,.06);
    overflow-x: auto;
}
.ep-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 780px;
}

/* Head */
.ep-table thead tr {
    background: #f7f6ff;
    border-bottom: 1.5px solid rgba(32,0,130,.08);
}
.ep-table thead th {
    padding: 11px 14px;
    text-align: left;
    font-size: 10.5px; font-weight: 700;
    letter-spacing: .8px;
    text-transform: uppercase;
    color: var(--color5);
    white-space: nowrap;
}
.ep-table thead th:first-child { padding-left: 20px; width: 40px; }
.ep-table thead th:last-child  { padding-right: 20px; }

/* Body rows */
.ep-table tbody .ep-data-row {
    border-bottom: 1px solid rgba(0,0,0,.04);
    transition: background .12s;
    cursor: pointer;
    animation: rowIn .28s ease both;
}
.ep-table tbody .ep-data-row:last-of-type { border-bottom: none; }
.ep-table tbody .ep-data-row:hover { background: var(--c1-5); }
.ep-table tbody .ep-data-row.ep--expanded { background: var(--c1-5); }

.ep-table td {
    padding: 12px 14px;
    vertical-align: middle;
    font-size: 13px;
}
.ep-table td:first-child { padding-left: 20px; }
.ep-table td:last-child  { padding-right: 20px; }

/* ── TOGGLE ICON ────────────────────────────────────────────────── */
.ep-toggle {
    width: 22px; height: 22px;
    border-radius: 50%;
    background: var(--color9);
    border: 1px solid rgba(0,0,0,.08);
    display: flex; align-items: center; justify-content: center;
    color: var(--color5);
    transition: transform .2s, background .15s, color .15s, border-color .15s;
    flex-shrink: 0;
}
.ep-data-row.ep--expanded .ep-toggle {
    transform: rotate(180deg);
    background: var(--c1-10);
    color: var(--color1);
    border-color: var(--c1-18);
}

/* ── STUDENT CELL ───────────────────────────────────────────────── */
.ep-student {
    display: flex; align-items: center; gap: 11px;
    min-width: 190px;
}
.ep-avatar {
    width: 37px; height: 37px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12.5px; font-weight: 800;
    flex-shrink: 0;
    letter-spacing: .5px;
    color: var(--color1);
    background: var(--c1-10);
    border: 1.5px solid var(--c1-18);
    transition: transform .15s;
}
.ep-avatar--f {
    color: var(--color7);
    background: var(--c7-10);
    border-color: var(--c7-18);
}
.ep-data-row:hover .ep-avatar { transform: scale(1.06); }
.ep-sname {
    font-weight: 700; font-size: 13.5px;
    color: var(--color2); line-height: 1.2;
}
.ep-smeta {
    font-size: 11.5px; color: var(--color5);
    margin-top: 1px;
}

/* ── MONO ───────────────────────────────────────────────────────── */
.ep-mono {
    font-family: var(--mono);
    font-size: 11.5px;
    background: var(--color9);
    color: var(--color2);
    padding: 3px 7px;
    border-radius: var(--r-xs);
    letter-spacing: .2px;
    white-space: nowrap;
}

/* ── COURSE CELL ────────────────────────────────────────────────── */
.ep-ccode {
    display: inline-block;
    background: var(--c1-10);
    color: var(--color1);
    font-size: 10.5px; font-weight: 800;
    padding: 2px 8px;
    border-radius: var(--r-xs);
    letter-spacing: .4px;
    margin-bottom: 3px;
}
.ep-cname { font-size: 12px; color: var(--color5); }

/* ── BADGE ──────────────────────────────────────────────────────── */
.ep-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11.5px; font-weight: 600;
    white-space: nowrap;
}
.ep-badge::before {
    content: '';
    width: 6px; height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}
.s-enrolled  { background: rgba(34,197,94,.1);    color: #15803d; }
.s-enrolled::before  { background: var(--green); box-shadow: 0 0 4px rgba(34,197,94,.6); }
.s-leave     { background: rgba(245,158,11,.1);   color: #b45309; }
.s-leave::before     { background: var(--amber); }
.s-graduated { background: rgba(129,140,248,.12); color: #4f46e5; }
.s-graduated::before { background: var(--violet); }
.s-dropped   { background: rgba(248,113,113,.1);  color: #dc2626; }
.s-dropped::before   { background: var(--red); }

/* ── YEAR ───────────────────────────────────────────────────────── */
.ep-year {
    display: inline-block;
    background: var(--color9);
    border: 1px solid rgba(0,0,0,.07);
    color: var(--color2);
    font-size: 12px; font-weight: 600;
    padding: 3px 9px;
    border-radius: var(--r-xs);
    white-space: nowrap;
}

/* ── SECTION CELL ───────────────────────────────────────────────── */
.ep-sec-tag {
    display: inline-flex; align-items: center; gap: 4px;
    background: var(--color9);
    border: 1px solid rgba(0,0,0,.07);
    color: var(--color2);
    font-size: 12px; font-weight: 600;
    padding: 3px 8px;
    border-radius: var(--r-xs);
    margin-bottom: 5px;
}
.ep-sec-tag svg { width: 11px; height: 11px; color: var(--color5); }
.ep-bar {
    height: 4px;
    background: rgba(0,0,0,.07);
    border-radius: 4px;
    max-width: 88px;
    overflow: hidden;
}
.ep-bar__inner {
    height: 100%; border-radius: 4px;
    transition: width .5s ease;
}
.ep-bar-lbl { font-size: 10.5px; color: var(--color5); margin-top: 2px; }

/* ── EXPAND DETAIL ──────────────────────────────────────────────── */
.ep-detail-row { display: none; }
.ep-detail-row.ep--open { display: table-row; }
.ep-detail-cell {
    padding: 0 !important;
    background: #f7f6ff;
    border-bottom: 1.5px solid var(--c1-10) !important;
}
.ep-detail-inner {
    padding: 16px 20px 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 12px 24px;
    animation: detailIn .18s ease;
}
@keyframes detailIn {
    from { opacity:0; transform:translateY(-5px); }
    to   { opacity:1; transform:translateY(0); }
}
.ep-fl { }
.ep-fl__k {
    font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .7px;
    color: var(--color4);
    margin-bottom: 2px;
}
.ep-fl__v {
    font-size: 13px; font-weight: 500;
    color: var(--color2);
}

/* ── EMPTY ──────────────────────────────────────────────────────── */
.ep-empty {
    text-align: center;
    padding: 70px 20px;
}
.ep-empty__icon {
    width: 58px; height: 58px;
    background: var(--c1-5);
    border: 1px dashed var(--color4);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px;
    color: var(--color4);
}
.ep-empty h3 { font-size: 15px; font-weight: 700; color: var(--color2); }
.ep-empty p  { font-size: 13px; color: var(--color5); margin-top: 4px; }

/* ── ANIMATE ────────────────────────────────────────────────────── */
@keyframes rowIn {
    from { opacity:0; transform:translateY(5px); }
    to   { opacity:1; transform:translateY(0); }
}

/* ── RESPONSIVE ─────────────────────────────────────────────────── */
@media (max-width: 860px) {
    .ep-hero, .ep-toolbar { padding-left: 20px; padding-right: 20px; }
    .ep-table-wrap { margin-left: 20px; margin-right: 20px; }
    .ep-stats { flex-wrap: wrap; }
    .ep-stat  { flex: 1 1 40%; border-right: none; border-bottom: 1px solid rgba(255,255,255,.08); }
}
</style>

<div class="module-header">
    <h2>Student Monitoring</h2>
    <p>Track and manage student information and status</p>
</div>


<div class="ep">

    <!-- ── HERO ── -->
    <div class="ep-hero">
        <div class="ep-hero__inner">
            <div class="ep-hero__text">
                <h1>Enrolled Students</h1>
                <p>Click any row to expand full student profile</p>
            </div>
            <div class="ep-hero__actions">
                <button class="ep-btn ep-btn--glass" onclick="epExportCSV()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export CSV
                </button>
                <button class="ep-btn ep-btn--glass" onclick="window.print()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print
                </button>
            </div>
        </div>

        <!-- Stat strip -->
        <div class="ep-stats">
            <div class="ep-stat ep-stat--all">
                <div class="ep-stat__num"><?= $stats['total'] ?></div>
                <div class="ep-stat__lbl">Total</div>
            </div>
            <div class="ep-stat ep-stat--enrolled">
                <div class="ep-stat__num"><?= $stats['enrolled'] ?></div>
                <div class="ep-stat__lbl">Enrolled</div>
            </div>
            <div class="ep-stat ep-stat--leave">
                <div class="ep-stat__num"><?= $stats['on_leave'] ?></div>
                <div class="ep-stat__lbl">On Leave</div>
            </div>
            <div class="ep-stat ep-stat--grad">
                <div class="ep-stat__num"><?= $stats['graduated'] ?></div>
                <div class="ep-stat__lbl">Graduated</div>
            </div>
            <div class="ep-stat ep-stat--dropped">
                <div class="ep-stat__num"><?= $stats['dropped'] ?></div>
                <div class="ep-stat__lbl">Dropped</div>
            </div>
        </div>
    </div>

    <!-- ── NOTICE ── -->
    <?php if ($error): ?>
    <div class="ep-notice">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>Database unavailable — showing mock data. Update <code>$host / $db / $user / $pass</code> to connect. <em>(<?= htmlspecialchars(substr($error, 0, 120)) ?>)</em></span>
    </div>
    <?php endif; ?>

    <!-- ── TOOLBAR ── -->
    <div class="ep-toolbar">
        <div class="ep-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="epSearch" placeholder="Search name, student no., email…" autocomplete="off">
        </div>

        <select class="ep-sel" id="epStatus">
            <option value="">All Statuses</option>
            <option value="enrolled">Enrolled</option>
            <option value="on_leave">On Leave</option>
            <option value="graduated">Graduated</option>
            <option value="dropped">Dropped</option>
        </select>

        <select class="ep-sel" id="epCourse">
            <option value="">All Courses</option>
            <?php foreach ($courses as $c): ?>
            <option value="<?= htmlspecialchars($c['course_code']) ?>"><?= htmlspecialchars($c['course_code']) ?></option>
            <?php endforeach; ?>
        </select>

        <select class="ep-sel" id="epYear">
            <option value="">All Year Levels</option>
            <?php for ($y=1;$y<=5;$y++): ?>
            <option value="<?= $y ?>"><?= ep_yearLabel($y) ?> Year</option>
            <?php endfor; ?>
        </select>

        <span class="ep-count">Showing <strong id="epCount"><?= count($students) ?></strong> students</span>
    </div>

    <!-- ── TABLE ── -->
    <div class="ep-table-wrap">
        <table class="ep-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Student</th>
                    <th>Student No.</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Year</th>
                    <th>Section</th>
                    <th>Enrolled</th>
                </tr>
            </thead>
            <tbody id="epBody">

            <?php if (empty($students)): ?>
            <tr><td colspan="8">
                <div class="ep-empty">
                    <div class="ep-empty__icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3>No students found</h3>
                    <p>No enrollment records are available yet.</p>
                </div>
            </td></tr>

            <?php else: foreach ($students as $i => $s):
                $name = ep_fullName($s);
                $sm   = ep_statusMeta($s['enrollment_status']);
                $fill = ($s['max_students'] && $s['section_name'])
                        ? ep_sectionFill((int)$s['section_enrolled_count'], (int)$s['max_students']) : 0;
                $fillColor = $fill >= 90 ? 'var(--red)' : ($fill >= 70 ? 'var(--amber)' : 'var(--color7)');
                $rid  = 'epd-' . $s['id'];
            ?>
            <!-- DATA ROW -->
            <tr class="ep-data-row"
                style="animation-delay:<?= $i*28 ?>ms"
                data-name="<?= htmlspecialchars(strtolower($name)) ?>"
                data-snum="<?= htmlspecialchars(strtolower($s['student_number'])) ?>"
                data-email="<?= htmlspecialchars(strtolower($s['applicant_email'])) ?>"
                data-status="<?= htmlspecialchars($s['enrollment_status']) ?>"
                data-course="<?= htmlspecialchars($s['course_code']) ?>"
                data-year="<?= (int)$s['year_level'] ?>"
                data-sex="<?= htmlspecialchars($s['sex']) ?>"
                onclick="epToggle(this,'<?= $rid ?>')"
            >
                <td><div class="ep-toggle">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                </div></td>

                <td>
                    <div class="ep-student">
                        <div class="ep-avatar <?= $s['sex']==='Female' ? 'ep-avatar--f' : '' ?>"><?= ep_initials($s) ?></div>
                        <div>
                            <div class="ep-sname"><?= htmlspecialchars($name) ?></div>
                            <div class="ep-smeta"><?= htmlspecialchars($s['sex']) ?> · <?= htmlspecialchars($s['applicant_email']) ?></div>
                        </div>
                    </div>
                </td>

                <td><span class="ep-mono"><?= htmlspecialchars($s['student_number']) ?></span></td>

                <td>
                    <span class="ep-ccode"><?= htmlspecialchars($s['course_code']) ?></span>
                    <div class="ep-cname"><?= htmlspecialchars($s['course_name']) ?></div>
                </td>

                <td><span class="ep-badge <?= $sm['cls'] ?>"><?= $sm['label'] ?></span></td>

                <td><span class="ep-year"><?= ep_yearLabel((int)$s['year_level']) ?> Yr</span></td>

                <td>
                    <?php if ($s['section_name']): ?>
                    <div class="ep-sec-tag">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>
                        <?= htmlspecialchars($s['section_name']) ?>
                    </div>
                    <div class="ep-bar"><div class="ep-bar__inner" style="width:<?= $fill ?>%;background:<?= $fillColor ?>;"></div></div>
                    <div class="ep-bar-lbl"><?= (int)$s['section_enrolled_count'] ?>/<?= (int)$s['max_students'] ?></div>
                    <?php else: ?>
                    <span style="font-size:12px;color:var(--color4);">Unassigned</span>
                    <?php endif; ?>
                </td>

                <td style="font-size:12.5px;color:var(--color5);white-space:nowrap;">
                    <?= date('M j, Y', strtotime($s['enrolled_at'])) ?>
                </td>
            </tr>

            <!-- DETAIL ROW -->
            <tr class="ep-detail-row" id="<?= $rid ?>">
                <td class="ep-detail-cell" colspan="8">
                    <div class="ep-detail-inner">
                        <div class="ep-fl">
                            <div class="ep-fl__k">Application No.</div>
                            <div class="ep-fl__v"><span class="ep-mono"><?= htmlspecialchars($s['application_number']) ?></span></div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Username</div>
                            <div class="ep-fl__v">@<?= htmlspecialchars($s['username']) ?></div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Contact Number</div>
                            <div class="ep-fl__v"><?= htmlspecialchars($s['contact_number']) ?></div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Date of Birth</div>
                            <div class="ep-fl__v">
                                <?= date('F j, Y', strtotime($s['date_of_birth'])) ?>
                                <span style="color:var(--color5);font-size:11.5px;">(<?= ep_age($s['date_of_birth']) ?> yrs old)</span>
                            </div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Civil Status</div>
                            <div class="ep-fl__v"><?= htmlspecialchars($s['civil_status']) ?></div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">City / Province</div>
                            <div class="ep-fl__v"><?= htmlspecialchars($s['address_city']) ?>, <?= htmlspecialchars($s['address_province']) ?></div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Last School Attended</div>
                            <div class="ep-fl__v">
                                <?= htmlspecialchars($s['school_last_attended']) ?>
                                <span style="color:var(--color5);font-size:11.5px;">(Grad. <?= htmlspecialchars($s['year_graduated']) ?>)</span>
                            </div>
                        </div>
                        <?php if ($s['section_name']): ?>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Academic Year</div>
                            <div class="ep-fl__v"><?= htmlspecialchars($s['academic_year']) ?> · <?= htmlspecialchars($s['semester']) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="ep-fl">
                            <div class="ep-fl__k">Course Duration</div>
                            <div class="ep-fl__v"><?= (int)$s['duration_years'] ?>-year program</div>
                        </div>
                        <div class="ep-fl">
                            <div class="ep-fl__k">User Email</div>
                            <div class="ep-fl__v" style="word-break:break-all;"><?= htmlspecialchars($s['user_email']) ?></div>
                        </div>
                    </div>
                </td>
            </tr>

            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div><!-- /ep-table-wrap -->
</div><!-- /ep -->

<script>
/* ── EXPAND / COLLAPSE ─────────────────────────────────────────── */
function epToggle(tr, rid) {
    var detail = document.getElementById(rid);
    if (!detail) return;
    var isOpen = detail.classList.contains('ep--open');
    // Close all
    document.querySelectorAll('.ep-detail-row.ep--open').forEach(function(el){ el.classList.remove('ep--open'); });
    document.querySelectorAll('.ep-data-row.ep--expanded').forEach(function(el){ el.classList.remove('ep--expanded'); });
    if (!isOpen) {
        detail.classList.add('ep--open');
        tr.classList.add('ep--expanded');
    }
}

/* ── FILTER ────────────────────────────────────────────────────── */
(function(){
    var search = document.getElementById('epSearch');
    var status = document.getElementById('epStatus');
    var course = document.getElementById('epCourse');
    var year   = document.getElementById('epYear');
    var sex    = document.getElementById('epSex');
    var count  = document.getElementById('epCount');

    function run() {
        var q  = search.value.toLowerCase().trim();
        var st = status.value, co = course.value, yr = year.value, sx = sex.value;
        var n = 0;
        document.querySelectorAll('#epBody .ep-data-row').forEach(function(row){
            var ok =
                (!q  || row.dataset.name.includes(q) || row.dataset.snum.includes(q) || row.dataset.email.includes(q)) &&
                (!st || row.dataset.status === st) &&
                (!co || row.dataset.course === co) &&
                (!yr || row.dataset.year   === yr) &&
                (!sx || row.dataset.sex    === sx);

            // figure out linked detail row from onclick attr
            var m = row.getAttribute('onclick').match(/'(epd-[^']+)'/);
            var dr = m ? document.getElementById(m[1]) : null;

            row.style.display = ok ? '' : 'none';
            if (dr && !ok) dr.classList.remove('ep--open');
            if (ok) n++;
        });
        count.textContent = n;
    }

    [search,status,course,year,sex].forEach(function(el){
        el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', run);
    });
})();

/* ── CSV EXPORT ────────────────────────────────────────────────── */
function epExportCSV() {
    var rows  = document.querySelectorAll('#epBody .ep-data-row');
    var lines = [['Name','Student Number','Course','Status','Year Level','Sex','Email']];
    rows.forEach(function(r){
        if (r.style.display === 'none') return;
        lines.push([
            r.querySelector('.ep-sname')  ? r.querySelector('.ep-sname').textContent.trim()  : '',
            r.querySelector('.ep-mono')   ? r.querySelector('.ep-mono').textContent.trim()   : '',
            (r.querySelector('.ep-ccode') ? r.querySelector('.ep-ccode').textContent.trim() : '') + ' ' +
            (r.querySelector('.ep-cname') ? r.querySelector('.ep-cname').textContent.trim() : ''),
            r.querySelector('.ep-badge')  ? r.querySelector('.ep-badge').textContent.trim()  : '',
            r.querySelector('.ep-year')   ? r.querySelector('.ep-year').textContent.trim()   : '',
            r.dataset.sex   || '',
            r.dataset.email || ''
        ]);
    });
    var csv  = lines.map(function(row){ return row.map(function(v){ return '"'+String(v).replace(/"/g,'""')+'"'; }).join(','); }).join('\n');
    var blob = new Blob([csv], {type:'text/csv'});
    var a    = document.createElement('a');
    a.href   = URL.createObjectURL(blob);
    a.download = 'enrolled_students_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}
</script>