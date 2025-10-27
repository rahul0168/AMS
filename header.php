<link rel="stylesheet" href="https://getbootstrap.com/docs/5.0/examples/headers/headers.css">
<link rel="stylesheet" href="https://getbootstrap.com/docs/5.0/dist/css/bootstrap.min.css">
<script src="https://getbootstrap.com/docs/5.0/dist/js/bootstrap.bundle.min.js"></script>
<header class="p-3 mb-3 border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <a href="/" class="d-flex align-items-center mb-2 mb-lg-0 text-dark text-decoration-none">
                <h3>AMS</h3>
            </a>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <?php
                $current_page = basename($_SERVER['PHP_SELF']);
                ?>

                    <li class="nav-item">
                        <a href="dashboard.php"
                            class="nav-link px-2 <?php echo ($current_page === 'dashboard.php') ? 'text-primary fw-bold' : 'link-dark'; ?>">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="attendance_summary.php"
                            class="nav-link px-2 <?php echo ($current_page === 'attendance_summary.php') ? 'text-primary fw-bold' : 'link-dark'; ?>">
                            Attendance Summary
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="events_crud.php"
                            class="nav-link px-2 <?php echo ($current_page === 'events_crud.php') ? 'text-primary fw-bold' : 'link-dark'; ?>">
                            Manage Events
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="users_crud.php"
                            class="nav-link px-2 <?php echo ($current_page === 'users_crud.php') ? 'text-primary fw-bold' : 'link-dark'; ?>">
                            Manage Users
                        </a>
                    </li>
            

            </ul>

    
            <div class="dropdown text-end">
                <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://github.com/mdo.png" alt="mdo" width="32" height="32" class="rounded-circle"> <?php echo $current_user['name']; ?>
                </a>
                <ul class="dropdown-menu text-small" aria-labelledby="dropdownUser1" >
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>

                </ul>
            </div>
        </div>
    </div>
</header>