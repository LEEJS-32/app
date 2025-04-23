<?php
require '../../db/db_connect.php';
include '../../_header.php';

// Get the product_id from the query parameter
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id <= 0) {
    die("Invalid product ID.");
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// Decode images and video
$image_urls = json_decode($product['image_url'], true);
$video_url = $product['video_url'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
    <link rel="stylesheet" href="../../css/product_details.css"> <!-- Add your CSS file -->
</head>
<body>
    <div class="product-details-container">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>

        <!-- Product Images -->
        <div class="product-images">
            <?php
            if (is_array($image_urls) && !empty($image_urls)) {
                foreach ($image_urls as $image) {
                    echo "<img src='/" . htmlspecialchars($image) . "' alt='Product Image' class='product-image'>";
                }
            } else {
                echo "<img src='/img/default-product.jpg' alt='Default Product Image' class='product-image'>";
            }
            ?>
        </div>

        <!-- Product Video -->
        <?php if (!empty($video_url)) { ?>
            <div class="product-video">
                <video controls>
                    <source src="/<?php echo htmlspecialchars($video_url); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        <?php } ?>

        <!-- Product Details -->
        <div class="product-info">
            <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
            <p><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand']); ?></p>
            <p><strong>Color:</strong> <?php echo htmlspecialchars($product['color']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
            <p><strong>Stock:</strong> <?php echo intval($product['stock']); ?></p>
        </div>

        <!-- Add to Cart -->
        <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
            <?php if ($product['stock'] > 0) { ?>
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo intval($product['stock']); ?>">
                <button type="submit" class="add-to-cart-btn">Add to Cart</button>
            <?php } else { ?>
                <button type="button" class="out-of-stock-btn" disabled>Out of Stock</button>
            <?php } ?>
        </form>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>