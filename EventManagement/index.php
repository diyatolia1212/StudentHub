
<?php
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

require "db.php";

$category = $_GET["category"] ?? "";
$status = $_GET["status"] ?? "";

$sql = "SELECT * FROM events WHERE 1=1";
$types = "";
$params = [];

if ($category != "") {
    $sql .= " AND category = ?";
    $types .= "s";
    $params[] = $category;
}

if ($status != "") {
    $sql .= " AND status = ?";
    $types .= "s";
    $params[] = $status;
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if ($types != "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$message = $_GET["message"] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Management</title>
    <style>
        body {
            background: #fff9d6;
            font-family: Arial;
            margin: 25px;
        }

        h1 { color: #6c4ab6; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #999;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #6c4ab6;
            color: white;
        }

        input, select, button {
            padding: 8px;
            margin: 5px;
        }

        a { color: #5633a5; }

        img {
            width: 100px;
            height: 70px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<h1>Event Management Admin Panel</h1>

<p style="color:green;">
    <?php echo htmlspecialchars($message); ?>
</p>

<a href="add.php">Add New Event</a> |
<a href="logout.php">Logout</a>

<h3>Filter Events</h3>

<form method="get">
    <select name="category">
        <option value="">All Categories</option>
        <option value="Technology">Technology</option>
        <option value="Cultural">Cultural</option>
        <option value="Sports">Sports</option>
    </select>

    <select name="status">
        <option value="">All Statuses</option>
        <option value="Open">Open</option>
        <option value="Closed">Closed</option>
    </select>

    <button type="submit">Filter</button>
    <a href="index.php">Clear Filter</a>
</form>

<table>
<tr>
    <th>ID</th>
    <th>Title</th>
    <th>Description</th>
    <th>Date</th>
    <th>Category</th>
    <th>Status</th>
    <th>Poster</th>
    <th>Actions</th>
</tr>

<?php while ($row = $result->fetch_assoc()) { ?>
<tr>
    <td><?php echo $row["id"]; ?></td>
    <td><?php echo htmlspecialchars($row["title"]); ?></td>
    <td><?php echo htmlspecialchars($row["description"]); ?></td>
    <td><?php echo htmlspecialchars($row["event_date"]); ?></td>
    <td><?php echo htmlspecialchars($row["category"]); ?></td>
    <td><?php echo htmlspecialchars($row["status"]); ?></td>

    <td>
        <?php if ($row["poster"] != "") { ?>
            <img src="uploads/<?php echo rawurlencode($row["poster"]); ?>"
                 alt="Event poster">
        <?php } else { ?>
            No poster
        <?php } ?>
    </td>

    <td>
        <a href="edit.php?id=<?php echo $row["id"]; ?>">Edit</a>

        <form action="delete.php" method="post"
              onsubmit="return confirm('Delete this event?');">
            <input type="hidden" name="id"
                   value="<?php echo $row["id"]; ?>">
            <button type="submit">Delete</button>
        </form>
    </td>
</tr>
<?php } ?>

</table>
</body>
</html>
