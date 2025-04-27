<?php
include '../../_header.php';
require_once '../../_base.php';
ob_start();


// ----------------------------------------------------------------------------

// Member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user['user_id'];
$name = $user['name'];
$role = $user['role'];

// ----------------------------------------------------------------------------

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

// Fetch categories for the dropdown
try {
    $categories_stmt = $_db->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Fetch product details for editing
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    try {
        $stm = $_db->prepare("SELECT * FROM products WHERE product_id = :product_id");
        $stm->execute([':product_id' => $product_id]);
        $product = $stm->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            die("Product not found.");
        }
    } catch (PDOException $e) {
        die("Error fetching product: " . $e->getMessage());
    }
}

// Handle form submission for updating the product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
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
            if (!empty($product['video_url'])) {
                $videoPath = __DIR__ . "/../../videos/" . basename($product['video_url']);
                if (file_exists($videoPath)) {
                    if (unlink($videoPath)) {
                        echo "Existing video deleted successfully.<br>";
                    } else {
                        echo "Failed to delete the existing video.<br>";
                    }
                } else {
                    echo "Video file does not exist: " . $videoPath . "<br>";
                }
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
    try {
        $stm = $_db->prepare("
            UPDATE products SET
                name = :name, description = :description, price = :price, stock = :stock, category_id = :category_id, 
                status = :status, discount = :discount, discounted_price = :discounted_price, brand = :brand, 
                color = :color, rating = :rating, reviews_count = :reviews_count, image_url = :image_url, 
                video_url = :video_url, updated_at = NOW()
            WHERE product_id = :product_id
        ");

        $stm->execute([
            ':name' => $product_name,
            ':description' => $description,
            ':price' => $price,
            ':stock' => $stock,
            ':category_id' => $category_id,
            ':status' => $status,
            ':discount' => $discount,
            ':discounted_price' => $discounted_price,
            ':brand' => $brand,
            ':color' => $color,
            ':rating' => $rating,
            ':reviews_count' => $reviews_count,
            ':image_url' => $image_urls_json,
            ':video_url' => $video_url,
            ':product_id' => $product_id,
        ]);

        echo "Product updated successfully!";
        header("Location: adminProduct.php");
        exit;
    } catch (PDOException $e) {
        die("Error updating product: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="../../css/adminUpdateProduct.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>
    <div class="container">
        <div class="Left">
        <?php include __DIR__ . '/../../_nav.php'; ?>    
        </div>
    </div class ="divider"></div>
        <div class="right">
   
    <h1>Update Product</h1>
    <form id="updateProductForm" action="adminUpdateProduct.php?product_id=<?php echo htmlspecialchars($product['product_id']); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required><br>

        <label for="brand">Brand:</label>
        <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" required><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea><br>

        <label for="price">Price:</label>
        <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required><br>

        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required><br>

        <label for="color">Color:</label>
<input type="text" id="color" name="color" value="<?php echo htmlspecialchars($product['color']); ?>" required><br>

        <label for="category">Category:</label>
        <select id="category" name="category" required>
            <option value="">Select a Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="active" <?php echo ($product['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo ($product['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            <option value="discontinued" <?php echo ($product['status'] == 'discontinued') ? 'selected' : ''; ?>>Discontinued</option>
        </select><br>

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

        <input type="submit" value="Update Product">
    </form>
    <button onclick="window.location.href='adminProduct.php'">Back to Product List</button>
        </div> <!-- End of right div -->
        </div><!-- End of container div -->

    <script>
        $(document).ready(function () {
            let existingImages = <?php echo !empty($product['image_url']) ? $product['image_url'] : '[]'; ?>;
            existingImages = typeof existingImages === 'string' ? JSON.parse(existingImages) : existingImages;

            console.log('Initial existingImages:', existingImages); // Debugging line

            const dropZone = $('#drop_zone');
            const fileInput = $('#image_url');
            const previewContainer = $('#imagePreview');

            // Drag-and-drop functionality
            dropZone.on('click', function () {
                fileInput.click();
            });

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
                handleFiles(files);
            });

            fileInput.on('change', function () {
                const files = fileInput[0].files;
                handleFiles(files);
            });

            function handleFiles(files) {
                Array.from(files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const imageUrl = e.target.result;
                            const previewHtml = `
                                <div class="image-preview">
                                    <img src="${imageUrl}" alt="Uploaded Image">
                                    <span class="remove-image" data-src="${imageUrl}">×</span>
                                </div>
                            `;
                            previewContainer.append(previewHtml);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Remove image
            $(document).on('click', '.remove-image', function () {
                console.log('Remove button clicked'); // Debugging line
                const removedSrc = $(this).siblings('img').attr('src');
                console.log('Image to remove:', removedSrc); // Debugging line
                $(this).parent('.image-preview').remove();

                // Update the hidden input field for existing images
                existingImages = existingImages.filter(src => src !== removedSrc);
                console.log('Updated existingImages:', existingImages); // Debugging line
                $('#existing_images').val(JSON.stringify(existingImages));
                console.log('Updated hidden input value:', $('#existing_images').val()); // Debugging line
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
                            window.location.href = jsonResponse.redirect;
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