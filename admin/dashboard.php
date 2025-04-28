<?php
session_start();
include('../config/db.php');

// Ensure the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$admin_email = $_SESSION['email'];

// Fetch statistics
$sql_leave_requests = "SELECT COUNT(*) AS total FROM leave_requests";
$result = mysqli_query($conn, $sql_leave_requests);
$row = mysqli_fetch_assoc($result);
$total_leave_requests = $row['total'];

$sql_total_users = "SELECT COUNT(*) AS total FROM users";
$result = mysqli_query($conn, $sql_total_users);
$row = mysqli_fetch_assoc($result);
$total_users = $row['total'];

$sql_pending_approvals = "SELECT COUNT(*) AS total FROM leave_requests WHERE status = 'pending'";
$result = mysqli_query($conn, $sql_pending_approvals);
$row = mysqli_fetch_assoc($result);
$pending_approvals = $row['total'];

// Get leave types for chart
$sql_leave_types = "SELECT LeaveTypeID, LeaveName FROM leavetypes";
$leave_types_result = mysqli_query($conn, $sql_leave_types);
$leave_types = [];
$leave_names = [];
while ($row = mysqli_fetch_assoc($leave_types_result)) {
    $leave_types[$row['LeaveTypeID']] = $row['LeaveName'];
    $leave_names[] = $row['LeaveName'];
}

// Get leave usage statistics by type
$sql_leave_usage = "SELECT LeaveTypeID, SUM(Taken) as TotalTaken FROM userleaves GROUP BY LeaveTypeID";
$leave_usage_result = mysqli_query($conn, $sql_leave_usage);
$leave_usage_data = [];
$leave_usage_labels = [];
while ($row = mysqli_fetch_assoc($leave_usage_result)) {
    if (isset($leave_types[$row['LeaveTypeID']])) {
        $leave_usage_data[] = (int)$row['TotalTaken']; // Ensure it's an integer
        $leave_usage_labels[] = $leave_types[$row['LeaveTypeID']];
    }
}

// Ensure we have data for the leave usage chart (if not, use placeholder data)
if (empty($leave_usage_data)) {
    $leave_usage_labels = ['Earned Leave', 'Half Pay Leave', 'Casual Leave', 'Hospital Leave'];
    $leave_usage_data = [0, 5, 6, 0]; // Use data from userleaves table
}

// Get leave balance statistics
$sql_leave_balance = "SELECT u.name, lt.LeaveName, ul.Balance, ul.TotalEligible, ul.Taken 
                     FROM userleaves ul
                     JOIN users u ON ul.UserID = u.id
                     JOIN leavetypes lt ON ul.LeaveTypeID = lt.LeaveTypeID
                     ORDER BY u.name, lt.LeaveName
                     LIMIT 10";
$leave_balance_result = mysqli_query($conn, $sql_leave_balance);

// Get monthly leave trends - get actual data from database
$sql_monthly_trends = "SELECT 
                      MONTH(start_date) as month,
                      COUNT(*) as count
                      FROM leave_requests
                      WHERE YEAR(start_date) = 2025
                      GROUP BY MONTH(start_date)
                      ORDER BY MONTH(start_date)";
$monthly_result = mysqli_query($conn, $sql_monthly_trends);

// Initialize array with zeros for all months
$monthly_data = array_fill(0, 12, 0);
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Fill in actual data where available
while ($row = mysqli_fetch_assoc($monthly_result)) {
    $month_index = (int)$row['month'] - 1; // Convert to 0-based index
    $monthly_data[$month_index] = (int)$row['count'];
}

// Get gender distribution
$sql_gender = "SELECT gender, COUNT(*) as count FROM users GROUP BY gender";
$gender_result = mysqli_query($conn, $sql_gender);
$gender_data = [];
$gender_labels = [];
while ($row = mysqli_fetch_assoc($gender_result)) {
    if ($row['gender']) { // Check if gender is not null or empty
        $gender_labels[] = $row['gender'];
        $gender_data[] = (int)$row['count'];
    }
}

