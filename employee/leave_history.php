<?php
session_start();
include('../config/db.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch leave history for the logged-in employee
$sql = "SELECT lr.*, lt.LeaveName 
        FROM leave_requests lr
        LEFT JOIN leavetypes lt ON lr.leave_type_id = lt.LeaveTypeID
        WHERE lr.user_id = ?
        ORDER BY lr.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$leave_history = [];
while ($row = $result->fetch_assoc()) {
    $leave_history[] = $row;
}

$stmt->close();
?>


<?php include('header.php'); ?>

<div style="width: 100%; max-width: 1000px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); padding: 40px;">
    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
        <div style="background: #4f6cea; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-history" style="color: white; font-size: 1.3rem;"></i>
        </div>
        <h2 style="margin: 0; color: #2d3748; font-size: 1.8rem;"> Leave History</h2>
    </div>

    <?php if (count($leave_history) > 0): ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: 800px;">
                <thead>
                    <tr>
                        <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Leave Type</th>
                        <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Start Date</th>
                        <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">End Date</th>
                        <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Number of Days</th>
                        <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Reason</th>
                        <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Status</th>
                        <th style="padding: 15px; text-align: left; background: #4f6cea; color: white; font-weight: 500; position: sticky; top: 0; z-index: 10;">Applied On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leave_history as $leave): ?>
                        <tr style="transition: all 0.2s;"
                            onmouseover="this.style.backgroundColor='#f8fafc'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.05)';" 
                            onmouseout="this.style.backgroundColor='white'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #334155; font-weight: 500;"><?php echo htmlspecialchars($leave['LeaveName']); ?></td>
                            <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php echo date('M d, Y', strtotime($leave['start_date'])); ?></td>
                            <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php echo date('M d, Y', strtotime($leave['end_date'])); ?></td>
                            <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php echo htmlspecialchars( $leave['days']); ?></td>
                            <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($leave['reason']); ?>">
                                <?php echo htmlspecialchars($leave['reason']); ?>
                            </td>
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
                            <td style="padding: 15px; border-bottom: 1px solid #e2e8f0; color: #64748b;"><?php echo date('M d, Y h:i A', strtotime($leave['applied_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px 20px; background: #f8fafc; border-radius: 8px; margin-top: 20px;">
            <i class="fas fa-calendar-times" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px;"></i>
            <h3 style="margin: 0 0 10px 0; color: #475569;">No Leave History Found</h3>
            <p style="margin: 0; color: #64748b;">You haven't applied for any leaves yet.</p>
            <a href="apply_leave.php" style="display: inline-block; margin-top: 20px; background: #4f6cea; color: white; text-decoration: none; padding: 10px 25px; border-radius: 8px; font-weight: 500; transition: all 0.3s;"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(79, 108, 234, 0.3)';" 
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <i class="fas fa-plus"></i> Apply for Leave
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>