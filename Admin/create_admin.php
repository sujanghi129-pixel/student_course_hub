<?php
session_start();
require_once '../config/db.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($email && $password && $role) {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO admins (email, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            $message = "User created successfully!";
        } else {
            $message = "Error creating user.";
        }
    }
}
?>

<link rel="stylesheet" href="../css/create_admin.css">

<div class="container">

    <h2>Create User</h2>

    <?php if ($message): ?>
        <p class="success"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST">

        <input type="email" name="email" placeholder="Enter email" required>

        <input type="password" name="password" placeholder="Enter password" required>

        <select name="role">
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Create User</button>

    </form>
    <a href="dashboard.php" class="back-link" aria-label="Back to student-facing site">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back to dashboard
    </a>

</div>