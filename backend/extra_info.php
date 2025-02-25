<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['name'])) {
    header("Location: /pages/signup_login.php");
    exit();
}
$email = $_SESSION['email'];
$name = $_SESSION['name'];


?>