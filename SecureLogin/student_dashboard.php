
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
// Check session timeout (5 minutes)
if (time() - $_SESSION["last_activity"] > 300) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION["last_activity"] = time();

// Allow only students
if ($_SESSION["role"] != "student") {
    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body bgcolor="#fff9d6">
<h2>Student Dashboard</h2>
<p>
    Welcome,
    <?php echo htmlspecialchars($_SESSION["username"]); ?>!
</p>
<p>You are logged in as a student.</p>
<a href="logout.php">Logout</a>
</body>
</html>
