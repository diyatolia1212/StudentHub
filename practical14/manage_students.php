
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

$result = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background-color:#fff9d6;">

<div class="container mt-4">
    <h2>Manage Students</h2>

    <a href="dashboard.php" class="btn btn-primary mb-3">
        Back to Dashboard
    </a>

    <table class="table table-bordered bg-white">
        <tr>
            <?php
            if ($result && $result->num_rows > 0) {
                foreach ($result->fetch_fields() as $field) {
                    echo "<th>" .
                         htmlspecialchars($field->name) .
                         "</th>";
                }
            }
            ?>
        </tr>

        <?php
        if ($result && $result->num_rows > 0) {
            $result->data_seek(0);

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";

                foreach ($row as $value) {
                    echo "<td>" .
                         htmlspecialchars((string)$value) .
                         "</td>";
                }

                echo "</tr>";
            }
        } else {
            echo "<tr><td>No students found.</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
