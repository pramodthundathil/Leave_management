<?php
session_start();
include('../config/db.php'); 

// Ensure the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $gender = trim($_POST["gender"]);
    $designation = trim($_POST['designation']);

    // Check if fields are empty
    if (empty($name) || empty($email) || empty($password) || empty($designation)) {
        $message = "All fields are required!";
    } else {
        // Check if email already exists
        $checkQuery = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = "Email already exists!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $insertQuery = "INSERT INTO users (name, email, password, designation, gender) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $hashed_password, $designation, $gender);

            if (mysqli_stmt_execute($stmt)) {
                $userID = mysqli_insert_id($conn);
                
                // Insert leave balances for this user
                $year = date('Y');

                $leaveInsertQuery = "
                    INSERT INTO UserLeaves (UserID, LeaveTypeID, Year, TotalEligible, Taken, Balance)
                    SELECT 
                        ?,
                        lt.LeaveTypeID,
                        ?,
                        CASE
                            WHEN lt.LeaveName = 'Earned Leave' THEN 30
                            WHEN lt.LeaveName = 'Half Pay Leave' THEN 20
                            WHEN lt.LeaveName = 'Casual Leave' THEN 20
                            WHEN lt.LeaveName = 'Hospital Leave' THEN 0
                            WHEN lt.LeaveName = 'Maternity Leave' THEN 180
                            WHEN lt.LeaveName = 'Miscarriage Leave' THEN 42
                            WHEN lt.LeaveName = 'Paternity Leave' THEN 15
                            WHEN lt.LeaveName = 'Special Casual Leave' THEN 0
                            ELSE 0
                        END AS TotalEligible,
                        0 AS Taken,
                        CASE
                            WHEN lt.LeaveName = 'Earned Leave' THEN 30
                            WHEN lt.LeaveName = 'Half Pay Leave' THEN 20
                            WHEN lt.LeaveName = 'Casual Leave' THEN 20
                            WHEN lt.LeaveName = 'Hospital Leave' THEN 0
                            WHEN lt.LeaveName = 'Maternity Leave' THEN 180
                            WHEN lt.LeaveName = 'Miscarriage Leave' THEN 42
                            WHEN lt.LeaveName = 'Paternity Leave' THEN 15
                            WHEN lt.LeaveName = 'Special Casual Leave' THEN 0
                            ELSE 0
                        END AS Balance
                    FROM LeaveTypes lt
                    WHERE lt.GenderSpecific = 'All' 
                        OR (lt.GenderSpecific = 'Male' AND ? = 'Male')
                        OR (lt.GenderSpecific = 'Female' AND ? = 'Female')";
                
                $leaveStmt = mysqli_prepare($conn, $leaveInsertQuery);
                mysqli_stmt_bind_param($leaveStmt, 'isss', $userID, $year, $gender, $gender);
                
                if (mysqli_stmt_execute($leaveStmt)) {
                    $message = "User added successfully!";
                } else {
                    $message = "Error adding leave balances: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($leaveStmt);
            } else {
                $message = "Error adding user: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<?php include('header.php'); ?>
<!-- Main Content -->
<div style=" padding: 30px; flex-grow: 1; display: flex; justify-content: center; align-items: center;margin: auto; background: #f9f9f9; min-height: 100vh;">
    <div style="background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); padding: 40px; width: 100%; max-width: 700px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 70px; height: 70px; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 15px;">
                <i class="fas fa-user-plus" style="font-size: 30px; color: white;"></i>
            </div>
            <h1 style="color: #333; margin: 0; font-size: 24px; font-weight: 600;">Add New User</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div style="background: #ffebee; color: #f44336; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border-left: 4px solid #f44336;">
                <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                <label style="color: #555; font-weight: 500; font-size: 14px;">Full Name</label>
                <div style="position: relative;">
                    <i class="fas fa-user" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #667eea;"></i>
                    <input type="text" name="name" required style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                <label style="color: #555; font-weight: 500; font-size: 14px;">Email Address</label>
                <div style="position: relative;">
                    <i class="fas fa-envelope" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #667eea;"></i>
                    <input type="email" name="email" required style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
            <label style="display: block; margin-bottom: 8px; color: #555;">Gender:</label>
                <select name="gender" required
                style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>

                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                <label style="color: #555; font-weight: 500; font-size: 14px;">Password</label>
                <div style="position: relative;">
                    <i class="fas fa-lock" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #667eea;"></i>
                    <input type="password" name="password" required style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                    <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #667eea; cursor: pointer;" onclick="togglePasswordVisibility()"></i>
                </div>
                <p style="color: #888; font-size: 12px;"><i class="fas fa-info-circle" style="margin-right: 5px;"></i> Password must be at least 8 characters</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                <label style="color: #555; font-weight: 500; font-size: 14px;">Designation</label>
                <div style="position: relative;">
                    <i class="fas fa-briefcase" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #667eea;"></i>
                    <input type="text" name="designation" required style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                </div>
            </div>

            <button type="submit" style="background: linear-gradient(to right, #667eea, #764ba2); color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 500; cursor: pointer; transition: all 0.3s;">
                <i class="fas fa-user-plus" style="margin-right: 8px;"></i> Add User
            </button>
        </form>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordField = document.querySelector('input[name="password"]');
        const icon = document.getElementById('togglePassword');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

<?php include('footer.php'); ?>