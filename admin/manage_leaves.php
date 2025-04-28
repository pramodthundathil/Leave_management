<?php
session_start();
include('../config/db.php');

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch leave requests with user info and leave type name
$query = "
    SELECT 
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
    ORDER BY lr.id ASC
";

$result = mysqli_query($conn, $query);

// Optional: Handle query failure
if (!$result) {
    die("Error fetching leave requests: " . mysqli_error($conn));
}

include('header.php');
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
    crossorigin="anonymous"></script>

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
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    th,
    td {
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

    .pending {
        background: #fde4e4;
        color: #c0392b;
    }

    .approved {
        background: #d4edda;
        color: #28a745;
    }

    .rejected {
        background: #f8d7da;
        color: #dc3545;
    }
</style>

<div class="container">
    <h2>📅 Manage Leave Requests</h2>

 
    
    <?php if (isset($_SESSION['message']) || isset($_SESSION['error_message'])): ?>
        <script>
            alert('<?php echo isset($_SESSION['error_message']) ? $_SESSION['error_message'] : $_SESSION['message']; ?>');
        </script>
        <?php endif; ?>
    <table>
        <tr>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>Dates</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                    <small><?php echo htmlspecialchars($row['email']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($row['LeaveName']); ?></td>
                <td><strong><?php echo date('M j, Y', strtotime($row['start_date'])); ?></strong> to
                    <?php echo date('M j, Y', strtotime($row['end_date'])); ?></td>
                <td>
                    <span class="status <?php echo strtolower($row['status']); ?>">
                        <?php echo $row['status']; ?>
                    </span>

                </td>
                <td>
                    <!-- <button 
                    class="approve-btn" 
                    data-id="<?php echo $row['id']; ?>"
                    <?php if ($row['status'] !== 'pending')
                        echo 'disabled'; ?>
                    style="background: #28a745; color: white; padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer; margin-right: 8px;">
                    <i class="fas fa-check"></i> Approve -->
                    </button>


                    <?php if ($row['status'] === 'pending'): ?>
                        <button class="approve-btn" data-id="<?php echo $row['id']; ?>" <?php if ($row['status'] !== 'pending')
                               echo 'disabled'; ?>
                            style="background: #28a745; color: white; padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer; margin-right: 8px;">
                            <i class="fas fa-check"></i> Approve
                        </button>


                        <button data-bs-target="#exampleModal<?php echo $row['id']; ?>" data-bs-toggle="modal"
                            style="background: #dc3545; color: white; padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer;">
                            <i class="fas fa-times"></i> Reject
                        </button>



                    <?php endif; ?>


                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal<?php echo $row['id']; ?>" tabindex="-1"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Reason For Rejection
                                        <?php echo $row['name']; ?>'s Leave</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="reject.php">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
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

                    <!-- <button 
                    class="reject-btn" 
                    data-id="<?php echo $row['id']; ?>"
                    <?php if ($row['status'] !== 'pending')
                        echo 'disabled'; ?>
                    style="background: #dc3545; color: white; padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer;">
                    <i class="fas fa-times"></i> Reject
                    </button> -->







                </td>

            </tr>
        <?php } ?>
    </table>
</div>

<!-- AJAX -->
<script>
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

    document.querySelectorAll('.reject-btn').forEach(button => {
        button.addEventListener('click', function () {
            const leaveId = this.getAttribute('data-id');
            fetch('reject.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${leaveId}`
            }).then(response => response.text())
                .then(data => location.reload());
        });
    });
</script>
<?php include('footer.php'); ?>