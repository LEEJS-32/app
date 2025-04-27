<?php

require '../_base.php';
auth_user();

if (!isset($_SESSION['otp_email'])) {
    header("Location: forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Furniture.os</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/logo.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/password_reset.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="password-reset-container">
        <h2>Reset Password</h2>
        <form method="post" action="../backend/forgot_reset_password.php" class="password-reset-form">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" required minlength="8" placeholder="Enter new password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required minlength="8" placeholder="Confirm new password">
            </div>
            <button type="submit">Reset Password</button>
        </form>
    </div>

    <script>
        // Password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
