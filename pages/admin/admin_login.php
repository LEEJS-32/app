<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testing3</title>
    <script src="../../js/validation.js">
    </script>
</head>
<?php 
    session_start();
    $_SESSION['role'] = "admin"; 
?>
<body>
    <main>
        <h1>Admin Login</h1>
        <form method="post" action="../../backend/login.php" onsubmit="return validateForm()">
            <?php include '../../_base.php' ?>
            
            <label for="email">Email:</label>
            <?php html_text('email'); ?>
            <span id="email_error1" style="color:red;"></span>
            <br><br>

            <label for="password">Password</label>
            <?php html_pwd('password'); ?>
            <span id="pwd_error1" style="color:red;"></span>
            <span id="pwd_error2" style="color:red;"></span>
            <br><br>

            <button type="submit">Submit</button>
            <button type="reset">Cancel</button>
        </form>
    </main>
</body>
</html>
