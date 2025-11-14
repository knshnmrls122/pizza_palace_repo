<?php
/**
 * Setup script to enable employee_id column in Notifications table
 * This allows admins to target notifications to specific employees
 * 
 * Access this page once to run the migration, then you can delete or disable it
 */

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../pages/Login_Page.php");
    exit();
}

include('../includes/db_connection.php');
include('../includes/header.php');

$message = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'migrate') {
    // Check if column already exists
    $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM Notifications LIKE 'employee_id'");
    
    if ($colCheck && mysqli_num_rows($colCheck) > 0) {
        $message = 'Column employee_id already exists in Notifications table.';
    } else {
        // Add the column
        $alter = "ALTER TABLE Notifications ADD COLUMN employee_id VARCHAR(100) NULL AFTER notification_id";
        if (mysqli_query($conn, $alter)) {
            $success = true;
            $message = 'Successfully added employee_id column to Notifications table! You can now target notifications to specific employees.';
        } else {
            $error = 'Failed to add column: ' . mysqli_error($conn);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Setup Notifications - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <?php include('sidebar.php'); ?>
    <main class="ml-64 p-8">
        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Setup Notifications</h1>
                <p class="text-sm text-gray-600">Enable employee_id column for targeted notifications</p>
            </div>

            <?php if ($message): ?>
                <div class="mb-4 p-4 <?php echo $success ? 'bg-green-100 border border-green-200 text-green-800' : 'bg-blue-100 border border-blue-200 text-blue-800'; ?> rounded">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold mb-4">Database Setup</h2>
                <p class="text-sm text-gray-700 mb-4">
                    This setup adds an <code class="bg-gray-100 px-2 py-1 rounded">employee_id</code> column to the Notifications table.
                    This allows you to:
                </p>
                <ul class="text-sm text-gray-700 space-y-2 mb-6 list-disc list-inside">
                    <li>Target notifications to specific employees</li>
                    <li>Create global notifications (leave employee_id NULL)</li>
                    <li>Track which employees have read which notifications</li>
                </ul>

                <div class="bg-gray-50 p-4 rounded mb-6">
                    <h3 class="text-sm font-medium mb-2">Column Info:</h3>
                    <p class="text-xs text-gray-600 font-mono">ALTER TABLE Notifications ADD COLUMN employee_id VARCHAR(100) NULL AFTER notification_id;</p>
                </div>

                <form method="POST" class="space-y-3">
                    <input type="hidden" name="action" value="migrate">
                    <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                        <i class="fas fa-database mr-2"></i>Run Migration
                    </button>
                </form>

                <p class="text-xs text-gray-500 mt-4">
                    After running this migration, you can use the <a href="notifications.php" class="text-orange-500 hover:underline">Notifications page</a> to send targeted notifications.
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">Next Steps:</h3>
                <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside">
                    <li>Click "Run Migration" above to add the employee_id column.</li>
                    <li>Go to <a href="notifications.php" class="text-orange-500 hover:underline">Manage Notifications</a> to start sending targeted notifications.</li>
                    <li>Select a specific employee as recipient or leave it blank for all employees.</li>
                </ol>
            </div>
        </div>
    </main>
</body>
</html>
