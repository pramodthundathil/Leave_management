<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Unauthorized access.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval(value: $_POST['user_id']);

    // 1) Delete any leave requests for this user
    $delLeaves = mysqli_prepare($conn, "DELETE FROM leave_requests WHERE user_id = ?");
    mysqli_stmt_bind_param($delLeaves, "i", $id);
    mysqli_stmt_execute($delLeaves);
    mysqli_stmt_close($delLeaves);

    // 2) Now delete the user
    $delUser = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($delUser, "i", $id);
    if (mysqli_stmt_execute($delUser)) {
        echo "Success";
    } else {
        echo "Failed to delete user.";
    }
    mysqli_stmt_close($delUser);
} else {
    echo "Invalid request.";
}

