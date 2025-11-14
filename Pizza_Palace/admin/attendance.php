<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../pages/Login_Page.php");
    exit();
}
include("../includes/db_connection.php");
include("../includes/header.php");

$message = '';
$error = '';

// Handle save (bulk attendance)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $date = $_POST['date'] ?? date('Y-m-d');
    // validate date
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d) {
        $error = 'Invalid date format.';
    } else {
        $employee_ids = $_POST['employee_id'] ?? [];
        $statuses = $_POST['status'] ?? [];
        $times_in = $_POST['time_in'] ?? [];
        $times_out = $_POST['time_out'] ?? [];

        $cnt = 0;
        for ($i = 0; $i < count($employee_ids); $i++) {
            $emp = trim($employee_ids[$i]);
            if ($emp === '') continue;
            $status = in_array($statuses[$i], ['Present','Absent','Late']) ? $statuses[$i] : 'Present';
            $t_in = trim($times_in[$i]) ?: null;
            $t_out = trim($times_out[$i]) ?: null;

            // Check if record exists
            $sel = mysqli_prepare($conn, "SELECT attendance_id FROM Attendance WHERE employee_id = ? AND date = ? LIMIT 1");
            mysqli_stmt_bind_param($sel, 'ss', $emp, $date);
            mysqli_stmt_execute($sel);
            mysqli_stmt_store_result($sel);
            if (mysqli_stmt_num_rows($sel) > 0) {
                mysqli_stmt_bind_result($sel, $attId);
                mysqli_stmt_fetch($sel);
                mysqli_stmt_close($sel);
                // Update
                $upd = mysqli_prepare($conn, "UPDATE Attendance SET status = ?, time_in = ?, time_out = ? WHERE attendance_id = ?");
                mysqli_stmt_bind_param($upd, 'sssi', $status, $t_in, $t_out, $attId);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);
            } else {
                mysqli_stmt_close($sel);
                // Insert
                $ins = mysqli_prepare($conn, "INSERT INTO Attendance (employee_id, date, status, time_in, time_out) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($ins, 'sssss', $emp, $date, $status, $t_in, $t_out);
                mysqli_stmt_execute($ins);
                mysqli_stmt_close($ins);
            }
            $cnt++;
        }
        $message = "Saved attendance for $cnt employees on $date.";
    }
}

// Default date - today unless provided via GET
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Fetch employees
$empQ = "SELECT employee_id, first_name, last_name FROM Users WHERE role = 'Employee' ORDER BY first_name, last_name";
$empRes = mysqli_query($conn, $empQ);

// Fetch attendance for selected date into map
$attendanceMap = [];
$attQ = mysqli_prepare($conn, "SELECT status, time_in, time_out, employee_id FROM Attendance WHERE date = ?");
mysqli_stmt_bind_param($attQ, 's', $selectedDate);
mysqli_stmt_execute($attQ);
$attResult = mysqli_stmt_get_result($attQ);
while ($r = mysqli_fetch_assoc($attResult)) {
    $attendanceMap[$r['employee_id']] = $r;
}
mysqli_stmt_close($attQ);

// Recent history
$histQ = "SELECT date,
    SUM(status = 'Present') as present,
    SUM(status = 'Late') as late,
    SUM(status = 'Absent') as absent
    FROM Attendance
    GROUP BY date
    ORDER BY date DESC
    LIMIT 14";
$histRes = mysqli_query($conn, $histQ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Attendance - Pizza Palace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <?php include('sidebar.php'); ?>

    <main class="ml-64 p-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Attendance</h1>
                <form method="GET" class="flex items-center gap-2">
                    <input type="date" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>" class="px-3 py-2 border rounded-lg" />
                    <button type="submit" class="px-3 py-2 bg-orange-500 text-white rounded-lg">View</button>
                </form>
            </div>

            <?php if ($message): ?>
                <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>">

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Mark Attendance for <?php echo date('F d, Y', strtotime($selectedDate)); ?></h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-sm text-gray-600">
                                    <th class="py-2">Employee</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2">Time In</th>
                                    <th class="py-2">Time Out</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                                <?php while ($emp = mysqli_fetch_assoc($empRes)): 
                                    $eid = $emp['employee_id'];
                                    $att = $attendanceMap[$eid] ?? null;
                                ?>
                                <tr>
                                    <td class="py-3"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?><input type="hidden" name="employee_id[]" value="<?php echo htmlspecialchars($eid); ?>"></td>
                                    <td class="py-3">
                                        <select name="status[]" class="px-2 py-1 border rounded">
                                            <option value="Present" <?php echo ($att && $att['status'] === 'Present') ? 'selected' : ''; ?>>Present</option>
                                            <option value="Absent" <?php echo ($att && $att['status'] === 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                            <option value="Late" <?php echo ($att && $att['status'] === 'Late') ? 'selected' : ''; ?>>Late</option>
                                        </select>
                                    </td>
                                    <td class="py-3"><input type="time" name="time_in[]" value="<?php echo $att && $att['time_in'] ? htmlspecialchars($att['time_in']) : ''; ?>" class="px-2 py-1 border rounded" /></td>
                                    <td class="py-3"><input type="time" name="time_out[]" value="<?php echo $att && $att['time_out'] ? htmlspecialchars($att['time_out']) : ''; ?>" class="px-2 py-1 border rounded" /></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg">Save Attendance</button>
                    </div>
                </div>
            </form>

            <!-- Recent History -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Attendance Summary</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                        <thead>
                            <tr class="text-left text-sm text-gray-600">
                                <th class="py-2">Date</th>
                                <th class="py-2">Present</th>
                                <th class="py-2">Late</th>
                                <th class="py-2">Absent</th>
                                <th class="py-2">View</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while ($h = mysqli_fetch_assoc($histRes)): ?>
                            <tr>
                                <td class="py-3"><?php echo htmlspecialchars(date('F d, Y', strtotime($h['date']))); ?></td>
                                <td class="py-3"><?php echo intval($h['present']); ?></td>
                                <td class="py-3"><?php echo intval($h['late']); ?></td>
                                <td class="py-3"><?php echo intval($h['absent']); ?></td>
                                <td class="py-3"><a href="attendance.php?date=<?php echo htmlspecialchars($h['date']); ?>" class="text-orange-500">View</a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Optional: disable time inputs when Absent is selected
        document.querySelectorAll('select[name="status[]"]').forEach((sel, idx) => {
            const row = sel.closest('tr');
            const timeIn = row.querySelector('input[name="time_in[]"]');
            const timeOut = row.querySelector('input[name="time_out[]"]');
            function toggle() {
                if (sel.value === 'Absent') {
                    timeIn.value = '';
                    timeOut.value = '';
                    timeIn.disabled = true;
                    timeOut.disabled = true;
                    timeIn.classList.add('bg-gray-100');
                } else {
                    timeIn.disabled = false;
                    timeOut.disabled = false;
                    timeIn.classList.remove('bg-gray-100');
                }
            }
            sel.addEventListener('change', toggle);
            toggle();
        });
    </script>
</body>
</html>
