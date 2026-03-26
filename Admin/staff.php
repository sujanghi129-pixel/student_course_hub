<?php
session_start();

// 🔐 Check login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 🔐 Role check (only admin)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

require_once '../config/db.php';

// ✅ Fetch staff data
$result = $conn->query("SELECT * FROM staff");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Staff</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>

<div class="content">

    <h2>Staff Members</h2>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['StaffID'] ?></td>
                            <td><?= htmlspecialchars($row['Name']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">No staff found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>