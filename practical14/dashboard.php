
<?php

session_start();

$conn = new mysqli("localhost", "root", "", "studenthub");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$error = "";

// Handle login in this same file
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($username === "" || $password === "") {
        $error = "Please enter username and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, username, password, role
             FROM users
             WHERE username = ?
             LIMIT 1"
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
                $error = "Access denied. Admin account required.";
            }
        } else {
            $error = "Invalid username or password.";
        }

        $stmt->close();
    }
}

// Logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();

    header("Location: dashboard.php");
    exit;
}

// Check whether the user is logged in as admin
$isAdmin = isset($_SESSION['user_id'], $_SESSION['role'])
    && $_SESSION['role'] === 'admin';

if ($isAdmin) {
    // Dashboard statistics
    $userCount = $conn->query(
        "SELECT COUNT(*) AS total FROM users"
    )->fetch_assoc()['total'];

    $studentCount = $conn->query(
        "SELECT COUNT(*) AS total FROM students"
    )->fetch_assoc()['total'];

    $eventCount = $conn->query(
        "SELECT COUNT(*) AS total FROM events"
    )->fetch_assoc()['total'];

    // Latest registrations
    $latest = $conn->query(
        "SELECT * FROM registrations ORDER BY id DESC LIMIT 5"
    );

    // Log dashboard access only once per page load
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - StudentHub</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background-color:#fff9d6;">

<nav class="navbar navbar-dark" style="background-color:#6c4ab6;">
    <div class="container">
        <span class="navbar-brand">StudentHub Admin</span>

        <?php if ($isAdmin) { ?>
            <a href="dashboard.php?logout=1" class="btn btn-light">
                Logout
            </a>
        <?php } ?>
    </div>
</nav>

<div class="container mt-4">

<?php if (!$isAdmin) { ?>

    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4">
                <h3 class="text-center">Admin Login</h3>

                <?php if ($error !== "") { ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php } ?>

                <form method="post" action="dashboard.php">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username"
                               class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password"
                               class="form-control" required>
                    </div>

                    <button type="submit" name="login"
                            class="btn btn-primary w-100">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>

<?php } else { ?>

    <h2>Admin Dashboard</h2>

    <p>
        Welcome,
        <?php echo htmlspecialchars($_SESSION['username']); ?>!
    </p>

    <h4>Dashboard Statistics</h4>

    <div class="row mt-3">
        <div class="col-md-4 mb-3">
            <div class="card p-3 text-center">
                <h5>Total Users</h5>
                <h2><?php echo $userCount; ?></h2>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card p-3 text-center">
                <h5>Total Students</h5>
                <h2><?php echo $studentCount; ?></h2>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card p-3 text-center">
                <h5>Total Events</h5>
                <h2><?php echo $eventCount; ?></h2>
            </div>
        </div>
    </div>

    <h4 class="mt-4">Admin Menu</h4>

    <a href="dashboard.php" class="btn btn-primary mb-2">Dashboard</a>
    <a href="manage_students.php" class="btn btn-secondary mb-2">
        Manage Students
    </a>
    <a href="manage_events.php" class="btn btn-secondary mb-2">
        Manage Events
    </a>
    <a href="audit_logs.php" class="btn btn-secondary mb-2">
        Audit Logs
    </a>

    <h4 class="mt-4">Latest Registrations</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead>
                <tr>
                    <?php
                    if ($latest && $latest->num_rows > 0) {
                        foreach ($latest->fetch_fields() as $field) {
                            echo "<th>" .
                                htmlspecialchars($field->name) .
                                "</th>";
                        }
                    } else {
                        echo "<th>Registrations</th>";
                    }
                    ?>
                </tr>
            </thead>

            <tbody>
                <?php
                if ($latest && $latest->num_rows > 0) {
                    $latest->data_seek(0);

                    while ($row = $latest->fetch_assoc()) {
                        echo "<tr>";

                        foreach ($row as $value) {
                            echo "<td>" .
                                htmlspecialchars((string) $value) .
                                "</td>";
                        }

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td>No registrations found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

<?php
echo password_hash("Diya@123", PASSWORD_DEFAULT);}
?>


</div>
</body>
</html>
