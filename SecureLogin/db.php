<?php
$conn = new mysqli("localhost", "root", "", "securelogin");

if ($conn->connect_error) {
    die("Database connection failed");
}
?>