<?php
// signup_handler.php

// Include your database connection
include("db_connection.php"); // make sure this file defines $conn = new mysqli(...)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form inputs
    $employee_id = trim($_POST['employee_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Hash the password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if email or employee_id already exists
    $check_query = "SELECT * FROM Users WHERE email = ? OR employee_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $email, $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>
            alert('Email or Employee ID already exists. Please try again.');
            window.history.back();
        </script>";
        exit;
    }

    // Insert the new user
    $insert_query = "INSERT INTO Users 
        (first_name, last_name, email, contact_number, address, role, employee_id, password_hash)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssssssss", 
        $first_name, 
        $last_name, 
        $email, 
        $contact_number, 
        $address, 
        $role, 
        $employee_id, 
        $password_hash
    );

    if ($stmt->execute()) {
        echo "<script>
            alert('Account created successfully!');
            window.location.href = '../pages/Login_Page.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: Could not register user. Please try again later.');
            window.history.back();
        </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // Redirect if accessed without form submission
    header("Location: ../pages/Signup_Page.php");
    exit();
}
?>
