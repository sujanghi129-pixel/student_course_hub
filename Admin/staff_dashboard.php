<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

require_once '../config/db.php';

$staff_id = $_SESSION['staff_id'] ?? 0;

/* MODULES */
$stmt = $conn->prepare("
    SELECT ModuleID, ModuleName 
    FROM modules 
    WHERE ModuleLeaderID = ?
");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$moduleResult = $stmt->get_result();

/* PROGRAMMES */
$stmt2 = $conn->prepare("
    SELECT DISTINCT p.ProgrammeName
    FROM programmes p
    WHERE p.ProgrammeID IN (
        SELECT pm.ProgrammeID
        FROM programmemodules pm
        WHERE pm.ModuleID IN (
            SELECT ModuleID 
            FROM modules 
            WHERE ModuleLeaderID = ?
        )
    )
");
$stmt2->bind_param("i", $staff_id);
$stmt2->execute();
$programmeResult = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Dashboard</title>
<link rel="stylesheet" href="../css/dashboard.css">

<style>
body {
    margin: 0;
    font-family: 'DM Sans', sans-serif;
    background: radial-gradient(circle at top, #0f172a, #020617);
    color: #fff;
}
/* MAIN */
.main-content {
    margin-left: 260px;
    padding: 30px 40px;
}

/* TOPBAR */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(17, 24, 39, 0.7);
    backdrop-filter: blur(10px);
    padding: 20px 25px;
    border-radius: 14px;
    margin-bottom: 30px;
    border: 1px solid #1f2937;
}

.top-left h1 {
    margin: 0;
    font-size: 24px;
}

.top-left p {
    margin: 5px 0 0;
    color: #9ca3af;
}

.logout-btn {
    background: #ef4444;
    padding: 10px 18px;
    border-radius: 8px;
    color: #fff;
    text-decoration: none;
}

/* GRID */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
}


.logout-btn {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    padding: 10px 20px;
    border-radius: 10px;
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    transition: 0.2s;
}

.logout-btn:hover {
    transform: scale(1.05);
}

/* CARD */
.card {
    background: linear-gradient(145deg, #111827, #020617);
    padding: 28px;
    border-radius: 18px;
    border: 1px solid #1f2937;
    box-shadow: 0 20px 50px rgba(0,0,0,0.6);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-8px) scale(1.01);
    box-shadow: 0 30px 70px rgba(0,0,0,0.8);
}

.card h2 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 18px;
    letter-spacing: 0.5px;
}

/* LIST */
.card ul {
    list-style: none;
    padding: 0;
}

.card h2 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 18px;
    letter-spacing: 0.5px;
}.card li {
    padding: 12px 16px;
    background: linear-gradient(145deg, #1f2937, #111827);
    margin-bottom: 10px;
    border-radius: 10px;
    transition: 0.25s;
    border: 1px solid #1f2937;
}

.card li:hover {
    background: #374151;
    transform: translateX(6px);
}
</style>

</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- MAIN -->
<div class="main-content">

    <!-- HEADER -->
    <div class="topbar">
        <div class="top-left">
            <h1>Staff Dashboard</h1>
            <p>Welcome back, <?= $_SESSION['email'] ?></p>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- CONTENT -->
    <div class="dashboard-grid">

        <!-- MODULES -->
        <div class="card">
            <h2>📘 My Modules (<?= $moduleResult->num_rows ?>)</h2>

            <?php if ($moduleResult->num_rows > 0): ?>
                <ul>
                    <?php while($row = $moduleResult->fetch_assoc()): ?>
                        <li><?= htmlspecialchars($row['ModuleName']) ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No modules assigned.</p>
            <?php endif; ?>
        </div>

        <!-- PROGRAMMES -->
        <div class="card">
            <h2>🎓 My Programmes (<?= $programmeResult->num_rows ?>)</h2>

            <?php if ($programmeResult->num_rows > 0): ?>
                <ul>
                    <?php while($row = $programmeResult->fetch_assoc()): ?>
                        <li><?= htmlspecialchars($row['ProgrammeName']) ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No programmes found.</p>
            <?php endif; ?>
        </div>

    </div>

</div>

</body>
</html>