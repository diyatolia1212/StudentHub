
<?php
header("Content-Type: application/json; charset=UTF-8");

mysqli_report(MYSQLI_REPORT_OFF);

function sendJSON($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

try {
    $conn = new mysqli("localhost", "root", "", "studenthub");

    if ($conn->connect_error) {
        sendJSON(["error" => "Database connection failed"], 500);
    }

    $conn->set_charset("utf8mb4");

    $method = $_SERVER["REQUEST_METHOD"];

    // GET: Display all students
    if ($method === "GET") {
        $sql = "SELECT student_id, name, email, course, year
                FROM students ORDER BY student_id DESC";

        $result = $conn->query($sql);

        if (!$result) {
            sendJSON(["error" => "Unable to fetch students"], 500);
        }

        $students = [];

        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }

        sendJSON($students);
    }

    // Only POST is supported for changes
    if ($method !== "POST") {
        sendJSON(["error" => "Method not allowed"], 405);
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        sendJSON(["error" => "Invalid JSON data"], 400);
    }

    $action = $data["action"] ?? "add";

    // DELETE student
    if ($action === "delete") {
        $id = intval($data["student_id"] ?? 0);

        if ($id <= 0) {
            sendJSON(["error" => "Invalid student ID"], 400);
        }

        $stmt = $conn->prepare(
            "DELETE FROM students WHERE student_id = ?"
        );

        if (!$stmt) {
            sendJSON(["error" => "Database query failed"], 500);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            $stmt->close();
            sendJSON(["error" => "Student not found"], 404);
        }

        $stmt->close();
        sendJSON(["message" => "Student deleted successfully"]);
    }

    if ($action !== "add" && $action !== "update") {
        sendJSON(["error" => "Invalid action"], 400);
    }

    $name = trim($data["name"] ?? "");
    $email = trim($data["email"] ?? "");
    $course = trim($data["course"] ?? "");
    $year = intval($data["year"] ?? 0);
    $id = intval($data["student_id"] ?? 0);

    // Input validation
    if ($name === "" || $course === "" || $email === "") {
        sendJSON(["error" => "All fields are required"], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJSON(["error" => "Enter a valid email address"], 400);
    }

    if ($action === "update") {
        if ($id <= 0) {
            sendJSON(["error" => "Invalid student ID"], 400);
        }

        $stmt = $conn->prepare(
            "UPDATE students
             SET name = ?, email = ?, course = ?, year = ?
             WHERE student_id = ?"
        );

        if (!$stmt) {
            sendJSON(["error" => "Database query failed"], 500);
        }

        $stmt->bind_param("sssii", $name, $email, $course, $year, $id);

    } else {
        $stmt = $conn->prepare(
            "INSERT INTO students (name, email, course, year)
             VALUES (?, ?, ?, ?)"
        );

        if (!$stmt) {
            sendJSON(["error" => "Database query failed"], 500);
        }

        $stmt->bind_param("sssi", $name, $email, $course, $year);
    }

    if ($stmt->execute()) {
        $stmt->close();

        if ($action === "update") {
            sendJSON(["message" => "Student updated successfully"]);
        }

        sendJSON(["message" => "Student added successfully"]);
    }

    $errorNumber = $stmt->errno;
    $stmt->close();

    if ($errorNumber === 1062) {
        sendJSON(["error" => "This email already exists"], 409);
    }

    sendJSON(["error" => "Database operation failed"], 500);

} catch (Throwable $e) {
    sendJSON(["error" => "Server error occurred"], 500);
}
?>
