<?php
require_once 'config/db.php';

$name = "Admin";
$email = "admin@gmail.com";
$password = password_hash("123456", PASSWORD_DEFAULT);

$conn->query("
    INSERT INTO admins (Name, Email, Password)
    VALUES ('$name', '$email', '$password')
");

echo "Admin created!";