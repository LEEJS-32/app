<?php
require_once '../../_base.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    try {
        $stm = $_db->prepare("DELETE FROM products WHERE product_id = :product_id");
        $stm->execute([':product_id' => $product_id]);
        
        if ($stm->rowCount() > 0) {
            echo "Product deleted successfully";
        } else {
            echo "No product found with the given ID";
        }
    } catch (PDOException $e) {
        die("Error deleting product: " . $e->getMessage());
    }
} else {
    echo "Invalid request";
}
?>