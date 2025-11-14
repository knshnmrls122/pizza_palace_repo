<?php
// sidebar may be included after session_start() and after db connection is available
// compute unread notifications for the logged-in employee (if possible)
$unreadCount = 0;
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Employee' && isset($conn) && isset($_SESSION['employee_id'])) {
    $empId = $_SESSION['employee_id'];
    // Count unread notifications: notifications targeted to all (employee_id IS NULL) or to this employee, but not present in NotificationsRead
    $countQ = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM Notifications n LEFT JOIN NotificationsRead nr ON n.notification_id = nr.notification_id AND nr.employee_id = ? WHERE (n.employee_id IS NULL OR n.employee_id = ?) AND nr.notification_id IS NULL");
    if ($countQ) {
        mysqli_stmt_bind_param($countQ, 'ss', $empId, $empId);
        mysqli_stmt_execute($countQ);
        $res = mysqli_stmt_get_result($countQ);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $unreadCount = intval($row['cnt'] ?? 0);
        }
        mysqli_stmt_close($countQ);
    }
}

?>
<div class="fixed left-0 top-0 h-screen w-64 bg-white shadow-lg border-r border-gray-200 flex flex-col z-50">
    <!-- Logo and Title -->
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-orange-600 flex items-center gap-2">
            <i class="fas fa-pizza-slice"></i>
            <span>Employee Panel</span>
        </h2>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-4 px-3 space-y-1">
        <a href="dashboard.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fas fa-tachometer-alt w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Dashboard</span>
        </a>
        <a href="attendance.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fas fa-calendar-check w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Attendance</span>
        </a>
        <a href="notification.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fas fa-bell w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Notifications</span>
            <?php if ($unreadCount > 0): ?>
                <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700"><?php echo $unreadCount; ?></span>
            <?php endif; ?>
        </a>
        <!-- <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fas fa-user w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Profile</span>
        </a> -->
    </nav>

    <!-- Logout -->
    <div class="border-t border-gray-200 p-4">
        <a href="../includes/logout.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 rounded-lg transition-colors group">
            <i class="fas fa-sign-out-alt w-5 h-5 mr-3 text-gray-400 group-hover:text-red-500"></i>
            <span class="font-medium group-hover:text-red-500">Logout</span>
        </a>
    </div>
</div>
