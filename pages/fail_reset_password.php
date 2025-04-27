<?php
include_once '../_base.php';

// Get error message based on error parameter
$error = $_GET['error'] ?? 'unknown';
$error_messages = [
    'password_mismatch' => 'The passwords you entered do not match.',
    'update_failed' => 'Failed to update your password. Please try again.',
    'database_error' => 'A database error occurred. Please try again later.',
    'session_expired' => 'Your session has expired. Please start the password reset process again.',
    'unknown' => 'An unknown error occurred. Please try again.'
];

$error_message = $error_messages[$error] ?? $error_messages['unknown'];
?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Failed</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/logo.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<div id="info"><?= temp('info') ?></div>
<style>
    main {
        background-color: white;
    }

    h1 {
        font-size: 50px;
    }

    p {
        font-size: 17px;
    }

    button {
        margin-top: 15px;
        padding: 25px 10px;
        border-radius: 10px;
        background-color: black;
        color: white;
        transition: background-color 0.2s;
    }

    button:hover {
        background-color: white;
        color: black;
    }

    .error-message {
        color: #c62828;
        margin: 20px 0;
        padding: 10px;
        background-color: #ffebee;
        border-radius: 5px;
    }
</style>

<body>
<header>
    <?php include '../../_header.php'; ?>
</header>

<main>
    <div style="text-align: center; padding: 50px;">
        <img src="../../img/error.jpg" width="250px" height="250px">
        <h1>Password Reset Failed</h1>
        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <br>
        <p id="countdown-text">Redirecting to forgot password in <span id="countdown">10</span> seconds...</p>

        <button onclick="redirectNow()" style="padding: 8px 16px; font-size: 16px;">Try Again</button>

        <script>
            let seconds = 10;
            const countdownEl = document.getElementById("countdown");
            const countdownText = document.getElementById("countdown-text");

            const interval = setInterval(() => {
                seconds--;
                countdownEl.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    redirectNow();
                }
            }, 1000);

            function redirectNow() {
                window.location.href = "forgot_password.php"; 
            }
        </script>
    </div>
</main>

<footer>
    <?php include '../../_footer.php'; ?>
</footer>
</body> 