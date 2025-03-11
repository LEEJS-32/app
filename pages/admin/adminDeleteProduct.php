<?php
session_start();
include '../../_header.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "TESTING1";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    }
    $stmt->bind_param("i", $product_id);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Product deleted successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request";
}
?>