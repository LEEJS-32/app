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

// Fetch existing address data
try {
    $stm = $_db->prepare("SELECT * FROM address WHERE user_id = :user_id");
    $stm->execute([':user_id' => $user_id]);
    $address = $stm->fetch(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    error_log("Error fetching address: " . $e->getMessage());
    $address = null;
}
?>
</script>
<head>
    <title>Member Address</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="../../css/member/member_address.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
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

            <div class="info-card">
                <div class="info-header">
                    <h3>Address Information</h3>
                    <button type="button" class="edit-btn" id="toggleEdit">✎ Edit</button>
                </div>

                <div id="readonly-view">
                    <div class="info-item">
                        <label>Address Line 1</label>
                        <p><?= htmlspecialchars($address->address_line1 ?? 'Not set') ?></p>
                    </div>
                    <div class="info-item">
                        <label>Address Line 2</label>
                        <p><?= htmlspecialchars($address->address_line2 ?? 'Not set') ?></p>
                    </div>
                    <div class="info-item">
                        <label>City</label>
                        <p><?= htmlspecialchars($address->city ?? 'Not set') ?></p>
                    </div>
                    <div class="info-item">
                        <label>Country</label>
                        <p><?= htmlspecialchars($address->country ?? 'Not set') ?></p>
                    </div>
                    <div class="info-item">
                        <label>Post Code</label>
                        <p><?= htmlspecialchars($address->postal_code ?? 'Not set') ?></p>
                    </div>
                </div>

                <!-- Hidden editable form -->
                <form method="post" id="editForm" action="../../backend/edit_address.php" style="display:none;">
                    <?php if ($address): ?>
                        <input type="hidden" name="address_id" value="<?= htmlspecialchars($address->address_id) ?>">
                    <?php endif; ?>
                    
                    <div class="info-item">
                        <label for="address-line1">Address Line 1</label>
                        <input type="text" id="address-line1" name="address-line1" value="<?= htmlspecialchars($address->address_line1 ?? '') ?>" required>
                        <span id="address1_error" style="color:red;"></span>
                    </div>
                    <div class="info-item">
                        <label for="address-line2">Address Line 2</label>
                        <input type="text" id="address-line2" name="address-line2" value="<?= htmlspecialchars($address->address_line2 ?? '') ?>">
                    </div>
                    <div class="info-item">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($address->city ?? '') ?>" required>
                        <span id="city_error" style="color:red;"></span>
                    </div>
                    <div class="info-item">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" value="<?= htmlspecialchars($address->country ?? '') ?>" required>
                        <span id="country_error" style="color:red;"></span>
                    </div>
                    <div class="info-item">
                        <label for="postcode">Post Code</label>
                        <input type="number" id="postcode" name="postcode" value="<?= htmlspecialchars($address->postal_code ?? '') ?>" min="0" max="99999" required>
                        <span id="postcode_error" style="color:red;"></span>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="save-btn"><?= $address ? 'Update' : 'Add' ?> Address</button>
                        <button type="button" class="cancel-btn" onclick="document.getElementById('editForm').style.display='none'; document.getElementById('readonly-view').style.display='block';">Cancel</button>
                    </div>
                </form>
            </div>
        </div>  
    </div>

    <script>
        document.getElementById('toggleEdit').addEventListener('click', function() {
            const readonlyView = document.getElementById('readonly-view');
            const editForm = document.getElementById('editForm');
            
            if (readonlyView.style.display === 'none') {
                readonlyView.style.display = 'block';
                editForm.style.display = 'none';
            } else {
                readonlyView.style.display = 'none';
                editForm.style.display = 'block';
            }
        });
    </script>

    </main>

    <footer>
        <?php
            include __DIR__ . '/../../_footer.php';
        ?>
    </footer>
</body>
    