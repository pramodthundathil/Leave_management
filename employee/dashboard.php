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

// Fetch data using MySQLi
// 1. Leave balance
$query_balance = "SELECT * FROM leave_balance WHERE user_id = ?";
$stmt_balance = $conn->prepare($query_balance);
$stmt_balance->bind_param("i", $employee_id);
$stmt_balance->execute();
$result_balance = $stmt_balance->get_result();
if ($result_balance->num_rows > 0) {
    $leave_balance = $result_balance->fetch_assoc();
}
$stmt_balance->close();

// 2. Leave history (latest 3)
$query_history = "SELECT lr.*, lt.LeaveName 
                    FROM leave_requests lr
                    LEFT JOIN leavetypes lt ON lr.leave_type_id = lt.LeaveTypeID
                    WHERE user_id = ? ORDER BY start_date ASC LIMIT 3";
$stmt_history = $conn->prepare($query_history);
$stmt_history->bind_param("i", $employee_id);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
$leave_history = $result_history->fetch_all(MYSQLI_ASSOC);
$stmt_history->close();

// 3. Pending leave count
$query_pending = "SELECT COUNT(*) as total FROM leave_requests WHERE user_id = ? AND status = 'pending'";
$stmt_pending = $conn->prepare($query_pending);
$stmt_pending->bind_param("i", $employee_id);
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();
$row = $result_pending->fetch_assoc();
$pending_requests = $row['total'];
$stmt_pending->close();

// Calculate Casual Leave and Earned Leave taken
$leave_taken = [
    'casual_leave_taken' => 0,
    'earned_leave_taken' => 0
];

$sql_taken = "SELECT lt.LeaveTypeID, SUM(DATEDIFF(end_date, start_date) + 1) AS days_taken
              FROM leave_requests lr
              JOIN leavetypes lt ON lr.leave_type_id = lt.LeaveTypeID
              WHERE lr.user_id = ? AND lr.status = 'approved' AND lt.LeaveTypeID IN (1, 3)
              GROUP BY lt.LeaveTypeID";
$stmt_taken = $conn->prepare($sql_taken);
$stmt_taken->bind_param("i", $employee_id);
$stmt_taken->execute();
$result_taken = $stmt_taken->get_result();

while ($row_taken = $result_taken->fetch_assoc()) {
    if ($row_taken['LeaveTypeID'] == 1) { // Earned Leave
        $leave_taken['earned_leave_taken'] = $row_taken['days_taken'];
    } elseif ($row_taken['LeaveTypeID'] == 3) { // Casual Leave
        $leave_taken['casual_leave_taken'] = $row_taken['days_taken'];
    }
}

$stmt_taken->close();
?>

<?php include('header.php'); ?>

<!-- Dashboard header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tachometer-alt text-primary me-2"></i>Dashboard</h1>
    <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
            <i class="fas fa-user"></i>
        </div>
        <span><?php echo htmlspecialchars($employee_email); ?></span>
    </div>
</div>

<!-- Dashboard stats -->
<div class="row mb-4">
    <!-- Pending Requests -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Pending Requests</h6>
                        <h2 class="card-title mb-0"><?php echo $pending_requests; ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-clock text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Earned Leave -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Earned Leave Taken</h6>
                        <h2 class="card-title mb-0"><?php echo $leave_taken['earned_leave_taken'] ?? 0; ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-calendar-check text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Casual Leave -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Casual LeaveTaken</h6>
                        <h2 class="card-title mb-0"><?php echo $leave_taken['casual_leave_taken'] ?? 0; ?></h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-umbrella-beach text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Balance -->
<?php
// This code should be added to your existing PHP file after the initialization of $leave_balance

// Fetch employee's gender for gender-specific leaves
$sql_gender = "SELECT gender FROM users WHERE id = ?";
$stmt_gender = $conn->prepare($sql_gender);
$stmt_gender->bind_param("i", $employee_id);
$stmt_gender->execute();
$result_gender = $stmt_gender->get_result();
$employee_gender = '';
if ($row_gender = $result_gender->fetch_assoc()) {
    $employee_gender = $row_gender['gender'];
}

