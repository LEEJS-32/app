<?php
// ob_start(); // Start output buffering
include '../../_base.php';
include '../../_header.php';


// ----------------------------------------------------------------------------

// Member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;


// ----------------------------------------------------------------------------


// Initialize variables
$search_query = isset($_GET['search']) ? trim($_GET['search']) : "";
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : "";
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : "";
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Number of items per page
$offset = ($page - 1) * $limit;

// Build the base SQL query
$query = "SELECT p.*, c.name AS category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";
$params = [];

// Add filters to the query
if (!empty($search_query)) {
    $query .= " AND p.name LIKE :search";
    $params[':search'] = "%" . $search_query . "%";
}
if (!empty($category_filter)) {
    $query .= " AND p.category_id = :category";
    $params[':category'] = $category_filter;
}
if (!empty($status_filter)) {
    $query .= " AND p.status = :status";
    $params[':status'] = $status_filter;
}

// Validate sorting column and order
$sort_column = isset($_GET['column']) ? $_GET['column'] : 'product_id';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'asc';

$allowed_columns = ['product_id', 'price', 'stock', 'rating'];
$allowed_order = ['asc', 'desc'];

if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'product_id';
}
if (!in_array($sort_order, $allowed_order)) {
    $sort_order = 'asc';
}

// Add sorting to the query
$query .= " ORDER BY $sort_column $sort_order";

// Count total items
$count_query = "SELECT COUNT(*) FROM ($query) AS subquery";
try {
    $stm = $_db->prepare($count_query);
    foreach ($params as $key => $value) {
        $stm->bindValue($key, $value);
    }
    $stm->execute();
    $total_items = $stm->fetchColumn();
} catch (PDOException $e) {
    die("Error counting products: " . $e->getMessage());
}

// Calculate total pages
$total_pages = ceil($total_items / $limit);

// Fetch paginated results
$query .= " LIMIT :limit OFFSET :offset";
$params[':limit'] = $limit;
$params[':offset'] = $offset;

