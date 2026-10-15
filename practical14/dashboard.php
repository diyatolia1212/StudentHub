
<?php
session_start();

$conn = new mysqli("localhost", "root", "", "studenthub");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$error = "";

// Login
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? "");
    $password = $_POST['password'] ?? "";

    $stmt = $conn->prepare(
        "SELECT id, username, password, role
         FROM users WHERE username = ? LIMIT 1"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] === 'admin') {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Admin access required.";
        }
    } else {
        $error = "Invalid username or password.";
    }

    $stmt->close();
}

// Logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();

    header("Location: dashboard.php");
    exit;
}

// Admin check
$isAdmin = isset($_SESSION['user_id'], $_SESSION['role'])
    && $_SESSION['role'] === 'admin';

if ($isAdmin) {
    $userCount = $conn->query(
        "SELECT COUNT(*) AS total FROM users"
    )->fetch_assoc()['total'];

    $studentCount = $conn->query(
        "SELECT COUNT(*) AS total FROM students"
    )->fetch_assoc()['total'];

    $eventCount = $conn->query(
        "SELECT COUNT(*) AS total FROM events"
    )->fetch_assoc()['total'];

    $latest = $conn->query(
        "SELECT * FROM registrations ORDER BY id DESC LIMIT 5"
    );

    // Audit log
    $userId = (int) $_SESSION['user_id'];
    $action = "Opened admin dashboard";

    $log = $conn->prepare(
        "INSERT INTO audit_logs (user_id, action) VALUES (?, ?)"
    );

    if ($log) {
        $log->bind_param("is", $userId, $action);
        $log->execute();
        $log->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8
