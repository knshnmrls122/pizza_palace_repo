<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
	header("Location: ../pages/Login_Page.php");
	exit();
}
include("../includes/db_connection.php");
include("../includes/header.php");

// Messages
$message = '';
$error = '';

// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
	$first_name = trim($_POST['first_name'] ?? '');
	$last_name = trim($_POST['last_name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$contact_number = trim($_POST['contact_number'] ?? '');
	$address = trim($_POST['address'] ?? '');
	$role = $_POST['role'] ?? 'Employee';
	$employee_id = trim($_POST['employee_id'] ?? '');
	$password = $_POST['password'] ?? '';

	if (!$first_name || !$last_name || !$email || !$employee_id || !$password) {
		$error = 'Please fill in all required fields (first name, last name, email, employee ID, password).';
	} else {
		// Check unique email and employee_id
		$stmt = mysqli_prepare($conn, "SELECT user_id FROM Users WHERE email = ? OR employee_id = ? LIMIT 1");
		mysqli_stmt_bind_param($stmt, 'ss', $email, $employee_id);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);
		if (mysqli_stmt_num_rows($stmt) > 0) {
			$error = 'Email or Employee ID already exists.';
			mysqli_stmt_close($stmt);
		} else {
			mysqli_stmt_close($stmt);
			$password_hash = password_hash($password, PASSWORD_DEFAULT);
			$insert = mysqli_prepare($conn, "INSERT INTO Users (first_name, last_name, email, contact_number, address, role, employee_id, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
			mysqli_stmt_bind_param($insert, 'ssssssss', $first_name, $last_name, $email, $contact_number, $address, $role, $employee_id, $password_hash);
			if (mysqli_stmt_execute($insert)) {
				$message = 'Employee created successfully.';
			} else {
				$error = 'Failed to create employee.';
			}
			mysqli_stmt_close($insert);
		}
	}
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
	$user_id = intval($_POST['user_id'] ?? 0);
	$first_name = trim($_POST['first_name'] ?? '');
	$last_name = trim($_POST['last_name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$contact_number = trim($_POST['contact_number'] ?? '');
	$address = trim($_POST['address'] ?? '');
	$role = $_POST['role'] ?? 'Employee';
	$employee_id = trim($_POST['employee_id'] ?? '');
	$password = $_POST['password'] ?? '';

	if (!$user_id || !$first_name || !$last_name || !$email || !$employee_id) {
		$error = 'Please fill in all required fields.';
	} else {
		// Check uniqueness excluding current user
		$stmt = mysqli_prepare($conn, "SELECT user_id FROM Users WHERE (email = ? OR employee_id = ?) AND user_id != ? LIMIT 1");
		mysqli_stmt_bind_param($stmt, 'ssi', $email, $employee_id, $user_id);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);
		if (mysqli_stmt_num_rows($stmt) > 0) {
			$error = 'Email or Employee ID already used by another user.';
			mysqli_stmt_close($stmt);
		} else {
			mysqli_stmt_close($stmt);
			if ($password) {
				$password_hash = password_hash($password, PASSWORD_DEFAULT);
				$update = mysqli_prepare($conn, "UPDATE Users SET first_name = ?, last_name = ?, email = ?, contact_number = ?, address = ?, role = ?, employee_id = ?, password_hash = ? WHERE user_id = ?");
				mysqli_stmt_bind_param($update, 'ssssssssi', $first_name, $last_name, $email, $contact_number, $address, $role, $employee_id, $password_hash, $user_id);
			} else {
				$update = mysqli_prepare($conn, "UPDATE Users SET first_name = ?, last_name = ?, email = ?, contact_number = ?, address = ?, role = ?, employee_id = ? WHERE user_id = ?");
				mysqli_stmt_bind_param($update, 'sssssssi', $first_name, $last_name, $email, $contact_number, $address, $role, $employee_id, $user_id);
			}
			if (mysqli_stmt_execute($update)) {
				$message = 'Employee updated successfully.';
			} else {
				$error = 'Failed to update employee.';
			}
			mysqli_stmt_close($update);
		}
	}
}

// DELETE
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
// 	$user_id = intval($_POST['user_id'] ?? 0);
// 	if ($user_id) {
// 		$del = mysqli_prepare($conn, "DELETE FROM Users WHERE user_id = ?");
// 		mysqli_stmt_bind_param($del, 'i', $user_id);
// 		if (mysqli_stmt_execute($del)) {
// 			$message = 'Employee deleted.';
// 		} else {
// 			$error = 'Failed to delete employee.';
// 		}
// 		mysqli_stmt_close($del);
// 	} else {
// 		$error = 'Invalid user.';
// 	}
// }
// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id) {

        // 1. Get the employee_id for this user
        $stmt = mysqli_prepare($conn, "SELECT employee_id FROM users WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($row) {
            $employee_id = $row['employee_id'];

            // ---- DELETE FROM ALL CHILD TABLES FIRST ----

            // Attendance
            $del = mysqli_prepare($conn, "DELETE FROM attendance WHERE employee_id = ?");
            mysqli_stmt_bind_param($del, 's', $employee_id);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);

            // Performance
            $del = mysqli_prepare($conn, "DELETE FROM performance WHERE employee_id = ?");
            mysqli_stmt_bind_param($del, 's', $employee_id);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);

            // Payroll
            $del = mysqli_prepare($conn, "DELETE FROM payroll WHERE employee_id = ?");
            mysqli_stmt_bind_param($del, 's', $employee_id);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);

            // Notifications
            $del = mysqli_prepare($conn, "DELETE FROM notifications WHERE employee_id = ?");
            mysqli_stmt_bind_param($del, 's', $employee_id);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);

            // Notifications Read
            $del = mysqli_prepare($conn, "DELETE FROM notificationsread WHERE employee_id = ?");
            mysqli_stmt_bind_param($del, 's', $employee_id);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);
        }

        // ---- NOW DELETE THE USER ----
        $delUser = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
        mysqli_stmt_bind_param($delUser, 'i', $user_id);

        if (mysqli_stmt_execute($delUser)) {
            $message = 'Employee deleted successfully.';
        } else {
            $error = 'Failed to delete employee.';
        }

        mysqli_stmt_close($delUser);

    } else {
        $error = 'Invalid user.';
    }
}


