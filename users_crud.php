<?php
require_once "db.php";
require_once "auth.php";
require_once "csrf.php";
require_once "helpers.php";

require_login();
$current_user = current_user();
if ($current_user['role'] !== 'admin') {
    include 'header.php';
    echo "<div class='container'><div class='alert alert-danger mt-4'>Access denied. Admins only.</div></div>";
    include 'footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? '';

    $name = sanitize_text($_POST['name'] ?? '');
    $email = sanitize_text($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? 'viewer', ['admin', 'manager', 'viewer']) ? $_POST['role'] : 'viewer';
    $dept = sanitize_text($_POST['department_id'] ?? null);

    if ($action === 'create') {
        if (!is_valid_email($email) || strlen($password) < 6) {
            $error = "Invalid input.";
        } else {
            register_user($conn, $name, $email, $password, $role, $dept);
        }
        echo "<script>
            alert(' User added successfully');
            window.location.href = 'users_crud.php';
        </script>";
        exit;
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE users SET name=:name, email=:email, role=:role, department_id=:dept" . ($password ? ", password=PASSWORD(:password)" : "") . " WHERE id=:id");
        $params = [':name' => $name, ':email' => $email, ':role' => $role, ':dept' => $dept, ':id' => $id];
        if ($password) $params[':password'] = $password;
        $stmt->execute($params);
        echo "<script>
            alert(' User update successfully');
            window.location.href = 'users_crud.php';
        </script>";
        exit;
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo "<script>
            alert(' User delete successfully');
            window.location.href = 'users_crud.php';
        </script>";
        exit;
    }
}

// Fetch users
$stmt = $conn->query("SELECT id, name, email, role, department_id, created_at FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Users Admin</title>
    <link rel="stylesheet" href="https://getbootstrap.com/docs/5.0/examples/headers/headers.css">
    <link rel="stylesheet" href="https://getbootstrap.com/docs/5.0/dist/css/bootstrap.min.css">
    <script src="https://getbootstrap.com/docs/5.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="">
    <?php include 'header.php'; ?>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <div class="container ">
        <h2 class="mb-4">Users (Admin)</h2>

        <!-- Add User Button -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openModal()">Add User</button>

        <!-- Users Table -->
        <table id="UserTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Dept</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users  as $key => $u): ?>
                    <tr>
                        <td><?php echo $key + 1; ?></td>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo $u['role']; ?></td>
                        <td><?php echo $u['department_id']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='openModal(<?php echo json_encode($u); ?>)'>Edit</button>
                            <form method="post" style="display:inline">
                                <?php echo csrf_input_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" id="userForm">
                    <?php echo csrf_input_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="userId">

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="userName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="userEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="userPassword" placeholder="Leave blank to keep current">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" id="userRole">
                                <option value="viewer">Viewer</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department ID</label>
                            <input type="number" class="form-control" name="department_id" id="userDept">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="modalSubmit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Initialize DataTable -->
    <script>
        $(document).ready(function() {
            $('#UserTable').DataTable({
                responsive: true,
                order: [
                    [0, 'asc']
                ], // Sort by first column
                pageLength: 10,
                language: {
                    search: "Search events:",
                    lengthMenu: "Show _MENU_ entries per page"
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openModal(user = null) {
            const modalTitle = document.getElementById('modalTitle');
            const formAction = document.getElementById('formAction');
            const userId = document.getElementById('userId');
            const userName = document.getElementById('userName');
            const userEmail = document.getElementById('userEmail');
            const userPassword = document.getElementById('userPassword');
            const userRole = document.getElementById('userRole');
            const userDept = document.getElementById('userDept');

            if (user) {
                modalTitle.textContent = 'Edit User';
                formAction.value = 'update';
                userId.value = user.id;
                userName.value = user.name;
                userEmail.value = user.email;
                userPassword.value = '';
                userRole.value = user.role;
                userDept.value = user.department_id;
            } else {
                modalTitle.textContent = 'Add User';
                formAction.value = 'create';
                userId.value = '';
                userName.value = '';
                userEmail.value = '';
                userPassword.value = '';
                userRole.value = 'viewer';
                userDept.value = '';
            }

            const userModal = new bootstrap.Modal(document.getElementById('userModal'));
            userModal.show();
        }
    </script>
</body>

</html>