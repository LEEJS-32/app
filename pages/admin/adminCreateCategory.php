<?php
ob_start(); // Start output buffering
include '../../_header.php'; 

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TESTING1";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
        // Check if the category already exists
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "Category already exists.";
        } else {
            // Insert the new category
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $category_name);

            if ($stmt->execute()) {
                $success_message = "Category created successfully.";
                $category_name = ""; // Clear the input field
            } else {
                $error_message = "Error creating category: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

$conn->close();
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