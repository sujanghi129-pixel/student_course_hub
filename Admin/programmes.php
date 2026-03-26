<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$isAdmin = ($_SESSION['role'] === 'admin');
require_once '../config/db.php';

/* ── ADD PROGRAMME ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $name  = $conn->real_escape_string($_POST['name']);
    $level = (int)$_POST['level']; // LevelID
    $desc  = $conn->real_escape_string($_POST['description']);

    $conn->query("
        INSERT INTO programmes (ProgrammeName, LevelID, Description)
        VALUES ('$name', $level, '$desc')
    ");

    header('Location: programmes.php');
    exit;
}

/* ── DELETE PROGRAMME ── */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $conn->query("DELETE FROM programmes WHERE ProgrammeID = $id");

    header('Location: programmes.php');
    exit;
}

/* ── FETCH LEVELS (IMPORTANT) ── */
$levels = $conn->query("SELECT * FROM levels");

/* ── FETCH PROGRAMMES WITH JOIN ── */
$programmes = $conn->query("
    SELECT p.*, l.LevelName
    FROM programmes p
    LEFT JOIN levels l ON p.LevelID = l.LevelID
    ORDER BY p.ProgrammeID DESC
");

$rows = $programmes ? $programmes->fetch_all(MYSQLI_ASSOC) : [];
$total = count($rows);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Programmes — Admin · Student Course Hub</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/dashboard.css">
<style>
    /* Form inputs */
    .search-input {
        width: 100%;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 10px;
        padding: 0.65rem 1rem;
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.18s;
    }
    .search-input:focus { border-color: var(--teal); }
    .search-input option { background: var(--ink); }

    textarea.search-input { resize: vertical; min-height: 90px; }

    /* Toolbar */
    .students-toolbar {
        padding: 0.9rem 1.3rem;
        border-bottom: 1px solid var(--border);
    }
    .result-count {
        font-size: 0.85rem;
        color: var(--muted);
        font-weight: 500;
    }

    /* Level badges */
    .level-badge {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.2rem 0.65rem;
        border-radius: 999px;
        white-space: nowrap;
    }
    .level-ug {
        background: rgba(94,184,247,0.12);
        border: 1px solid rgba(94,184,247,0.25);
        color: var(--sky);
    }
    .level-pg {
        background: rgba(247,185,85,0.12);
        border: 1px solid rgba(247,185,85,0.25);
        color: var(--amber);
    }

    /* Delete button */
    .delete-btn {
        display: inline-block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #ff8a8a;
        border: 1px solid rgba(255,90,90,0.3);
        border-radius: 8px;
        padding: 0.3rem 0.8rem;
        text-decoration: none;
        transition: background 0.18s, border-color 0.18s;
    }
    .delete-btn:hover {
        background: rgba(255,90,90,0.1);
        border-color: rgba(255,90,90,0.5);
    }

    /* Panel padding override for form */
    .panel + .panel { margin-top: 1.5rem; }
    .panel-form { padding: 1.2rem 1.3rem; }
    .panel-form h3 {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        color: var(--white);
        margin-bottom: 1rem;
    }
</style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-inner">

        <div class="sidebar-logo">
            <div class="sidebar-logo-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                    <path d="M6 12v5c3 3 9 3 12 0v-5" />
                </svg>
            </div>
            <span>Niels<strong> Brock University</strong></span>
        </div>

        <p class="sidebar-section-label">Navigation</p>
        <nav>
            <ul class="sidebar-nav">
                <li>
                    <a href="dashboard.php" class="sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="14" y="14" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="students.php" class="sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        Students
                    </a>
                </li>
                <li>
                    <a href="programmes.php" class="sidebar-link active" aria-current="page">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                        </svg>
                        Programmes
                    </a>
                </li>
                <li>
                        <a href="staff.php" class="sidebar-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                            </svg>
                            👨‍🏫 Staff
                        </a>
                    </li>
                    <a href="create_admin.php" class="sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                        </svg>
                        ➕ Create User
                    </a>
                </li>
                <li>
                    <a href="modules.php" class="sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="16 18 22 12 16 6" />
                            <polyline points="8 6 2 12 8 18" />
                        </svg>
                        Modules
                    </a>
                </li>
                <li>
                    <a href="export.php" class="sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        Export Students
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" class="sidebar-link sidebar-link-muted">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                View Site
            </a>
            <a href="logout.php" class="sidebar-link sidebar-logout">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                Logout
            </a>
        </div>

    </div>
</aside>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <button class="topbar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar" aria-expanded="false"
            aria-controls="sidebar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6" />
                <line x1="3" y1="12" x2="21" y2="12" />
                <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
        </button>
        <div class="topbar-breadcrumb">
            <span>Admin</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6" />
            </svg>
            <span class="topbar-current">Programmes</span>
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
                    <h1 class="page-title">Programmes</h1>
                    <p class="page-sub"><?php echo $total; ?> programme<?php echo $total !== 1 ? 's' : ''; ?></p>
                </div>
            </div>

            <!-- ADD FORM -->
            <div class="panel panel-form">
                <h3>Add New Programme</h3>

                <form method="post" style="display:grid; gap:0.7rem; margin-top:1rem;">

                    <input type="text" name="name"
                           placeholder="Programme name"
                           required class="search-input">

                    <select name="level" class="search-input" required>

                        <?php while($lvl = $levels->fetch_assoc()): ?>

                            <option value="<?php echo $lvl['LevelID']; ?>">
                                <?php echo htmlspecialchars($lvl['LevelName']); ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                    <textarea name="description"
                              placeholder="Description..."
                              class="search-input"></textarea>

                    <button class="btn-primary">Add Programme</button>
                </form>
            </div>

            <!-- TABLE -->
            <div class="panel">

                <div class="students-toolbar">
                    <span class="result-count"><?php echo $total; ?> results</span>
                </div>

                <?php if (empty($rows)): ?>
                    <div class="empty-state">
                        <p>No programmes found.</p>
                    </div>
                <?php else: ?>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Programme</th>
                                <th>Level</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?php echo $row['ProgrammeID']; ?></td>

                                <td>
                                    <span class="prog-chip">
                                        <?php echo htmlspecialchars($row['ProgrammeName']); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    $level = $row['LevelName'] ?? '—';
                                    $cls   = $level === 'Undergraduate' ? 'level-ug' : 'level-pg';
                                    ?>
                                    <span class="level-badge <?php echo $cls; ?>">
                                        <?php echo htmlspecialchars($level); ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="programmes.php?delete=<?php echo $row['ProgrammeID']; ?>"
                                       class="delete-btn"
                                       onclick="return confirm('Delete programme?')">
                                       Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php endif; ?>

            </div><!-- /panel -->

        </div><!-- /content -->
    </div><!-- /main -->

<!-- sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    const overlay = document.getElementById('sidebar-overlay');

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
