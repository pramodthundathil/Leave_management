<?php
session_start();
include('../config/db.php');

// Check if the user is an admin or approver
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'approver')) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if the request is POST and has the required data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['action'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$leaveId = intval($_POST['id']);
$action = $_POST['action'];

// Validate action
if (!in_array($action, ['approve', 'reject'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Check if the leave request exists and is still pending
$checkSql = "SELECT status FROM leave_requests WHERE id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $leaveId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['success' => false, 'message' => 'Leave request not found']);
    exit();
}

$request = $result->fetch_assoc();

if (strtolower($request['status']) !== 'pending') {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Leave request has already been processed']);
    exit();
}

// Update the leave request status
$newStatus = ($action === 'approve') ? 'Approved' : 'Rejected';
$updateSql = "UPDATE leave_requests SET status = ? WHERE id = ?";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("si", $newStatus, $leaveId);

if ($updateStmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Leave request updated successfully']);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>
