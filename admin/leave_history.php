<?php
session_start();
include('../config/db.php');

// Ensure the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch leave history using MySQLi
$sql = "SELECT 
        lr.id, 
        u.name, 
        u.email, 
        lt.LeaveName, 
        lr.start_date, 
        lr.end_date, 
        lr.status
        FROM leave_requests lr
        JOIN users u ON lr.user_id = u.id
        JOIN leavetypes lt ON lr.leave_type_id = lt.LeaveTypeID
        ORDER BY lr.id ASC";

$result = mysqli_query($conn, $sql);

$leave_requests = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $leave_requests[] = $row;
    }
} else {
    die("Query failed: " . mysqli_error($conn));
}
?>


<?php include('header.php'); ?>

<style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f3f6;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 40px;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background: linear-gradient(to right, #3f51b5, #5a55ae);
            color: white;
        }
        .status {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .pending { background: #fde4e4; color: #c0392b; }
        .approved { background: #d4edda; color: #28a745; }
        .rejected { background: #f8d7da; color: #dc3545; }
    </style>

<div class="container">
    <h1>Leave History</h1>
    <table>
    <tr>
        <th>ID</th>
        <th>Employee Name</th>
        <th>Leave Type</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php foreach ($leave_requests as $leave) { ?>
        <tr>
            <td><?php echo htmlspecialchars($leave['id']); ?></td>
            <td><?php echo htmlspecialchars($leave['name']); ?></td>
            <td><?php echo htmlspecialchars($leave['LeaveName']); ?></td>
            <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
            <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
            <td>
                <span class="status <?php echo strtolower($leave['status']); ?>">
                    <?php echo ucfirst(htmlspecialchars($leave['status'])); ?>
                </span>

            </td>
            <td>
                <a class="btn btn-sm btn-info" href="leave_details.php?id=<?php echo $leave['id']; ?>" >View Details</a>
            </td>
        </tr>
    <?php } ?>
</table>

</div>

<?php include('footer.php'); ?>