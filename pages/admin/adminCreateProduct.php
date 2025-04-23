<?php
require_once '../../_base.php';

if (isset($_SESSION['form_errors'])) {
    foreach ($_SESSION['form_errors'] as $error) {
        echo "<p style='color: red;'>$error</p>";
    }
    unset($_SESSION['form_errors']); // Clear errors after displaying them
}

if (isset($_SESSION['form_data'])) {
    $form_data = $_SESSION['form_data'];
    unset($_SESSION['form_data']); // Clear form data after using it
} else {
    $form_data = [];
}

auth_user();
auth('admin');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TESTING1";
include '../../_header.php';

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
    $brand = $_POST['brand'];
    $color = $_POST['color'];
    // $rating = $_POST['rating'];
    // $reviews_count = $_POST['reviews_count'];

    // Calculate discounted price
    $discounted_price = $price - ($price * ($discount / 100));

    // Handle file uploads
    $image_urls = [];
    $target_dir = "../../img/";
    $_err = [];

    if (isset($_FILES['image_url']) && count($_FILES['image_url']['name']) > 0) {
        foreach ($_FILES['image_url']['name'] as $key => $fileName) { // Use $fileName instead of $name
            $fileType = $_FILES['image_url']['type'][$key];
            $fileSize = $_FILES['image_url']['size'][$key];
            $fileTmpName = $_FILES['image_url']['tmp_name'][$key];
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION); // Get the file extension

            if ($_FILES['image_url']['error'][$key] != 0) {
                $_err['photo'] = "Error uploading file: $fileName";
            } else if (!str_starts_with($fileType, 'image/')) {
                $_err['photo'] = "File $fileName must be an image.";
            } else if ($fileSize > 1 * 1024 * 1024) { // 1MB limit
                $_err['photo'] = "File $fileName exceeds the maximum size of 1MB.";
            } else {
                // Generate a unique filename using uniqid()
                $uniqueFileName = uniqid('img_', true) . '.' . $fileExtension;
                $target_file = $target_dir . $uniqueFileName;

                if (move_uploaded_file($fileTmpName, $target_file)) {
                    $image_urls[] = $target_file; // Store the unique file path
                } else {
                    $_err['photo'] = "Sorry, there was an error uploading your file: $fileName";
                }
            }
        }
    } else {
        $_err['photo'] = "At least one image is required.";
    }

    $video_url = null; // Default to null if no video is uploaded
    $target_video_dir = "../../videos/";
    if (isset($_FILES['video_url']) && $_FILES['video_url']['error'] == 0) {
        $videoFileName = $_FILES['video_url']['name'];
        $videoFileType = $_FILES['video_url']['type'];
        $videoFileSize = $_FILES['video_url']['size'];
        $videoTmpName = $_FILES['video_url']['tmp_name'];
        $videoFileExtension = pathinfo($videoFileName, PATHINFO_EXTENSION);

        // Validate video file type
        $allowedVideoTypes = ['mp4', 'avi', 'mov', 'wmv'];
        if (!in_array(strtolower($videoFileExtension), $allowedVideoTypes)) {
            $_err['video'] = "Invalid video format. Allowed formats: mp4, avi, mov, wmv.";
        } elseif ($videoFileSize > 10 * 1024 * 1024) { // 10MB limit
            $_err['video'] = "Video file size exceeds the maximum limit of 10MB.";
        } else {
            // Generate a unique filename for the video
            $uniqueVideoName = uniqid('video_', true) . '.' . $videoFileExtension;
            $target_video_file = $target_video_dir . $uniqueVideoName;

            if (move_uploaded_file($videoTmpName, $target_video_file)) {
                $video_url = $target_video_file; // Store the video file path
            } else {
                $_err['video'] = "Error uploading the video file.";
            }
        }
    }

    // If there are errors, store them in the session and redirect back to the same page
    if (!empty($_err)) {
        $_SESSION['form_errors'] = $_err;
        $_SESSION['form_data'] = $_POST; // Store form data in the session
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Convert image URLs array to JSON
    $name = $_POST['name']; 
    $image_urls_json = json_encode($image_urls);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category, image_url, video_url,status, discount, discounted_price, brand, color, created_at, updated_at) VALUES (?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    }
    $stmt->bind_param("ssdissssddss", $name, $description, $price, $stock, $category, $image_urls_json, $video_url,$status, $discount, $discounted_price, $brand, $color);
    // Execute the statement
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product added successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../../css/adminCreate.css">
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

            $("form").submit(function(event) {
                var discount = parseFloat($("#discount").val());
                // var rating = parseFloat($("#rating").val());
                var price = parseFloat($("#price").val());
                var stock = parseInt($("#stock").val());
                // var reviews_count = parseInt($("#reviews_count").val());

                var isValid = true;
                var errorMessage = "";

                if (isNaN(discount) || discount < 1 || discount > 100) {
                    isValid = false;
                    errorMessage += "Discount must be between 1 and 100.\n";
                }

                // if (isNaN(rating) || rating < 0 || rating > 5) {
                //     isValid = false;
                //     errorMessage += "Rating must be between 0 and 5.\n";
                // }

                if (discount < 0 || price < 0 || stock < 0 ) {
                    isValid = false;
                    errorMessage += "Values cannot be negative.\n";
                }

                if (!isValid) {
                    alert(errorMessage);
                    event.preventDefault();
                }
            });
                }
            }

            $("#price, #discount").on("input", calculateDiscountedPrice);

            $("form").submit(function(event) {
                var discount = parseFloat($("#discount").val());
                var price = parseFloat($("#price").val());
                var stock = parseInt($("#stock").val());

                var isValid = true;
                var errorMessage = "";

                if (isNaN(discount) || discount < 0 || discount > 100) {
                    isValid = false;
                    errorMessage += "Discount must be between 0 and 100.\n";
                }



                if (discount < 0 || price < 0 || stock < 0 ) {
                    isValid = false;
                    errorMessage += "Values cannot be negative.\n";
                }

                if (!isValid) {
                    alert(errorMessage);
                    event.preventDefault();
                }
            });

            $("#image_url").change(function() {
                handleFiles(this.files);
            });

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
    <h1>Add New Product</h1>
    <?php
    if (isset($_SESSION['success_message'])) {
        echo "<p style='color: green;'>" . $_SESSION['success_message'] . "</p>";
        unset($_SESSION['success_message']);
    }
    ?>
    <form action="adminCreateProduct.php" method="POST" enctype="multipart/form-data">
        <label for="name">Product Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : ''; ?>" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" required><?php echo isset($form_data['description']) ? htmlspecialchars($form_data['description']) : ''; ?></textarea><br><br>

        <label for="price">Price:</label><br>
        <input type="number" step="0.01" id="price" name="price" value="<?php echo isset($form_data['price']) ? htmlspecialchars($form_data['price']) : ''; ?>" required><br><br>

        <label for="stock">Stock:</label><br>
        <input type="number" id="stock" name="stock" value="<?php echo isset($form_data['stock']) ? htmlspecialchars($form_data['stock']) : ''; ?>" required><br><br>

        <label for="category">Category:</label><br>
        <select id="category" name="category">
            <option value="Sofas & armchairs" <?php echo (isset($form_data['category']) && $form_data['category'] == 'Sofas & armchairs') ? 'selected' : ''; ?>>Sofas & armchairs</option>
            <option value="Tables & chairs" <?php echo (isset($form_data['category']) && $form_data['category'] == 'Tables & chairs') ? 'selected' : ''; ?>>Tables & chairs</option>
            <option value="Storage & organisation" <?php echo (isset($form_data['category']) && $form_data['category'] == 'Storage & organisation') ? 'selected' : ''; ?>>Storage & organisation</option>
            <option value="Office furniture" <?php echo (isset($form_data['category']) && $form_data['category'] == 'Office furniture') ? 'selected' : ''; ?>>Office furniture</option>
            <option value="Beds & mattresses" <?php echo (isset($form_data['category']) && $form_data['category'] == 'Beds & mattresses') ? 'selected' : ''; ?>>Beds & mattresses</option>
            <option value="Textiles" <?php echo (isset($form_data['category']) && $form_data['category'] == 'Textiles') ? 'selected' : ''; ?>>Textiles</option>
            <option value="Rugs & mats & flooring" <?php echo (isset($form_data['category']) && $form_data['category'] == 'Rugs & mats & flooring') ? 'selected' : ''; ?>>Rugs & mats & flooring</option>
            <option value="Home decoration" <?php echo (isset($form_data['category']) && $form_data['category'] == 'Home decoration') ? 'selected' : ''; ?>>Home decoration</option>
            <option value="Lightning" <?php echo (isset($form_data['category']) && $form_data['category'] == 'Lightning') ? 'selected' : ''; ?>>Lightning</option>
        </select><br><br>

        <label for="image_url">Image URL:</label><br>
        <input type="file" id="image_url" name="image_url[]" accept="image/*" multiple style="display: none;"><br><br>
        <div id="drop_zone">Drag and drop images here</div>
        <div id="imagePreview"></div><br><br>

        <label for="video_url">Product Video:</label><br>
        <input type="file" id="video_url" name="video_url" accept="video/*"><br><br>

        <label for="status">Status:</label><br>
        <select id="status" name="status">
            <option value="active" <?php echo (isset($form_data['status']) && $form_data['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo (isset($form_data['status']) && $form_data['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            <option value="discontinued" <?php echo (isset($form_data['status']) && $form_data['status'] == 'discontinued') ? 'selected' : ''; ?>>Discontinued</option>
        </select><br><br>

        <label for="discount">Discount(%):</label><br>
        <input type="number" step="0.01" id="discount" name="discount" value="<?php echo isset($form_data['discount']) ? htmlspecialchars($form_data['discount']) : '0'; ?>"><br><br>

        <label for="discounted_price">Discounted Price:</label><br>
        <input type="text" id="discounted_price" name="discounted_price" readonly><br><br>

        <label for="brand">Brand:</label><br>
        <input type="text" id="brand" name="brand" value="<?php echo isset($form_data['brand']) ? htmlspecialchars($form_data['brand']) : ''; ?>"><br><br>

        <label for="color">Color:</label><br>
        <input type="text" id="color" name="color" value="<?php echo isset($form_data['color']) ? htmlspecialchars($form_data['color']) : ''; ?>"><br><br>

        <input type="submit" value="Add Product">
    </form>
    <button onclick="window.location.href='adminProduct.php'">Back to Product List</button>
</body>
</html>
