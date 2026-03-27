<?php
include("config/db.php");

// Validate Programme ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "<p>Invalid programme ID.</p>";
    exit;
}
$programmeId = (int)$_GET['id'];

// Detect which optional columns exist
$columnQuery = $conn->query("SHOW COLUMNS FROM Programmes");
$columns = [];
while ($col = $columnQuery->fetch_assoc()) {
    $columns[] = $col['Field'];
}

$requiredColumns = ['ProgrammeName', 'Description'];
$optionalColumns = ['Duration', 'EntryRequirements', 'LearningOutcomes', 'CareerPaths', 'Image'];
$selectColumns   = $requiredColumns;
foreach ($optionalColumns as $opt) {
    if (in_array($opt, $columns)) $selectColumns[] = $opt;
}

// Also pull level name & leader name
$stmt = $conn->prepare(
    "SELECT " . implode(', ', array_map(fn($c) => "p.$c", $selectColumns)) . ",
            l.LevelName,
            s.Name AS LeaderName
     FROM Programmes p
     LEFT JOIN Levels l ON p.LevelID = l.LevelID
     LEFT JOIN Staff  s ON p.ProgrammeLeaderID = s.StaffID
     WHERE p.ProgrammeID = ?"
);
$stmt->bind_param("i", $programmeId);
$stmt->execute();
$programme = $stmt->get_result()->fetch_assoc();

if (!$programme) {
    http_response_code(404);
    echo "<p>Programme not found.</p>";
    exit;
}

// Fallbacks
$programmeImage = !empty($programme['Image']) 
    ? 'images/banners/' . $programme['Image'] 
    : 'images/programme-placeholder.jpg';
$programmeDescription      = !empty($programme['Description'])         ? $programme['Description']         : 'No description available yet. Please check back soon.';
$programmeDuration         = !empty($programme['Duration'])            ? $programme['Duration']            : '3 Years';
$programmeEntryReq         = !empty($programme['EntryRequirements'])   ? $programme['EntryRequirements']   : 'Completed secondary education, personal statement, and two references. Contact admissions for programme-specific requirements.';
$programmeLearningOutcomes = !empty($programme['LearningOutcomes'])    ? $programme['LearningOutcomes']    : 'Graduates will apply theory to practice, solve complex problems, and demonstrate professional skills in the workplace.';
$programmeCareerPaths      = !empty($programme['CareerPaths'])         ? $programme['CareerPaths']         : 'Suitable careers include management, specialist practitioner, consultancy, and further academic research.';
$levelName                 = $programme['LevelName'] ?? 'Programme';
$leaderName                = $programme['LeaderName'] ?? 'TBA';
$isUG                      = ($levelName === 'Undergraduate');

// Fetch modules grouped by year, with module leader
$stmt2 = $conn->prepare(
    "SELECT m.ModuleID, m.ModuleName, m.Description AS ModuleDesc, pm.Year, s.Name AS ModuleLeader
     FROM ProgrammeModules pm
     JOIN Modules m  ON pm.ModuleID   = m.ModuleID
     LEFT JOIN Staff s ON m.ModuleLeaderID = s.StaffID
     WHERE pm.ProgrammeID = ?
     ORDER BY pm.Year, m.ModuleName"
);
$stmt2->bind_param("i", $programmeId);
$stmt2->execute();
$modulesResult = $stmt2->get_result();

$modulesByYear = [];
$totalModules  = 0;
while ($m = $modulesResult->fetch_assoc()) {
    $modulesByYear[$m['Year']][] = $m;
    $totalModules++;
}

$stmt->close();
$stmt2->close();

// Fetch other programmes that share any module with this one
// Result: ModuleID => [ ['ProgrammeID'=>..., 'ProgrammeName'=>...], ... ]
$stmt3 = $conn->prepare(
    "SELECT pm2.ModuleID,
            p2.ProgrammeID,
            p2.ProgrammeName
     FROM   ProgrammeModules pm2
     JOIN   Programmes p2 ON pm2.ProgrammeID = p2.ProgrammeID
     WHERE  pm2.ModuleID IN (
                SELECT ModuleID FROM ProgrammeModules WHERE ProgrammeID = ?
            )
       AND  pm2.ProgrammeID <> ?
     ORDER  BY p2.ProgrammeName"
);
$stmt3->bind_param("ii", $programmeId, $programmeId);
$stmt3->execute();
$sharedResult = $stmt3->get_result();

