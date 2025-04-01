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
    <main>
        <div class="login">
            <div class="left">
                <a href="../../index.php"><img class="logo" src="../../img/logo4.png" alt="logo4.png"></a>
                <!-- login -->
                <div class="form" id="login">
                    <h1>Member Log In</h1>
                    <form method="post" action="../../backend/login.php" >
                        <?php include ('../_base.php') ?>
                        <div class="input">
                            <label for="email">Email*</label><br>
                            <?php html_text('email'); ?>
                            <span id="email_error1" style="color:red;"></span>
                        </div>

                        <div class="input">
                            <label for="password">Password*</label><br>
                            <?php html_pwd('password'); ?>
                            <span id="pwd_error1" style="color:red;"></span>
                            <span id="pwd_error2" style="color:red;"></span>
                        </div>
                
                        <div class="rmb-forgot">
                            <div class="remember">
                                <input type="checkbox" id="remember" name="remember"><label class="rmb" for="remember">Remember Me</label>
                            </div>

                            <div class="forgot">
                                <a href="" class="forgot">Forgot Password?</a>
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
                    <form method="post" action="../../backend/signup.php" >
                        <div class="input">
                            <label for="name">Name*</label><br>
                            <?php html_text('name'); ?>
                            <span id="name_error1" style="color:red;"></span>
                        </div>
                    
                        <div class="input">
                            <label for="email">Email*</label><br>
                            <?php html_text('email'); ?>
                            <span id="email_error1" style="color:red;"></span>
                        </div>

                        <div class="input">
                            <label for="password">Password*</label><br>
                            <?php html_pwd('password'); ?>
                            <span id="pwd_error1" style="color:red;"></span>
                            <span id="pwd_error2" style="color:red;"></span>
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
    </script>
</body>

</html>