// Get the current year
$current_year = date('Y');

// Query to get leave balances for the employee
// Print an error message if prepare fails to help with debugging
$sql = "SELECT ul.LeaveTypeID, lt.LeaveName, ul.Balance 
        FROM userleaves ul
        JOIN leavetypes lt ON ul.LeaveTypeID = lt.LeaveTypeID
        WHERE ul.UserID = ? AND ul.Year = ?";
$stmt = $conn->prepare($sql);

// Check if prepare was successful
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
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

// Check for gender-specific leaves that might not exist in the UserLeave table
// For Maternity Leave (only for females)
if ($employee_gender == 'Female' && !isset($leave_balance['Maternity_leave'])) {
    $sql_maternity = "SELECT MaxPerYear FROM LeaveTypes WHERE LeaveTypeID = 5";
    $result_maternity = $conn->query($sql_maternity);
    if ($row_maternity = $result_maternity->fetch_assoc()) {
        $leave_balance['Maternity_leave'] = $row_maternity['MaxPerYear'] ?? 0;
    }
}

// For Paternity Leave (only for males)
if ($employee_gender == 'Male' && !isset($leave_balance['Paternity_leave'])) {
    $sql_paternity = "SELECT MaxPerYear FROM LeaveTypes WHERE LeaveTypeID = 7";
    $result_paternity = $conn->query($sql_paternity);
    if ($row_paternity = $result_paternity->fetch_assoc()) {
        $leave_balance['Paternity_leave'] = $row_paternity['MaxPerYear'] ?? 0;
    }
}

// For leaves that might not exist in UserLeave table, set defaults based on LeaveType
$leave_types = [
    1 => 'earned_leave',
    2 => 'Halfpay_leave',
    3 => 'casual_leave'
];

foreach ($leave_types as $leave_id => $leave_key) {
    if (!isset($leave_balance[$leave_key])) {
        $sql_default = "SELECT MaxPerYear FROM LeaveTypes WHERE LeaveTypeID = ?";
        $stmt_default = $conn->prepare($sql_default);
        $stmt_default->bind_param("i", $leave_id);
        $stmt_default->execute();
        $result_default = $stmt_default->get_result();
        if ($row_default = $result_default->fetch_assoc()) {
            $leave_balance[$leave_key] = $row_default['MaxPerYear'] ?? 0;
        }
    }
}



?>

<!-- Leave Balance -->
<div class="card mb-4">
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

<!-- Recent Leave History -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-list-ul text-primary me-2"></i> Recent Leave History</h5>
    </div>
    <div class="card-body">
        <?php if (count($leave_history) > 0): ?>
            <div class="list-group">
                <?php foreach ($leave_history as $request): ?>
                    <div class="list-group-item list-group-item-action border-0 border-bottom py-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3 rounded-circle p-2 
                                <?php echo $request['status'] == 'approved' ? 'bg-success bg-opacity-10' : 
                                    ($request['status'] == 'rejected' ? 'bg-danger bg-opacity-10' : 'bg-warning bg-opacity-10'); ?>">
                                <i class="fas 
                                    <?php echo $request['status'] == 'approved' ? 'fa-check-circle text-success' : 
                                        ($request['status'] == 'rejected' ? 'fa-times-circle text-danger' : 'fa-hourglass-half text-warning'); ?>">
                                </i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><?php echo ucfirst($request['LeaveName']); ?></h6>
                                    <span class="badge rounded-pill 
                                        <?php echo $request['status'] == 'approved' ? 'bg-success' : 
                                            ($request['status'] == 'rejected' ? 'bg-danger' : 'bg-warning'); ?>">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </div>
                                <p class="text-muted small mb-0">
                                    <?php echo date('M d, Y', strtotime($request['start_date'])); ?> to 
                                    <?php echo date('M d, Y', strtotime($request['end_date'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-end mt-3">
                <a href="leave_history.php" class="btn btn-sm btn-outline-primary">
                    View full history <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No leave history available</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('footer.php'); ?>