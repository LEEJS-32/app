<?php
require_once '../../_base.php';
include __DIR__ . '/../../_header.php';

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

// Member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;


// Fetch categories
try {
    $categories_stmt = $_db->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $categories_stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
    $status = $_POST['status'];
    $discount = $_POST['discount'];
    $brand = $_POST['brand'];
    $color = $_POST['color'];

    // Validate category
    $category_exists = false;
    try {
        $category_stmt = $_db->prepare("SELECT id FROM categories WHERE id = ?");
        $category_stmt->execute([$category]);
        $category_exists = $category_stmt->fetch();
    } catch (PDOException $e) {
        die("Error validating category: " . $e->getMessage());
    }

    if (!$category_exists) {
        $_err['category'] = "Selected category does not exist.";
    }

    // Calculate discounted price
    $discounted_price = $price - ($price * ($discount / 100));

    // Handle file uploads
    $image_urls = [];
    $target_dir = "../../img/";
    $_err = [];

    if (isset($_FILES['image_url']) && count($_FILES['image_url']['name']) > 0) {
        foreach ($_FILES['image_url']['name'] as $key => $fileName) {
            $fileType = $_FILES['image_url']['type'][$key];
            $fileSize = $_FILES['image_url']['size'][$key];
            $fileTmpName = $_FILES['image_url']['tmp_name'][$key];
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

            if ($_FILES['image_url']['error'][$key] != 0) {
                $_err['photo'] = "Error uploading file: $fileName";
            } else if (!str_starts_with($fileType, 'image/')) {
                $_err['photo'] = "File $fileName must be an image.";
            } else if ($fileSize > 1 * 1024 * 1024) { // 1MB limit
                $_err['photo'] = "File $fileName exceeds the maximum size of 1MB.";
            } else {
                $uniqueFileName = uniqid('img_', true) . '.' . $fileExtension;
                $target_file = $target_dir . $uniqueFileName;

                if (move_uploaded_file($fileTmpName, $target_file)) {
                    $image_urls[] = $target_file;
                } else {
                    $_err['photo'] = "Sorry, there was an error uploading your file: $fileName";
                }
            }
        }
    } else {
        $_err['photo'] = "At least one image is required.";
    }

    $video_url = null;
    $target_video_dir = "../../videos/";
    if (isset($_FILES['video_url']) && $_FILES['video_url']['error'] == 0) {
        $videoFileName = $_FILES['video_url']['name'];
        $videoFileType = $_FILES['video_url']['type'];
        $videoFileSize = $_FILES['video_url']['size'];
        $videoTmpName = $_FILES['video_url']['tmp_name'];
        $videoFileExtension = pathinfo($videoFileName, PATHINFO_EXTENSION);

        $allowedVideoTypes = ['mp4', 'avi', 'mov', 'wmv'];
        if (!in_array(strtolower($videoFileExtension), $allowedVideoTypes)) {
            $_err['video'] = "Invalid video format. Allowed formats: mp4, avi, mov, wmv.";
        } elseif ($videoFileSize > 10 * 1024 * 1024) { // 10MB limit
            $_err['video'] = "Video file size exceeds the maximum limit of 10MB.";
        } else {
            $uniqueVideoName = uniqid('video_', true) . '.' . $videoFileExtension;
            $target_video_file = $target_video_dir . $uniqueVideoName;

            if (move_uploaded_file($videoTmpName, $target_video_file)) {
                $video_url = $target_video_file;
            } else {
                $_err['video'] = "Error uploading the video file.";
            }
        }
    }

    if (!empty($_err)) {
        $_SESSION['form_errors'] = $_err;
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $image_urls_json = json_encode($image_urls);

    // Insert product into the database
    try {
        $stm = $_db->prepare("
            INSERT INTO products 
            (name, description, price, stock, category_id, image_url, video_url, status, discount, discounted_price, brand, color, created_at, updated_at) 
            VALUES (:name, :description, :price, :stock, :category_id, :image_url, :video_url, :status, :discount, :discounted_price, :brand, :color, NOW(), NOW())
        ");

        $stm->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':stock' => $stock,
            ':category_id' => $category,
            ':image_url' => $image_urls_json,
            ':video_url' => $video_url,
            ':status' => $status,
            ':discount' => $discount,
            ':discounted_price' => $discounted_price,
            ':brand' => $brand,
            ':color' => $color,
        ]);

        $_SESSION['success_message'] = "Product added successfully!";
        temp('info', 'Product added successfully!');
        // header('Location: adminProduct.php'); // Redirect to another page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        die("Error inserting product: " . $e->getMessage());
    }
}
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
    <script>
        $(document).ready(function () {
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

            // Use a Map to track files by unique name
            let filesMap = new Map();

            function updateFileInput() {
                const dt = new DataTransfer();
                filesMap.forEach(file => dt.items.add(file));
                $('#image_url')[0].files = dt.files;
            }

            function renderPreviews() {
                $('#imagePreview').empty();
                filesMap.forEach((file, key) => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const preview = $('<div>').addClass('image-preview');
                        const img = $('<img>').attr('src', e.target.result);
                        const removeBtn = $('<span>').addClass('remove-image').text('×').attr('data-key', key);
                        preview.append(img).append(removeBtn);
                        $('#imagePreview').append(preview);
                    };
                    reader.readAsDataURL(file);
                });
            }

            function handleFiles(files) {
                Array.from(files).forEach(file => {
                    if (!filesMap.has(file.name)) {
                        filesMap.set(file.name, file);
                    }
                });
                renderPreviews();
                updateFileInput();
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
                handleFiles(e.originalEvent.dataTransfer.files);
            });

            // Click to upload
            dropZone.click(function () {
                $('#image_url').click();
            });

            // File input change
            $('#image_url').change(function () {
                handleFiles(this.files);
            });

            // Remove image
            $(document).on('click', '.remove-image', function () {
                const key = $(this).attr('data-key');
                filesMap.delete(key);
                renderPreviews();
                updateFileInput();
            });
        });
    </script>
</head>
<body>
    <h1>Add New Product</h1>
<div class="container">
    <div class="left">
      <?php include __DIR__ . '/admin_nav.php'; ?>
    </div>
    <div class="divider"></div>

    <div class="right">
        <div class="form-container">

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
        <select id="category" name="category" required>
            <option value="">Select a Category</option>
            <?php
            if (!empty($categories)) {
                foreach ($categories as $row) {
                    $selected = (isset($form_data['category']) && $form_data['category'] == $row->id) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row->id) . "' $selected>" . htmlspecialchars($row->name) . "</option>";
                }
            } else {
                echo "<option value=''>No categories available</option>";
            }
            ?>
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
    </div>
    </div>
</div>
</body>

<footer>
        <?php
            include __DIR__ . '/../../_footer.php';
        ?>
    </footer>
</html>
