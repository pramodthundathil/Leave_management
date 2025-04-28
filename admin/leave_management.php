<?php
session_start();
include('../config/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get the filter value
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build the query dynamically based on the filter
$query = "SELECT * FROM leave_requests";
if ($status_filter === 'approved') {
    $query .= " WHERE status = 'approved'";
} elseif ($status_filter === 'pending') {
    $query .= " WHERE status = 'pending'";
} elseif ($status_filter === 'rejected') {
    $query .= " WHERE status = 'rejected'";
} elseif ($status_filter === 'not_approved') {
    $query .= " WHERE status != 'approved'";
}

$leave_requests = [];

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $leave_requests[] = $row;
    }
} else {
    error_log("Database error: " . mysqli_error($conn));
    $leave_requests = [];
    $error_message = "Failed to load leave requests. Please try again later.";
}

 // Close the database connection
?>

<?php include('header.php'); ?>



    <script>
        function filterLeaves() {
            var status = document.getElementById("leaveFilter").value;
            window.location.href = "leave_management.php?status=" + status;
        }
    </script>

<div class="container">
    <h2>Leave Management</h2>

    <?php if (isset($error_message)): ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="filter-section">
        <label for="leaveFilter">Filter Leaves:</label>
        <select id="leaveFilter" onchange="filterLeaves()">
            <option value="all" <?php echo ($status_filter === 'all') ? 'selected' : ''; ?>>All Leaves</option>
            <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="approved" <?php echo ($status_filter === 'approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="rejected" <?php echo ($status_filter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
            <option value="not_approved" <?php echo ($status_filter === 'not_approved') ? 'selected' : ''; ?>>Not Approved</option>
        </select>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee Name</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($leave_requests)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No leave requests found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($leave_requests as $leave): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($leave['id']); ?></td>
                        <td><?php echo htmlspecialchars($leave['employee_name']); ?></td>
                        <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                        <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($leave['status'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('footer.php'); ?>
