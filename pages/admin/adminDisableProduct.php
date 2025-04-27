<?php
require_once '../../_base.php';

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    // Database connection using PDO
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=TESTING1', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        die("Database connection failed: " . $e->getMessage());
    }

    try {
        // Update the product status to "inactive"
        $stm = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE product_id = :product_id");
        $stm->bindParam(':product_id', $product_id, PDO::PARAM_INT);

        if ($stm->execute()) {
            echo "Product disabled successfully";
        } else {
            http_response_code(500);
            echo "Error disabling product.";
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error disabling product: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "Invalid request";
}
?>