<?php
include("config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validation
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $programme = $_POST['programme'] ?? '';

    if ($name && $email && $programme) {

        // SQL Injection Protection
        $stmt = $conn->prepare("INSERT INTO InterestedStudents (ProgrammeID, StudentName, Email) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $programme, $name, $email);

        if ($stmt->execute()) {
            echo "Interest Registered Successfully";
        } else {
            echo "Error occurred";
        }

    } else {
        echo "Please fill all fields";
    }
}
?>