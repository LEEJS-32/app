<?php
require_once '../../_base.php';

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "TESTING1";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update the product status to "inactive"
    $stmt = $conn->prepare("UPDATE products SET status = 'inactive' WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo "Product disabled successfully";
    } else {
        http_response_code(500);
        echo "Error disabling product: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo "Invalid request";
}
?>