
<?php
session_start();

if (!isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') != 'admin') {
    die("Access denied! Admin only.");
}

$conn = new mysqli("localhost", "root", "", "studenthub");

if ($conn->connect_error) {
    die("Database connection failed");
}

$sql = "SELECT audit_logs.id, users.username,
        audit_logs.action, audit_logs.created_at
        FROM audit_logs
        LEFT JOIN users ON audit_logs.user_id = users.id
        ORDER BY audit_logs.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Audit Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background-color:#fff9d6;">

<div class="container mt-4">
    <h2>Admin Audit Logs</h2>

    <a href="dashboard.php" class="btn btn-primary mb-3">
        Back to Dashboard
    </a>

    <table class="table table-bordered table-striped">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Action</th>
            <th>Date and Time</th>
        </tr>

        <?php
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" .
                 htmlspecialchars($row['username'] ?? 'Unknown') .
                 "</td>";
            echo "<td>" .
                 htmlspecialchars($row['action']) .
                 "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
