<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../pages/Login_Page.php");
    exit();
}
include('../includes/db_connection.php');
include('../includes/header.php');

$message = '';
$error = '';

// Check if Notifications table has an employee_id column (for targeting individual employees)
$hasEmployeeColumn = false;
$colCheck = mysqli_query($conn, "SHOW COLUMNS FROM Notifications LIKE 'employee_id'");
if ($colCheck && mysqli_num_rows($colCheck) > 0) {
    $hasEmployeeColumn = true;
}

// Fetch employees for the recipient select
$emps = mysqli_query($conn, "SELECT employee_id, first_name, last_name FROM Users WHERE role = 'Employee' ORDER BY first_name, last_name");
$empNames = [];
if ($emps) {
    while ($e = mysqli_fetch_assoc($emps)) {
        $empNames[$e['employee_id']] = $e['first_name'] . ' ' . $e['last_name'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $msg = trim($_POST['message'] ?? '');
        $recipient = trim($_POST['employee_id'] ?? ''); // empty = all
        if ($title === '' || $msg === '') {
            $error = 'Title and message are required.';
        } else {
            if ($hasEmployeeColumn) {
                // If recipient specified, validate it exists
                if ($recipient !== '' && !isset($empNames[$recipient])) {
                    $error = 'Selected employee not found.';
                } else {
                    if ($recipient === '') {
                        // Insert global notification but with employee_id = NULL
                        $ins = mysqli_prepare($conn, "INSERT INTO Notifications (employee_id, title, message, date_sent) VALUES (NULL, ?, ?, NOW())");
                        mysqli_stmt_bind_param($ins, 'ss', $title, $msg);
                    } else {
                        // Insert targeted notification
                        $ins = mysqli_prepare($conn, "INSERT INTO Notifications (employee_id, title, message, date_sent) VALUES (?, ?, ?, NOW())");
                        mysqli_stmt_bind_param($ins, 'sss', $recipient, $title, $msg);
                    }
                    if ($ins) {
                        if (mysqli_stmt_execute($ins)) {
                            $message = 'Notification created.';
                        } else {
                            $error = 'Failed to create notification.';
                        }
                        mysqli_stmt_close($ins);
                    } else {
                        $error = 'Failed to prepare statement.';
                    }
                }
            } else {
                // Fallback: table doesn't support targeting, insert global notification
                $ins = mysqli_prepare($conn, "INSERT INTO Notifications (title, message, date_sent) VALUES (?, ?, NOW())");
                mysqli_stmt_bind_param($ins, 'ss', $title, $msg);
                if (mysqli_stmt_execute($ins)) {
                    $message = 'Notification created (global). To target individual employees enable Notifications.employee_id column.';
                } else {
                    $error = 'Failed to create notification.';
                }
                mysqli_stmt_close($ins);
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['notification_id'] ?? 0);
        if ($id) {
            $del = mysqli_prepare($conn, "DELETE FROM Notifications WHERE notification_id = ?");
            mysqli_stmt_bind_param($del, 'i', $id);
            if (mysqli_stmt_execute($del)) $message = 'Deleted.'; else $error = 'Failed to delete.';
            mysqli_stmt_close($del);
        }
    }
}

// Fetch recent notifications (include employee_id if present)
if ($hasEmployeeColumn) {
    $q = mysqli_prepare($conn, "SELECT notification_id, employee_id, title, message, date_sent FROM Notifications ORDER BY date_sent DESC LIMIT 50");
} else {
    $q = mysqli_prepare($conn, "SELECT notification_id, title, message, date_sent FROM Notifications ORDER BY date_sent DESC LIMIT 50");
}
mysqli_stmt_execute($q);
$res = mysqli_stmt_get_result($q);
mysqli_stmt_close($q);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Notifications - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <?php include('sidebar.php'); ?>
    <main class="ml-64 p-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Manage Notifications</h1>
                <button id="openCreateModal" class="px-4 py-2 bg-orange-500 text-white rounded">+ Create Notification</button>
            </div>

            <?php if ($message): ?>
                <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold mb-4">Recent Notifications</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                            <thead>
                                <tr class="text-left text-sm text-gray-600">
                                    <th class="py-2">Date</th>
                                    <th class="py-2">Title</th>
                                    <th class="py-2">Message</th>
                                    <th class="py-2">Recipient</th>
                                    <th class="py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php while ($row = mysqli_fetch_assoc($res)): ?>
                                <tr>
                                    <td class="py-3"><?php echo htmlspecialchars($row['date_sent']); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars(substr($row['message'],0,150)); ?></td>
                                    <td class="py-3"><?php echo ($hasEmployeeColumn && isset($row['employee_id']) && $row['employee_id'] !== null && $row['employee_id'] !== '') ? htmlspecialchars($empNames[$row['employee_id']] ?? $row['employee_id']) : '<em>All Employees</em>'; ?></td>
                                    <td class="py-3">
                                        <form method="POST" style="display:inline-block" onsubmit="return confirm('Delete this notification?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="notification_id" value="<?php echo intval($row['notification_id']); ?>">
                                            <button class="px-2 py-1 bg-red-50 text-red-600 rounded">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Create Notification Modal -->
    <div id="createModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">Create Notification</h3>
                <button id="closeModal" class="text-gray-500 text-2xl">&times;</button>
            </div>
            <form id="createForm" method="POST" class="px-6 py-4 space-y-4">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="text-sm font-medium">Recipient</label>
                    <select name="employee_id" class="mt-1 w-full px-3 py-2 border rounded">
                        <option value="">All Employees</option>
                        <?php foreach ($empNames as $eid => $ename): ?>
                            <option value="<?php echo htmlspecialchars($eid); ?>"><?php echo htmlspecialchars($ename . " ({$eid})"); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Title</label>
                    <input type="text" name="title" class="mt-1 w-full px-3 py-2 border rounded" required />
                </div>
                <div>
                    <label class="text-sm font-medium">Message</label>
                    <textarea name="message" rows="5" class="mt-1 w-full px-3 py-2 border rounded" required></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" id="cancelBtn" class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded">Send</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('createModal');
        const openBtn = document.getElementById('openCreateModal');
        const closeBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        openBtn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    </script>
</body>
</html>
