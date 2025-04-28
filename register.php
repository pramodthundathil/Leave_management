<?php
// Database connection
$host = 'localhost';
$dbname = 'leave_management';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// Form submission handling
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $designation = htmlspecialchars($_POST['designation']);
    $mobile = htmlspecialchars($_POST['mobile']);
    $gender = htmlspecialchars($_POST['gender']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, designation, mobile, gender, password) VALUES (:name, :email, :designation, :mobile, :gender, :password)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':designation', $designation);
        $stmt->bindParam(':mobile', $mobile);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        // Get the last inserted user ID
        $userID = $pdo->lastInsertId();

        // Insert leave balances for this user
        $year = date('Y');

        $leaveInsertQuery = "
    INSERT INTO UserLeaves (UserID, LeaveTypeID, Year, TotalEligible, Taken, Balance)
    SELECT 
        :userID,
        lt.LeaveTypeID,
        :year,
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
        OR (lt.GenderSpecific = 'Male' AND :gender = 'Male')
        OR (lt.GenderSpecific = 'Female' AND :gender = 'Female');
";

        $leaveStmt = $pdo->prepare($leaveInsertQuery);
        $leaveStmt->execute([
            ':userID' => $userID,
            ':year' => $year,
            ':gender' => $gender
        ]);

        $message = "Registration successful!";
        $messageType = "success";
        header("Location: login.php");
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $message = "Email already registered.";
            $messageType = "error";
        } else {
            $message = "Registration failed. Please try again.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Leave Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</head>

<body
    style="margin: 0; font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; overflow-x: hidden;">

    <!-- Navigation Bar -->
    <nav
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 0; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); position: fixed; width: 100%; top: 0; z-index: 1000;">
        <div
            style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center;">
                <a href="#"
                    style="color: white; text-decoration: none; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center;">
                    <i class="fas fa-calendar-alt" style="margin-right: 10px;"></i> ELP
                </a>
            </div>

            <div style="display: flex; align-items: center; gap: 20px;">
                <a href="index.php"
                    style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s; padding: 8px 12px; border-radius: 6px;"
                    onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'"
                    onmouseout="this.style.backgroundColor='transparent'">Home</a>

                <a href="#"
                    style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s; padding: 8px 12px; border-radius: 6px;"
                    onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'"
                    onmouseout="this.style.backgroundColor='transparent'">About</a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="Login.php"
                        style="background: white; color: #667eea; text-decoration: none; font-weight: 600; padding: 8px 20px; border-radius: 30px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.15)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'">Login</a>
                    <a href="register.php"
                        style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; font-weight: 600; padding: 8px 20px; border-radius: 30px; transition: all 0.3s;"
                        onmouseover="this.style.backgroundColor='rgba(255,255,255,0.3)'"
                        onmouseout="this.style.backgroundColor='rgba(255,255,255,0.2)'">Register</a>
                <?php else: ?>

                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div
        style="background: rgba(255, 255, 255, 0.9); padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); max-width: 700px;margin: 100px auto; position: relative; top: 30px;">
        <h2 style="color: #764ba2; text-align: center; margin-bottom: 30px;">User Registration</h2>

        <?php if (!empty($message)): ?>
            <div
                style="background-color: <?= $messageType == 'success' ? '#e6ffe6' : '#ffe6e6'; ?>; color: <?= $messageType == 'success' ? '#2d862d' : '#cc0000'; ?>; padding: 8px 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" style="display: flex; flex-direction: column; ">
            <div class="row">
            <div style="margin-bottom: 20px;" class="col-md-6">
                <label style="display: block; margin-bottom: 8px; color: #555;">Name:</label>
                <input type="text" name="name" required
                    style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
            </div>

            <div style="margin-bottom: 20px;" class="col-md-6">
                <label style="display: block; margin-bottom: 8px; color: #555;">Email:</label>
                <input type="email" name="email" required
                    style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
            </div>

            <div style="margin-bottom: 20px;" class="col-md-6">
                <label style="display: block; margin-bottom: 8px; color: #555;">Designation:</label>
                <input type="text" name="designation" required
                    style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
            </div>

            <div style="margin-bottom: 20px;" class="col-md-6">
                <label style="display: block; margin-bottom: 8px; color: #555;">Mobile No:</label>
                <input type="text" name="mobile" required pattern="\d{10}" title="Enter 10-digit mobile number"
                    style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
            </div>

            <div style="margin-bottom: 20px;" class="col-md-6">
                <label style="display: block; margin-bottom: 8px; color: #555;">Gender:</label>
                <select name="gender" required
                    style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>

                </select>
            </div>

            <div style="margin-bottom: 25px;" class="col-md-6">
                <label style="display: block; margin-bottom: 8px; color: #555;">Password:</label>
                <input type="password" name="password" required
                    style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
            </div>

            <div class="col-md-12">

                <input type="submit" value="Register"
                    style="background: linear-gradient(to right, #667eea, #764ba2); color: white; padding: 12px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer;width: 100%; font-weight: 500; letter-spacing: 0.5px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: all 0.3s;">

            </div>
            </div>
        </form>

        <p style="text-align: center; margin-top: 15px;">
            Already have an account?
            <a href="login.php" style="color: #6a0dad; font-weight: bold; text-decoration: none;">Login</a>
        </p>
    </div>

</body>

</html>