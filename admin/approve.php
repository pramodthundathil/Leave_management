<?php
session_start();
include('../config/db.php');

// Check if user is admin or approver
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'approver')) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $leave_id = intval($_POST['id']);
    $approver_id = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, get the leave request details
        $leave_query = "SELECT user_id, leave_type_id, days FROM leave_requests WHERE id = ?";
        $leave_stmt = $conn->prepare($leave_query);
        $leave_stmt->bind_param("i", $leave_id);
        $leave_stmt->execute();
        $leave_result = $leave_stmt->get_result();
        
        if ($leave_row = $leave_result->fetch_assoc()) {
            $user_id = $leave_row['user_id'];
            $leave_type_id = $leave_row['leave_type_id'];
            $days_taken = $leave_row['days'];
            $current_year = date('Y');
            
            // Check if user has leave balance record for this leave type and year
            $balance_query = "SELECT UserLeaveID, Taken, TotalEligible FROM userleaves 
                              WHERE UserID = ? AND LeaveTypeID = ? AND Year = ?";
            $balance_stmt = $conn->prepare($balance_query);
            $balance_stmt->bind_param("iii", $user_id, $leave_type_id, $current_year);
            $balance_stmt->execute();
            $balance_result = $balance_stmt->get_result();
            
            if ($balance_row = $balance_result->fetch_assoc()) {
                // User has existing balance record
                $user_leave_id = $balance_row['UserLeaveID'];
                $current_taken = $balance_row['Taken'];
                $total_eligible = $balance_row['TotalEligible'];
                
                // Calculate new values
                $new_taken = $current_taken + $days_taken;
                $new_balance = $total_eligible - $new_taken;
                
                // Check if user has enough leave balance
                if ($new_balance < 0) {
                    throw new Exception("Insufficient leave balance for this user.");
                }
                
                // Update user leave balance
                $update_query = "UPDATE userleaves 
                                 SET Taken = ?, Balance = ? 
                                 WHERE UserLeaveID = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("iii", $new_taken, $new_balance, $user_leave_id);
                $update_stmt->execute();
            } else {
                // No balance record exists - get default eligibility from leavetypes table
                $type_query = "SELECT MaxPerYear FROM leavetypes WHERE LeaveTypeID = ?";
                $type_stmt = $conn->prepare($type_query);
                $type_stmt->bind_param("i", $leave_type_id);
                $type_stmt->execute();
                $type_result = $type_stmt->get_result();
                
                if ($type_row = $type_result->fetch_assoc()) {
                    $total_eligible = $type_row['MaxPerYear'];
                    $new_taken = $days_taken;
                    $new_balance = $total_eligible - $new_taken;
                    
                    // Check if user has enough leave balance
                    if ($new_balance < 0) {
                        throw new Exception("Insufficient leave balance for this user.");
                    }
                    
                    // Create new balance record
                    $insert_query = "INSERT INTO userleaves 
                                    (UserID, LeaveTypeID, Year, TotalEligible, Taken, Balance) 
                                    VALUES (?, ?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("iiiiii", $user_id, $leave_type_id, $current_year, 
                                            $total_eligible, $new_taken, $new_balance);
                    $insert_stmt->execute();
                } else {
                    throw new Exception("Leave type not found.");
                }
            }
            
            // Update leave request status
            $status_query = "UPDATE leave_requests SET 
                            status = 'approved', 
                            approved_by = ?, 
                            approval_date = ? 
                            WHERE id = ?";
            $status_stmt = $conn->prepare($status_query);
            $status_stmt->bind_param("isi", $approver_id, $today, $leave_id);
            $status_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            echo "Leave approved successfully and balance updated.";
        } else {
            throw new Exception("Leave request not found.");
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>