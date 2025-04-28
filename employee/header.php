<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media (max-width: 767.98px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 80%;
                height: 100%;
                z-index: 1050;
                transition: all 0.3s;
            }
            
            #sidebar.show {
                left: 0;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.4);
                z-index: 1040;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>

<!-- Mobile navbar -->
<nav class="navbar navbar-expand-md navbar-dark bg-primary d-md-none">
    <div class="container-fluid">
        <button class="btn btn-primary" type="button" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">Employee Portal</a>
    </div>
</nav>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-primary sidebar collapse" style="min-height: 700px !important;">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4 d-none d-md-block">
                    <h5 class="text-white"><i class="fas fa-user-tie me-2"></i>Employee Portal</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'apply_leave.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="apply_leave.php">
                            <i class="fas fa-plus-circle me-2"></i>
                            Apply for Leave
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'leave_history.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="leave_history.php">
                            <i class="fas fa-history me-2"></i>
                            Leave History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'leave_balance.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="leave_balance.php">
                            <i class="fas fa-calendar-check me-2"></i>
                            Leave Balance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'leave_status.php' ? 'active bg-white bg-opacity-25' : ''; ?>" href="leave_status.php">
                            <i class="fas fa-hourglass-half me-2"></i>
                            Leave Status
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link text-white bg-white bg-opacity-10" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 bg-light">