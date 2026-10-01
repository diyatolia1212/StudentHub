<?php
include "db.php";
$message = "Database connected successfully!";
$sql = "SELECT students.name, events.event_name, events.event_date, events.location
        FROM registrations
        JOIN students ON registrations.student_id = students.student_id
        JOIN events ON registrations.event_id = events.event_id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>StudentHub - Practical 8</title>
</head>
<body bgcolor="#fff9d6">
    <h1>StudentHub</h1>
    <h2>MySQL Database Connection</h2>
    <p><?php echo $message; ?></p>
    <hr>
    <h2>Student Registrations</h2>
    <?php
    if (count($registrations) > 0) {
        foreach ($registrations as $row) {
            echo "Student Name: " . $row["name"] . "<br>";
            echo "Event: " . $row["event_name"] . "<br>";
            echo "Date: " . $row["event_date"] . "<br>";
            echo "Location: " . $row["location"] . "<br>";
            echo "<hr>";
        }
    } else {
        echo "No registrations found.";
    }
    ?>
</body>
</html>