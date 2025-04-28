<?php
session_start();
include('../config/db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Fetch leave requests based on user role
if ($user_role === 'admin') {
    // Admin can see all leave requests
    $sql = "SELECT leave_requests.id, users.name, leave_requests.leave_type, leave_requests.start_date, leave_requests.end_date, leave_requests.status 
            FROM leave_requests
            JOIN users ON leave_requests.user_id = users.id
            ORDER BY leave_requests.start_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $leave_requests = [];
    while ($row = $result->fetch_assoc()) {
        $leave_requests[] = $row;
    }
    $stmt->close();
} else {
    // Employees can see only their own leave requests
    $sql = "SELECT lr.*, lt.LeaveName 
        FROM leave_requests lr
        LEFT JOIN leavetypes lt ON lr.leave_type_id = lt.LeaveTypeID
        WHERE user_id = ? ORDER BY start_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $leave_requests = [];
    while ($row = $result->fetch_assoc()) {
        $leave_requests[] = $row;
    }
    $stmt->close();
}

// Handle leave status updates (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_role === 'admin') {
    $leave_id = $_POST['leave_id'];
    $new_status = $_POST['status'];

    $update_sql = "UPDATE leave_requests SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $leave_id);
    $update_stmt->execute();
    $update_stmt->close();

    header("Location: leave_status.php");
    exit();
}
?>


<?php include('header.php'); ?>
<div style="width: 100%; max-width: 1200px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); padding: 40px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="background: #4f6cea; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-hourglass-half" style="color: white; font-size: 1.3rem;"></i>
            </div>
            <h2 style="margin: 0; color: #2d3748; font-size: 1.8rem;">Leave Status</h2>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <?php if ($user_role === 'admin'): ?>
                        <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Employee Name</th>
                    <?php endif; ?>
                    <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Leave Type</th>
                    <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Start Date</th>
                    <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">End Date</th>
                    <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;"> Num of Days</th>
                    <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;"> Rejection Reason</th>
                    <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Status</th>
                    <?php if ($user_role === 'admin'): ?>
                        <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leave_requests as $leave): ?>
                    <tr style="transition: all 0.2s;"
                        onmouseover="this.style.backgroundColor='#f8fafc'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.05)';" 
                        onmouseout="this.style.backgroundColor='white'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <?php if ($user_role === 'admin'): ?>
                            <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #334155; font-weight: 500;"><?php echo htmlspecialchars($leave['name']); ?></td>
                        <?php endif; ?>
                        <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php echo htmlspecialchars($leave['LeaveName']); ?></td>
                        <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php echo date('M d, Y', strtotime($leave['start_date'])); ?></td>
                        <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php echo date('M d, Y', strtotime($leave['end_date'])); ?></td>
                        <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php echo htmlspecialchars( $leave['days']); ?></td>
                        <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php echo htmlspecialchars( $leave['rejection_reason']); ?></td>
                        <td style="padding: 15px; border-bottom: 1px solid #e2e8f0;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-weight: 500; font-size: 0.85rem; 
                                  <?php 
                                  if ($leave['status'] == 'approved') echo 'background-color: #10b98120; color: #10b981;';
                                  elseif ($leave['status'] == 'rejected') echo 'background-color: #ef444420; color: #ef4444;';
                                  else echo 'background-color: #f59e0b20; color: #f59e0b;';
                                  ?>">
                                <?php if ($leave['status'] == 'approved'): ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php elseif ($leave['status'] == 'rejected'): ?>
                                    <i class="fas fa-times-circle"></i>
                                <?php else: ?>
                                    <i class="fas fa-hourglass-half"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars(ucfirst($leave['status'])); ?>
                            </span>
                        </td>
                        <?php if ($user_role === 'admin'): ?>
                            <td style="padding: 15px; border-bottom: 1px solid #e2e8f0;">
                                <form action="leave_status.php" method="post" style="display: flex; gap: 10px; align-items: center;">
                                    <input type="hidden" name="leave_id" value="<?php echo htmlspecialchars($leave['id']); ?>">
                                    <select name="status" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; background-color: #f8fafc;"
                                            onfocus="this.style.borderColor='#4f6cea'; this.style.boxShadow='0 0 0 3px rgba(79, 108, 234, 0.1)';" 
                                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                        <option value="pending" <?php echo $leave['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $leave['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $leave['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                    <button type="submit" 
                                            style="padding: 8px 16px; background: linear-gradient(135deg, #4f6cea 0%, #3a56b2 100%); color: white; border: none; border-radius: 6px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 6px;"
                                            onmouseover="this.style.opacity='0.9'; this.style.transform='translateY(-2px)';" 
                                            onmouseout="this.style.opacity='1'; this.style.transform='translateY(0)';">
                                        <i class="fas fa-sync-alt" style="font-size: 0.9rem;"></i> Update
                                    </button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include('footer.php'); ?>