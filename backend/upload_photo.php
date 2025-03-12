<?php
session_start();
$email = $_SESSION['email'];
include '../db/db_connect.php';
include '../_base.php';

if (isset($_POST['image'])) {
    $imageData = post('image');

    // Remove 'data:image/png;base64,' from string
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData);

    // Generate unique filename
    $filePath = "../img/avatar/" . uniqid() . ".png";
    $fileName = "../" . $filePath;

    // Save file
    file_put_contents($filePath, $imageData);

    // Save to database
    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE email = ?");
    $stmt->bind_param("ss", $fileName, $email);

    if ($stmt->execute()) {
        // Redirect after successful upload
        echo "OK";
    } else {
        echo "Error saving image to database.";
    }
} else {
    echo "No image received.";
}

$conn->close();
?>
