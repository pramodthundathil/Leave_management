<?php
session_start();
include 'config/db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Leave Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; overflow-x: hidden;">

<!-- Navigation Bar -->
<nav style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 0; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); position: fixed; width: 100%; top: 0; z-index: 1000;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center;">
            <a href="#" style="color: white; text-decoration: none; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center;">
                <i class="fas fa-calendar-alt" style="margin-right: 10px;"></i> ELP
            </a>
        </div>
        
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="index.php" style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s; padding: 8px 12px; border-radius: 6px;" 
               onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'" 
               onmouseout="this.style.backgroundColor='transparent'">Home</a>

            <a href="#" style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s; padding: 8px 12px; border-radius: 6px;" 
               onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'" 
               onmouseout="this.style.backgroundColor='transparent'">About</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="Login.php" style="background: white; color: #667eea; text-decoration: none; font-weight: 600; padding: 8px 20px; border-radius: 30px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.15)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'">Login</a>
                <a href="register.php" style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; font-weight: 600; padding: 8px 20px; border-radius: 30px; transition: all 0.3s;"
                   onmouseover="this.style.backgroundColor='rgba(255,255,255,0.3)'"
                   onmouseout="this.style.backgroundColor='rgba(255,255,255,0.2)'">Register</a>
            <?php else: ?>
                
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section style="height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 0 20px; margin-top: -60px;">
    <div style="max-width: 800px;">
        <h1 style="font-size: 3.5rem; font-weight: 700; color: #2d3748; margin-bottom: 20px; line-height: 1.2; text-shadow: 1px 1px 3px rgba(0,0,0,0.1);">
            Welcome to <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Employee Leave Portal</span>
        </h1>
        <p style="font-size: 1.2rem; color: #4a5568; margin-bottom: 40px; line-height: 1.6;">
            Streamline your leave management process with our intuitive platform. 
            Employees can easily apply for leave, track balances, and get approvals online, 
            while administrators can efficiently manage all requests in one place.
        </p>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <?php if (isset($_SESSION['user_id'])): ?>
                
            <?php else: ?>
                <a href="login.php" style="background: linear-gradient(135deg, #ff9a44 0%, #ff6b6b 100%); color: white; text-decoration: none; font-weight: 600; padding: 12px 30px; border-radius: 30px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);"
                   onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(255,107,107,0.5)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255,107,107,0.4)'">
                    Login <i class="fas fa-sign-in-alt" style="margin-left: 8px;"></i>
                </a>
                <a href="register.php" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; text-decoration: none; font-weight: 600; padding: 12px 30px; border-radius: 30px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);"
                   onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(79,172,254,0.5)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(79,172,254,0.4)'">
                    Register <i class="fas fa-user-plus" style="margin-left: 8px;"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section style="padding: 80px 20px; background: white;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="text-align: center; font-size: 2.5rem; font-weight: 700; color: #2d3748; margin-bottom: 60px;">
            Why Choose Our <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Leave Portal</span>
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <!-- Feature 1 -->
            <div style="background: #f8fafc; border-radius: 12px; padding: 30px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); transition: all 0.3s; border-top: 4px solid #667eea;"
                 onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 30px rgba(0,0,0,0.1)'"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.05)'">
                <div style="width: 60px; height: 60px; background: rgba(102, 126, 234, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fas fa-clock" style="font-size: 1.5rem; color: #667eea;"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 600; color: #2d3748; margin-bottom: 15px;">Easy Leave Requests</h3>
                <p style="color: #4a5568; line-height: 1.6;">Submit leave requests with just a few clicks and track their status in real-time.</p>
            </div>
            
            <!-- Feature 2 -->
            <div style="background: #f8fafc; border-radius: 12px; padding: 30px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); transition: all 0.3s; border-top: 4px solid #4facfe;"
                 onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 30px rgba(0,0,0,0.1)'"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.05)'">
                <div style="width: 60px; height: 60px; background: rgba(79, 172, 254, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fas fa-chart-pie" style="font-size: 1.5rem; color: #4facfe;"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 600; color: #2d3748; margin-bottom: 15px;">Balance Tracking</h3>
                <p style="color: #4a5568; line-height: 1.6;">Always know your available leave balances for different leave types.</p>
            </div>
            
            <!-- Feature 3 -->
            <div style="background: #f8fafc; border-radius: 12px; padding: 30px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); transition: all 0.3s; border-top: 4px solid #ff9a44;"
                 onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 30px rgba(0,0,0,0.1)'"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.05)'">
                <div style="width: 60px; height: 60px; background: rgba(255, 154, 68, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fas fa-user-shield" style="font-size: 1.5rem; color: #ff9a44;"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 600; color: #2d3748; margin-bottom: 15px;">Admin Dashboard</h3>
                <p style="color: #4a5568; line-height: 1.6;">Comprehensive tools for administrators to manage and approve leave requests efficiently.</p>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: center; gap: 30px; margin-bottom: 30px;">
            <a href="#" style="color: white; font-size: 1.5rem; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><i class="fab fa-facebook"></i></a>
            <a href="#" style="color: white; font-size: 1.5rem; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><i class="fab fa-twitter"></i></a>
            <a href="#" style="color: white; font-size: 1.5rem; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><i class="fab fa-linkedin"></i></a>
            <a href="#" style="color: white; font-size: 1.5rem; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'"><i class="fab fa-instagram"></i></a>
        </div>
        <p style="margin-bottom: 20px;">© 2025 Employee Leave Portal. All rights reserved.</p>
        <div style="display: flex; justify-content: center; gap: 20px;">
            <a href="#" style="color: white; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.textDecoration='underline'">Privacy Policy</a>
            <a href="#" style="color: white; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.textDecoration='underline'">Terms of Service</a>
            <a href="#" style="color: white; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.textDecoration='underline'">Contact Us</a>
        </div>
    </div>
</footer>

</body>
</html>