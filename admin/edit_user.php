<?php
session_start();
include('../config/db.php');

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Check if a user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid user ID.");
}

$user_id = intval($_GET['id']);

// Fetch user details
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $designation = $_POST['designation'];

    // Update user details without updating the role
    $update_query = "UPDATE users SET name = ?, email = ?, designation = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "sssi", $name, $email, $designation, $user_id);

    if (mysqli_stmt_execute($update_stmt)) {
        $_SESSION['success'] = "User updated successfully.";
        header("Location: manage_users.php");
        exit();
    } else {
        echo "Failed to update user.";
    }

    mysqli_stmt_close($update_stmt);
}

mysqli_stmt_close($stmt);
?>


<?php include('header.php'); ?>
<div style="background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); padding: 40px; width: 100%; max-width: 500px;margin: auto; display: flex; flex-direction: column; align-items: center; justify-content: center;">
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); width: 70px; height: 70px; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 15px;">
            <i class="fas fa-user-edit" style="font-size: 30px; color: white;"></i>
        </div>
        <h2 style="color: #333; margin: 0; font-size: 24px; font-weight: 600;">Edit User</h2>
    </div>

    <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Name Field -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
            <label style="color: #555; font-weight: 500; font-size: 14px;">Full Name</label>
            <div style="position: relative;">
                <i class="fas fa-user" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #2196F3;"></i>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required 
                       style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s;"
                       onfocus="this.style.borderColor='#2196F3'; this.style.boxShadow='0 0 0 3px rgba(33, 150, 243, 0.2)';"
                       onblur="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none';">
            </div>
        </div>

        <!-- Email Field -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
            <label style="color: #555; font-weight: 500; font-size: 14px;">Email Address</label>
            <div style="position: relative;">
                <i class="fas fa-envelope" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #2196F3;"></i>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required 
                       style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s;"
                       onfocus="this.style.borderColor='#2196F3'; this.style.boxShadow='0 0 0 3px rgba(33, 150, 243, 0.2)';"
                       onblur="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none';">
            </div>
        </div>

        <!-- Designation Field -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
            <label style="color: #555; font-weight: 500; font-size: 14px;">Designation</label>
            <div style="position: relative;">
                <i class="fas fa-briefcase" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #2196F3;"></i>
                <input type="text" name="designation" value="<?php echo htmlspecialchars($user['designation'] ?? ''); ?>" required 
                       style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s;"
                       onfocus="this.style.borderColor='#2196F3'; this.style.boxShadow='0 0 0 3px rgba(33, 150, 243, 0.2)';"
                       onblur="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none';">
            </div>
        </div>

        <!-- Update Button -->
        <button type="submit" 
                style="background: linear-gradient(to right, #2196F3, #1976D2); color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 500; cursor: pointer; transition: all 0.3s; margin-top: 10px; box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(33, 150, 243, 0.4)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(33, 150, 243, 0.3)';">
            <i class="fas fa-save" style="margin-right: 8px;"></i> Update User
        </button>
    </form>
</div>
<?php include('footer.php'); ?>