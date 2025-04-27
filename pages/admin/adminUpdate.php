<?php
// filepath: c:\Users\shenl\OneDrive\Documents\app1\app\pages\admin\adminUpdateProduct.php
ob_start();
include '../../_base.php'; // Include base for $_db and other functions
// ----------------------------------------------------------------------------


// ----------------------------------------------------------------------------

// Member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;
// ----------------------------------------------------------------------------


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

// Fetch categories for the dropdown using PDO
try {
    $categories_stmt = $_db->query("SELECT id, name FROM categories ORDER BY name ASC");
    // Fetch all categories at once for the dropdown
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    try {
        $stmt = $_db->prepare("SELECT * FROM products WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product_data = $stmt->fetch(PDO::FETCH_ASSOC); // Use fetch directly
        if ($product_data) {
            $product = $product_data; // Assign fetched data to product array
        }
    } catch (PDOException $e) {
        die("Error fetching product details: " . $e->getMessage());
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $product_name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = (float)($_POST['price'] ?? 0.0); // Ensure float conversion
    $stock = (int)($_POST['stock'] ?? 0); // Ensure int conversion
    $category_id = (int)($_POST['category'] ?? 0); // Ensure int conversion
    $status = $_POST['status'] ?? '';
    $discount = (float)($_POST['discount'] ?? 0.0); // Ensure float conversion
    $brand = $_POST['brand'] ?? '';
    $color = $_POST['color'] ?? '';
    $rating = (float)($_POST['rating'] ?? 0.0); // Ensure float conversion
    $reviews_count = (int)($_POST['reviews_count'] ?? 0); // Ensure int conversion

    // Calculate discounted price
    $discounted_price = $price - ($price * ($discount / 100));

    // Handle file uploads
    $image_urls = [];
    $upload_dir = "img/"; // Relative path from the web root
    $target_dir = __DIR__ . "/../../img/"; // Absolute path to the image directory

    // Get existing images from POST data
    $existing_images = isset($_POST['existing_images']) ? json_decode($_POST['existing_images'], true) : [];
    if (!is_array($existing_images)) $existing_images = []; // Ensure it's an array
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
                    error_log("Error uploading file: $name");
                }
            }
        }
    }

    // Handle video upload
    $video_url = isset($_POST['existing_video_url']) ? $_POST['existing_video_url'] : ''; // Default to existing
    if (isset($_FILES['video_url']) && $_FILES['video_url']['error'] == 0) {
        $videoFileName = $_FILES['video_url']['name'];
        $videoTmpName = $_FILES['video_url']['tmp_name'];
        $videoFileExtension = pathinfo($videoFileName, PATHINFO_EXTENSION);

        $allowedVideoTypes = ['mp4', 'avi', 'mov', 'wmv'];
        if (!in_array(strtolower($videoFileExtension), $allowedVideoTypes)) {
            error_log("Invalid video format uploaded: $videoFileName");
        } else {
            $uniqueVideoName = uniqid('video_', true) . '.' . $videoFileExtension;
            $target_video_file = __DIR__ . "/../../videos/" . $uniqueVideoName; // Absolute path for the NEW video

            // --- Get the RELATIVE path of the current video from the database ---
            // We need to re-fetch the product data within the POST request
            // to ensure we have the *current* video URL before the update.
            $current_video_url_relative = '';
            try {
                $fetch_stmt = $_db->prepare("SELECT video_url FROM products WHERE product_id = :product_id");
                $fetch_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $fetch_stmt->execute();
                $current_video_url_relative = $fetch_stmt->fetchColumn(); // Get only the video_url column
            } catch (PDOException $e) {
                error_log("Error fetching current video URL for deletion: " . $e->getMessage());
                // Decide if you want to proceed without deleting or stop
            }

            // --- Construct the ABSOLUTE path for the OLD video ---
            // Combine the base directory with the relative path from the DB
            $current_video_path_absolute = null;
            if (!empty($current_video_url_relative)) {
                 // Make sure the relative path doesn't start with a slash if __DIR__ is used
                 $current_video_url_relative = ltrim($current_video_url_relative, '/');
                 $current_video_path_absolute = __DIR__ . "/../../" . $current_video_url_relative;
            }

            error_log("Attempting to upload new video to: " . $target_video_file); // Log new path
            error_log("Old video relative path from DB: " . ($current_video_url_relative ?: 'None')); // Log old relative path
            error_log("Calculated absolute path for old video: " . ($current_video_path_absolute ?: 'None')); // Log old absolute path

            if (move_uploaded_file($videoTmpName, $target_video_file)) {
                $video_url = "videos/" . $uniqueVideoName; // Store relative path of NEW video in DB

                error_log("New video uploaded successfully. Checking old video for deletion.");

                // --- Check and delete the OLD video ---
                if ($current_video_path_absolute && file_exists($current_video_path_absolute)) {
                    error_log("Old video exists at: " . $current_video_path_absolute . ". Attempting to delete.");
                    if (unlink($current_video_path_absolute)) {
                        error_log("Successfully deleted old video: " . $current_video_path_absolute);
                    } else {
                        error_log("!!! FAILED to delete old video: " . $current_video_path_absolute . ". Check permissions and path.");
                        // You might want to add specific error handling here
                    }
                } else if ($current_video_path_absolute) {
                     error_log("Old video path calculated, but file does not exist at: " . $current_video_path_absolute);
                } else {
                     error_log("No old video path found in database to delete.");
                }
            } else {
                error_log("!!! FAILED to upload new video file: $videoFileName");
            }
        }
    }

    // Convert images to JSON
    $image_urls_json = json_encode(array_values(array_unique($image_urls)), JSON_UNESCAPED_SLASHES);

    // Update database using PDO
    try {
        $stmt = $_db->prepare("
            UPDATE products SET
                name = :name, description = :description, price = :price, stock = :stock, category_id = :category_id,
                status = :status, discount = :discount, discounted_price = :discounted_price, brand = :brand,
                color = :color, rating = :rating, reviews_count = :reviews_count, image_url = :image_url,
                video_url = :video_url, updated_at = NOW()
            WHERE product_id = :product_id
        ");

        $params = [
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
            ':product_id' => $product_id
        ];

        if ($stmt->execute($params)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                "success" => true,
                "redirect" => "adminProduct.php",
                "message" => "Product updated successfully!"
            ]);
            exit;
        } else {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Statement execution failed.'
            ]);
            exit;
        }
    } catch (PDOException $e) {
        ob_clean();
        header('Content-Type: application/json');
        error_log("Database update error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Database update failed. Please check logs.'
        ]);
        exit;
    }
}

