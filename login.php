<?php
session_start();
include('config/db.php'); // MySQLi connection

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // 1️⃣ Check `admin` table
        $stmt = $conn->prepare("SELECT id, email, password FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['role'] = 'admin';
            session_regenerate_id(true);

            header('Location: admin/dashboard.php');
            exit();
        }

        // 2️⃣ If not admin, check `users` table
        $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            session_regenerate_id(true);

            switch ($user['role']) {
                case 'approver':
                    header('Location: approver/dashboard.php');
                    break;
                case 'employee':
                    header('Location: employee/dashboard.php');
                    break;
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                default:
                    $error = "Invalid role.";
                    break;
            }
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Leave Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
        crossorigin="anonymous"></script>
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
    <div class="login-container"
        style="background: rgba(255, 255, 255, 0.95); padding: 40px 50px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); max-width: 700px; animation: fadeInUp 0.6s ease-out; backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.2);margin: 100px auto; position: relative; z-index: 1;">
        <div style="text-align: center; margin-bottom: 10px;">
            <i class="fas fa-user-circle" style="font-size: 50px; color: #667eea; margin-bottom: 10px;"></i>
        </div>
        <h1 style="text-align: center; color: #764ba2; margin-bottom: 30px; font-weight: 600; font-size: 28px;">Welcome
            Back</h1>

        <?php if (!empty($error)): ?>
            <div class="error-message"
                style="color: #ff4757; margin: 15px 0; padding: 10px; background: rgba(255, 71, 87, 0.1); border-radius: 5px; text-align: center; animation: shake 0.5s;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="row">
                <div class="form-group col-md-6" style="margin-bottom: 25px; position: relative;">
                    <label for="email"
                        style="display: block; margin-bottom: 8px; font-weight: 500; color: #555;">Email:</label>
                    <div style="position: relative;">
                        <i class="fas fa-envelope"
                            style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #667eea;"></i>
                        <input type="email" name="email" id="email" required
                            style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #ddd; border-radius: 8px; transition: all 0.3s; font-size: 15px; background: rgba(245, 245, 245, 0.5);">
                    </div>
                </div>

                <div class="form-group col-md-6" style="margin-bottom: 25px; position: relative;">
                    <label for="password"
                        style="display: block; margin-bottom: 8px; font-weight: 500; color: #555;">Password:</label>
                    <div style="position: relative;">
                        <i class="fas fa-lock"
                            style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #667eea;"></i>
                        <input type="password" name="password" id="password" required
                            style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #ddd; border-radius: 8px; transition: all 0.3s; font-size: 15px; background: rgba(245, 245, 245, 0.5);">
                    </div>
                </div>

                <div style="margin-bottom: 20px; text-align: right;">
                    <a href="#" style="color: #667eea; text-decoration: none; font-size: 13px;">Forgot password?</a>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn"
                        style="background: linear-gradient(to right, #667eea, #764ba2); color: white; padding: 14px; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s; width: 100%; font-weight: 500; letter-spacing: 0.5px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                        Login <i class="fas fa-sign-in-alt" style="margin-left: 5px;"></i>
                    </button>
                </div>
            </div>
        </form>

        <div class="signup-link" style="text-align: center; margin-top: 25px; color: #666; font-size: 14px;">
            Don't have an account? <a href="register.php"
                style="color: #764ba2; text-decoration: none; font-weight: 500;">Create one</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        input:focus {
            border-color: #764ba2 !important;
            box-shadow: 0 0 0 2px rgba(118, 75, 162, 0.2) !important;
            background: white !important;
        }

        a:hover {
            opacity: 0.9;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5) !important;
        }
    </style>
</body>

</html>