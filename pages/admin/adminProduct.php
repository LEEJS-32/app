<?php 
ob_start(); // Start output buffering
include '../../_header.php'; 

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TESTING1";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
$types = "";

// Add filters to the query
if (!empty($search_query)) {
    $query .= " AND p.name LIKE ?";
    $params[] = "%" . $search_query . "%";
    $types .= "s";
}
if (!empty($category_filter)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}
if (!empty($status_filter)) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
    $types .= "s";
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
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total_items);
$stmt->fetch();
$stmt->close();

// Calculate total pages
$total_pages = ceil($total_items / $limit);

// Fetch paginated results
$query .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Product List</title>
    <link rel="stylesheet" href="../../css/adminProduct.css"> <!-- External CSS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle disable action (turn status to inactive)
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
                window.location.href = 'adminUpdateProduct.php?product_id=' + productId;
            });

            // Handle search
            $('#search').on('input', function() {
                var search = $(this).val();
                $.ajax({
                    url: 'fetchProducts.php',
                    type: 'GET',
                    data: { search: search },
                    success: function(response) {
                        $('tbody').html(response);
                    }
                });
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
        });
    </script>
</head>
<body>
    <h1>Product List</h1>
    <button onclick="window.location.href='adminCreateProduct.php'">Add New Product</button>
    <button onclick="window.location.href='adminCreateCategory.php'">Add New Category</button>

    <input type="text" id="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
    <select id="categoryFilter">
        <option value="">All Categories</option>
        <?php
        $categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
        while ($row = $categories_result->fetch_assoc()) {
            $selected = ($category_filter == $row['id']) ? "selected" : "";
            echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
        }
        ?>
    </select>
    <select id="statusFilter">
        <option value="">All Statuses</option>
        <option value="active" <?php echo $status_filter == "active" ? "selected" : ""; ?>>Active</option>
        <option value="inactive" <?php echo $status_filter == "inactive" ? "selected" : ""; ?>>Inactive</option>
        <option value="discontinued" <?php echo $status_filter == "discontinued" ? "selected" : ""; ?>>Discontinued</option>
    </select>

    <select id="sortOptions">
    <option hidden value="product_id_asc" <?php echo ($sort_column == 'product_id' && $sort_order == 'asc') ? 'selected' : ''; ?>>Product ID (Ascending)</option>
    <option hidden value="product_id_desc" <?php echo ($sort_column == 'product_id' && $sort_order == 'desc') ? 'selected' : ''; ?>>Product ID (Descending)</option>
    <option value="price_asc" <?php echo ($sort_column == 'price' && $sort_order == 'asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
    <option value="price_desc" <?php echo ($sort_column == 'price' && $sort_order == 'desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
    <option value="stock_asc" <?php echo ($sort_column == 'stock' && $sort_order == 'asc') ? 'selected' : ''; ?>>Stock (Low to High)</option>
    <option value="stock_desc" <?php echo ($sort_column == 'stock' && $sort_order == 'desc') ? 'selected' : ''; ?>>Stock (High to Low)</option>
    <option value="rating_asc" <?php echo ($sort_column == 'rating' && $sort_order == 'asc') ? 'selected' : ''; ?>>Rating (Low to High)</option>
    <option value="rating_desc" <?php echo ($sort_column == 'rating' && $sort_order == 'desc') ? 'selected' : ''; ?>>Rating (High to Low)</option>
    </select>

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
                <th>Brand</th>
                <th>Color</th>
                <th>Rating</th>
                <th>Reviews Count</th>
                <th>Time Created</th>
                <th>Time Updated</th>
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
                        <td><?php echo htmlspecialchars($product['brand']); ?></td>
                        <td><?php echo htmlspecialchars($product['color']); ?></td>
                        <td><?php echo $product['rating']; ?></td>
                        <td><?php echo $product['reviews_count']; ?></td>
                        <td><?php echo $product['created_at']; ?></td>
                        <td><?php echo $product['updated_at']; ?></td>
                        <td>
                            <a href="adminUpdateProduct.php?product_id=<?php echo $product['product_id']; ?>">Update</a>
                            <a href="#" class="disable" data-id="<?php echo $product['product_id']; ?>">Disable</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="18">No products found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($total_pages > 1): ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>" 
                   class="<?php echo $i == $page ? 'active' : ''; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</body>
</html>