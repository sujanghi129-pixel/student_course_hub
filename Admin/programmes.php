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
<title>Programmes — Admin</title>

<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="../css/student.css">
</head>

<body class="main-layout">

<!-- SIDEBAR -->
<aside class="sidebar">
    <h2 style="color:white;">CourseHub</h2>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-link">Dashboard</a>
        <a href="students.php" class="sidebar-link">Students</a>
        <a href="programmes.php" class="sidebar-link active">Programmes</a>
        <a href="modules.php" class="sidebar-link">Modules</a>
    </nav>
</aside>

<!-- MAIN -->
<div class="main">
<div class="content">

<!-- HEADER -->
<div class="page-heading">
    <div>
        <h1 class="page-title">Programmes</h1>
        <p class="page-sub"><?php echo $total; ?> programme<?php echo $total !== 1 ? 's' : ''; ?></p>
    </div>
</div>

<!-- ADD FORM -->
<div class="panel" style="padding:1.2rem; margin-bottom:1rem;">
    <h3>Add New Programme</h3>

    <form method="post" style="display:grid; gap:0.7rem; margin-top:1rem;">

        <input type="text" name="name"
               placeholder="Programme name"
               required class="search-input">

        <!-- 🔥 FIXED DROPDOWN -->
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

</div>

</div>
</div>

</body>
</html>
<?php if ($isAdmin): ?>
    <button>Add Student</button>
<?php endif; ?>