<?php
// filepath: c:\Users\shenl\OneDrive\Documents\app1\app\pages\admin\adminUpdateProduct.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include '../../_header.php';
ob_start();

// Initialize product array to prevent undefined variable warnings
$product = [
    'product_id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'stock' => '',
    'category_id' => '',
    'status' => '',
    'discount' => '',
    'brand' => '',
    'color' => '',
    'rating' => '',
    'reviews_count' => '',
    'image_url' => '[]',
    'video_url' => ''
];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TESTING1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch categories for the dropdown
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if (!$categories_result) {
    die("Error fetching categories: " . $conn->error);
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
    $category_id = (int)$_POST['category'] ?? 0;
    $status = $_POST['status'] ?? '';
    $discount = (float)$_POST['discount'] ?? 0.0;
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

    // Process new image uploads
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

    // Handle video upload
    if (isset($_FILES['video_url']) && $_FILES['video_url']['error'] == 0) {
        $videoFileName = $_FILES['video_url']['name'];
        $videoTmpName = $_FILES['video_url']['tmp_name'];
        $videoFileExtension = pathinfo($videoFileName, PATHINFO_EXTENSION);

        $allowedVideoTypes = ['mp4', 'avi', 'mov', 'wmv'];
        if (!in_array(strtolower($videoFileExtension), $allowedVideoTypes)) {
            echo "Invalid video format. Allowed formats: mp4, avi, mov, wmv.<br>";
        } else {
            $uniqueVideoName = uniqid('video_', true) . '.' . $videoFileExtension;
            $target_video_file = __DIR__ . "/../../videos/" . $uniqueVideoName;

            // Unlink the old video if it exists
            if (!empty($product['video_url']) && file_exists(__DIR__ . "/../../" . $product['video_url'])) {
                unlink(__DIR__ . "/../../" . $product['video_url']);
            }

            if (move_uploaded_file($videoTmpName, $target_video_file)) {
                $video_url = "videos/" . $uniqueVideoName; // Store relative path
            } else {
                echo "Error uploading the video file.<br>";
            }
        }
    } else {
        // Preserve the existing video URL if no new video is uploaded
        $video_url = isset($_POST['existing_video_url']) ? $_POST['existing_video_url'] : '';
    }

    // Convert images to JSON
    $image_urls_json = json_encode(array_values(array_unique($image_urls)), JSON_UNESCAPED_SLASHES);

    // Update database
    $stmt = $conn->prepare("
        UPDATE products SET
            name = ?, description = ?, price = ?, stock = ?, category_id = ?, status = ?, discount = ?, 
            discounted_price = ?, brand = ?, color = ?, rating = ?, reviews_count = ?, image_url = ?, 
            video_url = ?, updated_at = NOW()
        WHERE product_id = ?
    ");
    $stmt->bind_param(
        "ssdiisddssiissi",
        $product_name,
        $description,
        $price,
        $stock,
        $category_id,
        $status,
        $discount,
        $discounted_price,
        $brand,
        $color,
        $rating,
        $reviews_count,
        $image_urls_json,
        $video_url,
        $product_id
    );

    if ($stmt->execute()) {
        temp('info', 'Product updated successfully!');
        echo json_encode([
            'success' => true,
            'redirect' => 'adminProduct.php',
            'message' => temp('info') // Include the flash message in the response
        ]);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'error' => $stmt->error
        ]);
        exit;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../../css/adminUpdateProduct.css">
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
        <input type="hidden" id="existing_video_url" name="existing_video_url" value="<?php echo htmlspecialchars($product['video_url']); ?>">

        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea><br><br>

        <label for="price">Price:</label><br>
        <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required><br><br>

        <label for="stock">Stock:</label><br>
        <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required><br><br>

        <label for="category">Category:</label><br>
        <select id="category" name="category" required>
            <option value="">Select a Category</option>
            <?php
            if ($categories_result->num_rows > 0) {
                while ($row = $categories_result->fetch_assoc()) {
                    $selected = ($product['category_id'] == $row['id']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
                }
            } else {
                echo "<option value=''>No categories available</option>";
            }
            ?>
        </select><br><br>

        <label for="image_url">Images:</label><br>
        <input type="file" id="image_url" name="image_url[]" accept="image/*" multiple><br><br>
        <div id="drop_zone">Click or drag images here</div>
        <div id="imagePreview">
            <?php
            $existing_images = json_decode($product['image_url'], true);
            if (!empty($existing_images)) {
                foreach ($existing_images as $image) {
                    echo "<div class='image-preview'>";
                    echo "<img src='/" . htmlspecialchars($image) . "' alt='Product Image'>";
                    echo "<span class='remove-image' data-src='" . htmlspecialchars($image) . "'>×</span>";
                    echo "</div>";
                }
            }
            ?>
        </div><br><br>

        <label for="video_url">Product Video:</label><br>
        <input type="file" id="video_url" name="video_url" accept="video/*"><br><br>
        <?php if (!empty($product['video_url'])): ?>
            <video controls style="max-width: 300px;">
                <source src="/<?php echo htmlspecialchars($product['video_url']); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <p>Current Video: <?php echo htmlspecialchars($product['video_url']); ?></p>
        <?php endif; ?>

        <label for="status">Status:</label><br>
        <select id="status" name="status" required>
            <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="discontinued" <?php echo $product['status'] == 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
        </select><br><br>

        <label for="discount">Discount (%):</label><br>
        <input type="number" step="0.01" id="discount" name="discount" value="<?php echo htmlspecialchars($product['discount']); ?>"><br><br>

        <label for="brand">Brand:</label><br>
        <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>"><br><br>

        <label for="color">Color:</label><br>
        <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($product['color']); ?>"><br><br>

        <label for="rating">Rating:</label><br>
        <input type="number" step="0.01" id="rating" name="rating" value="<?php echo htmlspecialchars($product['rating']); ?>"><br><br>

        <label for="reviews_count">Reviews Count:</label><br>
        <input type="number" id="reviews_count" name="reviews_count" value="<?php echo htmlspecialchars($product['reviews_count']); ?>"><br><br>

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
                        const jsonResponse = JSON.parse(response);
                        if (jsonResponse.success) {
                            alert(jsonResponse.message);
                            window.location.href ("adminProduct.php");
                        } else {
                            alert('Error: ' + jsonResponse.error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        alert('An error occurred while updating the product.');
                    }
                });
            });
        });
    </script>
</body>
</html>