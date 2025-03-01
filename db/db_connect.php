<?php
$host = "localhost";  
$user = "root";       
$pwd = "";           
$dbname = "testing1"; 

// Create a connection
$conn = new mysqli($host, $user, $pwd, $dbname);

// Check if connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully<br>";
?>