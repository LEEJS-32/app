<?php 
session_start();
ob_start(); 
include '../../_header.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #drop_zone {
            border: 2px dashed #ccc;
            border-radius: 10px;
            width: 100%;
            height: 200px;
            text-align: center;
            line-height: 200px;
            color: #ccc;
            font-size: 20px;
        }
        #drop_zone.dragover {
            border-color: #000;
            color: #000;
        }
        .image-preview {
            display: inline-block;
            margin: 10px;
        }
        .image-preview img {
            max-width: 100px;
            max-height: 100px;
        }
    </style>
    <script>
        $(document).ready(function() {
            function calculateDiscountedPrice() {
                var price = parseFloat($("#price").val());
                var discount = parseFloat($("#discount").val());
                if (!isNaN(price) && !isNaN(discount) && discount >= 0 && discount <= 100) {
                    var discountedPrice = price - (price * (discount / 100));
                    $("#discounted_price").val(discountedPrice.toFixed(2));
                } else {
                    $("#discounted_price").val("");
                }
            }

            $("#price, #discount").on("input", calculateDiscountedPrice);

            $("#image_url").change(function() {
                handleFiles(this.files);
            });

            // Drag and drop functionality
            var dropZone = $('#drop_zone');
            var fileInput = $('#image_url');

            dropZone.on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.addClass('dragover');
            });

            dropZone.on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.removeClass('dragover');
            });

            dropZone.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.removeClass('dragover');
                var files = e.originalEvent.dataTransfer.files;
                fileInput[0].files = files;
                handleFiles(files);
            });

            function handleFiles(files) {
                $('#imagePreview').empty();
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = $('<img>').attr('src', e.target.result);
                        var preview = $('<div>').addClass('image-preview').append(img);
                        $('#imagePreview').append(preview);
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
    </script>
</head>
<body>
    <h1>Update Product</h1>
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

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['product_id'])) {
        $product_id = $_GET['product_id'];
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        $product_name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $category = $_POST['category'];
        $status = $_POST['status'];
        $discount = $_POST['discount'];
        $weight = $_POST['weight'];
        $length = $_POST['length'];
        $width = $_POST['width'];
        $height = $_POST['height'];
        $brand = $_POST['brand'];
        $color = $_POST['color'];
        $rating = $_POST['rating'];
        $reviews_count = $_POST['reviews_count'];

        // Calculate discounted price
        $discounted_price = $price - ($price * ($discount / 100));

        // Handle file uploads
        $image_urls = [];
        $target_dir = "../../img/";
        foreach ($_FILES['image_url']['name'] as $key => $image_name) {
            if ($_FILES['image_url']['error'][$key] == 0) {
                $target_file = $target_dir . basename($image_name);
                if (move_uploaded_file($_FILES['image_url']['tmp_name'][$key], $target_file)) {
                    $image_urls[] = $target_file;
                } else {
                    echo "Sorry, there was an error uploading your file: $image_name<br>";
                }
            }
        }

        // Convert image URLs array to JSON
        $image_urls_json = json_encode($image_urls);

        // Prepare and bind
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, image_url = ?, status = ?, discount = ?, discounted_price = ?, weight = ?, length = ?, width = ?, height = ?, brand = ?, color = ?, rating = ?, reviews_count = ?, updated_at = NOW() WHERE product_id = ?");
        if (!$stmt) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
        $stmt->bind_param("ssdisdssdddddssdii", $product_name, $description, $price, $stock, $category, $image_urls_json, $status, $discount, $discounted_price, $weight, $length, $width, $height, $brand, $color, $rating, $reviews_count, $product_id);

        // Execute the statement
        if ($stmt->execute()) {
            header("Location: adminUpdateProduct.php?product_id=" . $product_id);
            temp('info', 'Record updated successfully');
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();

        // Re-fetch the product details after update
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
    }

    $conn->close();
    ?>

    <form action="adminUpdateProduct.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
        <label for="name">Product Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea><br><br>

        <label for="price">Price:</label><br>
        <input type="number" step="0.01" id="price" name="price" value="<?php echo $product['price']; ?>" required><br><br>

        <label for="stock">Stock:</label><br>
        <input type="number" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required><br><br>

        <label for="category">Category:</label><br>
        <select id="category" name="category">
            <option value="Sofas & armchairs" <?php if ($product['category'] == 'Sofas & armchairs') echo 'selected'; ?>>Sofas & armchairs</option>
            <option value="Tables & chairs" <?php if ($product['category'] == 'Tables & chairs') echo 'selected'; ?>>Tables & chairs</option>
            <option value="Storage & organisation" <?php if ($product['category'] == 'Storage & organisation') echo 'selected'; ?>>Storage & organisation</option>
            <option value="Office furniture" <?php if ($product['category'] == 'Office furniture') echo 'selected'; ?>>Office furniture</option>
            <option value="Beds & mattresses" <?php if ($product['category'] == 'Beds & mattresses') echo 'selected'; ?>>Beds & mattresses</option>
            <option value="Textiles" <?php if ($product['category'] == 'Textiles') echo 'selected'; ?>>Textiles</option>
            <option value="Rugs & mats & flooring" <?php if ($product['category'] == 'Rugs & mats & flooring') echo 'selected'; ?>>Rugs & mats & flooring</option>
            <option value="Home decoration" <?php if ($product['category'] == 'Home decoration') echo 'selected'; ?>>Home decoration</option>
            <option value="Lightning" <?php if ($product['category'] == 'Lightning') echo 'selected'; ?>>Lightning</option>
        </select><br><br>

        <label for="image_url">Image URL:</label><br>
        <input type="file" id="image_url" name="image_url[]" accept="image/*" multiple style="display: none;"><br><br>
        <div id="drop_zone">Drag and drop images here</div>
        <div id="imagePreview"></div><br><br>

        <label for="status">Status:</label><br>
        <select id="status" name="status">
            <option value="active" <?php if ($product['status'] == 'active') echo 'selected'; ?>>Active</option>
            <option value="inactive" <?php if ($product['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
            <option value="discontinued" <?php if ($product['status'] == 'discontinued') echo 'selected'; ?>>Discontinued</option>
        </select><br><br>

        <label for="discount">Discount(%):</label><br>
        <input type="number" step="0.01" id="discount" name="discount" value="<?php echo $product['discount']; ?>"><br><br>

        <label for="discounted_price">Discounted Price:</label><br>
        <input type="text" id="discounted_price" name="discounted_price" value="<?php echo $product['discounted_price']; ?>" readonly><br><br>

        <label for="weight">Weight:</label><br>
        <input type="number" step="0.01" id="weight" name="weight" value="<?php echo $product['weight']; ?>"><br><br>

        <label for="length">Length:</label><br>
        <input type="number" step="0.01" id="length" name="length" value="<?php echo $product['length']; ?>"><br><br>

        <label for="width">Width:</label><br>
        <input type="number" step="0.01" id="width" name="width" value="<?php echo $product['width']; ?>"><br><br>

        <label for="height">Height:</label><br>
        <input type="number" step="0.01" id="height" name="height" value="<?php echo $product['height']; ?>"><br><br>

        <label for="brand">Brand:</label><br>
        <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>"><br><br>

        <label for="color">Color:</label><br>
        <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($product['color']); ?>"><br><br>

        <label for="rating">Rating:</label><br>
        <input type="number" step="0.01" id="rating" name="rating" value="<?php echo $product['rating']; ?>"><br><br>

        <label for="reviews_count">Reviews Count:</label><br>
        <input type="number" id="reviews_count" name="reviews_count" value="<?php echo $product['reviews_count']; ?>"><br><br>

        <input type="submit" value="Update Product">
    </form>
</body>
</html>