
<?php
session_start();

if (isset($_SESSION["admin"])) {
    header("Location: index.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($username == "admin" && $password == "admin123") {
        session_regenerate_id(true);
        $_SESSION["admin"] = true;

        header("Location: index.php");
        exit;
    } else {
        $message = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
</head>

<body style="background:#fff9d6; font-family:Arial; margin:40px;">

    <h2>Event Management - Admin Login</h2>

    <p style="color:red;">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <form method="post">
        <label>Username:</label><br>
        <input type="text" name="username" required>
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required>
        <br><br>

        <button type="submit">Login</button>
    </form>

</body>
</html>
