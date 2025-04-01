<?php
require_once __DIR__ . 'database.php';
require_once __DIR__ . 'backend/admin/member.php';

$pdo = $pdo;
$member = new Member($pdo);

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        throw new Exception('All fields are required');
    }

    // Check if username/email exists
    $stmt = $pdo->prepare("SELECT id FROM members WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        throw new Exception('Username or email already exists');
    }

    // Handle file upload
    $photo = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $file = $_FILES['profile_photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception('Invalid file type. Allowed: ' . implode(', ', $allowed));
        }
        
        $photo = uniqid() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], __DIR__ . '/../../public/assets/uploads/' . $photo);
    }

    // Create member
    $memberId = $member->create($username, $email, $password, $photo);
    
    $response['success'] = true;
    $response['message'] = 'Registration successful!';
    $response['memberId'] = $memberId;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>