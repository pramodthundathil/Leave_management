<?php
// header.php - Include this at the top of all admin pages
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management System - Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 1.5rem 1rem;
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-item {
            margin: 0.25rem 1rem;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 0.35rem;
            transition: all 0.3s;
        }
        
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .sidebar-icon {
            margin-right: 0.75rem;
            width: 1.5rem;
            text-align: center;
        }
        
        .content {
            margin-left: 250px;
            padding: 1rem;
        }
        
        .topbar {
            height: 4.375rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .notification-icon {
            position: relative;
            cursor: pointer;
        }
        
        .notification-icon .badge {
            position: absolute;
            top: -5px;
            right: -8px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
        }
        
        .border-left-primary {
            border-left: 0.25rem solid var(--primary-color);
        }
        
        .border-left-success {
            border-left: 0.25rem solid var(--success-color);
        }
        
        .border-left-info {
            border-left: 0.25rem solid var(--info-color);
        }
        
        .border-left-warning {
            border-left: 0.25rem solid var(--warning-color);
        }
        
        .border-left-danger {
            border-left: 0.25rem solid var(--danger-color);
        }
        
        .progress {
            height: 1rem;
            overflow: hidden;
            font-size: 0.75rem;
            background-color: #eaecf4;
        }
        
        .chart-area {
            position: relative;
            height: 300px;
        }
        
        .chart-pie {
            position: relative;
            height: 250px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100px;
            }
            
            .sidebar-brand {
                padding: 1rem 0.5rem;
            }
            
            .sidebar-text {
                display: none;
            }
            
            .sidebar-icon {
                margin-right: 0;
            }
            
            .sidebar-link {
                justify-content: center;
                padding: 0.8rem 0.5rem;
            }
            
            .content {
                margin-left: 100px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><i class="fas fa-leaf"></i> <span class="sidebar-text">ELP Admin</span></h2>
        </div>
        
        <div class="sidebar-menu">
            <div class="sidebar-item">
                <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt sidebar-icon"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="manage_users.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users sidebar-icon"></i>
                    <span class="sidebar-text">Manage Users</span>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="leave_history.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'leave_history.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history sidebar-icon"></i>
                    <span class="sidebar-text">Leave History</span>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="manage_leaves.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_leaves.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks sidebar-icon"></i>
                    <span class="sidebar-text">Manage Leaves</span>
                </a>
            </div>
            
            <!-- <div class="sidebar-item">
                <a href="leave_types.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'leave_types.php' ? 'active' : ''; ?>">
                    <i class="fas fa-list-alt sidebar-icon"></i>
                    <span class="sidebar-text">Leave Types</span>
                </a>
            </div> -->
            
            <!-- <div class="sidebar-item">
                <a href="reports.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar sidebar-icon"></i>
                    <span class="sidebar-text">Reports</span>
                </a>
            </div> -->
            
            <div class="sidebar-item mt-5">
                <a href="logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt sidebar-icon"></i>
                    <span class="sidebar-text">Logout</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="content">
        <!-- Top Navigation Bar -->
        <nav class="topbar">
            <div>
                <h4 class="m-0">Employee Leave Portal</h4>
            </div>
            
            <div class="d-flex align-items-center gap-4">
                <a href="notifications.php" class="notification-icon">
                    <i class="fas fa-bell text-muted fs-5"></i>
                    <?php if (isset($pending_approvals) && $pending_approvals > 0): ?>
                        <span class="badge rounded-pill bg-danger"><?php echo $pending_approvals; ?></span>
                    <?php endif; ?>
                </a>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="user-name"><?php echo isset($admin_email) ? htmlspecialchars($admin_email) : 'Admin'; ?></span>
                </div>
            </div>
        </nav>