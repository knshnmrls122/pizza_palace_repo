<?php
    include("../includes/header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza Palace | Employee Login</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="min-h-screen">
    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <img src="../assets/Bg.jpg" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-white100/70 to-black/70"></div>
    </div>

    <!-- Login Container -->
    <div class="min-h-screen flex items-center justify-center px-4 relative z-10">
        <div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-full max-w-md">
            <div class="text-center mb-6">
                <img src="../assets/logo.jpg" alt="Pizza Palace Logo" class="w-20 h-20 mx-auto rounded-full shadow-md mb-4">
                <h2 class="text-2xl font-bold text-orange-700">🍕 Employee Login</h2>
            </div>

            <form action="../includes/login_handler.php" method="POST" class="space-y-6">
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-id-badge"></i></span>
                    <input type="text" name="employee_id" placeholder="Employee ID" required
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                </div>

                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" placeholder="Password" required
                        class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                    <button type="button" onclick="togglePassword()" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>

                <button type="submit" class="w-full bg-orange-500 text-white py-2 rounded-lg hover:bg-orange-600 transition-colors duration-300 font-semibold">
                    Login
                </button>
            </form>

            <div class="mt-6 text-center space-y-2">
                <!-- <p class="text-gray-600">Don't have an account? <a href="Signup_Page.php" class="text-orange-500 hover:text-orange-600 font-semibold">Register</a></p> -->
                <p class="text-gray-600">Back to <a href="Home_Page.php" class="text-orange-500 hover:text-orange-600 font-semibold">Home</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.getElementById("eye-icon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>
