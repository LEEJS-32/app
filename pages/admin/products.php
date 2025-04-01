<?php

include ('../../_base.php');

// ----------------------------------------------------------------------------

//member role
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
        require '../../db/db_connect.php';
    
        // Fetch avatar from database
        $sql = "SELECT avatar FROM users WHERE user_id = '$user_id'";
        $result = $conn->query($sql);
        $imageUrl = __DIR__ . "/../../img/avatar/avatar.jpg"; // Default avatar
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
        // If avatar exists, update the image URL
            if (!empty($row["avatar"])) {
                $imageUrl = $row["avatar"];
            }
        }
    ?>
    <div class="container">
        <div class="left">
            <div class="profile">
                <img src="../../img/avatar/avatar.jpg" alt="User Avatar">
                <div class="profile-text">
                    <h3><?php echo ($name); ?></h3>
                    <p><?php echo ($role); ?></p>
                </div>
            </div>

            <ul class="nav">
                <li><a href="admin_profile.php"><i class='bx bxs-dashboard'></i>DashBoard</a></li>
                <li><a href="#"><i class='bx bxs-user-account' ></i>Members</a></li>
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
</body>
    