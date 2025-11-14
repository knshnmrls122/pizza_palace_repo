<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Employee') {
    header("Location: ../pages/Login_Page.php");
    exit();
}
include("../includes/db_connection.php");
include("../includes/header.php");
// sidebar will be included inside body so it appears after the document head

$message = '';
$error = '';

$employee_id = $_SESSION['employee_id'];

// Today's date
$today = date('Y-m-d');

// Handle actions: checkin / checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'checkin') {
        // Prevent duplicate
        $stmt = mysqli_prepare($conn, "SELECT attendance_id, time_in FROM Attendance WHERE employee_id = ? AND date = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ss', $employee_id, $today);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            // already has record
            $message = 'You have already checked in today.';
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            $time_in = date('H:i:s');
            // Determine late threshold (09:00)
            $status = ($time_in > '09:00:00') ? 'Late' : 'Present';
            $ins = mysqli_prepare($conn, "INSERT INTO Attendance (employee_id, date, status, time_in) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, 'ssss', $employee_id, $today, $status, $time_in);
            if (mysqli_stmt_execute($ins)) {
                $message = 'Checked in at ' . date('H:i');
            } else {
                $error = 'Failed to check in.';
            }
            mysqli_stmt_close($ins);
        }
    } elseif ($action === 'checkout') {
        // Find today's attendance
        $stmt = mysqli_prepare($conn, "SELECT attendance_id, time_out FROM Attendance WHERE employee_id = ? AND date = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ss', $employee_id, $today);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        if (!$row) {
            $error = 'No check-in found for today.';
        } elseif ($row['time_out']) {
            $message = 'You have already checked out.';
        } else {
            $time_out = date('H:i:s');
            $upd = mysqli_prepare($conn, "UPDATE Attendance SET time_out = ? WHERE attendance_id = ?");
            mysqli_stmt_bind_param($upd, 'si', $time_out, $row['attendance_id']);
            if (mysqli_stmt_execute($upd)) {
                $message = 'Checked out at ' . date('H:i');
            } else {
                $error = 'Failed to check out.';
            }
            mysqli_stmt_close($upd);
        }
    }
}

// Fetch today's attendance for this employee
$stmt = mysqli_prepare($conn, "SELECT * FROM Attendance WHERE employee_id = ? AND date = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ss', $employee_id, $today);
mysqli_stmt_execute($stmt);
$todayRes = mysqli_stmt_get_result($stmt);
$todayAtt = mysqli_fetch_assoc($todayRes);
mysqli_stmt_close($stmt);

// Fetch last 7 days history for this employee
$histStmt = mysqli_prepare($conn, "SELECT date, status, time_in, time_out FROM Attendance WHERE employee_id = ? ORDER BY date DESC LIMIT 7");
mysqli_stmt_bind_param($histStmt, 's', $employee_id);
mysqli_stmt_execute($histStmt);
$histRes = mysqli_stmt_get_result($histStmt);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>My Attendance - Pizza Palace</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <?php include('sidebar.php'); ?>
    <main class="ml-64 p-8">
        <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Hello, <?php echo htmlspecialchars($_SESSION['first_name']); ?></h1>
            <p class="text-gray-600">Manage your attendance for <?php echo date('F d, Y'); ?>.</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Today's Attendance</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="text-lg font-semibold text-gray-800"><?php echo $todayAtt ? htmlspecialchars($todayAtt['status']) : '-'; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Time In</p>
                    <p class="text-lg font-semibold text-gray-800"><?php echo $todayAtt && $todayAtt['time_in'] ? htmlspecialchars(substr($todayAtt['time_in'],0,5)) : '-'; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Time Out</p>
                    <p class="text-lg font-semibold text-gray-800"><?php echo $todayAtt && $todayAtt['time_out'] ? htmlspecialchars(substr($todayAtt['time_out'],0,5)) : '-'; ?></p>
                </div>
            </div>
            <div class="mt-4">
                <?php if (!$todayAtt): ?>
                    <form method="POST" class="inline-block">
                        <input type="hidden" name="action" value="checkin">
                        <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">Check In</button>
                    </form>
                <?php elseif (!$todayAtt['time_out']): ?>
                    <form method="POST" class="inline-block">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">Check Out</button>
                    </form>
                <?php else: ?>
                    <span class="text-sm text-gray-600">✓ You have completed today's attendance.</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Attendance</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                    <thead>
                        <tr class="text-left text-sm text-gray-600">
                            <th class="py-2">Date</th>
                            <th class="py-2">Status</th>
                            <th class="py-2">Time In</th>
                            <th class="py-2">Time Out</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($row = mysqli_fetch_assoc($histRes)): ?>
                        <tr>
                            <td class="py-3"><?php echo htmlspecialchars(date('F d, Y', strtotime($row['date']))); ?></td>
                            <td class="py-3"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td class="py-3"><?php echo $row['time_in'] ? htmlspecialchars(substr($row['time_in'],0,5)) : '-'; ?></td>
                            <td class="py-3"><?php echo $row['time_out'] ? htmlspecialchars(substr($row['time_out'],0,5)) : '-'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
