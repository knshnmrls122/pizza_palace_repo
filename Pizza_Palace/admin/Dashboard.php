<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../pages/Login_Page.php");
    exit();
}
include("../includes/db_connection.php");
include("../includes/header.php");

// Fetch statistics
$today = date('Y-m-d');

// Total Employees
$empQuery = "SELECT COUNT(*) as total FROM Users WHERE role = 'Employee'";
$empResult = mysqli_query($conn, $empQuery);
$totalEmployees = mysqli_fetch_assoc($empResult)['total'];

// Present Today
$presentQuery = "SELECT COUNT(*) as present FROM Attendance WHERE date = '$today' AND status = 'Present'";
$presentResult = mysqli_query($conn, $presentQuery);
$presentToday = mysqli_fetch_assoc($presentResult)['present'];

// Late Today
$lateQuery = "SELECT COUNT(*) as late FROM Attendance WHERE date = '$today' AND status = 'Late'";
$lateResult = mysqli_query($conn, $lateQuery);
$lateToday = mysqli_fetch_assoc($lateResult)['late'];

// Recent Notifications
$notifQuery = "SELECT * FROM Notifications ORDER BY date_sent DESC LIMIT 5";
$notifResult = mysqli_query($conn, $notifQuery);

// Top Performers
$perfQuery = "SELECT u.first_name, u.last_name, p.performance_score 
              FROM Performance p 
              JOIN Users u ON p.employee_id = u.employee_id 
              WHERE p.month = DATE_FORMAT(NOW(), '%Y-%m')
              ORDER BY p.performance_score DESC 
              LIMIT 3";
$perfResult = mysqli_query($conn, $perfQuery);

// Prepare attendance labels and counts for the last 6 days (keeps labels aligned with dates)
$attendance_labels = [];
$attendance_present = [];
$attendance_late = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $attendance_labels[] = date('D', strtotime($date)); // Short day name Mon, Tue, ...
    $q1 = "SELECT COUNT(*) as count FROM Attendance WHERE date = '$date' AND status = 'Present'";
    $r1 = mysqli_query($conn, $q1);
    $attendance_present[] = (int) mysqli_fetch_assoc($r1)['count'];

    $q2 = "SELECT COUNT(*) as count FROM Attendance WHERE date = '$date' AND status = 'Late'";
    $r2 = mysqli_query($conn, $q2);
    $attendance_late[] = (int) mysqli_fetch_assoc($r2)['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pizza Palace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <?php include("sidebar.php"); ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?> 👋
            </h1>
            <p class="text-gray-600">Here's your administrative overview for <?php echo date('F d, Y'); ?></p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Employees Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-orange-100 rounded-lg p-3">
                        <i class="fas fa-users text-orange-500 text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-orange-500 bg-orange-50 px-2.5 py-0.5 rounded-full">Total</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-700 mb-1"><?php echo $totalEmployees; ?></h3>
                <p class="text-gray-500 text-sm">Total Employees</p>
            </div>

            <!-- Present Today Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 rounded-lg p-3">
                        <i class="fas fa-user-check text-green-500 text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-green-500 bg-green-50 px-2.5 py-0.5 rounded-full">Today</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-700 mb-1"><?php echo $presentToday; ?></h3>
                <p class="text-gray-500 text-sm">Present Today</p>
            </div>

            <!-- Late Today Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-yellow-100 rounded-lg p-3">
                        <i class="fas fa-clock text-yellow-500 text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-yellow-500 bg-yellow-50 px-2.5 py-0.5 rounded-full">Today</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-700 mb-1"><?php echo $lateToday; ?></h3>
                <p class="text-gray-500 text-sm">Late Today</p>
            </div>
        </div>

        <!-- Analytics Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Attendance Overview -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Weekly Attendance Overview</h2>
                    <select id="weekSelector" class="text-sm border rounded-lg px-2 py-1">
                        <option value="thisWeek">This Week</option>
                        <option value="lastWeek">Last Week</option>
                    </select>
                </div>
                <div class="h-64">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            <!-- Performance Distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Performance Distribution</h2>
                    <select id="monthSelector" class="text-sm border rounded-lg px-2 py-1">
                        <option value="thisMonth">This Month</option>
                        <option value="lastMonth">Last Month</option>
                    </select>
                </div>
                <div class="h-64">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Attendance Chart
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(attendanceCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($attendance_labels); ?>,
                    datasets: [{
                        label: 'Present',
                        data: <?php echo json_encode($attendance_present); ?>,
                        backgroundColor: 'rgba(34, 197, 94, 0.2)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    },
                    {
                        label: 'Late',
                        data: <?php echo json_encode($attendance_late); ?>,
                        backgroundColor: 'rgba(234, 179, 8, 0.2)',
                        borderColor: 'rgb(234, 179, 8)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });

            // Performance Chart
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            const performanceChart = new Chart(performanceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Excellent (90-100%)', 'Good (70-89%)', 'Average (50-69%)', 'Needs Improvement (<50%)'],
                    datasets: [{
                        data: [<?php
                            // Get performance distribution for current month
                            $excellent = mysqli_fetch_assoc(mysqli_query($conn, 
                                "SELECT COUNT(*) as count FROM Performance 
                                WHERE month = DATE_FORMAT(NOW(), '%Y-%m') 
                                AND performance_score >= 90"))['count'];
                            $good = mysqli_fetch_assoc(mysqli_query($conn, 
                                "SELECT COUNT(*) as count FROM Performance 
                                WHERE month = DATE_FORMAT(NOW(), '%Y-%m') 
                                AND performance_score >= 70 AND performance_score < 90"))['count'];
                            $average = mysqli_fetch_assoc(mysqli_query($conn, 
                                "SELECT COUNT(*) as count FROM Performance 
                                WHERE month = DATE_FORMAT(NOW(), '%Y-%m') 
                                AND performance_score >= 50 AND performance_score < 70"))['count'];
                            $improvement = mysqli_fetch_assoc(mysqli_query($conn, 
                                "SELECT COUNT(*) as count FROM Performance 
                                WHERE month = DATE_FORMAT(NOW(), '%Y-%m') 
                                AND performance_score < 50"))['count'];
                            echo "$excellent,$good,$average,$improvement";
                        ?>],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',  // Green
                            'rgba(59, 130, 246, 0.8)', // Blue
                            'rgba(234, 179, 8, 0.8)',  // Yellow
                            'rgba(239, 68, 68, 0.8)'   // Red
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });

            // Handle week selector change
            document.getElementById('weekSelector').addEventListener('change', function() {
                // You can implement AJAX call here to update the attendance chart
            });

            // Handle month selector change
            document.getElementById('monthSelector').addEventListener('change', function() {
                // You can implement AJAX call here to update the performance chart
            });
        });
    </script>
</body>
</html>