$sharedProgrammes = []; // [ moduleId => [ ['ProgrammeID'=>..,'ProgrammeName'=>..], .. ] ]
while ($row = $sharedResult->fetch_assoc()) {
    $sharedProgrammes[(int)$row['ModuleID']][] = [
        'ProgrammeID'   => $row['ProgrammeID'],
        'ProgrammeName' => $row['ProgrammeName'],
    ];
}
$stmt3->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($programme['ProgrammeName']); ?> — Student Course Hub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <style>
    /* ── SHARED-MODULE BADGES ── */
    .pd-shared-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        margin-top: 0.55rem;
    }
    .pd-shared-label {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--muted);
        white-space: nowrap;
    }
    .pd-shared-badge {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 500;
        padding: 0.18rem 0.6rem;
        border-radius: 999px;
        background: rgba(94,184,247,0.10);
        color: var(--sky);
        border: 1px solid rgba(94,184,247,0.28);
        text-decoration: none;
        transition: background var(--transition), border-color var(--transition);
        white-space: nowrap;
    }
    .pd-shared-badge:hover {
        background: rgba(94,184,247,0.22);
        border-color: rgba(94,184,247,0.55);
    }

    /* ── PAGE-SPECIFIC OVERRIDES ── */

    /* Hero banner */
    .pd-hero {
        position: relative;
        min-height: 340px;
        display: flex;
        align-items: flex-end;
        overflow: hidden;
        padding: 0;
    }

    .pd-hero-img {
        position: absolute;
        inset: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        object-position: center;
        filter: brightness(0.35) saturate(0.7);
        transition: transform 8s ease;
    }
    .pd-hero:hover .pd-hero-img { transform: scale(1.04); }

    /* gradient over image */
    .pd-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to bottom,
            rgba(5,13,26,0.1) 0%,
            rgba(5,13,26,0.55) 55%,
            rgba(5,13,26,0.97) 100%
        );
    }

    .pd-hero-content {
        position: relative;
        z-index: 1;
        width: 100%;
        padding: 2.5rem clamp(1.5rem, 8vw, 5rem) 2.8rem;
    }

    .pd-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: rgba(255,255,255,0.55);
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .pd-breadcrumb a {
        color: var(--teal);
        text-decoration: none;
        transition: opacity var(--transition);
    }
    .pd-breadcrumb a:hover { opacity: 0.75; }
    .pd-breadcrumb span { color: rgba(255,255,255,0.35); }

    .pd-level-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 0.3rem 0.85rem;
        border-radius: 999px;
        margin-bottom: 1rem;
    }
    .pd-badge-ug {
        background: rgba(94,184,247,0.18);
        color: var(--sky);
        border: 1px solid rgba(94,184,247,0.4);
    }
    .pd-badge-pg {
        background: rgba(247,185,85,0.18);
        color: var(--amber);
        border: 1px solid rgba(247,185,85,0.4);
    }

    .pd-title {
        font-family: 'Syne', sans-serif;
        font-size: clamp(2rem, 5vw, 3.4rem);
        font-weight: 800;
        color: var(--white);
        letter-spacing: -0.03em;
        line-height: 1.05;
        margin-bottom: 1.2rem;
        max-width: 720px;
    }

    .pd-stat-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.2rem;
    }

    .pd-stat {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.88rem;
        color: rgba(255,255,255,0.7);
        font-weight: 500;
    }
    .pd-stat strong { color: var(--white); font-weight: 700; }
    .pd-stat svg { opacity: 0.6; flex-shrink: 0; }

    /* ── BODY LAYOUT ── */
    .pd-body {
        max-width: 1100px;
        margin: 0 auto;
        padding: 3rem clamp(1.5rem, 5vw, 3rem) 5rem;
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 2.5rem;
        align-items: start;
    }

    /* ── MAIN COLUMN ── */
    .pd-main { min-width: 0; }

    .pd-section {
        margin-bottom: 2.8rem;
        animation: fadeUp 0.5s ease both;
    }
    .pd-section:nth-child(1) { animation-delay: 0.05s; }
    .pd-section:nth-child(2) { animation-delay: 0.12s; }
    .pd-section:nth-child(3) { animation-delay: 0.18s; }
    .pd-section:nth-child(4) { animation-delay: 0.24s; }

    .pd-section-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--teal);
        margin-bottom: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }
    .pd-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(46,232,197,0.35), transparent);
    }

    .pd-section-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--white);
        letter-spacing: -0.02em;
        margin-bottom: 0.85rem;
    }

    .pd-prose {
        color: var(--text);
        font-size: 0.97rem;
        line-height: 1.78;
        font-weight: 300;
    }

    /* ── MODULES ── */
    .pd-year-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1.4rem;
    }

    .pd-year-tab {
        background: var(--panel);
        border: 1px solid var(--border);
        color: var(--muted);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.83rem;
        font-weight: 600;
        padding: 0.4rem 1rem;
        border-radius: 999px;
        cursor: pointer;
        transition: all var(--transition);
        letter-spacing: 0.02em;
    }
    .pd-year-tab:hover,
    .pd-year-tab.active {
        background: rgba(46,232,197,0.1);
        border-color: var(--teal);
        color: var(--teal);
    }

    .pd-year-panel { display: none; }
    .pd-year-panel.active { display: block; }

    .pd-module-list {
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }

    .pd-module-item {
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1rem 1.2rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        transition: border-color var(--transition), transform var(--transition), box-shadow var(--transition);
        cursor: default;
    }
    .pd-module-item:hover {
        border-color: rgba(46,232,197,0.28);
        transform: translateX(4px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }

    .pd-module-icon {
        width: 36px; height: 36px;
        border-radius: 8px;
        background: linear-gradient(135deg, rgba(46,232,197,0.15), rgba(94,184,247,0.12));
        border: 1px solid rgba(46,232,197,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.9rem;
    }

    .pd-module-info { flex: 1; min-width: 0; }

    .pd-module-name {
        font-family: 'Syne', sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--white);
        margin-bottom: 0.2rem;
        line-height: 1.3;
    }

    .pd-module-leader {
        font-size: 0.78rem;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* ── SIDEBAR ── */
    .pd-sidebar {
        position: sticky;
        top: 88px;
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
        animation: fadeUp 0.5s 0.3s ease both;
    }

    .pd-card {
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.4rem;
        transition: border-color var(--transition);
    }
    .pd-card:hover { border-color: rgba(46,232,197,0.2); }

    .pd-card-title {
        font-family: 'Syne', sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 1.1rem;
    }

    .pd-info-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid var(--border);
        font-size: 0.88rem;
    }
    .pd-info-row:last-child { border-bottom: none; padding-bottom: 0; }
    .pd-info-row:first-of-type { padding-top: 0; }

    .pd-info-label {
        color: var(--muted);
        font-weight: 500;
        white-space: nowrap;
    }
    .pd-info-value {
        color: var(--white);
        font-weight: 600;
        text-align: right;
    }

    /* CTA card */
    .pd-cta-card {
        background: linear-gradient(145deg, rgba(46,232,197,0.08), rgba(94,184,247,0.06));
        border: 1px solid rgba(46,232,197,0.25);
        border-radius: var(--radius);
        padding: 1.6rem;
        text-align: center;
    }

    .pd-cta-card h3 {
        font-family: 'Syne', sans-serif;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--white);
        margin-bottom: 0.5rem;
    }

    .pd-cta-card p {
        font-size: 0.85rem;
        color: var(--muted);
        line-height: 1.55;
        margin-bottom: 1.2rem;
    }

    .pd-cta-btn {
        display: block;
        width: 100%;
        padding: 0.8rem;
        border-radius: 999px;
        border: none;
        font-family: 'DM Sans', sans-serif;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        transition: transform var(--transition), box-shadow var(--transition), opacity var(--transition);
        margin-bottom: 0.6rem;
    }

    .pd-cta-btn-primary {
        background: linear-gradient(135deg, var(--teal), var(--sky));
        color: var(--navy);
        box-shadow: 0 6px 20px rgba(46,232,197,0.25);
    }
    .pd-cta-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(46,232,197,0.38);
    }

    .pd-cta-btn-ghost {
        background: var(--panel);
        color: var(--text);
        border: 1px solid var(--border);
    }
    .pd-cta-btn-ghost:hover {
        border-color: rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.07);
        transform: translateY(-1px);
    }

    /* leader card */
    .pd-leader {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .pd-leader-avatar {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--teal), var(--sky));
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 1rem;
        color: var(--navy);
        flex-shrink: 0;
    }
    .pd-leader-info p:first-child {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 0.92rem;
        color: var(--white);
        margin-bottom: 0.1rem;
    }
    .pd-leader-info p:last-child {
        font-size: 0.78rem;
        color: var(--muted);
    }

    /* next steps */
    .pd-steps {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .pd-step {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.88rem;
        color: var(--text);
        line-height: 1.5;
    }
    .pd-step-num {
        width: 22px; height: 22px;
        border-radius: 50%;
        background: rgba(46,232,197,0.12);
        border: 1px solid rgba(46,232,197,0.3);
        color: var(--teal);
        font-size: 0.72rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .pd-step a { color: var(--teal); text-decoration: none; }
    .pd-step a:hover { text-decoration: underline; }

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
        .pd-body {
            grid-template-columns: 1fr;
        }
        .pd-sidebar {
            position: static;
        }
    }

    @media (max-width: 560px) {
        .pd-hero { min-height: 280px; }
        .pd-title { font-size: 1.8rem; }
        .pd-stat-row { gap: 0.8rem; }
        .pd-body { padding: 2rem 1.25rem 4rem; }
        .pd-module-item { flex-direction: column; gap: 0.6rem; }
    }
    </style>
</head>
<body>

<!-- ── NAV (matches index.php) ── -->
<nav class="nav">
    <a href="index.php" class="nav-logo">Student <span>Course Hub</span></a>
    <input type="checkbox" id="nav-toggle" class="nav-toggle" style="display:none;">
    <label for="nav-toggle" class="nav-toggle-label" aria-label="Toggle menu">&#9776;</label>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="index.php#courses">Courses</a></li>
        <li><a href="index.php#register">Register</a></li>
        <li><a href="index.php#contact">Contact</a></li>
        <li><a href="Admin/login.php" class="nav-admin">Admin</a></li>
    </ul>
</nav>

<!-- ── HERO BANNER ── -->
<header class="pd-hero">
    <img
        src="<?php echo htmlspecialchars($programmeImage); ?>"
        alt="<?php echo htmlspecialchars($programme['ProgrammeName']); ?> banner image"
        class="pd-hero-img"
        onerror="this.src='images/programme-placeholder.jpg'"
    >
    <div class="pd-hero-content">

        <nav class="pd-breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <a href="index.php#courses">Programmes</a>
            <span>›</span>
            <span style="color:rgba(255,255,255,0.7);"><?php echo htmlspecialchars($programme['ProgrammeName']); ?></span>
        </nav>

        <span class="pd-level-badge <?php echo $isUG ? 'pd-badge-ug' : 'pd-badge-pg'; ?>">
            <?php echo $isUG ? '🎓' : '🏅'; ?>
            <?php echo htmlspecialchars($levelName); ?>
        </span>

        <h1 class="pd-title"><?php echo htmlspecialchars($programme['ProgrammeName']); ?></h1>

        <div class="pd-stat-row">
            <div class="pd-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <strong><?php echo htmlspecialchars($programmeDuration); ?></strong>&nbsp;duration
            </div>
            <div class="pd-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <strong><?php echo $totalModules; ?></strong>&nbsp;modules
            </div>
            <div class="pd-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Led by&nbsp;<strong><?php echo htmlspecialchars($leaderName); ?></strong>
            </div>
            <div class="pd-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo count($modulesByYear); ?>&nbsp;<strong>year<?php echo count($modulesByYear) !== 1 ? 's' : ''; ?></strong>&nbsp;of study
            </div>
        </div>

    </div>
</header>

<!-- ── BODY ── -->
<div class="pd-body">

    <!-- ── MAIN COLUMN ── -->
    <main class="pd-main">

        <!-- Overview -->
        <section class="pd-section" aria-labelledby="overview-title">
            <p class="pd-section-label">Overview</p>
            <h2 class="pd-section-title" id="overview-title">About this programme</h2>
            <p class="pd-prose"><?php echo nl2br(htmlspecialchars($programmeDescription)); ?></p>
        </section>

        <!-- Modules by year -->
        <section class="pd-section" aria-labelledby="modules-title">
            <p class="pd-section-label">Curriculum</p>
            <h2 class="pd-section-title" id="modules-title">Modules by year</h2>

            <?php if (empty($modulesByYear)): ?>
                <p class="pd-prose">No modules are listed for this programme yet.</p>
            <?php else: ?>
                <!-- Year tabs -->
                <div class="pd-year-tabs" role="tablist" aria-label="Module years">
                    <?php $first = true; foreach ($modulesByYear as $year => $mods): ?>
                        <button
                            class="pd-year-tab<?php echo $first ? ' active' : ''; ?>"
                            role="tab"
                            aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
                            aria-controls="year-panel-<?php echo $year; ?>"
                            data-year="<?php echo $year; ?>"
                        >Year <?php echo htmlspecialchars($year); ?>
                            <span style="opacity:0.55;font-weight:400;"> · <?php echo count($mods); ?></span>
                        </button>
                    <?php $first = false; endforeach; ?>
                </div>

                <!-- Year panels -->
                <?php $first = true; foreach ($modulesByYear as $year => $mods): ?>
                    <div
                        class="pd-year-panel<?php echo $first ? ' active' : ''; ?>"
                        id="year-panel-<?php echo $year; ?>"
                        role="tabpanel"
                    >
                        <div class="pd-module-list">
                            <?php foreach ($mods as $i => $mod): ?>
                                <div class="pd-module-item">
                                    <div class="pd-module-icon" aria-hidden="true">
                                        <?php
                                        $icons = ['📐','💻','🔐','🤖','📊','🌐','🔬','⚙️','📱','🧠'];
                                        echo $icons[$i % count($icons)];
                                        ?>
                                    </div>
                                    <div class="pd-module-info">
                                        <p class="pd-module-name"><?php echo htmlspecialchars($mod['ModuleName']); ?></p>
                                        <?php if (!empty($mod['ModuleLeader'])): ?>
                                            <p class="pd-module-leader">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                                <?php echo htmlspecialchars($mod['ModuleLeader']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($mod['ModuleDesc'])): ?>
                                            <p style="font-size:0.8rem;color:var(--muted);margin-top:0.3rem;line-height:1.5;">
                                                <?php echo htmlspecialchars(mb_strimwidth($mod['ModuleDesc'], 0, 120, '…')); ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php
                                        $mid = (int)$mod['ModuleID'];
                                        if (!empty($sharedProgrammes[$mid])):
                                        ?>
                                        <div class="pd-shared-row" aria-label="Also taught in">
                                            <span class="pd-shared-label">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                                Also in
                                            </span>
                                            <?php foreach ($sharedProgrammes[$mid] as $sp): ?>
                                                <a href="programme-details.php?id=<?php echo (int)$sp['ProgrammeID']; ?>"
                                                   class="pd-shared-badge"
                                                   title="View <?php echo htmlspecialchars($sp['ProgrammeName']); ?>">
                                                    <?php echo htmlspecialchars($sp['ProgrammeName']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php $first = false; endforeach; ?>
            <?php endif; ?>
        </section>

        <!-- What you'll learn -->
        <section class="pd-section" aria-labelledby="outcomes-title">
            <p class="pd-section-label">Learning outcomes</p>
            <h2 class="pd-section-title" id="outcomes-title">What you will learn</h2>
            <p class="pd-prose"><?php echo nl2br(htmlspecialchars($programmeLearningOutcomes)); ?></p>
        </section>

        <!-- Careers -->
        <section class="pd-section" aria-labelledby="careers-title">
            <p class="pd-section-label">After graduation</p>
            <h2 class="pd-section-title" id="careers-title">Career opportunities</h2>
            <p class="pd-prose"><?php echo nl2br(htmlspecialchars($programmeCareerPaths)); ?></p>
        </section>

    </main>

    <!-- ── SIDEBAR ── -->
    <aside class="pd-sidebar" aria-label="Programme info">

        <!-- CTA -->
        <div class="pd-cta-card">
            <h3>Ready to apply?</h3>
            <p>Join prospective students interested in this programme and get updates on open days and deadlines.</p>
            <a href="index.php#register" class="pd-cta-btn pd-cta-btn-primary">Register Interest</a>
            <a href="brochure.pdf" class="pd-cta-btn pd-cta-btn-ghost">📄 Download Brochure</a>
        </div>

        <!-- Quick facts -->
        <div class="pd-card">
            <p class="pd-card-title">Programme facts</p>
            <div class="pd-info-row">
                <span class="pd-info-label">Level</span>
                <span class="pd-info-value"><?php echo htmlspecialchars($levelName); ?></span>
            </div>
            <div class="pd-info-row">
                <span class="pd-info-label">Duration</span>
                <span class="pd-info-value"><?php echo htmlspecialchars($programmeDuration); ?></span>
            </div>
            <div class="pd-info-row">
                <span class="pd-info-label">Total modules</span>
                <span class="pd-info-value"><?php echo $totalModules; ?></span>
            </div>
            <div class="pd-info-row">
                <span class="pd-info-label">Years of study</span>
                <span class="pd-info-value"><?php echo count($modulesByYear); ?></span>
            </div>
        </div>

        <!-- Programme leader -->
        <div class="pd-card">
            <p class="pd-card-title">Programme leader</p>
            <div class="pd-leader">
                <div class="pd-leader-avatar" aria-hidden="true">
                    <?php echo strtoupper(substr(explode(' ', $leaderName)[count(explode(' ', $leaderName)) - 1], 0, 2)); ?>
                </div>
                <div class="pd-leader-info">
                    <p><?php echo htmlspecialchars($leaderName); ?></p>
                    <p>Programme Director</p>
                </div>
            </div>
        </div>

        <!-- Entry requirements -->
        <div class="pd-card">
            <p class="pd-card-title">Entry requirements</p>
            <p style="font-size:0.87rem;color:var(--text);line-height:1.6;">
                <?php echo nl2br(htmlspecialchars($programmeEntryReq)); ?>
            </p>
        </div>

        <!-- Next steps -->
        <div class="pd-card">
            <p class="pd-card-title">Next steps</p>
            <div class="pd-steps">
                <div class="pd-step">
                    <span class="pd-step-num">1</span>
                    <span>Review the <a href="apply.php">application instructions</a></span>
                </div>
                <div class="pd-step">
                    <span class="pd-step-num">2</span>
                    <span>Prepare your transcript, CV, and references</span>
                </div>
                <div class="pd-step">
                    <span class="pd-step-num">3</span>
                    <span>Contact <a href="mailto:admissions@university.example">admissions</a> with questions</span>
                </div>
                <div class="pd-step">
                    <span class="pd-step-num">4</span>
                    <span>Submit your application via the student portal</span>
                </div>
            </div>
        </div>

    </aside>

</div>

<!-- ── FOOTER ── -->
<footer class="footer" id="contact">
    <div class="footer-grid">
        <div>
            <p class="footer-brand">Student <span>Course Hub</span></p>
            <p class="footer-desc">Your gateway to undergraduate and postgraduate programmes. Explore, compare, and register your interest in seconds.</p>
        </div>
        <div>
            <p class="footer-heading">Quick Links</p>
            <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#courses">Courses</a></li>
                <li><a href="index.php#register">Register</a></li>
                <li><a href="Admin/login.php">Admin</a></li>
            </ul>
        </div>
        <div>
            <p class="footer-heading">Contact</p>
            <address class="footer-address">
                College of Computer Science<br>
                Sankt Petri Passage 5<br>
                1165 København K<br>
                Copenhagen, Denmark
            </address>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 Student Course Hub · All rights reserved</p>
        <p>Built for CTEC2712</p>
    </div>
</footer>

<script>
// Tab switching for module years
document.querySelectorAll('.pd-year-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const year = tab.dataset.year;

        // Update tabs
        document.querySelectorAll('.pd-year-tab').forEach(t => {
            t.classList.remove('active');
            t.setAttribute('aria-selected', 'false');
        });
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');

        // Update panels
        document.querySelectorAll('.pd-year-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('year-panel-' + year).classList.add('active');
    });
});
</script>

</body>
</html>