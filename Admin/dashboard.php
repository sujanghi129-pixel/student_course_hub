<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
$isAdmin = ($_SESSION['role'] === 'admin');

require_once '../config/db.php';

// Fetch quick stats
$stats = [];

$r = $conn->query("SELECT COUNT(*) AS c FROM InterestedStudents");
$stats['students'] = $r ? $r->fetch_assoc()['c'] : 0;

$r = $conn->query("SELECT COUNT(*) AS c FROM Programmes");
$stats['programmes'] = $r ? $r->fetch_assoc()['c'] : 0;

$r = $conn->query("SELECT COUNT(*) AS c FROM Modules");
$stats['modules'] = $r ? $r->fetch_assoc()['c'] : 0;

// Recent registrations
$recent = [];
$r = $conn->query(
    "SELECT i.StudentName, i.Email, i.RegisteredAt, p.ProgrammeName
     FROM InterestedStudents i
     JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
     ORDER BY i.RegisteredAt DESC
     LIMIT 5"
);
if ($r) while ($row = $r->fetch_assoc()) $recent[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Admin · Student Course Hub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<!-- ── SIDEBAR ─────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-inner">

        <div class="sidebar-logo">
            <div class="sidebar-logo-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <span>Course<strong>Hub</strong></span>
        </div>

        <p class="sidebar-section-label">Navigation</p>
        <nav>
            <ul class="sidebar-nav">
                <li>
                    <a href="dashboard.php" class="sidebar-link active" aria-current="page">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="students.php" class="sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Students
                        <?php if ($stats['students'] > 0): ?>
                            <span class="sidebar-badge"><?php echo $stats['students']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="programmes.php" class="sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        Programmes
                    </a>
                </li>
                <li>
                    <a href="modules.php" class="sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                        Modules
                    </a>
                </li>
                <li>
                    <a href="export.php" class="sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export Students
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" class="sidebar-link sidebar-link-muted">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                View Site
            </a>
            <a href="logout.php" class="sidebar-link sidebar-logout">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </div>

    </div>
</aside>

<!-- ── MAIN ────────────────────────────────────────────── -->
<div class="main">

    <!-- topbar -->
    <header class="topbar">
        <button class="topbar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="false" aria-controls="sidebar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-breadcrumb">
            <span>Admin</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <span class="topbar-current">Dashboard</span>
        </div>
        <div class="topbar-right">
            <div class="topbar-avatar" aria-label="Admin user">A</div>
        </div>
    </header>

    <!-- page content -->
    <div class="content">

        <!-- heading -->
        <div class="page-heading">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-sub">Welcome back. Here's what's happening today.</p>
            </div>
            <a href="export.php" class="btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
        </div>

        <!-- stat cards -->
        <div class="stat-grid">

            <div class="stat-card" style="--accent:#2ee8c5;">
                <div class="stat-icon" style="background:rgba(46,232,197,0.12);color:#2ee8c5;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="stat-body">
                    <p class="stat-label">Registered Students</p>
                    <p class="stat-value"><?php echo number_format($stats['students']); ?></p>
                </div>
                <a href="students.php" class="stat-link">View all →</a>
            </div>

            <div class="stat-card" style="--accent:#5eb8f7;">
                <div class="stat-icon" style="background:rgba(94,184,247,0.12);color:#5eb8f7;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <div class="stat-body">
                    <p class="stat-label">Programmes</p>
                    <p class="stat-value"><?php echo number_format($stats['programmes']); ?></p>
                </div>
                <a href="programmes.php" class="stat-link">Manage →</a>
            </div>

            <div class="stat-card" style="--accent:#f7b955;">
                <div class="stat-icon" style="background:rgba(247,185,85,0.12);color:#f7b955;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                </div>
                <div class="stat-body">
                    <p class="stat-label">Modules</p>
                    <p class="stat-value"><?php echo number_format($stats['modules']); ?></p>
                </div>
                <a href="modules.php" class="stat-link">Manage →</a>
            </div>

            <div class="stat-card" style="--accent:#c084fc;">
                <div class="stat-icon" style="background:rgba(192,132,252,0.12);color:#c084fc;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
                <div class="stat-body">
                    <p class="stat-label">Export</p>
                    <p class="stat-value">CSV</p>
                </div>
                <a href="export.php" class="stat-link">Download →</a>
            </div>

        </div>

        <!-- quick actions + recent table -->
        <div class="dashboard-grid">

            <!-- quick actions -->
            <section class="panel" aria-labelledby="actions-title">
                <div class="panel-header">
                    <h2 class="panel-title" id="actions-title">Quick Actions</h2>
                </div>
                <div class="actions-list">
                    <a href="programmes.php?action=new" class="action-item">
                        <div class="action-icon" style="background:rgba(46,232,197,0.1);color:#2ee8c5;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </div>
                        <div>
                            <p class="action-label">Add Programme</p>
                            <p class="action-sub">Create a new degree programme</p>
                        </div>
                        <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                    <a href="modules.php?action=new" class="action-item">
                        <div class="action-icon" style="background:rgba(94,184,247,0.1);color:#5eb8f7;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </div>
                        <div>
                            <p class="action-label">Add Module</p>
                            <p class="action-sub">Create a new teaching module</p>
                        </div>
                        <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                    <a href="students.php" class="action-item">
                        <div class="action-icon" style="background:rgba(247,185,85,0.1);color:#f7b955;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <p class="action-label">View Mailing List</p>
                            <p class="action-sub">See all interested students</p>
                        </div>
                        <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                    <a href="export.php" class="action-item">
                        <div class="action-icon" style="background:rgba(192,132,252,0.1);color:#c084fc;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </div>
                        <div>
                            <p class="action-label">Export CSV</p>
                            <p class="action-sub">Download student mailing list</p>
                        </div>
                        <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </section>

            <!-- recent registrations -->
            <section class="panel" aria-labelledby="recent-title">
                <div class="panel-header">
                    <h2 class="panel-title" id="recent-title">Recent Registrations</h2>
                    <a href="students.php" class="panel-link">View all</a>
                </div>

                <?php if (empty($recent)): ?>
                    <div class="empty-state">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        <p>No registrations yet</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Programme</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent as $reg): ?>
                                    <tr>
                                        <td>
                                            <div class="student-cell">
                                                <div class="student-avatar">
                                                    <?php echo strtoupper(substr($reg['StudentName'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="student-name"><?php echo htmlspecialchars($reg['StudentName']); ?></p>
                                                    <p class="student-email"><?php echo htmlspecialchars($reg['Email']); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="prog-chip"><?php echo htmlspecialchars($reg['ProgrammeName']); ?></span>
                                        </td>
                                        <td class="date-cell">
                                            <?php echo date('d M Y', strtotime($reg['RegisteredAt'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

        </div>
    </div><!-- /content -->
</div><!-- /main -->

<!-- sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<script>
const sidebar  = document.getElementById('sidebar');
const toggle   = document.getElementById('sidebar-toggle');
const overlay  = document.getElementById('sidebar-overlay');

function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('show');
    toggle.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
}

toggle.addEventListener('click', () =>
    sidebar.classList.contains('open') ? closeSidebar() : openSidebar()
);
overlay.addEventListener('click', closeSidebar);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
</script>

</body>
</html>