<?php 
require_once '../../_base.php';
require_once '../../db/db_connect.php';

// Check if user is already logged in
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    redirect('../admin/admin_profile.php');
}

// Get error message
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="\css\header.css">
    <link rel="stylesheet" href="\css\logo.css">
    <link rel="stylesheet" href="\css\style.css">
    <link rel="stylesheet" href="\css\admin_login.css">
    <script src="../../js/validation.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <main>
        <div class="login">
            <div class="left">
                <a href="../../index.php"><img class="logo" src="../../img/logo4.png" alt="logo4.png"></a>
                <div class="form">
                    <h1>Admin Log In</h1>
                    <?php if ($error): ?>
                        <div class="error-message" style="color: red; margin-bottom: 15px;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <?php unset($_SESSION['error']); // Clear the error after displaying it ?>
                    <form method="post" action="../../backend/admin_login.php" onsubmit="return validateAdminLogin()">
                        <div class="input">
                            <label for="admin_email">Email*</label><br>
                            <input type="text" id="admin_email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <span id="admin_email_error" style="color:red;"></span>
                        </div>

                        <div class="input">
                            <label for="admin_password">Password*</label><br>
                            <input type="password" id="admin_password" name="password">
                            <span id="admin_pwd_error" style="color:red;"></span>
                        </div>
                
                        <div class="rmb-forgot">
                            <div class="remember">
                                <input type="checkbox" id="remember" name="remember">
                                <label class="rmb" for="remember">Remember Me</label>
                            </div>
                            <div class="forgot">
                                <a href="../forgot_password.php" class="forgot">Forgot Password?</a>
                            </div>
                        </div>
                        <br><br>
                        
                        <button type="submit">Submit</button>
                    </form>
                    <p>Not an Admin? <a href="../signup_login.php">Member Log In</a></p>
                </div>
            </div>
            <div class="right">
                <img id="login-img" src="../../img/login.jpg" alt="login_pic.jpg">
            </div>
        </div>
    </main>
    <script>
        function validateAdminLogin() {
            let email = document.getElementById('admin_email').value;
            let password = document.getElementById('admin_password').value;
            let emailError = document.getElementById('admin_email_error');
            let pwdError = document.getElementById('admin_pwd_error');
            let isValid = true;

            // Reset error messages
            emailError.textContent = '';
            pwdError.textContent = '';

            // Email validation
            if (email.trim() === '') {
                emailError.textContent = 'Email is required';
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailError.textContent = 'Please enter a valid email address';
                isValid = false;
            }

            // Password validation
            if (password.trim() === '') {
                pwdError.textContent = 'Password is required';
                isValid = false;
            } else if (password.length < 8) {
                pwdError.textContent = 'Password must be at least 8 characters long';
                isValid = false;
            } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(password)) {
                pwdError.textContent = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character';
                isValid = false;
            }

            return isValid;
        }
    </script>
</body>
</html>
