<?php 
ob_start(); // Start output buffering
include '../../_header.php'; ?>


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
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Handle delete action
            $('.delete').click(function(e) {
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
            $('.update').click(function(e) {
                e.preventDefault();
                var productId = $(this).data('id');
                window.location.href = 'adminUpdateProduct.php?product_id=' + productId;
            });
        });
    </script>
</head>
<body>
    <h1>Product List</h1>
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
                <th>Discount</th>
                <th>Discounted Price</th>
                <th>Weight</th>
                <th>Length</th>
                <th>Width</th>
                <th>Height</th>
                <th>Brand</th>
                <th>Color</th>
                <th>Rating</th>
                <th>Reviews Count</th>
                <th>Created At</th>
                <th>Updated At</th>
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
                echo "<tr><td colspan='20'>No products found</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>