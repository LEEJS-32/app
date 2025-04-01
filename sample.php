<?php
include_once '_base.php';

// ----------------------------------------------------------------------------
// Check session/cookie
auth_user();

//Check role (member/admin/both)
auth(''); 

// ----------------------------------------------------------------------------
?>
<head>
</head>

<body>
<header>
    <?php
        include '_header.php';
    ?>
</header>

<main>
    <!-- Put your code here -->
</main>

<footer>
    <?php
        include '_footer.php';
    ?>
</footer>
</body>