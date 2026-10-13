<?php
require "db.php";
$users = [
    ["admin", "admin@example.com", "admin123", "admin"],
    ["student", "student@example.com", "student123", "student"]
];
foreach ($users as $user) {
    $username = $user[0];
    $email = $user[1];
    $password = password_hash($user[2], PASSWORD_DEFAULT);
    $role = $user[3];
    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, password, role)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
         password = VALUES(password), role = VALUES(role)"
    );
    $stmt->bind_param("ssss", $username, $email, $password, $role);
    $stmt->execute();
}
echo "Test users created successfully!";
?>
