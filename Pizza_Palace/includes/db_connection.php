<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';  // Don't use 'localhost'
$user = 'root';
$password = '';       // Leave blank if no password
$database = 'db_pizzapalace';   

$conn = mysqli_connect($host, $user, $password, $database  ,port: 4306);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>