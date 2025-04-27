<?php  
include_once '../../_base.php';
require '../../db/db_connect.php';
include '../../_header.php';

// Check if the user is logged in
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$user_id = $user ? $user->user_id : 'Guest';
$user_name = $user ? $user->name : 'Guest';

// Fetch filter values from GET parameters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : "";
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : 10000;
$brand_filter = isset($_GET['brand']) ? trim($_GET['brand']) : "";
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : "";
$color_filter = isset($_GET['color']) ? trim($_GET['color']) : "";
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : "";

// Pagination variables
$items_per_page = 12; // Number of products per page
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Current page number
$offset = ($current_page - 1) * $items_per_page; // Calculate the offset

// Fetch unique brands
$sql_brands = "SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != ''";
$stm_brands = $_db->prepare($sql_brands);
$stm_brands->execute();
$brands_result = $stm_brands->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique categories from the categories table
$sql_categories = "SELECT id, name FROM categories ORDER BY name ASC";
$stm_categories = $_db->prepare($sql_categories);
$stm_categories->execute();
$categories_result = $stm_categories->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique colors
$sql_colors = "SELECT DISTINCT color FROM products WHERE color IS NOT NULL AND color != ''";
$stm_colors = $_db->prepare($sql_colors);
$stm_colors->execute();
$colors_result = $stm_colors->fetchAll(PDO::FETCH_ASSOC);

// Build the SQL query with filters
$sql = "SELECT * FROM products WHERE status IN ('active', 'discontinued')";

if (!empty($search_query)) {
    $sql .= " AND name LIKE :search_query";
}
if (!empty($brand_filter)) {
    $sql .= " AND brand = :brand_filter";
}
if (!empty($category_filter)) {
    $sql .= " AND category_id = :category_filter";
}
if (!empty($min_price) || !empty($max_price)) {
    $sql .= " AND price BETWEEN :min_price AND :max_price";
}
if (!empty($color_filter)) {
    $sql .= " AND color = :color_filter";
}

