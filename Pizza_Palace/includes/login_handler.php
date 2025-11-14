<?php
// login_handler.php
session_start();
include("db_connection.php"); // or db_connect.php depending on your filename

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = trim($_POST['employee_id']);
    $password = $_POST['password'];

    // Prepare query
    $query = "SELECT * FROM Users WHERE employee_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Store session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            // Redirect by role
            if ($user['role'] === 'Admin') {
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                header("Location: ../employee/dashboard.php");
                exit();
            }
        } else {
            echo "<script>
                alert('Invalid password. Please try again.');
                window.history.back();
            </script>";
        }
    } else {
        echo "<script>
            alert('Employee ID not found.');
            window.history.back();
        </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../pages/Login_Page.php");
    exit();
}
?>
