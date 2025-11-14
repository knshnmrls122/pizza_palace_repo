<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Employee') {
    header('Location: ../pages/Login_Page.php');
    exit();
}
include('../includes/db_connection.php');
include('../includes/header.php');

$employee_id = $_SESSION['employee_id'];

// Handle marking as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'mark_read') {
        $nid = intval($_POST['notification_id'] ?? 0);
        if ($nid) {
            // Insert a read record if not exists
            $ins = mysqli_prepare($conn, "INSERT IGNORE INTO NotificationsRead (notification_id, employee_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($ins, 'is', $nid, $employee_id);
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);
        }
    } elseif ($action === 'mark_all') {
        // Insert all unread notifications for this employee
        $insAll = mysqli_prepare($conn, "INSERT INTO NotificationsRead (notification_id, employee_id)
            SELECT n.notification_id, ? FROM Notifications n
            LEFT JOIN NotificationsRead nr ON n.notification_id = nr.notification_id AND nr.employee_id = ?
            WHERE nr.notification_id IS NULL AND (n.employee_id IS NULL OR n.employee_id = ?)");
        if ($insAll) {
            mysqli_stmt_bind_param($insAll, 'sss', $employee_id, $employee_id, $employee_id);
            mysqli_stmt_execute($insAll);
            mysqli_stmt_close($insAll);
        }
    }
}

// Fetch recent notifications for this employee, with read status
$noteStmt = mysqli_prepare($conn, "SELECT n.notification_id, n.title, n.message, n.date_sent, nr.read_at
    FROM Notifications n
    LEFT JOIN NotificationsRead nr ON n.notification_id = nr.notification_id AND nr.employee_id = ?
    WHERE (n.employee_id IS NULL OR n.employee_id = ?)
    ORDER BY n.date_sent DESC
    LIMIT 50");
mysqli_stmt_bind_param($noteStmt, 'ss', $employee_id, $employee_id);
mysqli_stmt_execute($noteStmt);
$noteRes = mysqli_stmt_get_result($noteStmt);
mysqli_stmt_close($noteStmt);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <title>Notifications - Employee</title>
</head>
<body class="bg-gray-50">
    <?php include('sidebar.php'); ?>
    <main class="ml-64 p-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
                    <p class="text-sm text-gray-600">Latest announcements and messages from management.</p>
                </div>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="mark_all">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Mark All as Read</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <?php if (mysqli_num_rows($noteRes) > 0): ?>
                    <ul class="space-y-4">
                        <?php while ($n = mysqli_fetch_assoc($noteRes)): ?>
                            <li class="p-4 bg-gray-50 rounded border <?php echo $n['read_at'] ? 'border-gray-200 opacity-75' : 'border-orange-200 bg-orange-50'; ?>">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($n['title']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($n['date_sent']); ?></div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <?php if (!$n['read_at']): ?>
                                            <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-medium">Unread</span>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="action" value="mark_read">
                                                <input type="hidden" name="notification_id" value="<?php echo intval($n['notification_id']); ?>">
                                                <button type="submit" class="text-xs px-2 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200">Mark Read</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="inline-block px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-medium">Read</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($n['message'])); ?></div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-sm text-gray-600">No notifications yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