// Handle sorting
switch ($sort_by) {
    case 'latest':
        $sql .= " ORDER BY created_at DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
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

// Add LIMIT and OFFSET for pagination
$sql .= " LIMIT :limit OFFSET :offset";

$stm = $_db->prepare($sql);

// Bind parameters
if (!empty($search_query)) {
    $stm->bindValue(':search_query', "%$search_query%", PDO::PARAM_STR);
}
if (!empty($brand_filter)) {
    $stm->bindValue(':brand_filter', $brand_filter, PDO::PARAM_STR);
}
if (!empty($category_filter)) {
    $stm->bindValue(':category_filter', $category_filter, PDO::PARAM_INT);
}
if (!empty($min_price) || !empty($max_price)) {
    $stm->bindValue(':min_price', $min_price, PDO::PARAM_INT);
    $stm->bindValue(':max_price', $max_price, PDO::PARAM_INT);
}
if (!empty($color_filter)) {
    $stm->bindValue(':color_filter', $color_filter, PDO::PARAM_STR);
}
$stm->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stm->bindValue(':offset', $offset, PDO::PARAM_INT);

$stm->execute();
$result = $stm->fetchAll(PDO::FETCH_ASSOC);

// Build the count SQL with the same filters
$count_sql = "SELECT COUNT(*) FROM products WHERE status IN ('active', 'discontinued')";
if (!empty($search_query)) {
    $count_sql .= " AND name LIKE :search_query";
}
if (!empty($brand_filter)) {
    $count_sql .= " AND brand = :brand_filter";
}
if (!empty($category_filter)) {
    $count_sql .= " AND category_id = :category_filter";
}
if (!empty($min_price) || !empty($max_price)) {
    $count_sql .= " AND price BETWEEN :min_price AND :max_price";
}
if (!empty($color_filter)) {
    $count_sql .= " AND color = :color_filter";
}

$count_stm = $_db->prepare($count_sql);

// Bind parameters for count query
if (!empty($search_query)) {
    $count_stm->bindValue(':search_query', "%$search_query%", PDO::PARAM_STR);
}
if (!empty($brand_filter)) {
    $count_stm->bindValue(':brand_filter', $brand_filter, PDO::PARAM_STR);
}
if (!empty($category_filter)) {
    $count_stm->bindValue(':category_filter', $category_filter, PDO::PARAM_INT);
}
if (!empty($min_price) || !empty($max_price)) {
    $count_stm->bindValue(':min_price', $min_price, PDO::PARAM_INT);
    $count_stm->bindValue(':max_price', $max_price, PDO::PARAM_INT);
}
if (!empty($color_filter)) {
    $count_stm->bindValue(':color_filter', $color_filter, PDO::PARAM_STR);
}

$count_stm->execute();
$total_items = $count_stm->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Build query string for pagination links, excluding 'page'
$query_params = $_GET;
unset($query_params['page']);
$query_string = http_build_query($query_params);
$query_string = $query_string ? $query_string . '&' : '';

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
    <link rel="stylesheet" href="../../css/product.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

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
                        $('.pagination').html($(response).find('.pagination').html());
                    },
                    error: function () {
                        alert('Error applying filters or sorting.');
                    }
                });
            });

            // Handle pagination click (AJAX)
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                const url = $(this).attr('href');
                $.get(url, function (response) {
                    $('.product-container').html($(response).find('.product-container').html());
                    $('.pagination').html($(response).find('.pagination').html());
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

    <div class="main-container">
        <form method="GET" action="product_list.php" class="filter-form">
            <!-- Search Bar -->
            <div class="search-container">
                <input type="search" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </div>
            <div class="content-container">
                <!-- Sidebar Filters -->
                <div class="filter-section">
                    <h3>Filters</h3>
                    <!-- Price Filter -->
                    <div class="filter-group">
                        <label for="minPrice">Price Range:</label>
                        <input type="number" id="minPrice" name="min_price" min="0" max="10000" step="1" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : 0; ?>" placeholder="Min Price">
                        <span style="display:inline-block; width:20px; height:2px; background:#ccc; vertical-align:middle; margin:0 24px;"></span>
                        <input type="number" id="maxPrice" name="max_price" min="0" max="10000" step="1" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : 10000; ?>" placeholder="Max Price">
                    </div>

                    <!-- Brand Filter -->
                    <div class="filter-group">
                        <label for="brandFilter">Brand:</label>
                        <select id="brandFilter" name="brand">
                            <option value="">All Brands</option>
                            <?php
                            foreach ($brands_result as $row) {
                                $selected = ($brand_filter === $row['brand']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['brand']) . "' $selected>" . htmlspecialchars($row['brand']) . "</option>";
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
                            foreach ($categories_result as $row) {
                                $selected = ($category_filter === $row['id']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
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
                            foreach ($colors_result as $row) {
                                $selected = ($color_filter === $row['color']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['color']) . "' $selected>" . htmlspecialchars($row['color']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Sorting Options -->
                    <div class="filter-group">
                        <label for="sortBy">Sort By:</label>
                        <select id="sortBy" name="sort">
                            <option value="">Default</option>
                            <option value="latest" <?php echo $sort_by === 'latest' ? 'selected' : ''; ?>>Latest</option>
                            <option value="price_asc" <?php echo $sort_by === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo $sort_by === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating_asc" <?php echo $sort_by === 'rating_asc' ? 'selected' : ''; ?>>Rating: Low to High</option>
                            <option value="rating_desc" <?php echo $sort_by === 'rating_desc' ? 'selected' : ''; ?>>Rating: High to Low</option>
                        </select>
                    </div>
                </div>

                <!-- Product Section -->
                <div class="product-container">
                    <ul class="product-list">
                        <?php
                        if (!empty($result)) {
                            foreach ($result as $row) {
                                $stock = intval($row['stock']);
                                $is_discontinued = $row['status'] === 'discontinued';
                                ?>
                                <li class="product-item">
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
                                            <div class="product-name">
                                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                            </div>
                                            <div class="price-container">
                                            <?php if ($row['price'] != $row['discounted_price']) { ?>
                                                <p id="price" class="original-price">RM<?php echo number_format($row['price'], 2); ?></p>
                                                <p id="discounted_price" class="discounted-price">RM<?php echo number_format($row['discounted_price'], 2); ?></p>
                                            <?php } else { ?>
                                                <p id="price" class="no-discount-price">RM<?php echo number_format($row['price'], 2); ?></p>
                                            <?php } ?>
                                        </div>
                            
                                        </div>
                                    </a>
                                </li>
                                <?php
                            }
                        } else {
                            echo "<li class='no-products'>No products found.</li>";
                        }
                        ?>
                    </ul>
                </div> <!-- End of product-container -->
            </div> <!-- End of content-container -->

            <!-- Pagination -->
            <div class="pagination">
                <!-- Previous Button -->
                <?php if ($current_page > 1): ?>
                    <a href="?<?php echo $query_string; ?>page=<?php echo $current_page - 1; ?>" class="prev">&lt;</a>
                <?php else: ?>
                    <span class="prev disabled">&lt;</span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?<?php echo $query_string; ?>page=<?php echo $i; ?>"<?php if ($i == $current_page) echo ' class="active"'; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <!-- Next Button -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="?<?php echo $query_string; ?>page=<?php echo $current_page + 1; ?>" class="next">&gt;</a>
                <?php else: ?>
                    <span class="next disabled">&gt;</span>
                <?php endif; ?>
            </div><!-- End of pagination -->
        </form>
    </div><!-- End of main-container -->
</body>
<footer>
    <?php include __DIR__ . '/../../_footer.php'; ?>
</footer>
</html>