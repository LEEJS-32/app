<?php session_start(); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible"
        content="IE=edge">
        <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
        <title>Testing3</title>
        <script src="../js/validation.js" defer></script>

    </head>

    <body>
        <?php
        if (isset($_SESSION['error_exist'])) {
            echo "<p style='color:red;'>".$_SESSION['error_exist']."</p>";
            unset($_SESSION['error_exist']); 
        }
        ?>

        <main>
            <h1>Sign Up</h1>
            <form method="post" action="../backend/signup.php" onsubmit="return validateForm()">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name">
                <span id="name_error1" style="color:red;"></span>
                <br><br>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email">
                <span id="email_error1" style="color:red;"></span>
                <br><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
                <span id="pwd_error1" style="color:red;"></span>
                <span id="pwd_error2" style="color:red;"></span>
                <br><br>
                <input type="checkbox" id="remember" name="remember"><label for="remember">Remember Me</label>
                <a href="" class="forgot">Forgot Password?</a>
                <br><br>
                <button type="submit">Submit</button>
                <button type="reset">Cancel</button>
            </form>
            <br><br><br>

            <?php
            if (isset($_SESSION['error_not_exist'])) {
                echo "<p style='color:red;'>".$_SESSION['error_not_exist']."</p>";
                unset($_SESSION['error_not_exist']); 
            }
            
            if (isset($_SESSION['error_pwd'])) {
                echo "<p style='color:red;'>".$_SESSION['error_pwd']."</p>";
                unset($_SESSION['error_pwd']); 
            }
            ?>

            <h1>Login</h1>
            <form method="post" action="../backend/login.php">
            <label for="email">Email:</label>
                <input type="email" id="email" name="email">
                <span id="email_error1" style="color:red;"></span>
                <br><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
                <span id="pwd_error1" style="color:red;"></span>
                <span id="pwd_error2" style="color:red;"></span>
                <br><br>
                <button type="submit">Submit</button>
                <button type="reset">Cancel</button>
            </form>
        </main>
        <footer></footer>
    </body>

</html>