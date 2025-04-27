<?php
include_once('_base.php');
auth_user();


redirect('pages/home.php');

?>
<!-- <a href="delete_database.php">Delete database</a>
<a href="pages/signup_login.php">Sign Up/Login</a>
<a href="pages/admin/admin_login.php">Admin Login</a>
<a href="pages/member/product_list.php">See Product</a>
<a href="pages/member/add_to_cart.php">Add to cart</a>
<a href="pages/member/view_cart.php">View cart</a>
<br><br>

<h1>Use sample.php as reference code!!!!!!!!!!!!!!</h1> -->

<?php 
require 'config/database.php';
include 'db/insert.php';
 ?>
