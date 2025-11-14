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

// Handle POST actions: create, update, delete, export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'create' || $action === 'update') {
        $employee_id = trim($_POST['employee_id'] ?? '');
        $month = trim($_POST['month'] ?? date('Y-m'));
        $score = intval($_POST['performance_score'] ?? 0);
        $remarks = trim($_POST['remarks'] ?? '');

        if (!$employee_id || $score < 0) {
            $error = 'Employee and score are required.';
        } else {
            if ($action === 'create') {
                $ins = mysqli_prepare($conn, "INSERT INTO Performance (employee_id, month, performance_score, remarks) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($ins, 'ssis', $employee_id, $month, $score, $remarks);
                if (mysqli_stmt_execute($ins)) $message = 'Performance record created.'; else $error = 'Failed to create.';
                mysqli_stmt_close($ins);
            } else {
                $id = intval($_POST['performance_id'] ?? 0);
                $upd = mysqli_prepare($conn, "UPDATE Performance SET employee_id = ?, month = ?, performance_score = ?, remarks = ? WHERE performance_id = ?");
                mysqli_stmt_bind_param($upd, 'ssisi', $employee_id, $month, $score, $remarks, $id);
                if (mysqli_stmt_execute($upd)) $message = 'Updated.'; else $error = 'Failed to update.';
                mysqli_stmt_close($upd);
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['performance_id'] ?? 0);
        if ($id) {
            $del = mysqli_prepare($conn, "DELETE FROM Performance WHERE performance_id = ?");
            mysqli_stmt_bind_param($del, 'i', $id);
            if (mysqli_stmt_execute($del)) $message = 'Deleted.'; else $error = 'Failed to delete.';
            mysqli_stmt_close($del);
        }
    } elseif ($action === 'export') {
        $month = $_POST['month'] ?? date('Y-m');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="performance_' . $month . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Employee ID','Name','Month','Score','Remarks']);
        $q = mysqli_prepare($conn, "SELECT p.employee_id, u.first_name, u.last_name, p.month, p.performance_score, p.remarks FROM Performance p JOIN Users u ON p.employee_id = u.employee_id WHERE p.month = ? ORDER BY p.performance_score DESC");
        mysqli_stmt_bind_param($q, 's', $month);
        mysqli_stmt_execute($q);
        $res = mysqli_stmt_get_result($q);
        while ($r = mysqli_fetch_assoc($res)) {
            fputcsv($out, [$r['employee_id'], $r['first_name'] . ' ' . $r['last_name'], $r['month'], $r['performance_score'], $r['remarks']]);
        }
        mysqli_stmt_close($q);
        fclose($out);
        exit();
    }
}

// Month filter
$filterMonth = $_GET['month'] ?? date('Y-m');

// Fetch performance records
$stmt = mysqli_prepare($conn, "SELECT p.*, u.first_name, u.last_name FROM Performance p JOIN Users u ON p.employee_id = u.employee_id WHERE p.month = ? ORDER BY p.performance_score DESC");
mysqli_stmt_bind_param($stmt, 's', $filterMonth);
mysqli_stmt_execute($stmt);
$perfRes = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// Fetch all employees for select
$emps = mysqli_query($conn, "SELECT employee_id, first_name, last_name FROM Users WHERE role = 'Employee' ORDER BY first_name, last_name");

