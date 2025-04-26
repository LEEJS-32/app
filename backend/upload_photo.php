<?php
require_once '../_base.php';

auth_user();
auth();

$user = $_SESSION['user'];
$user_id = $user->user_id;

if (isset($_POST['image'])) {
    $imageData = post('image');

    // Remove 'data:image/png;base64,' from string
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData);

    // Generate unique filename
    $filePath = "../img/avatar/" . uniqid() . ".png";
    $fileName = "../" . $filePath;

    try {
        // Save file
        file_put_contents($filePath, $imageData);

        // Save to database
        $stm = $_db->prepare("UPDATE users SET avatar = :avatar WHERE user_id = :user_id");
        $stm->execute([
            ':avatar' => $fileName,
            ':user_id' => $user_id
        ]);

        // Update session
        $_SESSION['user']->avatar = $fileName;

        // Redirect after successful upload
        echo "OK";
    } catch (PDOException $e) {
        error_log("Upload photo error: " . $e->getMessage());
        echo "Error saving image to database.";
    }
} else {
    echo "No image received.";
}
?>
