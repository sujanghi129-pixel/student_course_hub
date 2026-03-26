<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Course Hub</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<!-- ─── NAVIGATION ──────────────────────────────────────────── -->
<nav class="nav">
    <a href="index.php" class="nav-logo">Student <span>Course Hub</span></a>
    <input type="checkbox" id="nav-toggle" class="nav-toggle" style="display:none;">
    <label for="nav-toggle" class="nav-toggle-label" aria-label="Toggle menu">&#9776;</label>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="#courses">Courses</a></li>
        <li><a href="#register">Register</a></li>
        <li><a href="#withdraw">Withdraw</a></li>
        <li><a href="#contact">Contact</a></li>
        <li><a href="Admin/login.php" class="nav-admin">Admin</a></li>
    </ul>
</nav>

<!-- ─── HERO ────────────────────────────────────────────────── -->
<header class="hero">
    <p class="hero-eyebrow">University of Computer Science</p>
    <h1 class="hero-title">Find your <em>perfect</em> programme</h1>
    <p class="hero-sub">Explore undergraduate and postgraduate programmes designed to launch your career in technology.</p>
    <div class="hero-actions">
        <a href="#courses" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Browse Programmes
        </a>
        <a href="#register" class="btn btn-ghost">Register Interest</a>
    </div>
</header>

<!-- ─── SEARCH ───────────────────────────────────────────────── -->
<section class="search-section" id="courses">
    <div class="search-wrapper">
        <label class="search-label" for="search-input">Search programmes</label>
        <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="search-bar">
                <input
                    type="text"
                    id="search-input"
                    name="q"
                    value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                    placeholder="e.g. Cyber Security, Machine Learning…"
                    aria-label="Search programmes"
                >
                <button type="submit">Search</button>
            </div>
            <?php $currentLevel = isset($_GET['level']) ? $_GET['level'] : ''; ?>
            <div class="filter-pills" role="group" aria-label="Filter by level">
                <button type="submit" name="level" value=""
                    class="filter-pill<?php echo $currentLevel === '' ? ' active' : ''; ?>"
                    aria-pressed="<?php echo $currentLevel === '' ? 'true' : 'false'; ?>">
                    All Programmes
                </button>
                <button type="submit" name="level" value="Undergraduate"
                    class="filter-pill<?php echo $currentLevel === 'Undergraduate' ? ' active' : ''; ?>"
                    aria-pressed="<?php echo $currentLevel === 'Undergraduate' ? 'true' : 'false'; ?>">
                    🎓 Undergraduate
                </button>
                <button type="submit" name="level" value="Postgraduate"
                    class="filter-pill<?php echo $currentLevel === 'Postgraduate' ? ' active' : ''; ?>"
                    aria-pressed="<?php echo $currentLevel === 'Postgraduate' ? 'true' : 'false'; ?>">
                    🏅 Postgraduate
                </button>
            </div>
        </form>
    </div>
</section>

<!-- ─── COURSE GRID ──────────────────────────────────────────── -->
<?php
include __DIR__ . '/config/db.php';

$search = trim($_GET['q'] ?? '');
$level  = $_GET['level'] ?? '';

// Build safe query with prepared statement
$conditions = [];
$params     = [];
$types      = '';

