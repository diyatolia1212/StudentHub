
<?php
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

require "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $date = $_POST["event_date"];
    $category = $_POST["category"];
    $status = $_POST["status"];
    $poster = "";

    if ($title == "" || $description == "" || $date == "") {
        $message = "Please fill all required fields.";
    } elseif (!in_array($category, ["Technology", "Cultural", "Sports"])
        || !in_array($status, ["Open", "Closed"])) {
        $message = "Invalid category or status.";
    } else {
        $valid = true;

        if (isset($_FILES["poster"]) &&
            $_FILES["poster"]["error"] != UPLOAD_ERR_NO_FILE) {

            if ($_FILES["poster"]["error"] != UPLOAD_ERR_OK) {
                $message = "Poster upload failed.";
                $valid = false;
            } elseif ($_FILES["poster"]["size"] > 2 * 1024 * 1024) {
                $message = "Poster must be 2 MB or smaller.";
                $valid = false;
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES["poster"]["tmp_name"]);

                $allowed = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                if (!isset($allowed[$mime]) ||
                    getimagesize($_FILES["poster"]["tmp_name"]) === false) {
                    $message = "Only JPG, PNG and WEBP images are allowed.";
                    $valid = false;
                } else {
                    $poster = bin2hex(random_bytes(16)) . "." . $allowed[$mime];

                    if (!move_uploaded_file(
                        $_FILES["poster"]["tmp_name"],
                        __DIR__ . "/uploads/" . $poster
                    )) {
                        $message = "Could not save the poster.";
                        $valid = false;
                    }
                }
            }
        }

        if ($valid) {
            $stmt = $conn->prepare(
                "INSERT INTO events
                (title, description, event_date, category, status, poster)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssss",
                $title, $description, $date, $category, $status, $poster
            );

            if ($stmt->execute()) {
                header("Location: index.php?message=" .
                    urlencode("Event added successfully!"));
                exit;
            } else {
                if ($poster != "") {
                    @unlink(__DIR__ . "/uploads/" . $poster);
                }
                $message = "Failed to add event.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Event</title>
</head>
<body style="background:#fff9d6;font-family:Arial;margin:30px;">

<h2>Add New Event</h2>
<p style="color:red;"><?php echo htmlspecialchars($message); ?></p>

<form method="post" enctype="multipart/form-data">
    Event Title:<br>
    <input type="text" name="title" required><br><br>

    Description:<br>
    <textarea name="description" required></textarea><br><br>

    Event Date:<br>
    <input type="date" name="event_date" required><br><br>

    Category:<br>
    <select name="category" required>
        <option value="Technology">Technology</option>
        <option value="Cultural">Cultural</option>
        <option value="Sports">Sports</option>
    </select><br><br>

    Status:<br>
    <select name="status" required>
        <option value="Open">Open</option>
        <option value="Closed">Closed</option>
    </select><br><br>

    Event Poster (optional):<br>
    <input type="file" name="poster"
           accept=".jpg,.jpeg,.png,.webp"><br>
    <small>JPG, PNG or WEBP; maximum 2 MB.</small><br><br>

    <button type="submit">Add Event</button>
</form>

<a href="index.php">Back to Events</a>
</body>
</html>
