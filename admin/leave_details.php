<?php
session_start();
include('../config/db.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Check if ID is passed
if (!isset($_GET['id'])) {
    echo "Invalid request. Leave ID is missing.";
    exit();
}

$leave_id = intval($_GET['id']);

// Fetch leave details



$sql = "SELECT lr.*, u.name, u.email, lt.LeaveName
        FROM leave_requests lr
        JOIN users u ON lr.user_id = u.id
        JOIN leavetypes lt ON lr.leave_type_id = lt.LeaveTypeID
        WHERE lr.id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $leave_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($leave = mysqli_fetch_assoc($result)) {
    // Leave found
} else {
    echo "No leave request found with the given ID.";
    exit();
}
?>

<?php include('header.php'); ?>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .detail { margin: 15px 0; }
        .label { font-weight: bold; color: #444; }
        .value { margin-left: 10px; color: #555; }
        a.back-link { display: inline-block; margin-top: 20px; text-decoration: none; color: #6e8efb; }
    </style>


<div class="container">
    <h2>Leave Request Details</h2>

    <div class="detail"><span class="label">Employee Name:</span><span class="value"><?php echo htmlspecialchars($leave['name']); ?></span></div>
    <div class="detail"><span class="label">Email:</span><span class="value"><?php echo htmlspecialchars($leave['email']); ?></span></div>
    <div class="detail"><span class="label">Leave Type:</span><span class="value"><?php echo htmlspecialchars($leave['LeaveName']); ?></span></div>
    <div class="detail"><span class="label">Start Date:</span><span class="value"><?php echo htmlspecialchars($leave['start_date']); ?></span></div>
    <div class="detail"><span class="label">End Date:</span><span class="value"><?php echo htmlspecialchars($leave['end_date']); ?></span></div>
    <div class="detail"><span class="label">Status:</span><span class="value"><?php echo htmlspecialchars($leave['status']); ?></span></div>
    <div class="detail"><span class="label">Reason:</span><span class="value"><?php echo nl2br(htmlspecialchars($leave['reason'])); ?></span></div>
    <div class="detail"><span class="label">Submitted On:</span><span class="value"><?php echo htmlspecialchars($leave['created_at']); ?></span></div>

    <a href="leave_history.php" class="back-link">← Back to Leave History</a>
</div>


<?php include('footer.php'); ?>