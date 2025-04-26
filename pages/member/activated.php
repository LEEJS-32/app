<?php
include_once '../../_base.php';

// ----------------------------------------------------------------------------
// Check session/cookie
// auth_user();

// // //Check role (member/admin/both)
// auth(''); 

// ----------------------------------------------------------------------------
?>
<head>
    <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible"
        content="IE=edge">
        <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
        <title>Account Activated</title>
        <link rel="stylesheet" href="..\..\css\header.css">
        <link rel="stylesheet" href="..\..\css\logo.css">
        <link rel="stylesheet" href="..\..\css\style.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

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
</style>

<body>
<header>

</header>

<main>
<main>
    <div style="text-align: center; padding: 50px; ">
        <img src="../../img/tick.jpg" width="250px" height="250px">
        <h1>Account Activated</h1>
        <p>Enjoy your shopping experience with us!</p>
        <br>
        <p id="countdown-text">Redirecting in <span id="countdown">10</span> seconds...</p>

        <button onclick="redirectNow()" style="padding: 8px 16px; font-size: 16px;">Get Started!</button>

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
                window.location.href = "../extra_info.php"; 
            }
        </script>
    </div>
</main>



<footer>

</footer>
</body>