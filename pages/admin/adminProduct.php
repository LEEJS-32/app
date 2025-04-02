<?php 
ob_start(); // Start output buffering
include '../../_header.php'; 

// Initialize variables to avoid undefined variable warnings
$search_query = isset($_GET['search']) ? trim($_GET['search']) : "";
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : "";
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Product List</title>
    <link rel="stylesheet" href="../../css/adminProduct.css">

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
                applyFilters();
            });

            // Handle category filter
            $('#categoryFilter').change(function() {
                applyFilters();
            });

            // Handle status filter
            $('#statusFilter').change(function() {
                applyFilters();
            });

            // Function to apply filters
            function applyFilters() {
                const search = $('#search').val();
                const category = $('#categoryFilter').val();
                const status = $('#statusFilter').val();

                $.ajax({
                    url: 'adminProduct.php',
                    type: 'GET',
                    data: {
                        search: search,
                        category: category,
                        status: status
                    },
                    success: function (response) {
                        // Replace the table body with the filtered results
                        $('tbody').html($(response).find('tbody').html());
                    },
                    error: function () {
                        alert('Error applying filters.');
                    }
                });
            }
        });
    </script>
</head>
<body>
    <h1>Product List</h1>
    <button onclick="window.location.href='adminCreateProduct.php'">Add New Product</button>

    <!-- Search Bar -->
    <input type="text" id="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">

    <!-- Category Filter -->
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

    <!-- Status Filter -->
    <select id="statusFilter">
        <option value="">All Statuses</option>
        <option value="active" <?php echo $status_filter == "active" ? "selected" : ""; ?>>Active</option>
        <option value="inactive" <?php echo $status_filter == "inactive" ? "selected" : ""; ?>>Inactive</option>
        <option value="discontinued" <?php echo $status_filter == "discontinued" ? "selected" : ""; ?>>Discontinued</option>
    </select>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th class="sortable" data-column="product_id" data-order="desc">Product ID &#9660;</th>
                <th class="sortable" data-column="name" data-order="desc">Name &#9660;</th>
                <th>Description</th>
                <th class="sortable" data-column="price" data-order="desc">Price &#9660;</th>
                <th class="sortable" data-column="stock" data-order="desc">Stock &#9660;</th>
                <th>Category</th>
                <th>Images</th>
                <th>Status</th>
                <th class="sortable" data-column="discount" data-order="desc">Discount &#9660;</th>
                <th class="sortable" data-column="discounted_price" data-order="desc">Discounted Price &#9660;</th>
                <th class="sortable" data-column="weight" data-order="desc">Weight &#9660;</th>
                <th class="sortable" data-column="length" data-order="desc">Length &#9660;</th>
                <th class="sortable" data-column="width" data-order="desc">Width &#9660;</th>
                <th class="sortable" data-column="height" data-order="desc">Height &#9660;</th>
                <th class="sortable" data-column="brand" data-order="desc">Brand &#9660;</th>
                <th class="sortable" data-column="color" data-order="desc">Color &#9660;</th>
                <th class="sortable" data-column="rating" data-order="desc">Rating &#9660;</th>
                <th class="sortable" data-column="reviews_count" data-order="desc">Reviews Count &#9660;</th>
                <th class="sortable" data-column="created_at" data-order="desc">Created At &#9660;</th>
                <th class="sortable" data-column="updated_at" data-order="desc">Updated At &#9660;</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "TESTING1";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Fetch filter values from GET parameters
            $search_query = isset($_GET['search']) ? trim($_GET['search']) : "";
            $category_filter = isset($_GET['category']) ? trim($_GET['category']) : "";
            $status_filter = isset($_GET['status']) ? trim($_GET['status']) : "";

            // Build the SQL query with filters
            $sql = "SELECT * FROM products WHERE 1=1"; // Start with a base query

            if (!empty($search_query)) {
                $sql .= " AND name LIKE '%" . $conn->real_escape_string($search_query) . "%'";
            }

            if (!empty($category_filter)) {
                $sql .= " AND category = '" . $conn->real_escape_string($category_filter) . "'";
            }

            if (!empty($status_filter)) {
                $sql .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
            }

            // Execute the query
            $result = $conn->query($sql);

            if (!$result) {
                die("Error fetching products: " . $conn->error);
            }

            if ($result->num_rows > 0) {
                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["product_id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                    echo "<td><div class='scrollable-description'>" . htmlspecialchars($row["description"]) . "</div></td>";                    echo "<td>" . $row["price"] . "</td>";
                    echo "<td>" . $row["stock"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["category"]) . "</td>";

                    // Decode the JSON-encoded image URLs
                    $image_urls = json_decode($row["image_url"]);
                    echo "<td>";
                    if (is_array($image_urls)) {
                        foreach ($image_urls as $image_url) {
                            echo "<img src='/" . htmlspecialchars($image_url) . "' alt='Product Image' style='max-width: 100px; max-height: 100px; margin: 5px;'>";
                        }
                    }
                    echo "</td>";

                    echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                    echo "<td>" . $row["discount"] . "</td>";
                    echo "<td>" . $row["discounted_price"] . "</td>";
                    echo "<td>" . $row["weight"] . "</td>";
                    echo "<td>" . $row["length"] . "</td>";
                    echo "<td>" . $row["width"] . "</td>";
                    echo "<td>" . $row["height"] . "</td>";
                    echo "<td>" . $row["brand"] . "</td>";
                    echo "<td>" . $row["color"] . "</td>";
                    echo "<td>" . $row["rating"] . "</td>";
                    echo "<td>" . $row["reviews_count"] . "</td>";
                    echo "<td>" . $row["created_at"] . "</td>";
                    echo "<td>" . $row["updated_at"] . "</td>";
                    echo "<td class='action-buttons'>";
                    echo "<a href='#' class='update' data-id='" . $row["product_id"] . "'>Update</a>";
                    echo "<a href='#' class='delete' data-id='" . $row["product_id"] . "'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='21'>No products found</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>