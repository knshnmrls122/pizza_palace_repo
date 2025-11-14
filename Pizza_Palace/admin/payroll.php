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

// Expected payroll table schema (assumption):
// payroll_id INT PK, employee_id VARCHAR, month VARCHAR(7) 'YYYY-MM', gross DECIMAL, deductions DECIMAL, net DECIMAL, remarks TEXT

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'create' || $action === 'update') {
        $employee_id = trim($_POST['employee_id'] ?? '');
        $month = trim($_POST['month'] ?? date('Y-m'));
        $gross = floatval($_POST['gross'] ?? 0);
        $deductions = floatval($_POST['deductions'] ?? 0);
        $net = floatval($_POST['net'] ?? ($gross - $deductions));
        $remarks = trim($_POST['remarks'] ?? '');

        if (!$employee_id) {
            $error = 'Employee is required.';
        } else {
            if ($action === 'create') {
                $ins = mysqli_prepare($conn, "INSERT INTO Payroll (employee_id, month, gross, deductions, net, remarks) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($ins, 'ssddds', $employee_id, $month, $gross, $deductions, $net, $remarks);
                if (mysqli_stmt_execute($ins)) $message = 'Payroll record created.'; else $error = 'Failed to create payroll.';
                mysqli_stmt_close($ins);
            } else {
                $id = intval($_POST['payroll_id'] ?? 0);
                $upd = mysqli_prepare($conn, "UPDATE Payroll SET employee_id = ?, month = ?, gross = ?, deductions = ?, net = ?, remarks = ? WHERE payroll_id = ?");
                mysqli_stmt_bind_param($upd, 'ssdddsi', $employee_id, $month, $gross, $deductions, $net, $remarks, $id);
                if (mysqli_stmt_execute($upd)) $message = 'Payroll updated.'; else $error = 'Failed to update payroll.';
                mysqli_stmt_close($upd);
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['payroll_id'] ?? 0);
        if ($id) {
            $del = mysqli_prepare($conn, "DELETE FROM Payroll WHERE payroll_id = ?");
            mysqli_stmt_bind_param($del, 'i', $id);
            if (mysqli_stmt_execute($del)) $message = 'Deleted payroll record.'; else $error = 'Failed to delete.';
            mysqli_stmt_close($del);
        }
    } elseif ($action === 'export') {
        $month = $_POST['month'] ?? date('Y-m');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="payroll_' . $month . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Employee ID','Name','Month','Gross','Deductions','Net','Remarks']);
        $q = mysqli_prepare($conn, "SELECT p.employee_id, u.first_name, u.last_name, p.month, p.gross, p.deductions, p.net, p.remarks FROM Payroll p JOIN Users u ON p.employee_id = u.employee_id WHERE p.month = ? ORDER BY u.first_name, u.last_name");
        mysqli_stmt_bind_param($q, 's', $month);
        mysqli_stmt_execute($q);
        $res = mysqli_stmt_get_result($q);
        while ($r = mysqli_fetch_assoc($res)) {
            fputcsv($out, [$r['employee_id'], $r['first_name'] . ' ' . $r['last_name'], $r['month'], $r['gross'], $r['deductions'], $r['net'], $r['remarks']]);
        }
        mysqli_stmt_close($q);
        fclose($out);
        exit();
    }
}

$filterMonth = $_GET['month'] ?? date('Y-m');

$stmt = mysqli_prepare($conn, "SELECT p.*, u.first_name, u.last_name FROM Payroll p JOIN Users u ON p.employee_id = u.employee_id WHERE p.month = ? ORDER BY u.first_name, u.last_name");
mysqli_stmt_bind_param($stmt, 's', $filterMonth);
mysqli_stmt_execute($stmt);
$payRes = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$totQ = mysqli_prepare($conn, "SELECT SUM(gross) AS total_gross, SUM(deductions) AS total_deductions, SUM(net) AS total_net FROM Payroll WHERE month = ?");
mysqli_stmt_bind_param($totQ, 's', $filterMonth);
mysqli_stmt_execute($totQ);
$totRes = mysqli_stmt_get_result($totQ);
$totals = mysqli_fetch_assoc($totRes);
mysqli_stmt_close($totQ);

$emps = mysqli_query($conn, "SELECT employee_id, first_name, last_name FROM Users WHERE role = 'Employee' ORDER BY first_name, last_name");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Payroll - Pizza Palace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <?php include('sidebar.php'); ?>
    <main class="ml-64 p-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Payroll</h1>
                <div class="flex items-center gap-2">
                    <form method="GET" class="flex items-center gap-2">
                        <input type="month" name="month" value="<?php echo htmlspecialchars($filterMonth); ?>" class="px-3 py-2 border rounded-lg" />
                    </form>
                    <form method="POST" style="display:inline-block">
                        <input type="hidden" name="action" value="export">
                        <input type="hidden" name="month" value="<?php echo htmlspecialchars($filterMonth); ?>">
                        <button class="px-3 py-2 bg-orange-500 text-white rounded">Export CSV</button>
                    </form>
                    <button id="openAdd" class="px-3 py-2 bg-orange-500 text-white rounded ml-2">+ Add Payroll</button>
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
                    <h2 class="text-lg font-semibold mb-4">Payroll Records for <?php echo htmlspecialchars($filterMonth); ?></h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700">
                            <thead>
                                <tr class="text-left text-sm text-gray-600">
                                    <th class="py-2">Employee</th>
                                    <th class="py-2">Month</th>
                                    <th class="py-2">Gross</th>
                                    <th class="py-2">Deductions</th>
                                    <th class="py-2">Net</th>
                                    <th class="py-2">Remarks</th>
                                    <th class="py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php while ($r = mysqli_fetch_assoc($payRes)): ?>
                                <tr>
                                    <td class="py-3"><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name'] . " ({$r['employee_id']})"); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($r['month']); ?></td>
                                    <td class="py-3">₱<?php echo number_format($r['gross'],2); ?></td>
                                    <td class="py-3">₱<?php echo number_format($r['deductions'],2); ?></td>
                                    <td class="py-3">₱<?php echo number_format($r['net'],2); ?></td>
                                    <td class="py-3"><?php echo htmlspecialchars($r['remarks']); ?></td>
                                    <td class="py-3">
                                        <div class="flex gap-2">
                                            <button class="editBtn px-2 py-1 bg-blue-50 text-blue-600 rounded" data-item='<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES); ?>'>Edit</button>
                                            <button class="delBtn px-2 py-1 bg-red-50 text-red-600 rounded" data-id="<?php echo intval($r['payroll_id']); ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold mb-4">Summary</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between"><span>Total Gross</span><strong>₱<?php echo number_format($totals['total_gross'] ?? 0,2); ?></strong></div>
                        <div class="flex justify-between"><span>Total Deductions</span><strong>₱<?php echo number_format($totals['total_deductions'] ?? 0,2); ?></strong></div>
                        <div class="flex justify-between"><span>Total Net</span><strong>₱<?php echo number_format($totals['total_net'] ?? 0,2); ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modals -->
    <div id="payModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 id="payTitle" class="text-lg font-semibold">Add Payroll</h3>
                <button id="closePay" class="text-gray-500">&times;</button>
            </div>
            <form id="payForm" method="POST" class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="action" id="payAction" value="create">
                <input type="hidden" name="payroll_id" id="payId" value="">
                <div>
                    <label class="text-sm">Employee</label>
                    <select name="employee_id" id="payEmployee" required class="mt-1 w-full px-3 py-2 border rounded">
                        <?php mysqli_data_seek($emps,0); while ($e = mysqli_fetch_assoc($emps)): ?>
                            <option value="<?php echo htmlspecialchars($e['employee_id']); ?>"><?php echo htmlspecialchars($e['first_name'] . ' ' . $e['last_name'] . ' (' . $e['employee_id'] . ')'); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm">Month</label>
                    <input type="month" name="month" id="payMonth" value="<?php echo htmlspecialchars($filterMonth); ?>" class="mt-1 w-full px-3 py-2 border rounded" />
                </div>
                <div>
                    <label class="text-sm">Gross</label>
                    <input type="number" step="0.01" name="gross" id="payGross" class="mt-1 w-full px-3 py-2 border rounded" />
                </div>
                <div>
                    <label class="text-sm">Deductions</label>
                    <input type="number" step="0.01" name="deductions" id="payDeductions" class="mt-1 w-full px-3 py-2 border rounded" />
                </div>
                <div>
                    <label class="text-sm">Net</label>
                    <input type="number" step="0.01" name="net" id="payNet" class="mt-1 w-full px-3 py-2 border rounded" />
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm">Remarks</label>
                    <textarea name="remarks" id="payRemarks" class="mt-1 w-full px-3 py-2 border rounded" rows="3"></textarea>
                </div>
                <div class="md:col-span-2 flex justify-end gap-2">
                    <button type="button" id="cancelPay" class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="delModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold mb-2">Confirm Delete</h3>
            <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this payroll record?</p>
            <form id="delForm" method="POST" class="flex justify-end gap-2">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="payroll_id" id="delId" value="">
                <button type="button" id="cancelDel" class="px-4 py-2 border rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded">Delete</button>
            </form>
        </div>
    </div>

    <script>
        const payModal = document.getElementById('payModal');
        const delModal = document.getElementById('delModal');
        const openAdd = document.getElementById('openAdd');
        const closePay = document.getElementById('closePay');
        const cancelPay = document.getElementById('cancelPay');
        const payForm = document.getElementById('payForm');
        const payAction = document.getElementById('payAction');
        const payId = document.getElementById('payId');

        function openModal(modal){ modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow='hidden'; }
        function closeModal(modal){ modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.style.overflow=''; }

        openAdd.addEventListener('click', ()=>{ payForm.reset(); payAction.value='create'; payId.value=''; document.getElementById('payTitle').textContent='Add Payroll'; openModal(payModal); });
        closePay.addEventListener('click', ()=>closeModal(payModal));
        cancelPay.addEventListener('click', ()=>closeModal(payModal));

        document.querySelectorAll('.editBtn').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const item = JSON.parse(btn.getAttribute('data-item'));
                payAction.value='update'; payId.value=item.payroll_id; document.getElementById('payEmployee').value=item.employee_id; document.getElementById('payMonth').value=item.month; document.getElementById('payGross').value=item.gross; document.getElementById('payDeductions').value=item.deductions; document.getElementById('payNet').value=item.net; document.getElementById('payRemarks').value=item.remarks; document.getElementById('payTitle').textContent='Edit Payroll'; openModal(payModal);
            });
        });

        document.querySelectorAll('.delBtn').forEach(btn=>{
            btn.addEventListener('click', ()=>{ document.getElementById('delId').value = btn.getAttribute('data-id'); openModal(delModal); });
        });
        document.getElementById('cancelDel').addEventListener('click', ()=>closeModal(delModal));

        [payModal, delModal].forEach(m=> m.addEventListener('click', e=>{ if(e.target===m) closeModal(m); }));
    </script>
</body>
</html>
