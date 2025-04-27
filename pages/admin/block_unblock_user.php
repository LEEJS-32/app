<?php
include ('../../_base.php');

// Verify admin privileges
auth_user();
auth('admin');

// CSRF protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Invalid CSRF token';
    header('Location: admin_members.php');
    exit;
}

// Prevent self-blocking
if ($_POST['user_id'] == $_SESSION['user']['user_id']) {
    $_SESSION['error'] = 'You cannot block your own account';
    header('Location: admin_members.php');
    exit;
}

// Validate action
$valid_actions = ['block', 'unblock'];
if (!in_array($_POST['action'], $valid_actions)) {
    $_SESSION['error'] = 'Invalid action';
    header('Location: admin_members.php');
    exit;
}

// Update user status
require '../../db/db_connect.php';

$new_status = $_POST['action'] === 'block' ? 'blocked' : 'active';
$user_id = $_POST['user_id'];

$stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
$stmt->bind_param('ss', $new_status, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['success'] = "User account successfully " . ($new_status === 'blocked' ? 'blocked' : 'activated');
} else {
    $_SESSION['error'] = 'No changes made. User may not exist.';
}

$stmt->close();
$conn->close();

header('Location: admin_members.php');
exit;