<?php

$conn = new mysqli("localhost", "root", "", "secure_register");

if ($conn->connect_error) {
    die("Database connection failed");
}

$message = "";

if (isset($_POST['register'])) {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // Backend validation

    if ($username == "" || $email == "" ||
        $password == "" || $confirm == "") {

        $message = "Please fill all fields.";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Enter a valid email address.";
    }

    elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
    }

    elseif ($password != $confirm) {

        $message = "Passwords do not match.";
    }

    else {

        // Check duplicate username or email

        $check = $conn->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ?"
        );

        $check->bind_param("ss", $username, $email);

        $check->execute();

        $check->store_result();

        if ($check->num_rows > 0) {

            $message = "Username or email already exists.";
        }

        else {

            // Hash password

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user

            $stmt = $conn->prepare(
                "INSERT INTO users (username, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $username,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {

                $message = "Registration successful!";
            }

            else {

                $message = "Registration failed.";
            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>StudentHub Registration</title>

    <style>

        body {
            font-family: Arial;
            background-color: #eef5ff;
            text-align: center;
        }

        .container {
            background-color: white;
            width: 350px;
            margin: 50px auto;
            padding: 25px;
            border-radius: 10px;
        }

        h2 {
            color: #2457a7;
        }

        input {
            width: 90%;
            padding: 10px;
            margin: 8px;
        }

        button {
            background-color: #2457a7;
            color: white;
            padding: 10px 25px;
            border: none;
            cursor: pointer;
        }

        .message {
            color: #2457a7;
            margin: 15px;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>StudentHub Registration</h2>

    <form method="POST" action="">

        <input
            type="text"
            name="username"
            placeholder="Enter username"
            required
            maxlength="50"
        >

        <input
            type="email"
            name="email"
            placeholder="Enter email"
            required
            maxlength="100"
        >

        <input
            type="password"
            name="password"
            placeholder="Enter password"
            required
            minlength="6"
        >

        <input
            type="password"
            name="confirm"
            placeholder="Confirm password"
            required
            minlength="6"
        >

        <button type="submit" name="register">
            Register
        </button>

    </form>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

</div>

</body>

</html>