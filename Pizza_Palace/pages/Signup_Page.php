<?php
    include("../includes/header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza Palace | Employee Management System</title>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="min-h-screen">
    <!-- Background with overlay (same as home/login pages) -->
    <div class="fixed inset-0 z-0">
        <img src="../assets/Bg.jpg" alt="Background" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-white-100/70 to-black/70"></div>
    </div>

    <!-- Signup Container -->
    <div class="min-h-screen flex items-center justify-center px-4 relative z-10">
    <div class="bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-2xl w-full max-w-xl">
            <div class="text-center mb-4">
                <img src="../assets/logo.jpg" alt="Pizza Palace Logo" class="w-16 h-16 mx-auto rounded-full shadow-md mb-2">
                <h2 class="text-xl font-bold text-orange-700">📝 Create an Account</h2>
            </div>

            <form action="../includes/signup_handler.php" method="POST" class="space-y-4">
                <!-- Employee Info -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-400"><i class="fas fa-user"></i></span>
                        <input type="text" name="first_name" placeholder="First Name" required
                            class="w-full pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm">
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-400"><i class="fas fa-user"></i></span>
                        <input type="text" name="last_name" placeholder="Last Name" required
                            class="w-full pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm">
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-400"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" placeholder="Email" required
                            class="w-full pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm">
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-400"><i class="fas fa-phone"></i></span>
                        <input type="text" name="contact_number" placeholder="Contact Number"
                            class="w-full pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm">
                    </div>
                </div>

                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-400"><i class="fas fa-location-dot"></i></span>
                    <input type="text" name="address" placeholder="Address"
                        class="w-full pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm">
                </div>

                <!-- Account Info -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <label class="sr-only">Role</label>
                        <select name="role" required
                            class="w-full py-1.5 pl-3 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm">
                            <option value="Employee" selected>Employee</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-400"><i class="fas fa-id-badge"></i></span>
                        <input type="text" name="employee_id" placeholder="Employee ID" required
                            class="w-full pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm">
                    </div>
                    <div class="relative col-span-2">
                        <span class="absolute left-3 top-2 text-gray-400"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" placeholder="Password" required
                            class="w-full pl-10 pr-10 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none text-sm">
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-orange-500 text-white py-2 rounded-lg hover:bg-orange-600 transition-colors duration-300 font-semibold text-sm">Register</button>
            </form>

            <div class="mt-4 text-center text-sm space-y-1">
                <p class="text-gray-600">Already have an account? <a href="Login_Page.php" class="text-orange-500 hover:text-orange-600 font-semibold">Login</a></p>
                <p class="text-gray-600">Back to <a href="Home_Page.php" class="text-orange-500 hover:text-orange-600 font-semibold">Home</a></p>
            </div>
        </div>
    </div>

    <style>
        .text-shadow { text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
    </style>

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
