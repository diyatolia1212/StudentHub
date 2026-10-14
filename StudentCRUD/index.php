
<?php
$conn = new mysqli("localhost", "root", "", "studenthub");

if ($conn->connect_error) {
    die("Database connection failed!");
}

$msg = "";
$edit = ['student_id'=>0, 'name'=>'', 'email'=>'', 'course'=>'', 'year'=>''];

// Add or Update student
if (isset($_POST['save'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $course = $_POST['course'];
    $year = (int)$_POST['year'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE students SET name=?,email=?,course=?,year=? WHERE student_id=?");
        $stmt->bind_param("sssii", $name, $email, $course, $year, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO students(name,email,course,year) VALUES(?,?,?,?)");
        $stmt->bind_param("sssi", $name, $email, $course, $year);
    }

    $msg = $stmt->execute() ? "Student saved successfully!" : "Error: Email may already exist.";
    $stmt->close();
}

// Delete student
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = $stmt->affected_rows ? "Student deleted successfully!" : "Student not found.";
    $stmt->close();
}

// Load student for editing
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc() ?: $edit;
    $stmt->close();
}

// Search and filter
$search = $_GET['search'] ?? '';
$course = $_GET['course'] ?? '';

$sql = "SELECT * FROM students WHERE (name LIKE ? OR email LIKE ? OR course LIKE ?)";
$values = ["%$search%", "%$search%", "%$search%"];
$types = "sss";

if ($course != '') {
    $sql .= " AND course=?";
    $values[] = $course;
    $types .= "s";
}

$sql .= " ORDER BY student_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$values);
$stmt->execute();
$result = $stmt->get_result();

function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Management</title>
    <style>
        body { background:#fff9d6; font-family:Arial; padding:20px; }
        form, table { background:white; padding:12px; margin:15px 0; }
        input, select, button { padding:8px; margin:5px; }
        table { width:100%; border-collapse:collapse; }
        td, th { border:1px solid gray; padding:8px; }
        th { background:#dce8ff; }
        a, button { background:#245db5; color:white; padding:7px; }
        .msg { background:#d9f5d9; padding:10px; }
    </style>
</head>
<body>

<h1>Student Management System</h1>
<p class="msg"><?= e($msg) ?></p>

<h2><?= $edit['student_id'] ? 'Edit Student' : 'Add Student' ?></h2>

<form method="post">
    <input type="hidden" name="id" value="<?= e($edit['student_id']) ?>">
    <input name="name" placeholder="Name" required value="<?= e($edit['name']) ?>">
    <input type="email" name="email" placeholder="Email" required value="<?= e($edit['email']) ?>">

    <select name="course" required>
        <option value="">Select Course</option>
        <?php foreach (['BCA','BSc IT','BTech','MCA'] as $c): ?>
            <option value="<?= e($c) ?>" <?= $edit['course']==$c ? 'selected' : '' ?>>
                <?= e($c) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="number" name="year" min="1" max="6" placeholder="Year"
           required value="<?= e($edit['year']) ?>">
    <button name="save">Save Student</button>
    <a href="index.php">Clear</a>
</form>

<h2>Search and Filter</h2>
<form method="get">
    <input name="search" placeholder="Search name, email or course" value="<?= e($search) ?>">
    <select name="course">
        <option value="">All Courses</option>
        <?php foreach (['BCA','BSc IT','BTech','MCA'] as $c): ?>
            <option value="<?= e($c) ?>" <?= $course==$c ? 'selected' : '' ?>>
                <?= e($c) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button>Search</button>
</form>

<table>
<tr>
    <th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>Action</th>
</tr>

<?php if ($result->num_rows): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= e($row['student_id']) ?></td>
        <td><?= e($row['name']) ?></td>
        <td><?= e($row['email']) ?></td>
        <td><?= e($row['course']) ?></td>
        <td><?= e($row['year']) ?></td>
        <td>
            <a href="?edit=<?= e($row['student_id']) ?>">Edit</a>
            <a href="?delete=<?= e($row['student_id']) ?>"
               onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="6">No records found.</td></tr>
<?php endif; ?>
</table>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