if (!headers_sent()) {
    include '../../_header.php';
} else {
    error_log("Headers already sent before including _header.php in adminUpdate.php");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../../css/adminCreate.css">
    <!-- <link rel="stylesheet" href="../../css/adminUpdateProduct.css"> -->
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
<div class="container">
        <div class="left">
                <?php include __DIR__ . '/admin_nav.php';?>
                </div>
            <div class="divider" ></div>
            <div class="right">
            <div class="form-container">

<h1>Update Product</h1>

    <form id="updateProductForm" action="adminUpdate.php?product_id=<?php echo htmlspecialchars($product['product_id'] ?? ''); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id'] ?? ''); ?>">
        <input type="hidden" id="existing_video_url" name="existing_video_url" value="<?php echo htmlspecialchars($product['video_url'] ?? ''); ?>">

        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea><br><br>

        <label for="price">Price:</label><br>
        <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required><br><br>

        <label for="stock">Stock:</label><br>
        <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock'] ?? ''); ?>" required><br><br>

        <label for="category">Category:</label><br>
        <select id="category" name="category" required>
            <option value="">Select a Category</option>
            <?php
            if (!empty($categories)) {
                foreach ($categories as $row) {
                    $selected = (($product['category_id'] ?? '') == $row['id']) ? 'selected' : '';
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
            $existing_images_json = $product['image_url'] ?? '[]';
            $existing_images_arr = json_decode($existing_images_json, true);
            if (is_array($existing_images_arr) && !empty($existing_images_arr)) {
                foreach ($existing_images_arr as $image) {
                    echo "<div class='image-preview'>";
                    $img_src = (strpos($image, '/') === 0) ? $image : '/' . $image;
                    echo "<img src='" . htmlspecialchars($img_src) . "' alt='Product Image'>";
                    echo "<span class='remove-image' data-src='" . htmlspecialchars($image) . "'>×</span>";
                    echo "</div>";
                }
            }
            ?>
        </div><br><br>

        <label for="video_url">Product Video:</label><br>
        <input type="file" id="video_url" name="video_url" accept="video/*"><br><br>
        <?php if (!empty($product['video_url'])):
            $vid_src = (strpos($product['video_url'], '/') === 0) ? $product['video_url'] : '/' . $product['video_url'];
        ?>
            <video controls style="max-width: 300px;">
                <source src="<?php echo htmlspecialchars($vid_src); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <p>Current Video: <?php echo htmlspecialchars($product['video_url']); ?></p>
        <?php endif; ?>

        <label for="status">Status:</label><br>
        <select id="status" name="status" required>
            <option value="active" <?php echo ($product['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo ($product['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="discontinued" <?php echo ($product['status'] ?? '') == 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
        </select><br><br>

        <label for="discount">Discount (%):</label><br>
        <input type="number" step="0.01" id="discount" name="discount" value="<?php echo htmlspecialchars($product['discount'] ?? ''); ?>"><br><br>

        <label for="brand">Brand:</label><br>
        <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($product['brand'] ?? ''); ?>"><br><br>

        <label for="color">Color:</label><br>
        <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($product['color'] ?? ''); ?>"><br><br>

        <label for="rating">Rating:</label><br>
        <input type="number" step="0.01" id="rating" name="rating" value="<?php echo htmlspecialchars($product['rating'] ?? ''); ?>"><br><br>

        <label for="reviews_count">Reviews Count:</label><br>
        <input type="number" id="reviews_count" name="reviews_count" value="<?php echo htmlspecialchars($product['reviews_count'] ?? ''); ?>"><br><br>

        <input type="submit" value="Update Product">
    </form>
    <button onclick="window.location.href='adminProduct.php'">Back to Product List</button>
            </div>
            </div>
</div>
    
    <script>
        $(document).ready(function () {
            let existingImages = <?php echo !empty($product['image_url']) ? $product['image_url'] : '[]'; ?>;
            if (typeof existingImages === 'string') {
                try {
                    existingImages = JSON.parse(existingImages);
                } catch (e) {
                    existingImages = [];
                    console.error("Error parsing existing images JSON:", e);
                }
            }
            if (!Array.isArray(existingImages)) {
                existingImages = [];
            }

            let uploadedFiles = new Set();

            function handleFiles(files) {
                const fileInput = $('#image_url')[0];
                const currentDt = new DataTransfer();

                if (fileInput.files) {
                    Array.from(fileInput.files).forEach(file => {
                        if (!uploadedFiles.has(file.name)) {
                            currentDt.items.add(file);
                            uploadedFiles.add(file.name);
                        }
                    });
                }

                Array.from(files).forEach(file => {
                    if (!uploadedFiles.has(file.name)) {
                        currentDt.items.add(file);
                        uploadedFiles.add(file.name);
                    }
                });

                fileInput.files = currentDt.files;
                updateImagePreviews();
            }

            function updateImagePreviews() {
                const previewContainer = $('#imagePreview');
                previewContainer.empty();

                existingImages.forEach(imagePath => {
                    const preview = $('<div>').addClass('image-preview');
                    const imgSrc = (imagePath.startsWith('/') ? '' : '/') + imagePath;
                    const img = $('<img>').attr('src', imgSrc).attr('alt', 'Existing Image');
                    const removeBtn = $('<span>').addClass('remove-image').text('×').data('src', imagePath);
                    preview.append(img).append(removeBtn);
                    previewContainer.append(preview);
                });

                const fileInput = $('#image_url')[0];
                if (fileInput.files) {
                    Array.from(fileInput.files).forEach(file => {
                        if (!existingImages.includes("img/" + file.name)) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                const preview = $('<div>').addClass('image-preview');
                                const img = $('<img>').attr('src', e.target.result).attr('alt', 'New Image');
                                const removeBtn = $('<span>').addClass('remove-new-image').text('×').data('filename', file.name);
                                preview.append(img).append(removeBtn);
                                previewContainer.append(preview);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }
            }

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
                handleFiles(e.originalEvent.dataTransfer.files);
            });

            dropZone.click(function () {
                $('#image_url').click();
            });

            $('#image_url').change(function () {
                handleFiles(this.files);
            });

            $(document).on('click', '.remove-image', function () {
                const removedSrc = $(this).data('src');
                existingImages = existingImages.filter(src => src !== removedSrc);
                $(this).parent('.image-preview').remove();
                updateImagePreviews();
            });

            $(document).on('click', '.remove-new-image', function () {
                const filenameToRemove = $(this).data('filename');
                const fileInput = $('#image_url')[0];
                const dt = new DataTransfer();

                Array.from(fileInput.files).forEach(file => {
                    if (file.name !== filenameToRemove) {
                        dt.items.add(file);
                    }
                });

                fileInput.files = dt.files;
                uploadedFiles.delete(filenameToRemove);
                $(this).parent('.image-preview').remove();
                updateImagePreviews();
            });

            updateImagePreviews();

            $('#updateProductForm').submit(function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                formData.append('existing_images', JSON.stringify(existingImages));

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        console.log('Raw server response:', response);
                        console.log('Type of response:', typeof response);

                        try {
                            const jsonResponse = (typeof response === 'string') ? JSON.parse(response) : response;

                            console.log('Server response:', jsonResponse);
                            if (jsonResponse.success) {
                                alert(jsonResponse.message);
                                window.location.href = jsonResponse.redirect;
                            } else {
                                alert('Error: ' + (jsonResponse.error || 'Unknown error occurred.'));
                            }
                        } catch (e) {
                            alert('Invalid server response format.');
                            console.error('Error processing response:', e, 'Raw response was:', response);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', status, error, xhr.responseText);
                        alert('An error occurred while updating the product. Check console for details.');
                    }
                });
            });
        });
    </script>
</body>
</html>