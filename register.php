<?php
require_once "db.php";
require_once "auth.php";
require_once "csrf.php";
require_once "helpers.php";

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? '');
    $name = sanitize_text($_POST['name'] ?? '');
    $email = sanitize_text($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? 'viewer', ['admin','manager','viewer']) ? $_POST['role'] : 'viewer';
    $department_id = int_or_null($_POST['department_id'] ?? null);

    if (empty($name) || !is_valid_email($email) || strlen($password) < 6) {
        $errors[] = "Invalid input (password min 6 chars).";
    }

    if (empty($errors)) {
        try {
            $id = register_user($conn, $name, $email, $password, $role, $department_id);
            header('Location: login.php');
            exit;
        } catch (PDOException $ex) {
            $errors[] = "Registration failed: " . $ex->getMessage();
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Register</title></head><body>
<h2>Register</h2>
<?php foreach ($errors as $e) echo "<div style='color:red;'>".htmlspecialchars($e)."</div>"; ?>
<form method="post">
    <?php echo csrf_input_field(); ?>
    <label>Name: <input type="text" name="name" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <label>Role:
        <select name="role">
            <option value="viewer">Viewer</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
        </select>
    </label><br>
    <label>Department ID: <input type="number" name="department_id"></label><br>
    <button type="submit">Register</button>
</form>
</body></html>
