<?php

include ('../../_base.php');

// ----------------------------------------------------------------------------

//member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;

try {
    // Fetch avatar from database
    $stm = $_db->prepare("SELECT avatar FROM users WHERE user_id = :user_id");
    $stm->execute([':user_id' => $user_id]);
    $row = $stm->fetch(PDO::FETCH_OBJ);
    
    $imageUrl = __DIR__ . "/../../img/avatar/avatar.jpg"; // Default avatar
    
    if ($row && !empty($row->avatar)) {
        $imageUrl = $row->avatar;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
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
    <div class="container">
        <div class="left">
            <div class="profile">
                <img src="../../img/avatar/avatar.jpg" alt="User Avatar">
                <div class="profile-text">
                    <h3><?php echo htmlspecialchars($name); ?></h3>
                    <p><?php echo htmlspecialchars($role); ?></p>
                </div>
            </div>

            <ul class="nav">
                <li><a href="admin_profile.php"><i class='bx bxs-dashboard'></i>DashBoard</a></li>
                <li><a href="admin_members.php"><i class='bx bxs-user-account' ></i>Members</a></li>
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
    