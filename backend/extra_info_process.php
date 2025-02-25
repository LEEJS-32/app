<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['name'])) {
    header("Location: /pages/signup_login.php");
    exit();
}
$email = $_SESSION['email'];
$name = $_SESSION['name'];

$servername = "localhost";
$username = "root";
$password = "";
$database = "TESTING1";

$conn = new mysqli($servername, $username, $password, $database);

$gender = $_POST['gender'] ?? null;
$phonenum = $_POST['phonenum'] ?? null;
$dob = $_POST['dob'] ?? null;
$occupation = $_POST['occupation'] ?? null;
$address1 = $_POST['address-line1'] ?? null;
$address2 = $_POST['address-line2'] ?? null;
$city = $_POST['city'] ?? null;
$country = $_POST['country'] ?? null;
$postcode = $_POST['postcode'] ?? null;
$preference = $_POST['preference'] ?? null;

$stmt = $conn->prepare("UPDATE users SET gender=?, phonenum=?, preference=?, dob=?, occupation=? WHERE email=?");
$stmt->bind_param("ssssss",  $gender, $phonenum, $preference, $dob, $occupation, $email);

// Execute SQL query
if ($stmt->execute()) {
    echo "Data submitted successfully!";
    // header("Location: success_page.php"); // Redirect to a success page
} else {
    echo "Error: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();
?>