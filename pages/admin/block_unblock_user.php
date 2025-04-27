<?php
require '../../_base.php';
auth_user();
auth('admin');

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}

$user_id = $_POST['user_id'] ?? 0;
$action = $_POST['action'] ?? '';

if (!$user_id || !in_array($action, ['block', 'unblock'])) {
    die('Invalid request');
}

try {
    // Get current status
    $stm = $_db->prepare("SELECT status FROM users WHERE user_id = :user_id");
    $stm->execute([':user_id' => $user_id]);
    $user = $stm->fetch(PDO::FETCH_OBJ);

    if (!$user) {
        die('User not found');
    }

    // Update status
    $new_status = $action === 'block' ? 'blocked' : 'active';
    $stm = $_db->prepare("UPDATE users SET status = :status WHERE user_id = :user_id");
    $stm->execute([
        ':status' => $new_status,
        ':user_id' => $user_id
    ]);

    header("Location: admin_members.php");
    exit();
} catch (PDOException $e) {
    error_log("Error in block/unblock user: " . $e->getMessage());
    die('Error processing request');
}
?>

