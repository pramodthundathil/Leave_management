<?php
 // Start the session

// Check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if the user has a specific role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect to login if the user is not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect to login if the user does not have the required role
function requireRole($role) {
    requireLogin(); // Ensure the user is logged in
    if (!hasRole($role)) {
        header('Location: login.php');
        exit();
    }
}

// Logout the user
function logout() {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header('Location: login.php'); // Redirect to login page
    exit();
}
?>