// Fetch users
$usersResult = mysqli_query($conn, "SELECT * FROM Users ORDER BY date_created DESC");

// If edit requested, fetch single user
$editUser = null;
if (isset($_GET['edit_id'])) {
	$edit_id = intval($_GET['edit_id']);
	$stmt = mysqli_prepare($conn, "SELECT * FROM Users WHERE user_id = ? LIMIT 1");
	mysqli_stmt_bind_param($stmt, 'i', $edit_id);
	mysqli_stmt_execute($stmt);
	$res = mysqli_stmt_get_result($stmt);
	$editUser = mysqli_fetch_assoc($res);
	mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Manage Employees - Pizza Palace</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
	<?php include('sidebar.php'); ?>

	<main class="ml-64 p-8">
		<div class="max-w-6xl mx-auto">
			<div class="flex items-center justify-between mb-6">
				<h1 class="text-2xl font-bold text-gray-800">Employees</h1>
				<button id="openAddBtn" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">+ Add Employee</button>
			</div>

			<?php if ($message): ?>
				<div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-800 rounded"><?php echo htmlspecialchars($message); ?></div>
			<?php endif; ?>
			<?php if ($error): ?>
				<div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded"><?php echo htmlspecialchars($error); ?></div>
			<?php endif; ?>

			<!-- Note: Add/Edit form now uses modal dialogs (see below) -->

			<!-- Employees Table -->
			<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
				<table class="min-w-full divide-y divide-gray-200">
					<thead>
						<tr class="text-left text-sm text-gray-600">
							<th class="py-2">Name</th>
							<th class="py-2">Email</th>
							<th class="py-2">Contact</th>
							<th class="py-2">Role</th>
							<th class="py-2">Employee ID</th>
							<th class="py-2">Created</th>
							<th class="py-2">Actions</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-gray-100 text-sm text-gray-700">
						<?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
						<tr>
							<td class="py-3"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
							<td class="py-3"><?php echo htmlspecialchars($user['email']); ?></td>
							<td class="py-3"><?php echo htmlspecialchars($user['contact_number']); ?></td>
							<td class="py-3"><?php echo htmlspecialchars($user['role']); ?></td>
							<td class="py-3"><?php echo htmlspecialchars($user['employee_id']); ?></td>
							<td class="py-3"><?php echo htmlspecialchars(date('M d, Y', strtotime($user['date_created']))); ?></td>
							<td class="py-3">
								<div class="flex items-center gap-2">
									<button class="editBtn px-2 py-1 bg-blue-50 text-blue-600 rounded" data-user='<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES); ?>'>Edit</button>
									<button class="deleteBtn px-2 py-1 bg-red-50 text-red-600 rounded" data-userid="<?php echo intval($user['user_id']); ?>">Delete</button>
								</div>
							</td>
						</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		</div>
	</main>


	<!-- Modals -->
	<!-- Add / Edit Modal -->
	<div id="employeeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
		<div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
			<div class="flex items-center justify-between px-6 py-4 border-b">
				<h3 id="employeeModalTitle" class="text-lg font-semibold text-gray-800">Add Employee</h3>
				<button id="closeEmployeeModal" class="text-gray-500 hover:text-gray-700">&times;</button>
			</div>
			<form id="employeeForm" method="POST" class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-4">
				<input type="hidden" name="action" id="employeeFormAction" value="create">
				<input type="hidden" name="user_id" id="employeeFormUserId" value="">

				<div>
					<label class="text-sm font-medium text-gray-700">First name *</label>
					<input name="first_name" id="emp_first_name" required class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" />
				</div>
				<div>
					<label class="text-sm font-medium text-gray-700">Last name *</label>
					<input name="last_name" id="emp_last_name" required class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" />
				</div>
				<div>
					<label class="text-sm font-medium text-gray-700">Email *</label>
					<input name="email" id="emp_email" type="email" required class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" />
				</div>
				<div>
					<label class="text-sm font-medium text-gray-700">Contact number</label>
					<input name="contact_number" id="emp_contact_number" class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" />
				</div>
				<div class="md:col-span-2">
					<label class="text-sm font-medium text-gray-700">Address</label>
					<input name="address" id="emp_address" class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" />
				</div>
				<div>
					<label class="text-sm font-medium text-gray-700">Role</label>
					<select name="role" id="emp_role" class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
						<option value="Employee">Employee</option>
						<option value="Admin">Admin</option>
					</select>
				</div>
				<div>
					<label class="text-sm font-medium text-gray-700">Employee ID *</label>
					<input name="employee_id" id="emp_employee_id" required class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" />
				</div>
				<div>
					<label class="text-sm font-medium text-gray-700">Password <span id="empPasswordHint" class="text-gray-400 text-xs">*</span></label>
					<input name="password" id="emp_password" type="password" class="mt-1 w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" />
				</div>

				<div class="md:col-span-2 flex justify-end gap-2 mt-2">
					<button type="button" id="cancelEmployeeBtn" class="px-4 py-2 border rounded-lg">Cancel</button>
					<button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">Save</button>
				</div>
			</form>
		</div>
	</div>

	<!-- Delete Modal -->
	<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
		<div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
			<div class="px-6 py-4">
				<h3 class="text-lg font-semibold text-gray-800 mb-2">Confirm Delete</h3>
				<p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this employee? This action cannot be undone.</p>
				<form id="deleteForm" method="POST" class="flex justify-end gap-2">
					<input type="hidden" name="action" value="delete">
					<input type="hidden" name="user_id" id="deleteUserId" value="">
					<button type="button" id="cancelDeleteBtn" class="px-4 py-2 border rounded-lg">Cancel</button>
					<button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Delete</button>
				</form>
			</div>
		</div>
	</div>

	<script>
		// Modal helpers
		const employeeModal = document.getElementById('employeeModal');
		const deleteModal = document.getElementById('deleteModal');

		const openAddBtn = document.getElementById('openAddBtn');
		const closeEmployeeModal = document.getElementById('closeEmployeeModal');
		const cancelEmployeeBtn = document.getElementById('cancelEmployeeBtn');
		const employeeForm = document.getElementById('employeeForm');
		const employeeFormAction = document.getElementById('employeeFormAction');
		const employeeFormUserId = document.getElementById('employeeFormUserId');
		const empPasswordHint = document.getElementById('empPasswordHint');

		const deleteForm = document.getElementById('deleteForm');
		const deleteUserId = document.getElementById('deleteUserId');
		const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

		function openModal(modal) {
			modal.classList.remove('hidden');
			document.body.style.overflow = 'hidden';
			modal.classList.add('flex');
		}
		function closeModal(modal) {
			modal.classList.add('hidden');
			modal.classList.remove('flex');
			document.body.style.overflow = '';
		}

		// Open Add
		openAddBtn.addEventListener('click', () => {
			employeeForm.reset();
			employeeFormAction.value = 'create';
			employeeFormUserId.value = '';
			document.getElementById('employeeModalTitle').textContent = 'Add Employee';
			empPasswordHint.textContent = '*';
			openModal(employeeModal);
		});

		// Close Employee Modal
		[closeEmployeeModal, cancelEmployeeBtn].forEach(btn => btn.addEventListener('click', () => closeModal(employeeModal)));

		// Cancel delete
		cancelDeleteBtn.addEventListener('click', (e) => { e.preventDefault(); closeModal(deleteModal); });

		// Edit buttons
		document.querySelectorAll('.editBtn').forEach(btn => {
			btn.addEventListener('click', () => {
				const user = JSON.parse(btn.getAttribute('data-user'));
				// populate form
				document.getElementById('emp_first_name').value = user.first_name || '';
				document.getElementById('emp_last_name').value = user.last_name || '';
				document.getElementById('emp_email').value = user.email || '';
				document.getElementById('emp_contact_number').value = user.contact_number || '';
				document.getElementById('emp_address').value = user.address || '';
				document.getElementById('emp_role').value = user.role || 'Employee';
				document.getElementById('emp_employee_id').value = user.employee_id || '';
				document.getElementById('emp_password').value = '';
				employeeFormAction.value = 'update';
				employeeFormUserId.value = user.user_id;
				document.getElementById('employeeModalTitle').textContent = 'Edit Employee';
				empPasswordHint.textContent = '(leave blank to keep)';
				openModal(employeeModal);
			});
		});

		// Delete buttons
		document.querySelectorAll('.deleteBtn').forEach(btn => {
			btn.addEventListener('click', () => {
				const id = btn.getAttribute('data-userid');
				deleteUserId.value = id;
				openModal(deleteModal);
			});
		});

		// Close modal on overlay click
		[employeeModal, deleteModal].forEach(modal => {
			modal.addEventListener('click', (e) => {
				if (e.target === modal) closeModal(modal);
			});
		});
	</script>
</body>
</html>

