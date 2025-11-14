<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Employee') {
    header('Location: ../pages/Login_Page.php');
    exit();
}
include('../includes/db_connection.php');
include('../includes/header.php');

$employee_id = $_SESSION['employee_id'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

// Today's attendance
$todayStmt = mysqli_prepare($conn, "SELECT status, time_in, time_out FROM Attendance WHERE employee_id = ? AND date = CURDATE() LIMIT 1");
mysqli_stmt_bind_param($todayStmt, 's', $employee_id);
mysqli_stmt_execute($todayStmt);
$todayRes = mysqli_stmt_get_result($todayStmt);
$today = mysqli_fetch_assoc($todayRes);
mysqli_stmt_close($todayStmt);

// Last 7 days
$histStmt = mysqli_prepare($conn, "SELECT date, status FROM Attendance WHERE employee_id = ? ORDER BY date DESC LIMIT 7");
mysqli_stmt_bind_param($histStmt, 's', $employee_id);
mysqli_stmt_execute($histStmt);
$histRes = mysqli_stmt_get_result($histStmt);
mysqli_stmt_close($histStmt);

// notifications moved to employee/notifications.php (panel removed from dashboard)

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <title>Employee Dashboard - Pizza Palace</title>
    <style>
        /* small helper to keep cards visually balanced */
        .stat { min-width: 150px; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include('sidebar.php'); ?>

    <main class="ml-64 p-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Welcome back, <?php echo htmlspecialchars($first_name); ?> 👋</h1>
                    <p class="text-sm text-gray-600">Here's a quick summary of your recent activity.</p>
                </div>
                <div class="flex gap-2">
                    <a href="attendance.php" class="px-4 py-2 bg-orange-500 text-white rounded">Take Attendance</a>
                    <a href="profile.php" class="px-4 py-2 bg-gray-100 rounded">Profile</a>
                </div>
            </div>

            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold mb-4">Today's Attendance</h2>
                    <?php if ($today): ?>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <div class="p-4 bg-gray-50 rounded stat">
                                <div class="text-sm text-gray-500">Status</div>
                                <div class="text-xl font-semibold mt-1"><?php echo htmlspecialchars($today['status']); ?></div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded stat">
                                <div class="text-sm text-gray-500">Time In</div>
                                <div class="text-xl font-semibold mt-1"><?php echo htmlspecialchars($today['time_in']); ?></div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded stat">
                                <div class="text-sm text-gray-500">Time Out</div>
                                <div class="text-xl font-semibold mt-1"><?php echo htmlspecialchars($today['time_out'] ?? '—'); ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-600">You haven't recorded attendance for today yet. Use the button above to check in.</p>
                    <?php endif; ?>

                    <hr class="my-6" />

                    <h3 class="text-md font-medium mb-3">Last 7 days</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php while ($h = mysqli_fetch_assoc($histRes)): ?>
                            <div class="px-3 py-2 rounded bg-gray-50 border text-sm">
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($h['date']); ?></div>
                                <div class="font-medium"><?php echo htmlspecialchars($h['status']); ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
