<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/db.php';

// ── DELETE ──────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM InterestedStudents WHERE InterestID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: students.php?deleted=1");
    exit;
}

// ── FETCH ───────────────────────────────────────────────────
$students = $conn->query("
    SELECT i.InterestID, i.StudentName, i.Email, i.RegisteredAt,
           p.ProgrammeName, l.LevelName
    FROM InterestedStudents i
    JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
    LEFT JOIN Levels l ON p.LevelID = l.LevelID
    ORDER BY i.InterestID DESC
");

$rows       = $students ? $students->fetch_all(MYSQLI_ASSOC) : [];
$totalCount = count($rows);
$deleted    = isset($_GET['deleted']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students — Admin · Student Course Hub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        /* ── STUDENTS TOOLBAR ─────────────────────────────── */
        .students-toolbar {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            align-items: center;
            flex-wrap: wrap;
        }

        /* ── SEARCH ──────────────────────────────────────── */
        .search-wrap {
            position: relative;
            flex: 1;
            min-width: 180px;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 0.7rem;
            color: var(--muted);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 0.55rem 2.2rem 0.55rem 2.2rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--panel);
            color: var(--white);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            border-color: rgba(46,232,197,0.45);
            box-shadow: 0 0 0 3px rgba(46,232,197,0.08);
        }

        .search-input::placeholder { color: var(--muted); }

        .search-clear {
            position: absolute;
            right: 0.6rem;
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            padding: 0.15rem;
            display: flex;
            align-items: center;
        }
        .search-clear:hover { color: var(--white); }

        .result-count {
            font-size: 0.82rem;
            color: var(--muted);
            white-space: nowrap;
        }

        /* ── LEVEL BADGES ────────────────────────────────── */
        .level-badge {
            padding: 0.25rem 0.6rem;
            border-radius: 5px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .level-ug {
            background: rgba(94,184,247,0.1);
            color: var(--sky);
        }

        .level-pg {
            background: rgba(247,185,85,0.1);
            color: orange;
        }

        /* ── DELETE BUTTON ───────────────────────────────── */
        .delete-btn {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            padding: 0.4rem 0.7rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.82rem;
            transition: background 0.2s;
        }
        .delete-btn:hover { background: rgba(239,68,68,0.2); }

        /* ── TABLE ACTION COLUMN ─────────────────────────── */
        .th-action { text-align: right; }
        .data-table td:last-child { text-align: right; }

        /* ── TOAST ───────────────────────────────────────── */
        .toast {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1rem;
            border-radius: 8px;
            font-size: 0.88rem;
            margin-bottom: 1rem;
            animation: fadeIn 0.3s ease;
        }
        .toast-success {
            background: rgba(46,232,197,0.1);
            border: 1px solid rgba(46,232,197,0.3);
            color: var(--teal);
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

        /* ── MODAL ───────────────────────────────────────── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal {
            background: var(--ink);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            max-width: 380px;
            width: 90%;
            box-shadow: 0 24px 48px rgba(0,0,0,0.5);
        }

        .modal-icon {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: rgba(239,68,68,0.12);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
            color: #fca5a5;
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.5rem;
        }

        .modal-body {
            font-size: 0.88rem;
            color: var(--muted);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .modal-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        .modal-btn-cancel {
            padding: 0.55rem 1.1rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            font-size: 0.88rem;
            transition: background 0.2s, color 0.2s;
        }
        .modal-btn-cancel:hover { background: var(--panel); color: var(--white); }

        .modal-btn-delete {
            padding: 0.55rem 1.1rem;
            border-radius: 8px;
            background: rgba(239,68,68,0.85);
            color: #fff;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            transition: background 0.2s;
        }
        .modal-btn-delete:hover { background: rgba(239,68,68,1); }
    </style>
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
                    <a href="dashboard.php" class="sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="students.php" class="sidebar-link active" aria-current="page">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Students
                        <?php if ($totalCount > 0): ?>
                            <span class="sidebar-badge"><?php echo $totalCount; ?></span>
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
            <span class="topbar-current">Students</span>
        </div>
        <div class="topbar-right">
            <div class="topbar-avatar" aria-label="Admin user">A</div>
        </div>
    </header>

    <!-- content -->
    <div class="content">

        <!-- page heading -->
        <div class="page-heading">
            <div>
                <h1 class="page-title">Interested Students</h1>
                <p class="page-sub"><?php echo $totalCount; ?> student<?php echo $totalCount !== 1 ? 's' : ''; ?> registered interest</p>
            </div>
            <a href="export.php" class="btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
        </div>

        <!-- success toast -->
        <?php if ($deleted): ?>
            <div class="toast toast-success" id="toast" role="alert">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Student removed successfully.
            </div>
        <?php endif; ?>

        <!-- search + table panel -->
        <div class="panel">

            <!-- toolbar -->
            <div class="students-toolbar">
                <div class="search-wrap">
                    <svg class="search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input
                        type="text"
                        id="search"
                        placeholder="Search by name, email or programme…"
                        class="search-input"
                        oninput="filterStudents()"
                        aria-label="Search students"
                        autocomplete="off"
                    >
                    <button class="search-clear" id="search-clear" onclick="clearSearch()" aria-label="Clear search" style="display:none;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <span class="result-count" id="result-count"><?php echo $totalCount; ?> results</span>
            </div>

            <!-- table -->
            <?php if (empty($rows)): ?>
                <div class="empty-state">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <p>No students have registered interest yet.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table" id="students-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Programme</th>
                                <th>Level</th>
                                <th>Registered</th>
                                <th class="th-action">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr data-search="<?php echo strtolower(htmlspecialchars($row['StudentName'] . ' ' . $row['Email'] . ' ' . $row['ProgrammeName'])); ?>">
                                    <td>
                                        <div class="student-cell">
                                            <div class="student-avatar">
                                                <?php echo strtoupper(substr($row['StudentName'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <p class="student-name"><?php echo htmlspecialchars($row['StudentName']); ?></p>
                                                <p class="student-email"><?php echo htmlspecialchars($row['Email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="prog-chip"><?php echo htmlspecialchars($row['ProgrammeName']); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $level  = $row['LevelName'] ?? '—';
                                        $isUG   = $level === 'Undergraduate';
                                        $cls    = $isUG ? 'level-ug' : 'level-pg';
                                        ?>
                                        <span class="level-badge <?php echo $cls; ?>"><?php echo htmlspecialchars($level); ?></span>
                                    </td>
                                    <td class="date-cell">
                                        <?php echo !empty($row['RegisteredAt']) ? date('d M Y', strtotime($row['RegisteredAt'])) : '—'; ?>
                                    </td>
                                    <td>
                                        <button
                                            class="delete-btn"
                                            onclick="confirmDelete(<?php echo (int)$row['InterestID']; ?>, '<?php echo htmlspecialchars(addslashes($row['StudentName'])); ?>')"
                                            aria-label="Delete <?php echo htmlspecialchars($row['StudentName']); ?>"
                                        >
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- no-results row (hidden by default) -->
                <div class="empty-state" id="no-results" style="display:none;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <p>No students match your search.</p>
                </div>
            <?php endif; ?>

        </div><!-- /panel -->
    </div><!-- /content -->
</div><!-- /main -->

<!-- ── DELETE MODAL ─────────────────────────────────────── -->
<div class="modal-overlay" id="modal-overlay" aria-modal="true" role="dialog" aria-labelledby="modal-title" style="display:none;">
    <div class="modal">
        <div class="modal-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
        </div>
        <h3 class="modal-title" id="modal-title">Remove student?</h3>
        <p class="modal-body" id="modal-body">This will permanently delete this student from the mailing list.</p>
        <div class="modal-actions">
            <button class="modal-btn-cancel" onclick="closeModal()">Cancel</button>
            <a href="#" class="modal-btn-delete" id="modal-confirm">Delete</a>
        </div>
    </div>
</div>

<!-- sidebar overlay -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<script>
// ── Sidebar toggle ──
const sidebar = document.getElementById('sidebar');
const toggle  = document.getElementById('sidebar-toggle');
const overlay = document.getElementById('sidebar-overlay');

function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('show'); toggle.setAttribute('aria-expanded','true');  document.body.style.overflow='hidden'; }
function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('show'); toggle.setAttribute('aria-expanded','false'); document.body.style.overflow=''; }

toggle.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
overlay.addEventListener('click', closeSidebar);
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeSidebar(); closeModal(); } });

// ── Search ──
function filterStudents() {
    const q     = document.getElementById('search').value.toLowerCase().trim();
    const rows  = document.querySelectorAll('#students-table tbody tr');
    const clear = document.getElementById('search-clear');
    const count = document.getElementById('result-count');
    const noRes = document.getElementById('no-results');

    clear.style.display = q ? 'flex' : 'none';

    let visible = 0;
    rows.forEach(row => {
        const match = row.dataset.search.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    count.textContent = visible + ' result' + (visible !== 1 ? 's' : '');
    if (noRes) noRes.style.display = visible === 0 ? 'block' : 'none';
    const tableWrap = document.querySelector('.table-wrap');
    if (tableWrap) tableWrap.style.display = visible === 0 ? 'none' : '';
}

function clearSearch() {
    document.getElementById('search').value = '';
    filterStudents();
    document.getElementById('search').focus();
}

// ── Delete modal ──
const modalOverlay = document.getElementById('modal-overlay');
const modalConfirm = document.getElementById('modal-confirm');
const modalBody    = document.getElementById('modal-body');

function confirmDelete(id, name) {
    modalBody.textContent = `Remove "${name}" from the mailing list? This cannot be undone.`;
    modalConfirm.href = `students.php?delete=${id}`;
    modalOverlay.style.display = 'flex';
    document.getElementById('modal-overlay').querySelector('.modal-btn-cancel').focus();
}

function closeModal() {
    modalOverlay.style.display = 'none';
}

modalOverlay.addEventListener('click', e => { if (e.target === modalOverlay) closeModal(); });

// ── Auto-dismiss toast ──
const toast = document.getElementById('toast');
if (toast) setTimeout(() => toast.classList.add('toast-hide'), 3500);
</script>
<<<<<<< HEAD
=======

>>>>>>> a1dddadaa4f403f282a05f34584a6a0d22a38dc7

</body>
</html>