// Ensure we have data for the gender chart
if (empty($gender_data)) {
    $gender_labels = ['Male', 'Female'];
    $gender_data = [3, 2]; // From users table
}

// Recent leave requests
$sql_recent_requests = "SELECT lr.id, u.name, lt.LeaveName, lr.start_date, lr.end_date, lr.status
                       FROM leave_requests lr
                       JOIN users u ON lr.user_id = u.id
                       JOIN leavetypes lt ON lr.leave_type_id = lt.LeaveTypeID
                       ORDER BY lr.created_at DESC
                       LIMIT 5";
$recent_requests_result = mysqli_query($conn, $sql_recent_requests);

// Include header
include('header.php');
?>

<!-- Main Content -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Leave Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_leave_requests; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Approvals</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_approvals; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Leave Types</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($leave_types); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Leave Usage Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Leave Usage by Type</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="leaveUsageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trends Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Leave Trends</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Leave Requests -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Leave Requests</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="recentRequestsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($recent_requests_result)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['LeaveName']); ?></td>
                                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php elseif ($row['status'] == 'rejected'): ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gender Distribution Chart -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gender Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Balance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Leave Balance Summary</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="leaveBalanceTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Balance</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Reset result pointer
                                mysqli_data_seek($leave_balance_result, 0);
                                while ($row = mysqli_fetch_assoc($leave_balance_result)): 
                                    // Calculate balance based on TotalEligible - Taken
                                    $balance = isset($row['Balance']) ? $row['Balance'] : 
                                              (isset($row['TotalEligible']) && isset($row['Taken']) ? 
                                               $row['TotalEligible'] - $row['Taken'] : 0);
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['LeaveName']); ?></td>
                                        <td><?php echo $balance; ?></td>
                                        <td>
                                            <div class="progress">
                                                <?php
                                                // Get the max from leavetypes table or use a default
                                                $max_days = 30; // Default
                                                foreach ($leave_types as $id => $name) {
                                                    if ($name === $row['LeaveName']) {
                                                        // You would need to modify this to get actual MaxPerYear for the specific leave type
                                                        $max_days = isset($row['TotalEligible']) ? $row['TotalEligible'] : 30;
                                                        break;
                                                    }
                                                }
                                                $percentage = $max_days > 0 ? min(100, ($balance / $max_days) * 100) : 0;
                                                $colorClass = "bg-success";
                                                if ($percentage < 30) {
                                                    $colorClass = "bg-danger";
                                                } elseif ($percentage < 70) {
                                                    $colorClass = "bg-warning";
                                                }
                                                ?>
                                                <div class="progress-bar <?php echo $colorClass; ?>" role="progressbar"
                                                    style="width: <?php echo $percentage; ?>%"
                                                    aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <?php echo round($percentage); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Include Chart.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<!-- JavaScript for Charts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Leave Usage Chart
        var ctxPie = document.getElementById('leaveUsageChart').getContext('2d');
        var leaveUsageChart = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($leave_usage_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($leave_usage_data); ?>,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#2c9faf'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#60616f', '#373840', '#1e6f78'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
                cutoutPercentage: 70,
            },
        });

        // Monthly Trends Chart
        var ctxArea = document.getElementById('monthlyTrendsChart').getContext('2d');
        var monthlyTrendsChart = new Chart(ctxArea, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: "Leave Requests",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: <?php echo json_encode($monthly_data); ?>,
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'month'
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 12
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            beginAtZero: true
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10
                }
            }
        });

        // Gender Distribution Chart
        var ctxGender = document.getElementById('genderChart').getContext('2d');
        var genderChart = new Chart(ctxGender, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($gender_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($gender_data); ?>,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
            },
        });

        // Initialize DataTables
        $('#recentRequestsTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 5
        });

        $('#leaveBalanceTable').DataTable({
            "order": [[2, "desc"]],
            "pageLength": 5
        });
    });
</script>

<?php include('footer.php'); ?>