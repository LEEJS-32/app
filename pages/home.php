<?php
require '../_base.php';
auth_user();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible"
        content="IE=edge">
        <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
        <title>Home</title>
        <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="../css/logo.css">
        <link rel="stylesheet" href="../css/footer.css">
        <link rel="stylesheet" href="../css/home.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    </head>

    <body>
        <?php include '../_header.php'; ?>
        
        <main class="main">
        <div class="intro">
            <div class="intro-left">
                <h3>#The Best Online Furniture Store</h3>
                
                <div class='intro-big-content'>
                <h2><pre>Your Home, Your Style</pre></h2>

                <h1><pre>Our Passion</pre></h1>
                </div>
                
                <p class="intro-des">Crafting stylish and comfortable furniture with passion, making your house feel like home.</p>
                <div class="shop-now">
                    <button class="shop-now-btn">Shop Now&nbsp;&nbsp;<i class='bx bx-right-arrow-alt'></i></button>
                    <a href="" class="view-cat-link">View All Categories</a>
                </div>
            </div>

            <div class="intro-right">
                <img src="../img/intro.jpeg" class="img-intro1">
                <img src="../img/intro_img_2.jpeg" class="img-intro2">
                
            </div>
            
            <div class="intro-right-bottom">
                <div class="slot">
                    <div class="tag">
                        <h4># Mid-Year Sales</h4>
                        <p>June 2025<p>
                    </div>
                    <div class="icon-right"><i class='bx bx-chevron-right'></i></div>
                </div>
                <hr>
                <div class="slot">
                    <div class="tag">
                        <h4># New Arrivals</h4>
                        <p>July 2025<p>
                    </div>
                    <div class="icon-right"><i class='bx bx-chevron-right'></i></div>
                </div>
                <hr>
                <div class="slot">
                    <div class="tag">
                        <h4># Mothers' Day Offers</h4>
                        <p>August 2025<p>
                    </div>
                    <div class="icon-right"><i class='bx bx-chevron-right'></i></div>
                </div>
            </div>
        </main>

        <?php include '../_footer.php'; ?>
    </body>

</html>