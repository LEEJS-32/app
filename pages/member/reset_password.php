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
    <title>Password Reset</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="../../css/member/member.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script defer src="../../js/webcam.js"></script>
    <style>
        .g-recaptcha {
            display: flex;
            justify-content: center;
        }
    </style>
</head>

<div id="info"><?= temp('info')?></div>

<body>
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
            // Log error and continue with default avatar
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
                <li><a href="reset_password.php" class="active"><i class='bx bx-lock-alt' ></i>Password </a></li>
                <li><a href="member_address.php"><i class='bx bx-home-alt-2' ></i>My Address</a></li>
                <li><a href="view_cart.php"><i class='bx bx-shopping-bag' ></i>Shopping Cart</a></li>
                <li><a href="#"><i class='bx bx-heart' ></i>Wishlist</a></li>
                <li><a href="order_history.php"><i class='bx bx-food-menu'></i>My Orders</a></li>
            </ul>
        </div>

        <div class="divider"></div>

        <div class="right">
            <h1>Password Reset</h1>

            <!-- Reset password -->
            <form method="post" action="../../backend/reset_password.php" onsubmit="return validateResetPassword()">
                <label for="old-password">Old Password</label>
                <br><?php html_password('old-password'); ?><br>
                <span id="old_pwd_error" style="color:red;"></span><br>
                <label for="new-password">New Password</label>
                <br><?php html_password('new-password'); ?><br>
                <span id="new_pwd_error" style="color:red;"></span><br>
                <label for="confirm-password">Confirm New Password</label>
                <br><?php html_password('confirm-password'); ?><br>
                <span id="confirm_pwd_error" style="color:red;"></span><br>
                <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                <br><br>
                <button type="submit" id="confirm-btn">Confirm</button>
            </form>
        </div>  
    </div>

    <!-- Load reCAPTCHA  -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script>
        function validateResetPassword() {
            let oldPassword = document.getElementById('old-password').value;
            let newPassword = document.getElementById('new-password').value;
            let confirmPassword = document.getElementById('confirm-password').value;
            let oldPwdError = document.getElementById('old_pwd_error');
            let newPwdError = document.getElementById('new_pwd_error');
            let confirmPwdError = document.getElementById('confirm_pwd_error');
            let isValid = true;

            // Reset error messages
            oldPwdError.textContent = '';
            newPwdError.textContent = '';
            confirmPwdError.textContent = '';

            // Old password validation
            if (oldPassword.trim() === '') {
                oldPwdError.textContent = 'Old password is required';
                isValid = false;
            }

            // New password validation
            if (newPassword.trim() === '') {
                newPwdError.textContent = 'New password is required';
                isValid = false;
            } else if (newPassword.length < 8) {
                newPwdError.textContent = 'Password must be at least 8 characters long';
                isValid = false;
            } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(newPassword)) {
                newPwdError.textContent = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character';
                isValid = false;
            } else if (newPassword === oldPassword) {
                newPwdError.textContent = 'New password cannot be the same as old password';
                isValid = false;
            }

            // Confirm password validation
            if (confirmPassword.trim() === '') {
                confirmPwdError.textContent = 'Please confirm your new password';
                isValid = false;
            } else if (newPassword !== confirmPassword) {
                confirmPwdError.textContent = 'Passwords do not match';
                isValid = false;
            }

            return isValid;
        }
    </script>

    </main>

    <footer>
        <?php
            include __DIR__ . '/../../_footer.php';
        ?>
    </footer>
</body>
    