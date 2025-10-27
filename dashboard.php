<?php
require_once "db.php";
require_once "auth.php";
require_once "csrf.php";
require_login();
$current_user= current_user();
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
</head>

<body class="bg-light">
    <?php include 'header.php'; ?>
    
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-primary">Welcome, <?php echo htmlspecialchars($current_user['name']); ?> 👋</h2>
            <p class="text-muted mb-0">Role: <span class="fw-semibold"><?php echo htmlspecialchars($current_user['role']); ?></span></p>
        </div>

        <div class="row justify-content-center g-4">
            <div class="col-md-3 col-sm-6">
                <a href="attendance_summary.php" class="text-decoration-none">
                    <div class="card text-center shadow-sm border-0 h-100 hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                            <h5 class="fw-bold">Attendance Summary</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="events_crud.php" class="text-decoration-none">
                    <div class="card text-center shadow-sm border-0 h-100 hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-calendar-alt fa-3x text-success mb-3"></i>
                            <h5 class="fw-bold">Manage Events</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="users_crud.php" class="text-decoration-none">
                    <div class="card text-center shadow-sm border-0 h-100 hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-warning mb-3"></i>
                            <h5 class="fw-bold">Manage Users</h5>
                        </div>
                    </div>
                </a>
            </div>

          
        </div>
    </div>

    <style>
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
    </style>

    <script src="https://kit.fontawesome.com/a2d9b6a66c.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</body>


</html>
