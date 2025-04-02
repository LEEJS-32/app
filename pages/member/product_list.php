<?php 
require '../../db/db_connect.php';
include '../../_header.php'
// Check if the user is logged in
$user = $_SESSION['user'];
$user_id = $user['user_id'];
$user_name = $user['name'];

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user ? $user['user_id'] : 'Guest';
$user_name = $user ? $user['name'] : 'Guest';


// Fetch filter values from GET parameters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : "";
$price_filter = isset($_GET['price']) ? intval($_GET['price']) : 1000;
$brand_filter = isset($_GET['brand']) ? trim($_GET['brand']) : "";
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : "";

// Build the SQL query with filters
$sql = "SELECT * FROM products WHERE status = 'active'";

if (!empty($search_query)) {
    $sql .= " AND name LIKE '%" . $conn->real_escape_string($search_query) . "%'";
}

if (!empty($brand_filter)) {
    $sql .= " AND brand = '" . $conn->real_escape_string($brand_filter) . "'";
}

if (!empty($category_filter)) {
    $sql .= " AND category = '" . $conn->real_escape_string($category_filter) . "'";
}

if (!empty($price_filter)) {
    $sql .= " AND price <= " . $conn->real_escape_string($price_filter);
}

$result = $conn->query($sql);

if (!$result) {
    die("Error fetching products: " . $conn->error);
}

// Get alert message from session if exists
$alertMessage = isset($_SESSION['cart_message']) ? $_SESSION['cart_message'] : "";
unset($_SESSION['cart_message']); // Remove message after displaying it
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="../../css/product.css"> <!-- External CSS -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Handle price slider
            $('#priceRange').on('input', function () {
                $('#priceValue').text('$' + $(this).val());
            });

            // Handle filter changes
            $('.filter-form').on('change', 'input, select', function () {
                const formData = $('.filter-form').serialize();
                $.ajax({
                    url: 'product_list.php',
                    type: 'GET',
                    data: formData,
                    success: function (response) {
                        $('.product-container').html($(response).find('.product-container').html());
                    },
                    error: function () {
                        alert('Error applying filters.');
                    }
                });
            });
        });
    </script>
</head>
<body>

    <!-- Success Message -->
    <?php if (!empty($alertMessage)) { ?>
        <div id="successMessage" class="success-message"><?php echo htmlspecialchars($alertMessage); ?></div>
    <?php } ?>

    <div class="header">
        <h2>Product List</h2>
        <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
        <p><strong>User Name:</strong> <?php echo htmlspecialchars($user_name); ?></p>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
        <form method="GET" action="product_list.php">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>
    </div>


    <div class="main-container">
        <!-- Filter Section -->
        <div class="filter-section">
            <form class="filter-form">
                <h3>Filters</h3>

                <!-- Price Filter -->
                <div class="filter-group">
                    <label for="priceRange">Price:</label>
                    <input type="range" id="priceRange" name="price" min="0" max="1000" step="10" value="1000">
                    <span id="priceValue">$1000</span>

    <!-- View Cart Button -->
    <div class="view-cart-container">
        <a href="view_cart.php">
            <button class="view-cart-btn">View Cart</button>
        </a>
    </div>

    <div class="product-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $stock = intval($row['stock']);
                ?>
                <div class="product-card">
                    <?php
                    $image_urls = json_decode($row['image_url'], true); // Decode the JSON-encoded image URLs
                    if (is_array($image_urls) && !empty($image_urls)) {
                        // Use the first image in the array as the product image
                        $image_path = $image_urls[0];
                        echo "<img class='product-image' src='/" . htmlspecialchars($image_path) . "' alt='" . htmlspecialchars($row['name']) . "'>";
                    } else {
                        // Fallback image if no image is available
                        echo "<img class='product-image' src='/img/default-product.jpg' alt='Default Product Image'>";
                    }
                    ?>
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p>Price: $<?php echo number_format($row['price'], 2); ?></p>
                    <p>Brand: <?php echo htmlspecialchars($row['brand']); ?></p>
                    <p>Color: <?php echo htmlspecialchars($row['color']); ?></p>
                    <p><strong>Stock: <?php echo $stock; ?></strong></p>

                    <!-- Only show Add to Cart button if stock is available -->
                    <form action="add_to_cart.php" method="POST" class="product-form">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id']); ?>">
                        <?php if ($stock > 0) { ?>
                            <label>Quantity: <input type="number" name="quantity" value="1" min="1" max="<?php echo $stock; ?>"></label>
                            <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                        <?php } else { ?>
                            <button type="button" class="out-of-stock-btn" disabled>Out of Stock</button>
                        <?php } ?>
                    </form>

                </div>

                <!-- Brand Filter -->
                <div class="filter-group">
                    <label for="brandFilter">Brand:</label>
                    <select id="brandFilter" name="brand">
                        <option value="">All Brands</option>
                        <option value="Brand A">Brand A</option>
                        <option value="Brand B">Brand B</option>
                        <option value="Brand C">Brand C</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="filter-group">
                    <label for="categoryFilter">Category:</label>
                    <select id="categoryFilter" name="category">
                        <option value="">All Categories</option>
                        <option value="Sofas & armchairs">Sofas & armchairs</option>
                        <option value="Tables & chairs">Tables & chairs</option>
                        <option value="Storage & organisation">Storage & organisation</option>
                        <option value="Office furniture">Office furniture</option>
                        <option value="Beds & mattresses">Beds & mattresses</option>
                        <option value="Textiles">Textiles</option>
                        <option value="Rugs & mats & flooring">Rugs & mats & flooring</option>
                        <option value="Home decoration">Home decoration</option>
                        <option value="Lighting">Lighting</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Product Section -->
        <div class="product-container">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $stock = intval($row['stock']);
                    ?>
                    <div class="product-card">
                        <?php
                        $image_urls = json_decode($row['image_url'], true); // Decode the JSON-encoded image URLs
                        if (is_array($image_urls) && !empty($image_urls)) {
                            $image_path = $image_urls[0];
                            echo "<img class='product-image' src='/" . htmlspecialchars($image_path) . "' alt='" . htmlspecialchars($row['name']) . "'>";
                        } else {
                            echo "<img class='product-image' src='/img/default-product.jpg' alt='Default Product Image'>";
                        }
                        ?>
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p>Price: $<?php echo number_format($row['price'], 2); ?></p>
                        <p>Brand: <?php echo htmlspecialchars($row['brand']); ?></p>
                        <p>Color: <?php echo htmlspecialchars($row['color']); ?></p>
                        <p><strong>Stock: <?php echo $stock; ?></strong></p>

                        <form action="add_to_cart.php" method="POST" class="product-form">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id']); ?>">
                            <?php if ($stock > 0) { ?>
                                <label>Quantity: <input type="number" name="quantity" value="1" min="1" max="<?php echo $stock; ?>"></label>
                                <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                            <?php } else { ?>
                                <button type="button" class="out-of-stock-btn" disabled>Out of Stock</button>
                            <?php } ?>
                        </form>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='no-products'>No products found.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
