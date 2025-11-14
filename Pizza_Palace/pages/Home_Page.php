<?php
    include('../includes/header.php');
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
<body class="font-[Poppins]">

    <!-- Glassmorphism Sticky Navigation Bar -->
    <nav class="fixed w-full bg-white/70 shadow-lg z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="#home" class="text-2xl font-bold text-orange-500">🍕 Pizza Palace</a>
                <ul class="flex space-x-8 items-center">
                    <li><a href="#home" class="hover:text-orange-500 transition-colors">Home</a></li>
                    <li><a href="#about" class="hover:text-orange-500 transition-colors">About</a></li>
                    <li><a href="#features" class="hover:text-orange-500 transition-colors">Features</a></li>
                    <li><a href="#contact" class="hover:text-orange-500 transition-colors">Contact</a></li>
                    <li><a href="Login_Page.php" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors">Login</a></li>
                    <!-- <li><a href="Signup_Page.php" class="bg-white text-orange-500 border border-orange-500 px-4 py-2 rounded-lg hover:bg-orange-500 transition-colors">Sign Up</a></li> -->
                </ul>
            </div>
        </div>
    </nav>

    <!-- Home Section -->
    <section id="home" class="min-h-screen flex items-center justify-center pt-20 relative">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="../assets/Background.jpg" alt="Background" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-br from-white-100/20 to-black/80"></div>
        </div>
        <!-- Content -->
        <div class="container mx-auto px-6 text-center relative z-10">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6 text-shadow">Pizza Palace: Employee Management System</h2>
            <p class="text-lg md:text-xl text-white max-w-3xl mx-auto text-shadow-sm">Streamline your organization's workflow — manage employee data, attendance, payroll, and performance efficiently, all in one place.</p>
        </div>
    </section>

    <style>
        .text-shadow {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        .text-shadow-sm {
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }
    </style>

    <!-- About Section -->
    <section id="about" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl text-center md:text-4xl font-bold text-orange-700 mb-4">About the System</h2>
            <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                
                <!-- Left: Description -->
                <div>
                    <p class="text-lg text-gray-600 max-w-xl text-justify">This Employee Management System helps administrators and HR teams simplify their daily operations. It provides easy access to employee records, job details, and attendance tracking with a secure and user-friendly interface.</p>
                </div>

                <!-- Right: Bento-style image grid (2x2) -->
                <div class="max-w-xs mx-auto md:ml-auto">
                    <div class="grid grid-cols-2 gap-4 md:gap-6">
                        <div class="overflow-hidden rounded-xl shadow">
                            <img src="../assets/pizza1.jpg" alt="Pizza 1" class="w-full h-full object-cover aspect-square">
                        </div>
                        <div class="overflow-hidden rounded-xl shadow">
                            <img src="../assets/pizza2.jpg" alt="Pizza 2" class="w-full h-full object-cover aspect-square">
                        </div>
                        <div class="overflow-hidden rounded-xl shadow">
                            <img src="../assets/pizza3.jpg" alt="Pizza 3" class="w-full h-full object-cover aspect-square">
                        </div>
                        <div class="overflow-hidden rounded-xl shadow">
                            <img src="../assets/pizza4.jpg" alt="Pizza 4" class="w-full h-full object-cover aspect-square">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-orange-50">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-orange-700 mb-12">System Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <i class="fa-solid fa-user-gear text-3xl text-orange-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">User Management</h3>
                    <p class="text-gray-600">Add, update, and manage employee accounts securely. Assign roles and permissions with ease.</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <i class="fa-solid fa-calendar-check text-3xl text-orange-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Attendance Tracking</h3>
                    <p class="text-gray-600">Monitor clock-in and clock-out times, leaves, and absences — all recorded automatically.</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <i class="fa-solid fa-money-check-dollar text-3xl text-orange-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Payroll Management</h3>
                    <p class="text-gray-600">Automatically calculate salaries, deductions, and generate payslips with full transparency.</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <i class="fa-solid fa-chart-line text-3xl text-orange-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Performance Evaluation</h3>
                    <p class="text-gray-600">Track employee productivity, set KPIs, and generate performance-based reports.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="space-y-6">
                    <h2 class="text-3xl md:text-4xl font-bold text-orange-700 mb-8">Contact Support</h2>
                    <div class="space-y-4">
                        <p class="flex items-center text-gray-600">
                            <i class="fa-solid fa-envelope w-8 text-orange-500"></i>
                            <span class="font-semibold mr-2">Email:</span> support@employeesystem.com
                        </p>
                        <p class="flex items-center text-gray-600">
                            <i class="fa-solid fa-phone w-8 text-orange-500"></i>
                            <span class="font-semibold mr-2">Phone:</span> +63 912 345 6789
                        </p>
                        <p class="flex items-center text-gray-600">
                            <i class="fa-solid fa-location-dot w-8 text-orange-500"></i>
                            <span class="font-semibold mr-2">Office:</span> Tunasan Ruby Park Jasmin Street, Metro Manila
                        </p>
                    </div>
                </div>
                <div class="rounded-xl overflow-hidden shadow-lg">
                    <iframe 
                        src="https://maps.google.com/maps?q=manila&t=&z=13&ie=UTF8&iwloc=&output=embed" 
                        class="w-full h-[400px]"
                        style="border:0;" 
                        allowfullscreen>
                    </iframe>
            </div>
        </div>
    </section>

</body>
</html>
