<?php
include_once '../_base.php';

// ----------------------------------------------------------------------------
// Check session/cookie
auth_user();

//Check role (member/admin/both)
auth('admin', 'member'); 

$user = $_SESSION['user'];

// ----------------------------------------------------------------------------
?>
<head>
</head>

<body>
<header>
    <?php
        include '../_header.php';
    ?>
</header>

<main>
    <!-- Put your code here -->
    <?php if ($user['role'] == 'admin'): ?>
        <a href="admin/adminCreateProduct.php">Create Product</a>
        <a href="admin/adminProduct.php">Show Product</a>
    <?php endif ?>

    <?php if ($user['role'] == 'member'): ?>
        <!-- <a href=""></a> -->
    <?php endif ?>
</main>

<footer>
    <?php
        include '../_footer.php';
    ?>
</footer>
</body>