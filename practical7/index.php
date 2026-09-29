<?php
$message = "";
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from form
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $course = trim($_POST["course"]);

    // Server-side validation
    if (empty($name) || empty($email) || empty($phone) || empty($course)) {
        $message = "All fields are required.";

    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } 
    elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $message = "Phone number must contain exactly 10 digits.";
    } 
    else {
        // Sanitize input
        $name = htmlspecialchars($name);
        $email = htmlspecialchars($email);
        $phone = htmlspecialchars($phone);
        $course = htmlspecialchars($course);
        // Create student array
        $student = array(
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "course" => $course
        );
        // JSON file name
        $file = "students.json";
        // Read existing JSON data
        $data = file_get_contents($file);
        // Convert JSON into PHP array
        $students = json_decode($data, true);
        // Add new student
        $students[] = $student;
        // Convert PHP array into JSON
        $jsonData = json_encode($students, JSON_PRETTY_PRINT);
        // Store data in JSON file
        file_put_contents($file, $jsonData);
        $message = "Registration successful!";
    }
}
// Read JSON file to display records
$data = file_get_contents("students.json");
$students = json_decode($data, true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
</head>
<body>
    <h2>Student Registration Form</h2>
    <?php
    if (!empty($message)) {
        echo "<p><b>$message</b></p>";
    }
    ?>
    <form method="POST" action="">
        <label>Name:</label>
        <input type="text" name="name">
        <br><br>
        <label>Email:</label>
        <input type="text" name="email">
        <br><br>
        <label>Phone:</label>
        <input type="text" name="phone">
        <br><br>
        <label>Course:</label>
        <select name="course">
            <option value="">Select Course</option>
            <option value="Computer Engineering">
                Computer Engineering
            </option>
            <option value="Information Technology">
                Information Technology
            </option>
            <option value="Mechanical Engineering">
                Mechanical Engineering
            </option>
            <option value="Civil Engineering">
                Civil Engineering
            </option>
        </select>
        <br><br>
        <input type="submit" value="Register">
    </form>
    <hr>
    <h2>Stored Student Records</h2>
    <?php
    if (empty($students)) {
        echo "<p>No records found.</p>";
    } 
    else {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr>";
        echo "<th>Name</th>";
        echo "<th>Email</th>";
        echo "<th>Phone</th>";
        echo "<th>Course</th>";
        echo "</tr>";
        foreach ($students as $student) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($student["name"]) . "</td>";
            echo "<td>" . htmlspecialchars($student["email"]) . "</td>";
            echo "<td>" . htmlspecialchars($student["phone"]) . "</td>";
            echo "<td>" . htmlspecialchars($student["course"]) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    ?>
</body>
</html>