try {
    $stm = $_db->prepare($query);
    foreach ($params as $key => $value) {
        $stm->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stm->execute();
    $products = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}

// Fetch categories for the dropdown
try {
    $categories_stmt = $_db->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Product List</title>
    <link rel="stylesheet" href="../../css/adminProduct.css"> <!-- External CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle disable action
            $('body').on('click', '.disable', function(e) {
                e.preventDefault();
                var productId = $(this).data('id');
                if (confirm('Are you sure you want to disable this product?')) {
                    $.ajax({
                        url: 'adminDisableProduct.php',
                        type: 'POST',
                        data: { product_id: productId },
                        success: function(response) {
                            alert('Product disabled successfully');
                            location.reload();
                        },
                        error: function() {
                            alert('Error disabling product');
                        }
                    });
                }
            });

            // Handle update action
            $('body').on('click', '.update', function(e) {
                e.preventDefault();
                var productId = $(this).data('id');
                window.location.href = 'adminUpdate.php?product_id=' + productId;
            });


            // Handle category filter
            $('#categoryFilter').change(function() {
                var category = $(this).val();
                $.ajax({
                    url: 'fetchProducts.php',
                    type: 'GET',
                    data: { category: category },
                    success: function(response) {
                        $('tbody').html(response);
                    }
                });
            });

            // Handle status filter
            $('#statusFilter').change(function() {
                var status = $(this).val();
                $.ajax({
                    url: 'fetchProducts.php',
                    type: 'GET',
                    data: { status: status },
                    success: function(response) {
                        $('tbody').html(response);
                    }
                });
            });

            $('#sortOptions').change(function () {
                const selectedOption = $(this).val();
                const [column, order] = selectedOption.split('_');
                const search = $('#search').val();
                const category = $('#categoryFilter').val();
                const status = $('#statusFilter').val();

                // Redirect with sorting parameters
                window.location.href = `?column=${column}&order=${order}&search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}&status=${encodeURIComponent(status)}`;
            });


            $('#categoryFilter, #statusFilter').change(function() {
                const search = $('#search').val();
                const category = $('#categoryFilter').val();
                const status = $('#statusFilter').val();
                const sort = $('#sortOptions').val();
                const [column, order] = sort.split('_');

                // Redirect with updated parameters
                window.location.href = `?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}&status=${encodeURIComponent(status)}&column=${column}&order=${order}`;
            });

            // Toggle additional details
            $('body').on('click', '.toggle-details', function() {
                const productId = $(this).data('id');
                const detailsRow = $(`#details-${productId}`);
                detailsRow.toggle();
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButtons = document.querySelectorAll('.toggle-details');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const productId = this.getAttribute('data-id');
                    const detailsRow = document.getElementById(`details-${productId}`);

                    if (detailsRow.style.display === 'none') {
                        detailsRow.style.display = 'table-row';
                        this.textContent = 'Hide Details';
                    } else {
                        detailsRow.style.display = 'none';
                        this.textContent = 'Show Details';
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.getElementById('toggleAllDetails');
            const detailsRows = document.querySelectorAll('.details-row');

            toggleButton.addEventListener('click', function () {
                const isHidden = detailsRows[0].style.display === 'none';

                detailsRows.forEach(row => {
                    row.style.display = isHidden ? 'table-row' : 'none';
                });

                toggleButton.textContent = isHidden ? 'Hide Details' : 'Show Details';
            });
        });
    </script>
</head>
<body>

    <main>
        <div class="container">
            <div class="left">
                <?php include __DIR__ . '/admin_nav.php'; ?>
            </div>
            <div class="right">
                <h1>Product List</h1>


                <!-- Search and Filters -->
                <div class="filters-container">

                <button onclick="window.location.href='adminCreateProduct.php'">Add New Product</button>
                <button onclick="window.location.href='adminCreateCategory.php'">Add New Category</button>

                <form method="GET" action="">
                    <input type="text" id="search" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i> <!-- Font Awesome Icon -->
                    </button>
                </form>
                <select id="categoryFilter">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($category_filter == $category['id']) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $status_filter == "active" ? "selected" : ""; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter == "inactive" ? "selected" : ""; ?>>Inactive</option>
                    <option value="discontinued" <?php echo $status_filter == "discontinued" ? "selected" : ""; ?>>Discontinued</option>
                </select>
                <select id="sortOptions">
                    <option value="product_id_asc" <?php echo ($sort_column == 'product_id' && $sort_order == 'asc') ? 'selected' : ''; ?>>Product ID (Ascending)</option>
                    <option value="product_id_desc" <?php echo ($sort_column == 'product_id' && $sort_order == 'desc') ? 'selected' : ''; ?>>Product ID (Descending)</option>
                    <option value="price_asc" <?php echo ($sort_column == 'price' && $sort_order == 'asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                    <option value="price_desc" <?php echo ($sort_column == 'price' && $sort_order == 'desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
                    <option value="stock_asc" <?php echo ($sort_column == 'stock' && $sort_order == 'asc') ? 'selected' : ''; ?>>Stock (Low to High)</option>
                    <option value="stock_desc" <?php echo ($sort_column == 'stock' && $sort_order == 'desc') ? 'selected' : ''; ?>>Stock (High to Low)</option>
                    <option value="rating_asc" <?php echo ($sort_column == 'rating' && $sort_order == 'asc') ? 'selected' : ''; ?>>Rating (Low to High)</option>
                    <option value="rating_desc" <?php echo ($sort_column == 'rating' && $sort_order == 'desc') ? 'selected' : ''; ?>>Rating (High to Low)</option>
                </select>
                <button type="button" id="toggleAllDetails">Show Details</button>

                </div>

                <!-- Product Table -->
                <table>
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Images</th>
                            <th>Video</th>
                            <th>Status</th>
                            <th>Discount (%)</th>
                            <th>Discounted Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['product_id']; ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['description']); ?></td>
                                    <td><?php echo $product['price']; ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td>
                                        <?php
                                        $image_urls = json_decode($product['image_url']);
                                        if (is_array($image_urls)) {
                                            foreach ($image_urls as $image_url) {
                                                echo "<img src='/" . htmlspecialchars($image_url) . "' alt='Product Image' style='max-width: 100px; max-height: 100px; margin: 5px;'>";
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($product['video_url'])): ?>
                                            <video controls style="max-width: 150px; max-height: 150px;">
                                                <source src="/<?php echo htmlspecialchars($product['video_url']); ?>" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        <?php else: ?>
                                            No video available
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['status']); ?></td>
                                    <td><?php echo $product['discount']; ?></td>
                                    <td><?php echo $product['discounted_price']; ?></td>
                                    <td>
                                        <a href="adminUpdate.php?product_id=<?php echo $product['product_id']; ?>" class="update" data-id="<?php echo $product['product_id']; ?>">Update</a>
                                        <a href="#" class="disable" data-id="<?php echo $product['product_id']; ?>">Disable</a>
                                    </td>
                                </tr>
                                <tr class="details-row" id="details-<?php echo $product['product_id']; ?>" style="display: none;">
                                    <td colspan="12">
                                        <strong>Additional Details:</strong><br>
                                        <p><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand']); ?></p>
                                        <p><strong>Color:</strong> <?php echo htmlspecialchars($product['color']); ?></p>
                                        <p><strong>Rating:</strong> <?php echo $product['rating']; ?></p>
                                        <p><strong>Reviews Count:</strong> <?php echo $product['reviews_count']; ?></p>
                                        <p><strong>Time Created:</strong> <?php echo $product['created_at']; ?></p>
                                        <p><strong>Time Updated:</strong> <?php echo $product['updated_at']; ?></p>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="12">No products found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($total_pages > 1): ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>&column=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>" 
                   class="<?php echo $i == $page ? 'active' : ''; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
        </div>

        <footer>
        <?php
            include __DIR__ . '/../../_footer.php';
        ?>
    </footer>
    </body>
</html>