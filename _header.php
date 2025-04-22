<?php include_once ('_base.php'); ?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible"
        content="IE=edge">
        <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
        <!-- <title>Header</title> -->
        <link rel="stylesheet" href="\css\header.css">
        <link rel="stylesheet" href="\css\logo.css">
        <link rel="stylesheet" href="\css\style.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    </head>



    <script>
        function toggleDropdown() {
            const dropdown = document.querySelector(".profile-dropdown-list");
            dropdown.classList.toggle("active");
        }

        // Hide dropdown when clicking outside
        document.addEventListener("click", function (event) {
            const userButton = document.querySelector(".user");
            const dropdown = document.querySelector(".profile-dropdown-list");

            if (!userButton.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove("active");
            }
        });
    </script>

    <header>
        <nav>
            <div class="logo">
            <a href="/index.php"><img src="/img/logo4.png" alt="img/logo.png" class="logo"></a>
            </div>
        
            <div class="nav-bar">
            <ul>
            <?php if (($_user) && ($_user['role'] == 'admin')): ?>
                <li><a href="/pages/home.php">Home</a></li>
                <li><a href="">About us</a></li>
                <li><a href="/pages/admin/products.php">Products</a></li>
                <li><a href="">Contact</a></li>
            <?php endif ?>

            <?php if (($_user) && ($_user['role'] == 'member')): ?>
                <li><a href="/pages/home.php">Home</a></li>
                <li><a href="">About us</a></li>
                <li><a href="/pages/member/product_list.php">Products</a></li>
                <li><a href="">Contact</a></li>

            <?php else: ?>
                <li><a href="/pages/home.php">Home</a></li>
                <li><a href="">About us</a></li>
                <li><a href="/pages/member/product_list.php">Products</a></li>
                <li><a href="">Contact</a></li>
            <?php endif ?>

            </ul>
            </div>

            <div class="other">
            <button class="search"><i class='bx bx-search'></i></button>
            <a href="/pages/member/view_cart.php"><button class="cart"><i class='bx bx-shopping-bag'></i></button></a>
            <button class="user" onclick="toggleDropdown()"><i class='bx bx-user' ></i></button>
            <ul class="profile-dropdown-list">
                <?php if (($_user) && ($_user['role'] == 'member')): ?>
                    <li><a href="/pages/member/member_profile.php">My Profile</a></li>                
                    <li><a href="">Orders</a></li>
                    <li><a href="">Rewards</a></li>
                <?php endif ?>

                <?php if (($_user) && ($_user['role'] == 'admin')): ?>
                    <li><a href="/pages/admin/admin_profile.php">My Profile</a></li>                
                    <li><a href="">Members</a></li>
                    <li><a href="">Orders</a></li>
                <?php endif ?>

                <?php if($_user): ?>
                    <li><a href="/backend/logout.php">Log out</a></li>
                <?php else: ?>
                    <li><a href="/pages/signup_login.php">Sign Up/Login</a></li>
                    <li><a href="">Orders</a></li>
                    <li><a href="">Rewards</a></li>
                <?php endif ?>
            </ul>
            </div>
            
        </nav>

    </header>
            <!-- Flash message -->
             <?php include_once __DIR__ . '/_base.php'; ?>
            <div id="info"><?= temp('info') ?></div>
