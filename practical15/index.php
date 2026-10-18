
<?php
require_once "db.php";

$sql = "SELECT student_id, name, email, course
        FROM students ORDER BY student_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff9d6;
            margin: 0;
        }

        header {
            background-color: #6c4ab6;
            color: white;
            text-align: center;
            padding: 20px;
        }

        nav {
            background-color: #4d347f;
            padding: 12px;
            text-align: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 10px;
        }

        main {
            max-width: 900px;
            margin: 25px auto;
            padding: 15px;
        }

        section {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #6c4ab6;
            color: white;
        }

        .message {
            background-color: #fff3a3;
            padding: 12px;
        }

        footer {
            background-color: #4d347f;
            color: white;
            text-align: center;
            padding: 12px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        @media (max-width: 600px) {
            nav a {
                display: inline-block;
                margin: 6px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>StudentHub Portal</h1>
    <p>Practical 15 - Final Integration and Testing</p>
</header>

<nav aria-label="Main navigation">
    <a href="index.php">Home</a> |
    <a href="#students">Students</a> |
    <a href="#about">About</a>
</nav>

<main>
    <section id="about">
        <h2>Welcome to StudentHub</h2>
        <p>This portal manages student information.</p>
        <p class="message">Database connection successful!</p>
    </section>

    <section id="students">
        <h2>Student Records</h2>

        <?php if ($result && $result->num_rows > 0) { ?>

            <div class="table-wrap">
                <table>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                    </tr>

                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["student_id"]); ?></td>
                            <td><?php echo htmlspecialchars($row["name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["email"]); ?></td>
                            <td><?php echo htmlspecialchars($row["course"]); ?></td>
                        </tr>
                    <?php } ?>

                </table>
            </div>

        <?php } else { ?>

            <p>No student records found.</p>

        <?php } ?>
    </section>
</main>

<footer>
    <p>StudentHub Portal | Practical 15</p>
</footer>

</body>
</html>

<?php
$conn->close();
?>
