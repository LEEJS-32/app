<?php
require_once '../_base.php';


// Clear session data but keep session alive for temp message
session_unset();



// Remove token from database if "Remember Me" was used
if (isset($_COOKIE["remember_me"])) {
    try {
        $stm = $_db->prepare("DELETE FROM token WHERE token_id = :token");
        $stm->execute([':token' => $_COOKIE["remember_me"]]);

        // Expire the cookie
        setcookie("remember_me", "", time() - 3600, "/");
    } catch (PDOException $e) {
        error_log("Logout error: " . $e->getMessage());
    }
}


redirect("../pages/signup_login.php");


?>