<?php 
ob_start(); // Start output buffering
include '../../_header.php'; 
require '../../lib/SimplePager.php'; // Include the SimplePager class

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

// Build the base SQL query
$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search_query)) {
    $query .= " AND name LIKE ?";
    $params[] = "%" . $search_query . "%";
}
if (!empty($category_filter)) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
}
if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

// Use SimplePager for pagination
$pager = new SimplePager($query, $params, 10, $page); // 10 items per page
$products = $pager->result;
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
            // Handle delete action
            $('body').on('click', '.delete', function(e) {
                e.preventDefault();
                var productId = $(this).data('id');
                if (confirm('Are you sure you want to delete this product?')) {
                    $.ajax({
                        url: 'adminDeleteProduct.php',
                        type: 'GET',
                        data: { product_id: productId },
                        success: function(response) {
                            alert('Product deleted successfully');
                            location.reload();
                        },
                        error: function() {
                            alert('Error deleting product');
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

            // Handle sorting
            $('.sortable').click(function() {
                var column = $(this).data('column');
                var order = $(this).data('order');
                var text = $(this).html();
                text = text.substring(0, text.length - 1);

                if (order == 'desc') {
                    $(this).data('order', 'asc');
                    text += '&#9650;';
                } else {
                    $(this).data('order', 'desc');
                    text += '&#9660;';
                }
                $(this).html(text);

                $.ajax({
                    url: 'fetchProducts.php',
                    type: 'GET',
                    data: { column: column, order: order },
                    success: function(response) {
                        $('tbody').html(response);
                    }
                });
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
        });
    </script>
</head>
<body>
    <h1>Product List</h1>
    <button onclick="window.location.href='adminCreateProduct.php'">Add New Product</button>
    <input type="text" id="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
    <select id="categoryFilter">
        <option value="">All Categories</option>
        <option value="Sofas & armchairs" <?php echo $category_filter == "Sofas & armchairs" ? "selected" : ""; ?>>Sofas & armchairs</option>
        <option value="Tables & chairs" <?php echo $category_filter == "Tables & chairs" ? "selected" : ""; ?>>Tables & chairs</option>
        <option value="Storage & organisation" <?php echo $category_filter == "Storage & organisation" ? "selected" : ""; ?>>Storage & organisation</option>
        <option value="Office furniture" <?php echo $category_filter == "Office furniture" ? "selected" : ""; ?>>Office furniture</option>
        <option value="Beds & mattresses" <?php echo $category_filter == "Beds & mattresses" ? "selected" : ""; ?>>Beds & mattresses</option>
        <option value="Textiles" <?php echo $category_filter == "Textiles" ? "selected" : ""; ?>>Textiles</option>
        <option value="Rugs & mats & flooring" <?php echo $category_filter == "Rugs & mats & flooring" ? "selected" : ""; ?>>Rugs & mats & flooring</option>
        <option value="Home decoration" <?php echo $category_filter == "Home decoration" ? "selected" : ""; ?>>Home decoration</option>
        <option value="Lightning" <?php echo $category_filter == "Lightning" ? "selected" : ""; ?>>Lightning</option>
    </select>
    <select id="statusFilter">
        <option value="">All Statuses</option>
        <option value="active" <?php echo $status_filter == "active" ? "selected" : ""; ?>>Active</option>
        <option value="inactive" <?php echo $status_filter == "inactive" ? "selected" : ""; ?>>Inactive</option>
        <option value="discontinued" <?php echo $status_filter == "discontinued" ? "selected" : ""; ?>>Discontinued</option>
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
                <th>Status</th>
                <th>Discount (%)</th>
                <th>Discounted Price</th>
                <th>Weight</th>
                <th>Length</th>
                <th>Width</th>
                <th>Height</th>
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
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
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
                        <td><?php echo htmlspecialchars($product['status']); ?></td>
                        <td><?php echo $product['discount']; ?></td>
                        <td><?php echo $product['discounted_price']; ?></td>
                        <td><?php echo $product['weight']; ?></td>
                        <td><?php echo $product['length']; ?></td>
                        <td><?php echo $product['width']; ?></td>
                        <td><?php echo $product['height']; ?></td>
                        <td><?php echo htmlspecialchars($product['brand']); ?></td>
                        <td><?php echo htmlspecialchars($product['color']); ?></td>
                        <td><?php echo $product['rating']; ?></td>
                        <td><?php echo $product['reviews_count']; ?></td>
                        <td><?php echo $product['created_at']; ?></td>
                        <td><?php echo $product['updated_at']; ?></td>
                        <td>
                            <a href="#" class="update" data-id="<?php echo $product['product_id']; ?>">Update</a>
                            <a href="#" class="delete" data-id="<?php echo $product['product_id']; ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="21">No products found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php echo $pager->html(); ?>
    </div>
</body>
</html>