if ($search !== '') {
    $conditions[] = "(p.ProgrammeName LIKE ? OR p.Description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

if ($level === 'Undergraduate' || $level === 'Postgraduate') {
    $conditions[] = "l.LevelName = ?";
    $params[] = $level;
    $types   .= 's';
}

$sql = "SELECT p.ProgrammeID, p.ProgrammeName, p.Description, l.LevelName
        FROM Programmes p
        LEFT JOIN Levels l ON p.LevelID = l.LevelID";
if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY l.LevelName, p.ProgrammeName';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total  = $result->num_rows;
?>

<main>
<section class="courses-section">
    <div class="section-header">
        <h2 class="section-title">
            <?php echo ($level !== '' ? htmlspecialchars($level) . ' ' : '') . 'Programmes'; ?>
        </h2>
        <span class="section-count">
            <?php echo $total; ?> programme<?php echo $total !== 1 ? 's' : ''; ?> found
        </span>
    </div>

    <div class="course-grid">
        <?php if ($total > 0): ?>
            <?php $delay = 0; while ($row = $result->fetch_assoc()): $delay += 50; ?>
                <article class="course-card" style="animation-delay:<?php echo $delay; ?>ms">
                    <?php $isUG = ($row['LevelName'] === 'Undergraduate'); ?>
                    <span class="course-level-badge <?php echo $isUG ? 'badge-ug' : 'badge-pg'; ?>">
                        <?php echo htmlspecialchars($row['LevelName'] ?? 'Programme'); ?>
                    </span>
                    <a href="programme-details.php?id=<?php echo (int)$row['ProgrammeID']; ?>" class="course-card-title">
                        <?php echo htmlspecialchars($row['ProgrammeName']); ?>
                    </a>
                    <?php if (!empty($row['Description'])): ?>
                        <p class="course-card-desc"><?php echo htmlspecialchars($row['Description']); ?></p>
                    <?php endif; ?>
                    <div class="course-card-footer">
                        <a href="programme-details.php?id=<?php echo (int)$row['ProgrammeID']; ?>" class="course-card-link">
                            View details
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <p style="font-size:1.1rem;color:var(--text);margin-bottom:.5rem;">No programmes found</p>
                <p>Try a different search term or filter.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
</main>

<!-- ─── REGISTER + WITHDRAW SECTION ─────────────────────────── -->
<?php
// ── Withdraw logic ────────────────────────────────────────────
$withdrawMsg  = '';
$withdrawType = 'success';
$withdrawRegistrations = [];
$withdrawEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_lookup'])) {
    $withdrawEmail = trim($_POST['withdraw_email'] ?? '');
    if (!$withdrawEmail || !filter_var($withdrawEmail, FILTER_VALIDATE_EMAIL)) {
        $withdrawMsg  = 'Please enter a valid email address.';
        $withdrawType = 'error';
    } else {
        $ws = $conn->prepare(
            "SELECT i.InterestID, i.StudentName, p.ProgrammeName, l.LevelName
             FROM InterestedStudents i
             JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
             LEFT JOIN Levels l ON p.LevelID = l.LevelID
             WHERE i.Email = ?
             ORDER BY i.RegisteredAt DESC"
        );
        $ws->bind_param('s', $withdrawEmail);
        $ws->execute();
        $withdrawRegistrations = $ws->get_result()->fetch_all(MYSQLI_ASSOC);
        $ws->close();
        if (empty($withdrawRegistrations)) {
            $withdrawMsg  = 'No registrations found for that email address.';
            $withdrawType = 'error';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_confirm'])) {
    $interestId    = (int)($_POST['interest_id'] ?? 0);
    $withdrawEmail = trim($_POST['confirm_email'] ?? '');
    if ($interestId > 0 && $withdrawEmail) {
        $wd = $conn->prepare("DELETE FROM InterestedStudents WHERE InterestID = ? AND Email = ?");
        $wd->bind_param('is', $interestId, $withdrawEmail);
        $wd->execute();
        if ($wd->affected_rows > 0) {
            $withdrawMsg  = '✓ Your interest has been withdrawn successfully.';
            $withdrawType = 'success';
        } else {
            $withdrawMsg  = 'Could not remove that registration. Please try again.';
            $withdrawType = 'error';
        }
        $wd->close();
        // Re-fetch remaining
        $ws2 = $conn->prepare(
            "SELECT i.InterestID, i.StudentName, p.ProgrammeName, l.LevelName
             FROM InterestedStudents i
             JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
             LEFT JOIN Levels l ON p.LevelID = l.LevelID
             WHERE i.Email = ?
             ORDER BY i.RegisteredAt DESC"
        );
        $ws2->bind_param('s', $withdrawEmail);
        $ws2->execute();
        $withdrawRegistrations = $ws2->get_result()->fetch_all(MYSQLI_ASSOC);
        $ws2->close();
    }
}

// ── Register logic ────────────────────────────────────────────
$message     = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name      = trim($_POST['name']  ?? '');
    $email     = trim($_POST['email'] ?? '');
    $programme = (int)($_POST['programme'] ?? 0);

    if ($name === '' || $email === '' || $programme <= 0) {
        $message     = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message     = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $stmt2 = $conn->prepare("INSERT INTO InterestedStudents (ProgrammeID, StudentName, Email) VALUES (?, ?, ?)");
        $stmt2->bind_param('iss', $programme, $name, $email);
        if ($stmt2->execute()) {
            $message = '✓ Your interest has been registered! We\'ll be in touch soon.';
        } else {
            $message     = 'Something went wrong. Please try again.';
            $messageType = 'error';
        }
        $stmt2->close();
    }
}

// Fetch programme list for dropdown
$progRes  = $conn->query('SELECT ProgrammeID, ProgrammeName FROM Programmes ORDER BY ProgrammeName');
$progOpts = [];
while ($r = $progRes->fetch_assoc()) $progOpts[] = $r;
?>

<section class="register-section" id="register">
    <div class="register-inner">

        <!-- ── REGISTER FORM ── -->
        <p class="register-eyebrow">Get in touch</p>
        <h2 class="register-title">Register your interest</h2>
        <p class="register-sub">Sign up to receive open day invitations, application deadline reminders, and programme updates.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>#register" novalidate>
            <div class="form-group">
                <label class="form-label" for="reg-name">Full name</label>
                <input
                    class="form-input"
                    type="text"
                    id="reg-name"
                    name="name"
                    placeholder="Jane Smith"
                    required
                    autocomplete="name"
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                >
            </div>
            <div class="form-group">
                <label class="form-label" for="reg-email">Email address</label>
                <input
                    class="form-input"
                    type="email"
                    id="reg-email"
                    name="email"
                    placeholder="you@example.com"
                    required
                    autocomplete="email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                >
            </div>
            <div class="form-group">
                <label class="form-label" for="reg-programme">Programme of interest</label>
                <div class="select-wrapper">
                    <select class="form-select" id="reg-programme" name="programme" required>
                        <option value="">Select a programme…</option>
                        <?php foreach ($progOpts as $opt): ?>
                            <option
                                value="<?php echo (int)$opt['ProgrammeID']; ?>"
                                <?php echo (isset($_POST['programme']) && (int)$_POST['programme'] === (int)$opt['ProgrammeID']) ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($opt['ProgrammeName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" name="register" class="form-submit">Register Interest →</button>
        </form>

        <!-- ── DIVIDER ── -->
        <div class="section-divider" id="withdraw">
            <span>or</span>
        </div>

        <!-- ── WITHDRAW FORM ── -->
        <p class="register-eyebrow">Manage preferences</p>
        <h2 class="register-title">Withdraw interest</h2>
        <p class="register-sub">No longer interested? Enter your email to find and remove your programme registrations.</p>

        <?php if ($withdrawMsg): ?>
            <div class="alert alert-<?php echo $withdrawType; ?>" role="alert">
                <?php echo htmlspecialchars($withdrawMsg); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>#withdraw" novalidate>
            <div class="form-group">
                <label class="form-label" for="withdraw-email">Your email address</label>
                <input
                    class="form-input"
                    type="email"
                    id="withdraw-email"
                    name="withdraw_email"
                    placeholder="you@example.com"
                    required
                    autocomplete="email"
                    value="<?php echo htmlspecialchars($withdrawEmail); ?>"
                >
            </div>
            <button type="submit" name="withdraw_lookup" class="form-submit form-submit-outline">
                Find My Registrations →
            </button>
        </form>

        <!-- Step 2: show matching registrations -->
        <?php if (!empty($withdrawRegistrations)): ?>
            <div class="withdraw-list">
                <p class="withdraw-list-label">
                    Found <strong><?php echo count($withdrawRegistrations); ?></strong>
                    registration<?php echo count($withdrawRegistrations) !== 1 ? 's' : ''; ?>
                    for <strong><?php echo htmlspecialchars($withdrawEmail); ?></strong>
                </p>
                <?php foreach ($withdrawRegistrations as $reg): ?>
                    <div class="withdraw-item">
                        <div class="withdraw-item-info">
                            <p class="withdraw-item-name"><?php echo htmlspecialchars($reg['ProgrammeName']); ?></p>
                            <?php
                                $isUG     = ($reg['LevelName'] ?? '') === 'Undergraduate';
                                $badgeCls = $isUG ? 'badge-ug' : 'badge-pg';
                            ?>
                            <span class="course-level-badge <?php echo $badgeCls; ?>">
                                <?php echo htmlspecialchars($reg['LevelName'] ?? 'Programme'); ?>
                            </span>
                        </div>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>#withdraw" novalidate>
                            <input type="hidden" name="interest_id"   value="<?php echo (int)$reg['InterestID']; ?>">
                            <input type="hidden" name="confirm_email" value="<?php echo htmlspecialchars($withdrawEmail); ?>">
                            <button type="submit" name="withdraw_confirm" class="btn-withdraw">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                                Withdraw
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($withdrawEmail && empty($withdrawRegistrations) && $withdrawType === 'success'): ?>
            <div class="alert alert-success" role="alert">
                ✓ All registrations have been removed for this email.
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- ─── FOOTER ───────────────────────────────────────────────── -->
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
                <li><a href="#courses">Courses</a></li>
                <li><a href="#register">Register</a></li>
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

</body>
</html>