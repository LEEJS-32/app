<?php
// Database configuration
$host = 'localhost';
$dbname = 'testing1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Handle file upload
        $uploadDir = 'uploads/';
        $profilePhoto = $uploadDir . basename($_FILES['profile_photo']['name']);
        $fileType = strtolower(pathinfo($profilePhoto, PATHINFO_EXTENSION));
        
        // Validate file
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileType, $allowedTypes)) {
            die("Error: Only JPG, JPEG, PNG & GIF files are allowed.");
        }
        
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $profile_photo)) {
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO members (name, email, password, profile_photo) 
                                  VALUES (:name, :email, :password, :photo)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $password,
                ':photo' => $profile_photo
            ]);
            
            echo "Registration successful!";
        } else {
            echo "File upload failed.";
        }
    }
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>