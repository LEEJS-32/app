<?php
require_once '../_base.php';
require_once '../db/db_connect.php';

// Check if user is already logged in
if (isset($_SESSION['user']) && $_SESSION['user']->role === 'member') {
    redirect('member/member_profile.php');
}

// Get form data and errors from session
$form_data = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['form_data'], $_SESSION['errors']); // Clear after getting
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Login</title>
    <link rel="stylesheet" href="\css\header.css">
    <link rel="stylesheet" href="\css\logo.css">
    <link rel="stylesheet" href="\css\style.css">
    <link rel="stylesheet" href="\css\login.css">
    <script src="../../js/validation.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
<div id="info"><?= temp('info')?></div>
    <main>
        <div class="login">
            <div class="left">
                <a href="../../index.php"><img class="logo" src="../../img/logo4.png" alt="logo4.png"></a>
                <!-- login -->
                <div class="form" id="login">
                    <h1>Member Log In</h1>
                    <form method="post" action="../../backend/member_login.php" onsubmit="return validateLoginForm()">
                        <div class="input">
                            <label for="login_email">Email*</label><br>
                            <input type="text" id="login_email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                            <span id="login_email_error" style="color:red;"><?php echo $errors['email'] ?? ''; ?></span>
                        </div>

                        <div class="input">
                            <label for="login_password">Password*</label><br>
                            <input type="password" id="login_password" name="password">
                            <span id="login_pwd_error" style="color:red;"><?php echo $errors['password'] ?? ''; ?></span>
                        </div>
                
                        <div class="rmb-forgot">
                            <div class="remember">
                                <input type="checkbox" id="remember" name="remember" <?php echo isset($form_data['remember']) ? 'checked' : ''; ?>>
                                <label class="rmb" for="remember">Remember Me</label>
                            </div>
                            <div class="forgot">
                                <a href="forgot_password.php" class="forgot">Forgot Password?</a>
                            </div>
                        </div>
                        <br><br>
                        
                        <button type="submit">Submit</button>
                    </form>
                    <p>Not a member? <a href="javascript:void(0)" onclick="toggleForm()">Sign Up Now!</a></p>
                    <p>Admin? <a href="admin/admin_login.php">Admin Log In</a></p>
                </div>

                <!-- Sign Up -->
                <div class="form" id="sign-up">
                    <h1>Member Sign Up</h1>
                    <form method="post" action="../../backend/signup.php" onsubmit="return validateSignupForm()">
                        <div class="input">
                            <label for="signup_name">Name*</label><br>
                            <input type="text" id="signup_name" name="name" value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>">
                            <span id="signup_name_error" style="color:red;"><?php echo $errors['name'] ?? ''; ?></span>
                        </div>
                    
                        <div class="input">
                            <label for="signup_email">Email*</label><br>
                            <input type="text" id="signup_email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                            <span id="signup_email_error" style="color:red;"><?php echo $errors['email'] ?? ''; ?></span>
                        </div>

                        <div class="input">
                            <label for="signup_password">Password*</label><br>
                            <input type="password" id="signup_password" name="password">
                            <span id="signup_pwd_error" style="color:red;"><?php echo $errors['password'] ?? ''; ?></span>
                        </div>
                        
                        <button type="submit">Submit</button>
                    </form>
                    <p>Already have an account? <a href="javascript:void(0)" onclick="toggleForm()">Member Log In</a></p>
                    <p>Admin? <a href="admin/admin_login.php">Admin Log In</a></p>
                </div>
            </div>
            <div class="right">
                <img id="login-img" src="../../img/login.jpg" alt="login_pic.jpg">
            </div>
        </div>
    </main>

    <script>
        function toggleForm() {
            const loginForm = document.getElementById('login');
            const signupForm = document.getElementById('sign-up');

            if (loginForm.style.transform === 'translateX(0px)') {
                // If login form is currently in view, slide it out and bring sign-up form in
                loginForm.style.transform = 'translateX(-110%)';
                signupForm.style.transform = 'translateX(0)';
            } else {
                // If sign-up form is in view, slide it out and bring login form back
                loginForm.style.transform = 'translateX(0)';
                signupForm.style.transform = 'translateX(110%)';
            }
        }

        function validateLoginForm() {
            let email = document.getElementById('login_email').value;
            let password = document.getElementById('login_password').value;
            let emailError = document.getElementById('login_email_error');
            let pwdError = document.getElementById('login_pwd_error');
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
            }

            return isValid;
        }

        function validateSignupForm() {
            let name = document.getElementById('signup_name').value;
            let email = document.getElementById('signup_email').value;
            let password = document.getElementById('signup_password').value;
            let nameError = document.getElementById('signup_name_error');
            let emailError = document.getElementById('signup_email_error');
            let pwdError = document.getElementById('signup_pwd_error');
            let isValid = true;

            // Reset error messages
            nameError.textContent = '';
            emailError.textContent = '';
            pwdError.textContent = '';

            // Name validation
            if (name.trim() === '') {
                nameError.textContent = 'Name is required';
                isValid = false;
            } else if (name.length < 2) {
                nameError.textContent = 'Name must be at least 2 characters long';
                isValid = false;
            } else if (!/^[a-zA-Z\s]+$/.test(name)) {
                nameError.textContent = 'Name can only contain letters and spaces';
                isValid = false;
            }

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
