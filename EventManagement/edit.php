
<?php
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

require "db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid event ID.");
}

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $date = $_POST["event_date"];
    $category = $_POST["category"];
    $status = $_POST["status"];
    $poster = $event["poster"];
    $newPoster = "";

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
                    $newPoster = bin2hex(random_bytes(16))
                        . "." . $allowed[$mime];

                    if (!move_uploaded_file(
                        $_FILES["poster"]["tmp_name"],
                        __DIR__ . "/uploads/" . $newPoster
                    )) {
                        $message = "Could not save the new poster.";
                        $valid = false;
                    } else {
                        $poster = $newPoster;
                    }
                }
            }
        }

        if ($valid) {
            $stmt = $conn->prepare(
                "UPDATE events SET title=?, description=?,
                event_date=?, category=?, status=?, poster=? WHERE id=?"
            );

            $stmt->bind_param(
                "ssssssi",
                $title, $description, $date, $category,
                $status, $poster, $id
            );

            if ($stmt->execute()) {
                if ($newPoster != "" && $event["poster"] != "") {
                    $oldPath = __DIR__ . "/uploads/" . $event["poster"];
                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }

                header("Location: index.php?message=" .
                    urlencode("Event updated successfully!"));
                exit;
            } else {
                if ($newPoster != "") {
                    @unlink(__DIR__ . "/uploads/" . $newPoster);
                }
                $message = "Failed to update event.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
</head>
<body style="background:#fff9d6;font-family:Arial;margin:30px;">

<h2>Update Event</h2>
<p style="color:red;"><?php echo htmlspecialchars($message); ?></p>

<form method="post" enctype="multipart/form-data">
    Event Title:<br>
    <input type="text" name="title"
        value="<?php echo htmlspecialchars($event["title"]); ?>"
        required><br><br>

    Description:<br>
    <textarea name="description" required><?php
        echo htmlspecialchars($event["description"]);
    ?></textarea><br><br>

    Event Date:<br>
    <input type="date" name="event_date"
        value="<?php echo htmlspecialchars($event["event_date"]); ?>"
        required><br><br>

    Category:<br>
    <select name="category">
        <?php foreach (["Technology", "Cultural", "Sports"] as $c) { ?>
            <option value="<?php echo $c; ?>"
                <?php if ($event["category"] == $c) echo "selected"; ?>>
                <?php echo $c; ?>
            </option>
        <?php } ?>
    </select><br><br>

    Status:<br>
    <select name="status">
        <option value="Open"
            <?php if ($event["status"] == "Open") echo "selected"; ?>>
            Open
        </option>
        <option value="Closed"
            <?php if ($event["status"] == "Closed") echo "selected"; ?>>
            Closed
        </option>
    </select><br><br>

    Current Poster:<br>
    <?php if ($event["poster"] != "") { ?>
        <img width="120"
             src="uploads/<?php echo rawurlencode($event["poster"]); ?>"
             alt="Current poster"><br>
    <?php } else { ?>
        No poster uploaded.<br>
    <?php } ?>

    Replace Poster (optional):<br>
    <input type="file" name="poster"
           accept=".jpg,.jpeg,.png,.webp"><br>
    <small>JPG, PNG or WEBP; maximum 2 MB.</small><br><br>

    <button type="submit">Update Event</button>
</form>

<a href="index.php">Back to Events</a>
</body>
</html>
