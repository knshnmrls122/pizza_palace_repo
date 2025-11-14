<div class="fixed left-0 top-0 h-screen w-64 bg-white shadow-lg border-r border-gray-200 flex flex-col z-50">
    <!-- Logo and Title Section -->
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-orange-600 flex items-center gap-2">
            <i class="fas fa-pizza-slice"></i>
            <span>Admin Panel</span>
        </h2>
    </div>
    
    <!-- Navigation Links -->
    <nav class="flex-1 py-4 px-3 space-y-1">
        <a href="dashboard.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fas fa-home w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Dashboard</span>
        </a>
        <a href="employees.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fas fa-users w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Manage Employees</span>
        </a>
        <a href="attendance.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fas fa-calendar-check w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Attendance</span>
        </a>
        <a href="reports.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fas fa-chart-bar w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Reports</span>
        </a>
        <a href="notifications.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fas fa-bell w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Notifications</span>
        </a>
                <a href="payroll.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fa-solid fa-money-check-dollar  w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Payroll</span>
        </a>
        <a href="notifications.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 rounded-lg transition-colors group">
            <i class="fas fa-bell w-5 h-5 mr-3 text-gray-400 group-hover:text-orange-500"></i>
            <span class="font-medium group-hover:text-orange-500">Notifications</span>
        </a>
    </nav>
    
    <!-- Logout Section -->
    <div class="border-t border-gray-200 p-4">
        <a href="../includes/logout.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 rounded-lg transition-colors group">
            <i class="fas fa-sign-out-alt w-5 h-5 mr-3 text-gray-400 group-hover:text-red-500"></i>
            <span class="font-medium group-hover:text-red-500">Logout</span>
        </a>
    </div>
</div>