// Top performers (top 5 this month)
$topQ = mysqli_prepare($conn, "SELECT u.first_name, u.last_name, p.performance_score FROM Performance p JOIN Users u ON p.employee_id = u.employee_id WHERE p.month = ? ORDER BY p.performance_score DESC LIMIT 5");
mysqli_stmt_bind_param($topQ, 's', $filterMonth);
mysqli_stmt_execute($topQ);
$topRes = mysqli_stmt_get_result($topQ);
mysqli_stmt_close($topQ);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Performance Reports - Pizza Palace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <?php include('sidebar.php'); ?>
    <main class="ml-64 p-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Performance Reports</h1>
                <div class="flex items-center gap-2">
                    <form method="GET" class="flex items-center gap-2">
                        <input type="month" name="month" value="<?php echo htmlspecialchars($filterMonth); ?>" class="px-3 py-2 border rounded-lg" />
                        <button type="submit" class="px-3 py-2 bg-gray-100 rounded">Filter</button>
                    </form>
                    <form method="POST" style="display:inline-block">
                        <input type="hidden" name="action" value="export">
                        <input type="hidden" name="month" value="<?php echo htmlspecialchars($filterMonth); ?>">
                        <button class="px-3 py-2 bg-orange-500 text-white rounded">Export CSV</button>
                    </form>
                    <button id="openAdd" class="px-3 py-2 bg-orange-500 text-white rounded ml-2">+ Add</button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold mb-4">Records for <?php echo htmlspecialchars($filterMonth); ?></h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                            <thead>
                                <tr class="text-left text-sm text-gray-600">
                                    <th class="py-2">Employee</th>
                                    <th class="py-2">Month</th>
                                    <th class="py-2">Score</th>
                                    <th class="py-2">Remarks</th>
                                    <th class="py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php while ($r = mysqli_fetch_assoc($perfRes)): ?>
                                <tr>
                                    <td class="py-3"><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name'] . " ({$r['employee_id']})"); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($r['month']); ?></td>
                                    <td class="py-3"><?php echo intval($r['performance_score']); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($r['remarks']); ?></td>
                                    <td class="py-3">
                                        <div class="flex gap-2">
                                            <button class="editBtn px-2 py-1 bg-blue-50 text-blue-600 rounded" data-item='<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES); ?>'>Edit</button>
                                            <button class="delBtn px-2 py-1 bg-red-50 text-red-600 rounded" data-id="<?php echo intval($r['performance_id']); ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold mb-4">Top Performers</h2>
                    <ol class="list-decimal pl-5 space-y-2">
                        <?php while ($t = mysqli_fetch_assoc($topRes)): ?>
                            <li><?php echo htmlspecialchars($t['first_name'] . ' ' . $t['last_name'] . ' — ' . $t['performance_score'] . '%'); ?></li>
                        <?php endwhile; ?>
                    </ol>
                </div>
            </div>
        </div>
    </main>

    <!-- Modals for add/edit/delete -->
    <div id="perfModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 id="perfTitle" class="text-lg font-semibold">Add Performance</h3>
                <button id="closePerf" class="text-gray-500">&times;</button>
            </div>
            <form id="perfForm" method="POST" class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="action" id="perfAction" value="create">
                <input type="hidden" name="performance_id" id="perfId" value="">
                <div>
                    <label class="text-sm">Employee</label>
                    <select name="employee_id" id="perfEmployee" required class="mt-1 w-full px-3 py-2 border rounded">
                        <?php while ($e = mysqli_fetch_assoc($emps)): ?>
                            <option value="<?php echo htmlspecialchars($e['employee_id']); ?>"><?php echo htmlspecialchars($e['first_name'] . ' ' . $e['last_name'] . ' (' . $e['employee_id'] . ')'); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm">Month</label>
                    <input type="month" name="month" id="perfMonth" value="<?php echo htmlspecialchars($filterMonth); ?>" class="mt-1 w-full px-3 py-2 border rounded" />
                </div>
                <div>
                    <label class="text-sm">Score (%)</label>
                    <input type="number" name="performance_score" id="perfScore" min="0" max="100" class="mt-1 w-full px-3 py-2 border rounded" />
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm">Remarks</label>
                    <textarea name="remarks" id="perfRemarks" class="mt-1 w-full px-3 py-2 border rounded" rows="3"></textarea>
                </div>
                <div class="md:col-span-2 flex justify-end gap-2">
                    <button type="button" id="cancelPerf" class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="delModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold mb-2">Confirm Delete</h3>
            <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this performance record?</p>
            <form id="delForm" method="POST" class="flex justify-end gap-2">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="performance_id" id="delId" value="">
                <button type="button" id="cancelDel" class="px-4 py-2 border rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded">Delete</button>
            </form>
        </div>
    </div>

    <script>
        // Modal logic
        const perfModal = document.getElementById('perfModal');
        const delModal = document.getElementById('delModal');
        const openAdd = document.getElementById('openAdd');
        const closePerf = document.getElementById('closePerf');
        const cancelPerf = document.getElementById('cancelPerf');
        const perfForm = document.getElementById('perfForm');
        const perfAction = document.getElementById('perfAction');
        const perfId = document.getElementById('perfId');

        function openModal(modal){ modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow='hidden'; }
        function closeModal(modal){ modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.style.overflow=''; }

        openAdd.addEventListener('click', ()=>{
            perfForm.reset(); perfAction.value='create'; perfId.value=''; document.getElementById('perfTitle').textContent='Add Performance'; openModal(perfModal);
        });
        closePerf.addEventListener('click', ()=>closeModal(perfModal));
        cancelPerf.addEventListener('click', ()=>closeModal(perfModal));

        document.querySelectorAll('.editBtn').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const item = JSON.parse(btn.getAttribute('data-item'));
                perfAction.value='update'; perfId.value=item.performance_id; document.getElementById('perfEmployee').value=item.employee_id; document.getElementById('perfMonth').value=item.month; document.getElementById('perfScore').value=item.performance_score; document.getElementById('perfRemarks').value=item.remarks; document.getElementById('perfTitle').textContent='Edit Performance'; openModal(perfModal);
            });
        });

        document.querySelectorAll('.delBtn').forEach(btn=>{
            btn.addEventListener('click', ()=>{ document.getElementById('delId').value = btn.getAttribute('data-id'); openModal(delModal); });
        });
        document.getElementById('cancelDel').addEventListener('click', ()=>closeModal(delModal));

        // Close modals on overlay click
        [perfModal, delModal].forEach(m=> m.addEventListener('click', e=>{ if(e.target===m) closeModal(m); }));
    </script>
</body>
</html>
