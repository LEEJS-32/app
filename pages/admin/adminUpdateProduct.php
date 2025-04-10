<?php
// filepath: c:\Users\shenl\app\pages\admin\adminUpdateProduct.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include '../../_header.php';
ob_start();

// Initialize product array to prevent undefined variable warnings
if (!isset($product)) {
    $product = [
        'product_id' => '',
        'name' => '',
        'description' => '',
        'price' => '',
        'stock' => '',
        'category' => '',
        'status' => '',
        'discount' => '',
        'weight' => '',
        'length' => '',
        'width' => '',
        'height' => '',
        'brand' => '',
        'color' => '',
        'rating' => '',
        'reviews_count' => '',
        'image_url' => '[]'
    ];
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TESTING1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

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
    $product_id = (int)$_POST['product_id'];
    $product_name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = (float)$_POST['price'] ?? 0.0;
    $stock = (int)$_POST['stock'] ?? 0;
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? '';
    $discount = (float)$_POST['discount'] ?? 0.0;
    $weight = (float)$_POST['weight'] ?? 0.0;
    $length = (float)$_POST['length'] ?? 0.0;
    $width = (float)$_POST['width'] ?? 0.0;
    $height = (float)$_POST['height'] ?? 0.0;
    $brand = $_POST['brand'] ?? '';
    $color = $_POST['color'] ?? '';
    $rating = (float)$_POST['rating'] ?? 0.0;
    $reviews_count = (int)$_POST['reviews_count'] ?? 0;

    // Calculate discounted price
    $discounted_price = $price - ($price * ($discount / 100));

    // Handle file uploads
    $image_urls = [];
    $upload_dir = "img/"; // Relative path from the web root
    $target_dir = __DIR__ . "/../../img/"; // Absolute path to the image directory

    // Get existing images from POST data
    $existing_images = isset($_POST['existing_images']) ? json_decode($_POST['existing_images'], true) : [];
    $image_urls = $existing_images; // Start with existing images

    // Process new uploads
    if (!empty($_FILES['image_url']['name'][0])) {
        foreach ($_FILES['image_url']['name'] as $key => $name) {
            if ($_FILES['image_url']['error'][$key] == 0) {
                $target_file = $target_dir . basename($name);
                $relative_path = $upload_dir . basename($name); // Relative path for database

                if (move_uploaded_file($_FILES['image_url']['tmp_name'][$key], $target_file)) {
                    $image_urls[] = $relative_path; // Store relative path
                } else {
                    echo "Sorry, there was an error uploading your file: $name<br>";
                }
            }
        }
    }

    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    // Convert to JSON with duplicate removal
    $image_urls_json = json_encode(array_values(array_unique($image_urls)), JSON_UNESCAPED_SLASHES);

    // Update database
    $sql = "UPDATE products SET
                name = '" . $product_name . "',
                description = '" . $description . "',
                price = " . $price . ",
                stock = " . $stock . ",
                category = '" . $category . "',
                status = '" . $status . "',
                discount = " . $discount . ",
                weight = " . $weight . ",
                length = " . $length . ",
                width = " . $width . ",
                height = " . $height . ",
                brand = '" . $brand . "',
                color = '" . $color . "',
                rating = " . $rating . ",
                reviews_count = " . $reviews_count . ",
                image_url = '" . $image_urls_json . "',
                updated_at = NOW()
            WHERE product_id = " . $product_id;

    if ($conn->query($sql) === TRUE) {
        echo "Product updated successfully";
        header("Location: adminProduct.php"); // Redirect
        exit;
    } else {
        echo "Error updating product: " . $conn->error;
    }
}
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
            cursor: pointer;
        }

        #drop_zone.dragover {
            border-color: #000;
            color: #000;
        }

        .image-preview {
            display: inline-block;
            margin: 10px;
            position: relative;
        }

        .image-preview img {
            max-width: 100px;
            max-height: 100px;
        }

        .remove-image {
            position: absolute;
            top: -10px;
            right: -10px;
            cursor: pointer;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 5px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <h1>Update Product</h1>

    <form id="updateProductForm" action="adminUpdateProduct.php?product_id=<?php echo $product['product_id']; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">

        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>"
            required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description"
            required><?php echo htmlspecialchars($product['description']); ?></textarea><br><br>

        <label for="price">Price:</label><br>
        <input type="number" step="0.01" id="price" name="price"
            value="<?php echo htmlspecialchars($product['price']); ?>" required><br><br>

        <label for="stock">Stock:</label><br>
        <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>"
            required><br><br>

        <label for="category">Category:</label><br>
        <select id="category" name="category" required>
            <option value="Sofas & armchairs" <?php echo $product['category'] == 'Sofas & armchairs' ? 'selected' : ''; ?>>Sofas & armchairs</option>
            <option value="Tables & chairs" <?php echo $product['category'] == 'Tables & chairs' ? 'selected' : ''; ?>>Tables & chairs</option>
            <option value="Storage & organisation" <?php echo $product['category'] == 'Storage & organisation' ? 'selected' : ''; ?>>Storage & organisation</option>
            <option value="Office furniture" <?php echo $product['category'] == 'Office furniture' ? 'selected' : ''; ?>>Office furniture</option>
            <option value="Beds & mattresses" <?php echo $product['category'] == 'Beds & mattresses' ? 'selected' : ''; ?>>Beds & mattresses</option>
            <option value="Textiles" <?php echo $product['category'] == 'Textiles' ? 'selected' : ''; ?>>Textiles</option>
            <option value="Rugs & mats & flooring" <?php echo $product['category'] == 'Rugs & mats & flooring' ? 'selected' : ''; ?>>Rugs & mats & flooring</option>
            <option value="Home decoration" <?php echo $product['category'] == 'Home decoration' ? 'selected' : ''; ?>>Home decoration</option>
            <option value="Lightning" <?php echo $product['category'] == 'Lightning' ? 'selected' : ''; ?>>Lightning</option>
        </select><br><br>

        <label for="image_url">Images:</label><br>
        <input type="file" id="image_url" name="image_url[]" accept="image/*" multiple><br><br>
        <div id="drop_zone">Click or drag images here</div>
        <div id="imagePreview">
            <?php
            $existing_images = json_decode($product['image_url'], true);
            if (!empty($existing_images)) {
                foreach ($existing_images as $image) {
                    $image_path = trim($image); // Remove surrounding quotes and brackets
                    echo "Current image value: " . htmlspecialchars($image) . "<br>";
                    echo "<div class='image-preview'>";
                    // Use a root-relative path
                    echo "<img src='/" . htmlspecialchars($image_path) . "' alt='Product Image'>";
                    echo "<span class='remove-image' data-src='" . htmlspecialchars($image_path) . "'>×</span>";
                    echo "</div>";
                }
            }
            ?>
        </div>

        <label for="status">Status:</label><br>
        <select id="status" name="status" required>
            <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="discontinued" <?php echo $product['status'] == 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
        </select><br><br>

        <label for="discount">Discount (%):</label><br>
        <input type="number" step="0.01" id="discount" name="discount"
            value="<?php echo htmlspecialchars($product['discount']); ?>"><br><br>

        <label for="weight">Weight (kg):</label><br>
        <input type="number" step="0.01" id="weight" name="weight"
            value="<?php echo htmlspecialchars($product['weight']); ?>"><br><br>

        <label for="length">Length (cm):</label><br>
        <input type="number" step="0.01" id="length" name="length"
            value="<?php echo htmlspecialchars($product['length']); ?>"><br><br>

        <label for="width">Width (cm):</label><br>
        <input type="number" step="0.01" id="width" name="width"
            value="<?php echo htmlspecialchars($product['width']); ?>"><br><br>

        <label for="height">Height (cm):</label><br>
        <input type="number" step="0.01" id="height" name="height"
            value="<?php echo htmlspecialchars($product['height']); ?>"><br><br>

        <label for="brand">Brand:</label><br>
        <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>"><br><br>

        <label for="color">Color:</label><br>
        <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($product['color']); ?>"><br><br>

        <label for="rating">Rating:</label><br>
        <input type="number" step="0.01" id="rating" name="rating"
            value="<?php echo htmlspecialchars($product['rating']); ?>"><br><br>

        <label for="reviews_count">Reviews Count:</label><br>
        <input type="number" id="reviews_count" name="reviews_count"
            value="<?php echo htmlspecialchars($product['reviews_count']); ?>"><br><br>

        <input type="submit" value="Update Product">
    </form>
    <button onclick="window.location.href='adminProduct.php'">Back to Product List</button>
    <script>
        $(document).ready(function () {
            let existingImages = <?php echo !empty($product['image_url']) ? $product['image_url'] : '[]'; ?>;
            existingImages = typeof existingImages === 'string' ? JSON.parse(existingImages) : existingImages;

            let uploadedFiles = new Set();

            function handleFiles(files) {
                let uniqueFiles = Array.from(files).filter(file => {
                    if (uploadedFiles.has(file.name)) {
                        return false;
                    }
                    uploadedFiles.add(file.name);
                    return true;
                });

                // Clear existing previews
                $('#imagePreview').empty();

                // Show existing images
                existingImages.forEach(imagePath => {
                    const preview = $('<div>').addClass('image-preview');
                    const img = $('<img>').attr('src', "/" + imagePath);
                    const removeBtn = $('<span>').addClass('remove-image').text('×').data('src', imagePath);
                    preview.append(img).append(removeBtn);
                    $('#imagePreview').append(preview);
                });

                // Add new image previews
                uniqueFiles.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const preview = $('<div>').addClass('image-preview');
                        const img = $('<img>').attr('src', e.target.result);
                        const removeBtn = $('<span>').addClass('remove-image').text('×');
                        preview.append(img).append(removeBtn);
                        $('#imagePreview').append(preview);
                    };
                    reader.readAsDataURL(file);
                });

                return uniqueFiles;
            }

            // Drag-and-drop functionality
            const dropZone = $('#drop_zone');

            dropZone.on('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.addClass('dragover');
            });

            dropZone.on('dragleave', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.removeClass('dragover');
            });

            dropZone.on('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                const uniqueFiles = handleFiles(files);

                // Add files to the input element
                const fileInput = $('#image_url')[0];
                const dt = new DataTransfer();
                uniqueFiles.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;
            });

            // Click to upload
            dropZone.click(function () {
                $('#image_url').click();
            });

            // File input change
            $('#image_url').change(function () {
                const uniqueFiles = handleFiles(this.files);
                const dt = new DataTransfer();
                uniqueFiles.forEach(file => dt.items.add(file));
                this.files = dt.files;
            });

            // Remove image
            $(document).on('click', '.remove-image', function () {
                $(this).parent('.image-preview').remove();
                const removedSrc = $(this).data('src');
                existingImages = existingImages.filter(src => src !== removedSrc);
            });

            // Form submit
            $('#updateProductForm').submit(function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                // Add existing images that weren't removed
                formData.append('existing_images', JSON.stringify(existingImages));

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        console.log('Success:', response);
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            });
        });
    </script>
</body>

</html>