<?php
$admin_password = "admin_password"; // Replace with your actual admin password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hashed_password;
?>