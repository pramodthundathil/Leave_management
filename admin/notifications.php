<?php
session_start();
include('../config/db.php');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch pending leave requests with user details
$query = "SELECT lr.id, lr.user_id, lr.leave_type, lr.start_date, lr.end_date, lr.reason, lr.leave_type,
          lr.created_at, lr.status, u.name as employee_name, lt.LeaveName as leave_type_name
          FROM leave_requests lr 
          JOIN users u ON lr.user_id = u.id
          LEFT JOIN leavetypes lt ON lr.leave_type_id = lt.LeaveTypeID
          WHERE lr.status = 'pending' 
          ORDER BY lr.created_at DESC";

$result = mysqli_query($conn, $query);

$requests = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }
} else {
    die("Database query failed: " . mysqli_error($conn));
}

// Mark notifications as read if specified
if (isset($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    $query = "UPDATE leave_requests SET notification_read = 1 WHERE id = $id";
    mysqli_query($conn, $query);
    header("Location: notifications.php");
    exit();
}

// Mark all notifications as read
if (isset($_GET['mark_all_read'])) {
    $query = "UPDATE leave_requests SET notification_read = 1 WHERE status = 'pending'";
    mysqli_query($conn, $query);
    header("Location: notifications.php");
    exit();
}

// Count total notifications
$total_notifications = count($requests);

// Include header
include('header.php');
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bell me-2"></i> Pending Leave Request Notifications
                    </h6>
                    <?php if ($total_notifications > 0): ?>
                    <a href="?mark_all_read=true" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-check-double me-1"></i> Mark All as Read
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($total_notifications > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="notificationsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Duration</th>
                                        <th>Requested On</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $req): ?>
                                    <tr class="<?php echo isset($req['notification_read']) && $req['notification_read'] ? 'text-muted' : 'fw-bold'; ?>">
                                        <td><?php echo $req['id']; ?></td>
                                        <td><?php echo htmlspecialchars($req['employee_name']); ?></td>
                                        <td><?php echo htmlspecialchars($req['leave_type_name']); ?></td>
                                        <td>
                                            <?php 
                                                $start = new DateTime($req['start_date']);
                                                $end = new DateTime($req['end_date']);
                                                $interval = $start->diff($end);
                                                echo $start->format('M d, Y') . ' - ' . $end->format('M d, Y');
                                                echo '<br><span class="badge bg-info text-white">' . ($interval->days + 1) . ' days</span>';
                                            ?>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($req['created_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- <a href="view_request.php?id=<?php echo $req['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a> -->
                                                <button class="approve-btn btn btn-sm btn-success" data-id="<?php echo $req['id']; ?>" <?php if ($req['status'] !== 'pending') echo 'disabled'; ?> data-bs-toggle="tooltip" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-bs-target="#exampleModal<?php echo $req['id']; ?>" data-bs-toggle="modal" title="Reject">
                                                    <i class="fas fa-times"></i>
                                    </button>

                                    
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal<?php echo $req['id']; ?>" tabindex="-1"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Reason For Rejection
                                         Leave</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="reject.php">
                                        <input type="hidden" name="id" value="<?php echo $req['id']; ?>">
                                        <div class="mb-3">
                                            <label for="reason" class="form-label">Rejection Reason</label>
                                            <textarea class="form-control" id="reason" name="reason" rows="3"
                                                required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-danger"> Reject Leave</button>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                                </div>
                            </div>
                        </div>
                    </div>
                                                
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                            <h5>All caught up!</h5>
                            <p class="text-muted">There are no pending leave requests that require your attention.</p>
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <!-- <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i> Recent Leave Activity
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php
                        // Fetch recent activity
                        $activity_query = "SELECT lr.id, lr.status, lr.created_at, lr.updated_at,lr.leave_type 
                                          u.name as employee_name,
                                          FROM leave_requests lr 
                                          JOIN users u ON lr.user_id = u.id
                                          ORDER BY lr.updated_at DESC LIMIT 5";
                        $activity_result = mysqli_query($conn, $activity_query);
                        
                        if ($activity_result && mysqli_num_rows($activity_result) > 0):
                            while($activity = mysqli_fetch_assoc($activity_result)):
                                $status_class = 'info';
                                $status_icon = 'clock';
                                
                                if ($activity['status'] == 'approved') {
                                    $status_class = 'success';
                                    $status_icon = 'check-circle';
                                } elseif ($activity['status'] == 'rejected') {
                                    $status_class = 'danger';
                                    $status_icon = 'times-circle';
                                }
                        ?>
                        <div class="timeline-item">
                            <div class="timeline-item-marker">
                                <div class="timeline-item-marker-text">
                                    <?php echo date('M d', strtotime($activity['updated_at'])); ?>
                                </div>
                                <div class="timeline-item-marker-indicator bg-<?php echo $status_class; ?>">
                                    <i class="fas fa-<?php echo $status_icon; ?> text-white"></i>
                                </div>
                            </div>
                            <div class="timeline-item-content pt-0">
                                <div class="card shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="small text-muted">
                                            <?php echo date('F d, Y \a\t h:i A', strtotime($activity['updated_at'])); ?>
                                        </div>
                                        <div class="mt-2">
                                            <?php echo htmlspecialchars($activity['employee_name']); ?>'s 
                                            <span class="fw-bold"><?php echo htmlspecialchars($activity['leave_type']); ?></span> leave request was 
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($activity['status']); ?>
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <a href="view_request.php?id=<?php echo $activity['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No recent activity to display.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
</div>

<!-- Add custom CSS for timeline -->
<style>
    .timeline {
        position: relative;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0.75rem;
        height: 100%;
        width: 2px;
        background-color: #e3e6ec;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 2rem;
    }
    
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    
    .timeline-item-marker {
        position: absolute;
        left: -1.5rem;
        width: 3rem;
    }
    
    .timeline-item-marker-text {
        width: 100%;
        text-align: center;
        font-size: 0.75rem;
        color: #a2acba;
        margin-bottom: 0.25rem;
    }
    
    .timeline-item-marker-indicator {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 100%;
        background-color: #fff;
        border: 2px solid #e3e6ec;
        margin: 0 auto;
    }
    
    .timeline-item-content {
        padding-left: 1.5rem;
        padding-top: 0.25rem;
    }
    
    .notification-unread {
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    /* Custom styles for DataTables */
    #notificationsTable_wrapper .dataTables_length,
    #notificationsTable_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
</style>

<!-- Initialize DataTables and Tooltips -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    $('#notificationsTable').DataTable({
        "order": [[ 4, "desc" ]],
        "pageLength": 10,
        "language": {
            "emptyTable": "No pending notifications"
        }
    });
    
    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});


document.querySelectorAll('.approve-btn').forEach(button => {
        button.addEventListener('click', function () {
            const leaveId = this.getAttribute('data-id');
            fetch('approve.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${leaveId}`
            }).then(response => response.text())
                .then(data => location.reload());
        });
    });
</script>

<?php include('footer.php'); ?>