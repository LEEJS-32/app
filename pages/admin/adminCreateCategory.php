<?php
ob_start(); // Start output buffering
include '../../_header.php'; 
require_once '../../_base.php';


// Initialize variables
$category_name = "";
$error_message = "";
$success_message = "";


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['category_name']);

    // Validate input
    if (empty($category_name)) {
        $error_message = "Category name is required.";
    } else {
        try {
            // Check if the category already exists
            $stmt = $_db->prepare("SELECT id FROM categories WHERE name = :name");
            $stmt->execute([':name' => $category_name]);

            if ($stmt->fetch()) {
                $error_message = "Category already exists.";
            } else {
                // Insert the new category
                $stmt = $_db->prepare("INSERT INTO categories (name) VALUES (:name)");
                $stmt->execute([':name' => $category_name]);

                $success_message = "Category created successfully.";
                $category_name = ""; // Clear the input field
            }
        } catch (PDOException $e) {
            $error_message = "Error creating category: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Create Category</title>
    <link rel="stylesheet" href="../../css/adminCreateCategory.css"> <!-- External CSS -->
</head>
<body>
    <h1>Create New Category</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <form action="adminCreateCategory.php" method="POST">
        <label for="category_name">Category Name:</label><br>
        <input type="text" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category_name); ?>" required><br><br>
        <button type="submit">Create Category</button>
    </form>

    <br>
    <button onclick="window.location.href='adminProduct.php'">Back to Product List</button>
</body>
</html>