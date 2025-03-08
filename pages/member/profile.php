<?php
session_start();
require '../../backend/auth_check.php';
$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$role = $_SESSION['role'];
$email = $_SESSION['email'];

echo ($user_id);

echo "Welcome, user! <a href='../../backend/logout.php'>Logout</a>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webcam Avatar</title>
    <link rel="stylesheet" href="../../css/style.css">
    <script defer src="../../js/webcam.js"></script>
    <style>
        .avatar-container {
            width: 150px;
            height: 150px;
            border-radius: 15px;
            overflow: hidden;
            border: 2px solid #ccc;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .avatar-container img, 
        .avatar-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        video {
            display: none;
        }
    </style>
</head>
<body>

    <h2>Avatar Capture</h2>
    
    <div class="avatar-container">
        <img id="avatar" src="../../img/default-avatar.png" alt="Default Avatar">
        <video id="video" autoplay></video>
    </div>

    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>

    <br>
    <button id="openCamera">Open Camera</button>
    <button id="capture" style="display: none;">Capture</button>
    <form id="uploadForm" action="../../backend/upload_photo.php" method="POST">
        <input type="hidden" name="image" id="imageData">
        <button type="submit" id="upload" style="display: none;">Upload</button>
    </form>

</body>
</html>
