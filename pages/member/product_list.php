<?php  
require '../../db/db_connect.php';
include '../../_header.php';

// Check if the user is logged in
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user ? $user['user_id'] : 'Guest';
$user_name = $user ? $user['name'] : 'Guest';

// Fetch filter values from GET parameters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : "";
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : 10000;
$brand_filter = isset($_GET['brand']) ? trim($_GET['brand']) : "";
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : "";
$color_filter = isset($_GET['color']) ? trim($_GET['color']) : "";
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : "";

// Fetch unique brands
$brands_result = $conn->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != ''");

// Fetch unique categories from the categories table
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

if (!$categories_result) {
    die("Error fetching categories: " . $conn->error);
}

// Fetch unique colors
$colors_result = $conn->query("SELECT DISTINCT color FROM products WHERE color IS NOT NULL AND color != ''");

// Build the SQL query with filters
$sql = "SELECT * FROM products WHERE status IN ('active', 'discontinued')";

if (!empty($search_query)) {
    $sql .= " AND name LIKE '%" . $conn->real_escape_string($search_query) . "%'";
}

if (!empty($brand_filter)) {
    $sql .= " AND brand = '" . $conn->real_escape_string($brand_filter) . "'";
}

if (!empty($category_filter)) {
    $sql .= " AND category_id = " . $conn->real_escape_string($category_filter);
}

if (!empty($min_price) || !empty($max_price)) {
    $sql .= " AND price BETWEEN " . $conn->real_escape_string($min_price) . " AND " . $conn->real_escape_string($max_price);
}

if (!empty($color_filter)) {
    $sql .= " AND color = '" . $conn->real_escape_string($color_filter) . "'";
}

// Handle sorting
if (!empty($sort_by)) {
    switch ($sort_by) {
        case 'price_asc':
            $sql .= " ORDER BY price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY price DESC";
            break;
        case 'stock_asc':
            $sql .= " ORDER BY stock ASC";
            break;
        case 'stock_desc':
            $sql .= " ORDER BY stock DESC";
            break;
        case 'rating_asc':
            $sql .= " ORDER BY rating ASC";
            break;
        case 'rating_desc':
            $sql .= " ORDER BY rating DESC";
            break;
        default:
            $sql .= " ORDER BY name ASC"; // Default sorting
    }
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
            // Handle filter and sort changes
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
                        alert('Error applying filters or sorting.');
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
        <h2>Product Lists</h2>
        <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
        <p><strong>User Name:</strong> <?php echo htmlspecialchars($user_name); ?></p>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
        <form method="GET" action="product_list.php">
            <input type="search" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
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
                    <label for="minPrice">Price Range:</label>
                    <input type="number" id="minPrice" name="min_price" min="0" max="10000" step="1" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : 0; ?>" placeholder="Min Price">
                    <input type="number" id="maxPrice" name="max_price" min="0" max="10000" step="1" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : 10000; ?>" placeholder="Max Price">
                </div>

                <!-- Brand Filter -->
                <div class="filter-group">
                    <label for="brandFilter">Brand:</label>
                    <select id="brandFilter" name="brand">
                        <option value="">All Brands</option>
                        <?php
                        if ($brands_result->num_rows > 0) {
                            while ($row = $brands_result->fetch_assoc()) {
                                $selected = ($brand_filter === $row['brand']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['brand']) . "' $selected>" . htmlspecialchars($row['brand']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="filter-group">
                    <label for="categoryFilter">Category:</label>
                    <select id="categoryFilter" name="category">
                        <option value="">All Categories</option>
                        <?php
                        if ($categories_result->num_rows > 0) {
                            while ($row = $categories_result->fetch_assoc()) {
                                $selected = ($category_filter === $row['id']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- Color Filter -->
                <div class="filter-group">
                    <label for="colorFilter">Color:</label>
                    <select id="colorFilter" name="color">
                        <option value="">All Colors</option>
                        <?php
                        if ($colors_result->num_rows > 0) {
                            while ($row = $colors_result->fetch_assoc()) {
                                $selected = ($color_filter === $row['color']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['color']) . "' $selected>" . htmlspecialchars($row['color']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- Sorting Options -->
                <div class="filter-group">
                    <label for="sortBy">Sort By:</label>
                    <select id="sortBy" name="sort">
                        <option value="">Default</option>
                        <option value="price_asc" <?php echo $sort_by === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo $sort_by === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="stock_asc" <?php echo $sort_by === 'stock_asc' ? 'selected' : ''; ?>>Stock: Low to High</option>
                        <option value="stock_desc" <?php echo $sort_by === 'stock_desc' ? 'selected' : ''; ?>>Stock: High to Low</option>
                        <option value="rating_asc" <?php echo $sort_by === 'rating_asc' ? 'selected' : ''; ?>>Rating: Low to High</option>
                        <option value="rating_desc" <?php echo $sort_by === 'rating_desc' ? 'selected' : ''; ?>>Rating: High to Low</option>
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
                    $is_discontinued = $row['status'] === 'discontinued';
                    ?>
                    <a href="product_details.php?product_id=<?php echo htmlspecialchars($row['product_id']); ?>" class="product-link">
                        <div class="product-card <?php echo $is_discontinued ? 'discontinued' : ''; ?>">
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
                            <?php if ($is_discontinued) { ?>
                                <button type="button" class="discontinued-btn" disabled>Discontinued</button>
                            <?php } elseif ($stock > 0) { ?>
                                <label>Quantity: <input type="number" name="quantity" value="1" min="1" max="<?php echo $stock; ?>"></label>
                                <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                            <?php } else { ?>
                                <button type="button" class="out-of-stock-btn" disabled>Out of Stock</button>
                            <?php } ?>
                        </form>
                        </div>
                    </a>
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
