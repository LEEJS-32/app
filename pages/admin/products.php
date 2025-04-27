<?php

include ('../../_base.php');

// ----------------------------------------------------------------------------

// Member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user['user_id'];
$name = $user['name'];
$role = $user['role'];

// ----------------------------------------------------------------------------
?>
</script>
<head>
    <title>Product</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="../../css/nav.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script defer src="../../js/webcam.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <header>
        <?php 
            include __DIR__ . '/../../_header.php'; 
        ?>
    </header>

    <main>
    <?php
        // Fetch avatar from database
        $sql = "SELECT avatar FROM users WHERE user_id = :user_id";
        $stmt = $_db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $imageUrl = __DIR__ . "/../../img/avatar/avatar.jpg"; // Default avatar
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If avatar exists, update the image URL
            if (!empty($row["avatar"])) {
                $imageUrl = $row["avatar"];
            }
        }
    ?>
    <div class="container">
        <div class="left">
            <button id="navToggle" class="nav-toggle">
                <i class='bx bx-menu'></i>
            </button>
            <div class="profile">
                <img src="../../img/avatar/avatar.jpg" alt="User Avatar">
                <div class="profile-text">
                    <h3><?php echo htmlspecialchars($name); ?></h3>
                    <p><?php echo htmlspecialchars($role); ?></p>
                </div>
            </div>

            <ul class="nav">
                <li><a href="admin_profile.php"><i class='bx bxs-dashboard'></i>DashBoard</a></li>
                <li><a href="admin_members.php"><i class='bx bxs-user-account'></i>Members</a></li>
                <li><a href="products.php" class="active"><i class='bx bx-chair'></i>Products</a></li>
                <li><a href="#"><i class='bx bx-food-menu'></i>Orders</a></li>
            </ul>
        </div>

        <div class="divider"></div>

        <div class="right">
            <h1>Product Management</h1>

            <a href="adminCreateProduct.php">Create Product</a>
            <a href="adminProduct.php">Show Product</a>
            <a href="adminUpdateProduct.php">Update Product</a>
        </div>
    </div>

    </main>
    <footer>
        <?php
            include __DIR__ . '/../../_footer.php';
        ?>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navToggle = document.getElementById('navToggle');
            const leftContainer = document.querySelector('.left');

            navToggle.addEventListener('click', function () {
                leftContainer.classList.toggle('collapsed');
            });
        });
    </script>
</body>