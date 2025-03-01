<?php include '_header.php';?>

<a href="delete_database.php">Delete database</a>
<a href="pages/signup_login.php">Sign Up/Login</a>
<a href="pages/admin/admin_login.php">Admin Login</a>
<a href="pages/member/add_to_cart.php">Add to cart</a>
<a href="pages/member/view_cart.php">View cart</a>
<br><br>
<?php 
require 'database.php';
include 'db/insert.php';
 ?>
