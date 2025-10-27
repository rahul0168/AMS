<?php
require_once "db.php";
require_once "auth.php";
require_once "csrf.php";
require_once "helpers.php";

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? '');
    $email = sanitize_text($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!is_valid_email($email)) $errors[] = "Invalid email address.";
    if (empty($password)) $errors[] = "Password is required.";

    if (empty($errors)) {
        if (login_user($conn, $email, $password)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Employee Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for Icons -->
    <script src="https://kit.fontawesome.com/a2d9b6a66c.js" crossorigin="anonymous"></script>

    <style>
        body {
            background: linear-gradient(135deg, #0c4ea2 0%, #0d6efd 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            padding: 2rem;
        }
        .login-card .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(12,78,162,0.25);
            border-color: #0c4ea2;
        }
        .login-title {
            font-weight: 700;
            color: #0c4ea2;
        }
        .btn-primary {
            background-color: #0c4ea2;
            border-color: #0c4ea2;
        }
        .btn-primary:hover {
            background-color: #083a7a;
            border-color: #083a7a;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
            <h3 class="login-title"> Login</h3>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php echo csrf_input_field(); ?>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
        </form>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
