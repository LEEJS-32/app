<?php

include ('../../_base.php');

// ----------------------------------------------------------------------------

//member role
auth_user();
auth('member');

$new_user = false;
$_SESSION["new_user"] = $new_user;

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;
$_genders = ['male' => 'Male', 'female' => 'Female'];
// ----------------------------------------------------------------------------
?>
</script>
<head>
    <title>Member Profile</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="../../css/member/member.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script defer src="../../js/webcam.js"></script>
</head>

<body>
<div id="info"><?= temp('info') ?></div>
    <header>
        <?php 
            include __DIR__ . '/../../_header.php'; 
        ?>
    </header>

    <main>
    <?php
        try {
            // Fetch avatar from database
            $stm = $_db->prepare("SELECT * FROM users WHERE user_id = :user_id");
            $stm->execute([':user_id' => $user_id]);
            $row = $stm->fetch(PDO::FETCH_OBJ);
            
            $imageUrl = __DIR__ . "/../../img/avatar/avatar.jpg"; // Default avatar
            
            if ($row) {
                $email = $row->email;
                $name = $row->name;
                
                // If avatar exists, update the image URL
                if (!empty($row->avatar)) {
                    $imageUrl = $row->avatar;
                }
            }
        } catch (PDOException $e) {
            error_log("Error fetching avatar: " . $e->getMessage());
        }
    ?>
    
    <div class="container">
        <div class="left">
            <div class="profile">
                <img src="../../img/avatar/<?= htmlspecialchars($imageUrl) ?>" alt="Profile" class="profile-avatar" />
                <div class="profile-text">
                    <h3><?php echo htmlspecialchars($name); ?></h3>
                    <p><?php echo htmlspecialchars($role); ?></p>
                </div>
            </div>

            <ul class="nav">
                <li><a href="member_profile.php"><i class='bx bxs-user-detail' ></i>My Profile</a></li>
                <li><a href="reset_password.php"><i class='bx bx-lock-alt' ></i>Password </a></li>
                <li><a href="member_address.php"  class="active"><i class='bx bx-home-alt-2' ></i>My Address</a></li>
                <li><a href="view_cart.php"><i class='bx bx-shopping-bag' ></i>Shopping Cart</a></li>
                <li><a href="#"><i class='bx bx-heart' ></i>Wishlist</a></li>
                <li><a href="order_history.php"><i class='bx bx-food-menu'></i>My Orders</a></li>
            </ul>
        </div>

        <div class="divider"></div>

        <div class="right">
            <h1>Address Management</h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message" style="color: red; margin-bottom: 15px;">
                    <?php 
                        echo htmlspecialchars($_SESSION['error']); 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message" style="color: green; margin-bottom: 15px;">
                    <?php 
                        echo htmlspecialchars($_SESSION['success']); 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Address -->
            <form method="post" action="../../backend/extra_info_process.php">
                <label for="address-line1">Address Line 1</label>
                <br><input type="text" id="address-line1" name="address-line1" required>
                <br><br>
                <label for="address-line2">Address Line 2</label>
                <br><input type="text" id="address-line2" name="address-line2">
                <br><br>
                <label for="city">City</label>
                <br><input type="text" id="city" name="city" required>
                <br><br>
                <label for="country">Country</label>
                <br><input type="text" id="country" name="country" required>
                <br><br>
                <label for="postcode">Post Code</label>
                <br><input type="number" id="postcode" name="postcode" min="0" max="99999" required>
                <br><br>
                <button type="submit">Confirm</button>
                <button type="reset">Cancel</button>
            </form>
        </div>  
    </div>

    <!-- Load reCAPTCHA  -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    </main>

    <footer>
        <?php
            include __DIR__ . '/../../_footer.php';
        ?>
    </footer>
</body>
    