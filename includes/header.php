<?php
// Start the session
session_start();
include 'auth.php'; // Include authentication functions
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Leave Portal</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Include your CSS file -->
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>Employee Leave Portal</h1>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Home</a></li>

                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('employee')): ?>
                            <li><a href="employee/dashboard.php">Dashboard</a></li>
                            <li><a href="employee/apply_leave.php">Apply Leave</a></li>
                            <li><a href="employee/leave_status.php">Leave Status</a></li>
                            <li><a href="employee/leave_history.php">Leave History</a></li>
                        <?php elseif (hasRole('approver')): ?>
                            <li><a href="approver/dashboard.php">Dashboard</a></li>
                            <li><a href="approver/pending_requests.php">Pending Requests</a></li>
                            <li><a href="approver/approve_leave.php">Approve Leave</a></li>
                        <?php elseif (hasRole('admin')): ?>
                            <li><a href="admin/dashboard.php">Dashboard</a></li>
                            <li><a href="admin/manage_users.php">Manage Users</a></li>
                            <li><a href="admin/manage_leave_types.php">Manage Leave Types</a></li>
                            <li><a href="admin/reports.php">Reports</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <?php if (isLoggedIn() && isset($_SESSION['name'])): ?>
                <div class="user-greeting">
                    Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!
                </div>
            <?php endif; ?>
        </div>
    </header>
