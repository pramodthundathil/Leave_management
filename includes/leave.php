<?php
include('../config/db.php'); // Include MySQLi database connection

// Function to apply for leave
function applyLeave($user_id, $leave_type_id, $start_date, $end_date, $reason) {
    global $conn;

    // Check if the dates are valid
    if ($start_date > $end_date) {
        return "Invalid date range. Start date must be before end date.";
    }

    // Check if the user has sufficient leave balance
    $balance = getLeaveBalance($user_id, $leave_type_id);
    $days_requested = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;

    if ($balance < $days_requested) {
        return "Insufficient leave balance.";
    }

    // Insert the leave application into the database
    $sql = "INSERT INTO LeaveApplications (user_id, leave_type_id, start_date, end_date, reason, status) 
            VALUES (?, ?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $user_id, $leave_type_id, $start_date, $end_date, $reason);
    $stmt->execute();
    $stmt->close();

    return true;
}

// Function to fetch leave history for a user
function getLeaveHistory($user_id) {
    global $conn;

    $sql = "SELECT la.leave_id, lt.leave_type_name, la.start_date, la.end_date, la.reason, la.status, la.applied_on 
            FROM LeaveApplications la
            JOIN LeaveTypes lt ON la.leave_type_id = lt.leave_type_id
            WHERE la.user_id = ?
            ORDER BY la.applied_on DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave_history = [];

    while ($row = $result->fetch_assoc()) {
        $leave_history[] = $row;
    }

    $stmt->close();
    return $leave_history;
}

// Function to get leave balance for a user
function getLeaveBalance($user_id, $leave_type_id) {
    global $conn;

    $sql = "SELECT balance FROM LeaveBalances WHERE user_id = ? AND leave_type_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $leave_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $balance = 0;

    if ($row = $result->fetch_assoc()) {
        $balance = $row['balance'];
    }

    $stmt->close();
    return $balance;
}

// Function to update leave balance
function updateLeaveBalance($user_id, $leave_type_id, $days) {
    global $conn;

    $sql = "UPDATE LeaveBalances SET balance = balance - ? WHERE user_id = ? AND leave_type_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dii", $days, $user_id, $leave_type_id);
    $stmt->execute();
    $stmt->close();
}

// Function to approve or reject a leave application
function processLeaveApplication($leave_id, $status) {
    global $conn;

    // Update the leave application status
    $sql = "UPDATE LeaveApplications SET status = ? WHERE leave_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $leave_id);
    $stmt->execute();
    $stmt->close();

    // If approved, update leave balance
    if ($status === 'Approved') {
        $leave = getLeaveApplication($leave_id);
        $days = (strtotime($leave['end_date']) - strtotime($leave['start_date'])) / (60 * 60 * 24) + 1;
        updateLeaveBalance($leave['user_id'], $leave['leave_type_id'], $days);
    }

    return true;
}

// Function to fetch a specific leave application
function getLeaveApplication($leave_id) {
    global $conn;

    $sql = "SELECT * FROM LeaveApplications WHERE leave_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();
    $stmt->close();

    return $leave;
}
?>
