<?php
session_start();
include('../config/db.php');

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$users = [];
$msg = "";

// Fetch all users
$sql = "SELECT * FROM users ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

if ($result) {
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    die("Database error: " . mysqli_error($conn));
}

// Handle delete user
// Handle delete user
if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    
    // Start a transaction to ensure data integrity
    mysqli_begin_transaction($conn);
    
    try {
        // First delete records from userleaves table
        $leaveQuery = "DELETE FROM userleaves WHERE UserID = ?";
        $leaveStmt = mysqli_prepare($conn, $leaveQuery);
        mysqli_stmt_bind_param($leaveStmt, 'i', $userId);
        $leaveResult = mysqli_stmt_execute($leaveStmt);
        mysqli_stmt_close($leaveStmt);
        
        // Now delete the user
        $userQuery = "DELETE FROM users WHERE id = ?";
        $userStmt = mysqli_prepare($conn, $userQuery);
        mysqli_stmt_bind_param($userStmt, 'i', $userId);
        $userResult = mysqli_stmt_execute($userStmt);
        mysqli_stmt_close($userStmt);
        
        // If everything is successful, commit the transaction
        mysqli_commit($conn);
        header("Location: manage_users.php?msg=User deleted successfully");
        exit();
    } catch (Exception $e) {
        // If there's an error, roll back the transaction
        mysqli_rollback($conn);
        header("Location: manage_users.php?msg=Failed to delete user: " . $e->getMessage());
        exit();
    }
}
?>

<?php include('header.php'); ?>
<div style="padding: 30px; flex-grow: 1;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);">
        <h2 style="color: #333; margin: 0; font-size: 24px; font-weight: 600;">Manage Users</h2>
        <a href="add_user.php" style="background: linear-gradient(to right, #4CAF50, #45a049); color: white; padding: 12px 20px; border: none; border-radius: 8px; text-decoration: none; font-size: 15px; font-weight: 500; transition: all 0.3s; box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);">
            <i class="fas fa-user-plus" style="margin-right: 8px;"></i> Add New User
        </a>
       
    </div>


    <?php if (isset($_GET['msg']) && !empty($_GET['msg'])): ?>
    <div style="background: #f0f8ff; color: #333; padding: 10px 20px; border-radius: 5px; margin-top: 10px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
    <script>
        // Remove the message parameter from URL after 3 seconds
        setTimeout(function() {
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 3000);
    </script>
    <?php endif; ?>


    <?php if (!empty($users)): ?>
        <div style="background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: linear-gradient(to right, #667eea, #764ba2); color: white;">
                        <th style="padding: 15px; text-align: left; font-weight: 500;">ID</th>
                        <th style="padding: 15px; text-align: left; font-weight: 500;">Full Name</th>
                        <th style="padding: 15px; text-align: left; font-weight: 500;">Email</th>
                        <th style="padding: 15px; text-align: left; font-weight: 500;">Designation</th>
                        <th style="padding: 15px; text-align: left; font-weight: 500;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid #f0f0f0; transition: all 0.3s;" onmouseover="this.style.backgroundColor='#f9f9f9'">
                            <td style="padding: 15px; color: #555;"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td style="padding: 15px; color: #333; font-weight: 500;"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td style="padding: 15px; color: #555;"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td style="padding: 15px; color: #555;"><?php echo htmlspecialchars($user['designation']); ?></td>
                            <td style="padding: 15px;">
                                <!-- Edit Button -->
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" 
                                style="background: linear-gradient(to right, #FFA500, #FF8C00); color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 14px; margin-right: 8px; display: inline-block; transition: all 0.3s; box-shadow: 0 2px 5px rgba(255, 165, 0, 0.2);">
                                <i class="fas fa-edit" style="margin-right: 5px;"></i> Edit</a>

                                <!-- Delete Button -->
                             
                    <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user" value="1" style="background: linear-gradient(to right, #FF5252, #FF1744); color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; transition: all 0.3s; box-shadow: 0 2px 5px rgba(255, 82, 82, 0.2);">
                            <i class="fas fa-trash-alt" style="margin-right: 5px;"></i> Delete
                        </button>
                    </form>
                                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);">
            <i class="fas fa-users" style="font-size: 50px; color: #ddd; margin-bottom: 20px;"></i>
            <h3 style="color: #777; margin-bottom: 10px;">No Users Found</h3>
            <p style="color: #999; margin-bottom: 20px;">There are currently no users in the system.</p>
            <a href="add_user.php" style="background: linear-gradient(to right, #4CAF50, #45a049); color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block;">
                <i class="fas fa-user-plus" style="margin-right: 8px;"></i> Add Your First User
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?php  include('footer.php'); ?>