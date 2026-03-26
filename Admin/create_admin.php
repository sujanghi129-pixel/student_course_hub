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

<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
</head>
<body>

<h2>Create New User</h2>

<?php if ($message): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>

<form method="POST">

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Role:</label><br>
    <select name="role">
        <option value="staff">Staff</option>
        <option value="admin">Admin</option>
    </select><br><br>

    <button type="submit">Create User</button>

</form>

</body>
</html>