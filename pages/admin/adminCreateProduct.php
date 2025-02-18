<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

            $("form").submit(function(event) {
                var discount = parseFloat($("#discount").val());
                var rating = parseFloat($("#rating").val());
                var price = parseFloat($("#price").val());
                var stock = parseInt($("#stock").val());
                var weight = parseFloat($("#weight").val());
                var length = parseFloat($("#length").val());
                var width = parseFloat($("#width").val());
                var height = parseFloat($("#height").val());
                var reviews_count = parseInt($("#reviews_count").val());

                var isValid = true;
                var errorMessage = "";

                if (isNaN(discount) || discount < 1 || discount > 100) {
                    isValid = false;
                    errorMessage += "Discount must be between 1 and 100.\n";
                }

                if (isNaN(rating) || rating < 0 || rating > 5) {
                    isValid = false;
                    errorMessage += "Rating must be between 0 and 5.\n";
                }

                if (discount < 0 || rating < 0 || price < 0 || stock < 0 || weight < 0 || length < 0 || width < 0 || height < 0 || reviews_count < 0) {
                    isValid = false;
                    errorMessage += "Values cannot be negative.\n";
                }

                if (!isValid) {
                    alert(errorMessage);
                    event.preventDefault();
                }
            });

            $("#image_url").change(function() {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("#imagePreview").attr("src", e.target.result);
                    $("#imagePreview").show();
                }
                reader.readAsDataURL(this.files[0]);
            });
        });
    </script>
</head>
<body>
    <h1>Add New Product</h1>
    <form action="adminCreateProduct.php" method="POST" enctype="multipart/form-data">
        <label for="name">Product Name:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="price">Price:</label><br>
        <input type="number" step="0.01" id="price" name="price" required><br><br>

        <label for="stock">Stock:</label><br>
        <input type="number" id="stock" name="stock" required><br><br>

        <label for="category">Category:</label><br>
        <select id="category" name="category">
            <option value="Sofas & armchairs">Sofas & armchairs</option>
            <option value="Tables & chairs">Tables & chairs</option>
            <option value="Storage & organisation">Storage & organisation</option>
            <option value="Office furniture">Office furniture</option>
            <option value="Beds & mattresses">Beds & mattresses</option>
            <option value="Textiles">Textiles</option>
            <option value="Rugs & mats & flooring">Rugs & mats & flooring</option>
            <option value="Home decoration">Home decoration</option>
            <option value="Lightning">Lightning</option>
        </select><br><br>

        <label for="image_url">Image URL:</label><br>
        <input type="file" id="image_url" name="image_url" accept="image/*"><br><br>
        <img id="imagePreview" src="#" alt="Image Preview" style="display: none; max-width: 200px; max-height: 200px;"><br><br>

        <label for="status">Status:</label><br>
        <select id="status" name="status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="discontinued">Discontinued</option>
        </select><br><br>

        <label for="discount">Discount(%):</label><br>
        <input type="number" step="0.01" id="discount" name="discount"><br><br>

        <label for="discounted_price">Discounted Price:</label><br>
        <input type="text" id="discounted_price" name="discounted_price" readonly><br><br>

        <label for="weight">Weight:</label><br>
        <input type="number" step="0.01" id="weight" name="weight"><br><br>

        <label for="length">Length:</label><br>
        <input type="number" step="0.01" id="length" name="length"><br><br>

        <label for="width">Width:</label><br>
        <input type="number" step="0.01" id="width" name="width"><br><br>

        <label for="height">Height:</label><br>
        <input type="number" step="0.01" id="height" name="height"><br><br>

        <label for="brand">Brand:</label><br>
        <input type="text" id="brand" name="brand"><br><br>

        <label for="color">Color:</label><br>
        <input type="text" id="color" name="color"><br><br>

        <label for="rating">Rating:</label><br>
        <input type="number" step="0.01" id="rating" name="rating"><br><br>

        <label for="reviews_count">Reviews Count:</label><br>
        <input type="number" id="reviews_count" name="reviews_count"><br><br>

        <input type="submit" value="Add Product">
    </form>
</body>
</html>

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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
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

    // Handle file upload
    $image_url = "";
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $target_dir = "../../img/";
        $target_file = $target_dir . basename($_FILES["image_url"]["name"]);
        if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    $sql = "INSERT INTO products (name, description, price, stock, category, image_url, status, discount, discounted_price, weight, length, width, height, brand, color, rating, reviews_count, created_at, updated_at)
            VALUES ('$name', '$description', '$price', '$stock', '$category', '$image_url', '$status', '$discount', '$discounted_price', '$weight', '$length', '$width', '$height', '$brand', '$color', '$rating', '$reviews_count', NOW(), NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "New product added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>