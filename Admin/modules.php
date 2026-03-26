<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
$isAdmin = ($_SESSION['role'] === 'admin');

// handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $conn->query("INSERT INTO Modules (ModuleName) VALUES ('$name')");
    header('Location: modules.php');
    exit;
}

// handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM Modules WHERE ModuleID=$id");
    header('Location: modules.php');
    exit;
}

$modules = $conn->query('SELECT * FROM Modules');

// Fetch student count for sidebar badge
$r = $conn->query("SELECT COUNT(*) AS c FROM InterestedStudents");
$studentCount = $r ? $r->fetch_assoc()['c'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modules — Admin · Student Course Hub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">

    <style>
        /* ── Modules page extras ───────────────────────────── */
        .mod-form-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .mod-form-card h2 {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: var(--white);
        }
        .mod-form-row {
            display: flex;
            gap: .75rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .mod-form-row label {
            font-size: .8rem;
            color: var(--muted);
            display: flex;
            flex-direction: column;
            gap: .35rem;
            flex: 1 1 260px;
        }
        .mod-form-row input[type="text"] {
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .55rem .85rem;
            color: var(--text);
            font-size: .9rem;
            outline: none;
            transition: border-color .2s;
        }
        .mod-form-row input[type="text"]:focus {
            border-color: var(--teal);
        }
        .btn-add {
            background: var(--teal);
            color: #050d1a;
            font-weight: 700;
            font-size: .85rem;
            padding: .6rem 1.4rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity .2s;
            white-space: nowrap;
        }
        .btn-add:hover { opacity: .85; }

        .mod-table-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .mod-table-card h2 {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem;
            color: var(--white);
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .mod-table-card .table-wrap { overflow-x: auto; }
        .mod-table-card .data-table { width: 100%; border-collapse: collapse; }
        .mod-table-card .data-table th {
            text-align: left;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
            padding: .75rem 1.5rem;
            background: rgba(255,255,255,0.03);
            border-bottom: 1px solid var(--border);
        }
        .mod-table-card .data-table td {
            padding: .9rem 1.5rem;
            font-size: .88rem;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }
        .mod-table-card .data-table tbody tr:last-child td { border-bottom: none; }
        .mod-table-card .data-table tbody tr:hover { background: rgba(255,255,255,0.03); }

        .btn-delete {
            display: inline-block;
            padding: .3rem .85rem;
            border: 1px solid #f87171;
            border-radius: 6px;
            color: #f87171;
            font-size: .8rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s, color .2s;
        }
        .btn-delete:hover { background: #f87171; color: #fff; }
    </style>
</head>
<body>

    <!-- ── SIDEBAR ─────────────────────────────────────────── -->
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
                            <?php if ($studentCount > 0): ?>
                                <span class="sidebar-badge"><?php echo $studentCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="programmes.php" class="sidebar-link">
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
                    <li>
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
                        <a href="modules.php" class="sidebar-link active" aria-current="page">
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

    <!-- ── MAIN ────────────────────────────────────────────── -->
    <div class="main">

        <!-- topbar -->
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
                <span class="topbar-current">Modules</span>
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
                    <h1 class="page-title">Modules</h1>
                    <p class="page-sub">Create and manage teaching modules.</p>
                </div>
            </div>

            <!-- Add new module -->
            <div class="mod-form-card">
                <h2>Add new module</h2>
                <form method="post" action="modules.php">
                    <div class="mod-form-row">
                        <label>
                            Name
                            <input type="text" name="name" placeholder="e.g. Introduction to Programming" required>
                        </label>
                        <button type="submit" class="btn-add">Add</button>
                    </div>
                </form>
            </div>

            <!-- Existing modules table -->
            <div class="mod-table-card">
                <h2>Existing modules</h2>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $modules->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['ModuleID']; ?></td>
                                <td><?php echo htmlspecialchars($row['ModuleName']); ?></td>
                                <td>
                                    <a class="btn-delete"
                                       href="modules.php?delete=<?php echo $row['ModuleID']; ?>"
                                       onclick="return confirm('Delete module?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /content -->
    </div><!-- /main -->

    <!-- sidebar overlay for mobile -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggle  = document.getElementById('sidebar-toggle');
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
