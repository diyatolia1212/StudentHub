
<?php
session_start();
require "db.php";

$error = "";

// Check session timeout
if (isset($_SESSION["last_activity"]) &&
    time() - $_SESSION["last_activity"] > 300) {
    $_SESSION = [];
    session_destroy();
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT id, username, password, role
         FROM users WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            // Create a new session ID
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["last_activity"] = time();

            // Update last login time
            $update = $conn->prepare(
                "UPDATE users SET last_login = NOW() WHERE id = ?"
            );
            $update->bind_param("i", $user["id"]);
            $update->execute();

            // Redirect according to role
            if ($user["role"] == "admin") {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit;

        } else {
            $error = "Invalid username or password!";
        }
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Secure Login</title>
</head>
<body bgcolor="#fff9d6">

<h2>Secure Login</h2>

<form method="post" action="login.php">

    <label>Username:</label>
    <input type="text" name="username" required>
    <br><br>

    <label>Password:</label>
    <input type="password" name="password" required>
    <br><br>

    <button type="submit">Login</button>

</form>

<p style="color:red;">
    <?php echo htmlspecialchars($error); ?>
</p>

</body>
</html>
