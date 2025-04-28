<?php
/*
===================================
  Constants Configuration
===================================
*/

// Database Credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'leave_portal');

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_APPROVER', 'approver');
define('ROLE_EMPLOYEE', 'employee');

// Leave Status
define('STATUS_PENDING', 'Pending');
define('STATUS_APPROVED', 'Approved');
define('STATUS_REJECTED', 'Rejected');

// Leave Types
define('LEAVE_CASUAL', 'Casual');
define('LEAVE_EARNED', 'Earned');
define('LEAVE_MEDICAL', 'Medical');

// Notification Messages
define('NOTIFY_APPROVED', 'Your leave request has been approved.');
define('NOTIFY_REJECTED', 'Your leave request has been rejected.');
define('NOTIFY_NEW_POLICY', 'New leave policy updates available.');

?>
