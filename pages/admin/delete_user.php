<?php

require '../../_base.php';

// Verify if user is an admin
auth_user();
auth('admin');

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}

$user_id = $_POST['user_id'] ?? 0;

if (!$user_id) {
    die('Invalid request');
}

try {
    // Check if user exists
    $stm = $_db->prepare("SELECT user_id, role FROM users WHERE user_id = :user_id");
    $stm->execute([':user_id' => $user_id]);
    $user = $stm->fetch(PDO::FETCH_OBJ);

    if (!$user) {
        die('User not found');
    }

    // Prevent deleting admin users
    if ($user->role === 'admin') {
        die('Cannot delete admin users');
    }

    // Start transaction
    $_db->beginTransaction();

    try {
        // Delete user's address
        $stm = $_db->prepare("DELETE FROM address WHERE user_id = :user_id");
        $stm->execute([':user_id' => $user_id]);

        // Delete user's cart items
        $stm = $_db->prepare("DELETE FROM shopping_cart WHERE user_id = :user_id");
        $stm->execute([':user_id' => $user_id]);

        // Delete user's orders
        $stm = $_db->prepare("DELETE FROM orders WHERE user_id = :user_id");
        $stm->execute([':user_id' => $user_id]);

        // Delete user's vouchers
        $stm = $_db->prepare("DELETE FROM vouchers WHERE user_id = :user_id");
        $stm->execute([':user_id' => $user_id]);

        // Finally, delete the user
        $stm = $_db->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stm->execute([':user_id' => $user_id]);


        $_db->commit();
        header("Location: admin_members.php");
        exit();
    } catch (PDOException $e) {
        $_db->rollBack();
        throw $e;
    }
} catch (PDOException $e) {
    error_log("Error in delete user: " . $e->getMessage());
    die('Error processing request');
}
