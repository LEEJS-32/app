<?php

include_once '../../_base.php';
include '../../_header.php';

// Get the product_id from the query parameter
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id <= 0) {
    die("Invalid product ID.");
}

// Fetch product details
try {
    $stm = $_db->prepare("SELECT * FROM products WHERE product_id = :product_id");
    $stm->execute([':product_id' => $product_id]);
    $product = $stm->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }

    // Decode images and video
    $image_urls = json_decode($product['image_url'], true);
    $video_url = $product['video_url'];
} catch (PDOException $e) {
    die("Error fetching product: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
    <link rel="stylesheet" href="../../css/product_details.css"> <!-- Add your CSS file -->
    <script>
        function updateBigDisplay(type, src) {
            const bigDisplay = document.getElementById('bigDisplay');

            if (type === 'image') {
                bigDisplay.innerHTML = `
                    <img src="${src}" alt="Product Image" class="big-image">
                `;
            } else if (type === 'video') {
                bigDisplay.innerHTML = `
                    <video controls autoplay class="big-image">
                        <source src="${src}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                `;
            }
        }
    </script>
</head>
<body>
    <div class="product-details-container">
        <div class="product-images">
            <!-- Big Display Area -->
            <div class="big-display" id="bigDisplay">
                <?php if (!empty($image_urls[0])): ?>
                    <img src="/<?php echo htmlspecialchars($image_urls[0]); ?>" alt="Product Image" class="big-image">
                <?php endif; ?>
            </div>

            <!-- Small Images -->
            <div class="small-images">
                <?php foreach ($image_urls as $image): ?>
                    <img src="/<?php echo htmlspecialchars($image); ?>" alt="Product Thumbnail" class="small-image" onclick="updateBigDisplay('image', this.src)">
                <?php endforeach; ?>

                <?php if (!empty($video_url)): ?>
                    <video class="small-image" poster="../../img/video_thumbnail/thumbnail.png" onclick="updateBigDisplay('video', '/<?php echo htmlspecialchars($video_url); ?>')">
                        <source src="/<?php echo htmlspecialchars($video_url); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            </div>
        </div>
   

        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="price-container">
                <?php if ($product['price'] != $product['discounted_price']) { ?>
                    <p class="original-price" style="text-decoration: line-through;">
                        RM<?php echo number_format($product['price'], 2); ?>
                    </p>
                    <p class="discounted-price">
                        RM<?php echo number_format($product['discounted_price'], 2); ?>
                    </p>
                    <p class="discount-percentage">
                        <?php
                        $discount = (($product['price'] - $product['discounted_price']) / $product['price']) * 100;
                        echo round($discount, 2) . '% Off';
                        ?>
                    </p>
                <?php } else { ?>
                    <p class="no-discount-price">
                        RM<?php echo number_format($product['price'], 2); ?>
                    </p>
                <?php } ?>
            </div>
            <p><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand']); ?></p>
            <p><strong>Color:</strong> <?php echo htmlspecialchars($product['color']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
            <p><strong>Stock:</strong> <?php echo intval($product['stock']); ?></p>

            <!-- Add to Cart -->
            <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                <?php if ($product['stock'] > 0): ?>
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo intval($product['stock']); ?>">
                    <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                <?php else: ?>
                    <button type="button" class="out-of-stock-btn" disabled>Out of Stock</button>
                <?php endif; ?>
            </form>

            
        </div>
    </div>
</body>
<footer>
        <?php
            include __DIR__ . '/../../_footer.php';
        ?>
    </footer>
</html>