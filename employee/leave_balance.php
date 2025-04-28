<?php
session_start();
include('../config/db.php');

// Check if the employee is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../login.php');
    exit();
}

// Fetch employee details from the session
$employee_id = $_SESSION['user_id'];
$employee_email = $_SESSION['email'];

// Initialize variables
$leave_balance = [
    'Halfpay_leave' => 0,
    'casual_leave' => 0,
    'earned_leave' => 0,
    'Maternity_leave' => 0,
    'Paternity_leave' => 0
];
$leave_history = [];
$pending_requests = 0;

// Fetch employee's gender for gender-specific leaves
$sql_gender = "SELECT gender FROM users WHERE id = ?";
$stmt_gender = $conn->prepare($sql_gender);
if ($stmt_gender === false) {
    die("Prepare failed (gender): " . $conn->error);
}

$stmt_gender->bind_param("i", $employee_id);
$stmt_gender->execute();
$result_gender = $stmt_gender->get_result();
$employee_gender = '';
if ($row_gender = $result_gender->fetch_assoc()) {
    $employee_gender = $row_gender['gender'];
}
$stmt_gender->close();

// Get the current year
$current_year = date('Y');

// Query to get leave balances for the employee - using revised table names
$sql = "SELECT ul.LeaveTypeID, lt.LeaveName, ul.Balance 
        FROM userleaves ul
        JOIN leavetypes lt ON ul.LeaveTypeID = lt.LeaveTypeID
        WHERE ul.UserID = ? AND ul.Year = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed (balance): " . $conn->error);
}

$stmt->bind_param("ii", $employee_id, $current_year);
$stmt->execute();
$result = $stmt->get_result();

// Process leave balances
while ($row = $result->fetch_assoc()) {
    // Based on the leave type, update the corresponding leave balance
    switch ($row['LeaveTypeID']) {
        case 1: // Earned Leave
            $leave_balance['earned_leave'] = $row['Balance'];
            break;
        case 2: // Half Pay Leave
            $leave_balance['Halfpay_leave'] = $row['Balance'];
            break;
        case 3: // Casual Leave
            $leave_balance['casual_leave'] = $row['Balance'];
            break;
        case 5: // Maternity Leave
            if ($employee_gender == 'Female') {
                $leave_balance['Maternity_leave'] = $row['Balance'];
            }
            break;
        case 7: // Paternity Leave
            if ($employee_gender == 'Male') {
                $leave_balance['Paternity_leave'] = $row['Balance'];
            }
            break;
    }
}
$stmt->close();

// Check for gender-specific leaves that might not exist in the userleaves table
// For Maternity Leave (only for females)
if ($employee_gender == 'Female' && !isset($leave_balance['Maternity_leave'])) {
    $sql_maternity = "SELECT MaxPerYear FROM leavetypes WHERE LeaveTypeID = 5";
    $result_maternity = $conn->query($sql_maternity);
    if ($result_maternity && $row_maternity = $result_maternity->fetch_assoc()) {
        $leave_balance['Maternity_leave'] = $row_maternity['MaxPerYear'] ?? 0;
    }
}

// For Paternity Leave (only for males)
if ($employee_gender == 'Male' && !isset($leave_balance['Paternity_leave'])) {
    $sql_paternity = "SELECT MaxPerYear FROM leavetypes WHERE LeaveTypeID = 7";
    $result_paternity = $conn->query($sql_paternity);
    if ($result_paternity && $row_paternity = $result_paternity->fetch_assoc()) {
        $leave_balance['Paternity_leave'] = $row_paternity['MaxPerYear'] ?? 0;
    }
}

// For leaves that might not exist in userleaves table, set defaults based on LeaveType
$leave_types = [
    1 => 'earned_leave',
    2 => 'Halfpay_leave',
    3 => 'casual_leave'
];

foreach ($leave_types as $leave_id => $leave_key) {
    if (!isset($leave_balance[$leave_key]) || $leave_balance[$leave_key] == 0) {
        $sql_default = "SELECT MaxPerYear FROM leavetypes WHERE LeaveTypeID = ?";
        $stmt_default = $conn->prepare($sql_default);
        if ($stmt_default) {
            $stmt_default->bind_param("i", $leave_id);
            $stmt_default->execute();
            $result_default = $stmt_default->get_result();
            if ($row_default = $result_default->fetch_assoc()) {
                $leave_balance[$leave_key] = $row_default['MaxPerYear'] ?? 0;
            }
            $stmt_default->close();
        }
    }
}
?>
<?php include('header.php'); ?>
<!-- Leave Balance -->
<div class="card mb-4 mt-5">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-wallet text-primary me-2"></i> Your Leave Balance</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4 col-lg">
                <div class="bg-light p-3 rounded">
                    <div class="small text-muted mb-1">Earned Leave</div>
                    <div class="h4"><?php echo $leave_balance['earned_leave'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-4 col-lg">
                <div class="bg-light p-3 rounded">
                    <div class="small text-muted mb-1">Half Pay Leave</div>
                    <div class="h4"><?php echo $leave_balance['Halfpay_leave'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-4 col-lg">
                <div class="bg-light p-3 rounded">
                    <div class="small text-muted mb-1">Casual Leave</div>
                    <div class="h4"><?php echo $leave_balance['casual_leave'] ?? 0; ?></div>
                </div>
            </div>
            <?php if ($employee_gender == 'Female'): ?>
            <div class="col-md-4 col-lg">
                <div class="bg-light p-3 rounded">
                    <div class="small text-muted mb-1">Maternity Leave</div>
                    <div class="h4"><?php echo $leave_balance['Maternity_leave'] ?? 0; ?></div>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($employee_gender == 'Male'): ?>
            <div class="col-md-4 col-lg">
                <div class="bg-light p-3 rounded">
                    <div class="small text-muted mb-1">Paternity Leave</div>
                    <div class="h4"><?php echo $leave_balance['Paternity_leave'] ?? 0; ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>




