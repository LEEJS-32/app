<?php 
ob_start(); // Start output buffering
include '../../_header.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Product List</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons a {
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            background-color: blue;
            border-radius: 5px;
        }
        .action-buttons a.delete {
            background-color: red;
        }
        .sortable {
            cursor: pointer;
        }
    </style>
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
    <input type="text" id="search" placeholder="Search products...">
    <select id="categoryFilter">
        <option value="">All Categories</option>
        <option value="Sofas & armchairs">Sofas & armchairs</option>
        <option value="Tables & chairs">Tables & chairs</option>
        <option value="Storage & organisation">Storage & organisation</option>
        <option value="Office furniture">Office furniture</option>
        <option value="Beds & mattresses">Beds & mattresses</option>
        <option value="Textiles">Textiles</option>
        <option value="Rugs & mats & flooring">Rugs & mats & flooring</option>
        <option value="Home decoration">Home decoration</option>
        <option value="Lightning">Lightning</option>
    </select>
    <select id="statusFilter">
        <option value="">All Statuses</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="discontinued">Discontinued</option>
    </select>
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

            // Fetch products from the database
            $sql = "SELECT * FROM products";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["product_id"] . "</td>";
                    echo "<td>" . $row["name"] . "</td>";
                    echo "<td>" . $row["description"] . "</td>";
                    echo "<td>" . $row["price"] . "</td>";
                    echo "<td>" . $row["stock"] . "</td>";
                    echo "<td>" . $row["category"] . "</td>";

                    // Decode the JSON-encoded image URLs
                    $image_urls = json_decode($row["image_url"]);
                    echo "<td>";
                    if (is_array($image_urls)) {
                        foreach ($image_urls as $image_url) {
                            echo "<img src='" . $image_url . "' alt='Product Image' style='max-width: 100px; max-height: 100px; margin: 5px;'>";
                        }
                    }
                    echo "</td>";

                    echo "<td>" . $row["status"] . "</td>";
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