<?php
    session_start();
    $email = $_SESSION['email'];
    include '../db/db_connect.php';

    // Get the JSON data
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->image)) {
        $imageData = $data->image;

        // Remove 'data:image/png;base64,' from string
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = base64_decode($imageData);

        // Generate unique filename
        $fileName_save = "../img/avatar/" . uniqid() . ".png";
        $fileName = "../".$fileName_save;

        // Save file
        file_put_contents($fileName_save, $imageData);

        // Save to database
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE email = ?");
        $stmt->bind_param("ss", $fileName, $email);
        
        if ($stmt->execute()) {
            echo "Image saved successfully!";
        } else {
            echo "Error saving image.";
        }
    } else {
        echo "No image received.";
    }

    $conn->close();
?>