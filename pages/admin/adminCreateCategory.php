<?php
require_once '../../_base.php'; // Includes $_db and potentially session_start()
// Authentication checks

// Member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;



// Initialize variables for form repopulation and messages
$create_category_name = $_SESSION['form_data']['create_category_name'] ?? ''; // Use specific name for create form
unset($_SESSION['form_data']); // Clear after use

$_err = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']); // Clear after use

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']); // Clear after use


// Handle form submission (Create or Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST['action'] ?? 'create'; // Default to create

    if ($action === 'create') {
        // --- Handle Create ---
        $_SESSION['form_data'] = $_POST; // Store only create form data
        $category_name = trim($_POST['create_category_name'] ?? '');

        if (empty($category_name)) {
            $_err['create_category_name'] = "Category name is required.";
        } else {
            try {
                $stmt = $_db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(:name)");
                $stmt->execute([':name' => $category_name]);

                if ($stmt->fetch()) {
                    $_err['create_category_name'] = "Category '" . htmlspecialchars($category_name) . "' already exists.";
                } else {
                    $stmt = $_db->prepare("INSERT INTO categories (name) VALUES (:name)");
                    $stmt->execute([':name' => $category_name]);
                    temp('info', "Category '" . htmlspecialchars($category_name) . "' created successfully!");                    unset($_SESSION['form_data']); // Clear form data on success
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Error creating category: " . $e->getMessage());
                $_err['db_error_create'] = "Database error occurred while creating the category.";
            }
        }
    } elseif ($action === 'update') {
        // --- Handle Update ---
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $new_category_name = trim($_POST['update_category_name'] ?? '');

        if (empty($new_category_name)) {
             // Use category_id to make error message specific
            $_err['update_category_name_' . $category_id] = "Category name cannot be empty.";
        } elseif ($category_id === false || $category_id <= 0) {
             $_err['db_error_update'] = "Invalid category ID for update.";
        } else {
            try {
                // Check if the new name already exists for a DIFFERENT category
                $stmt = $_db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(:name) AND id != :id");
                $stmt->execute([':name' => $new_category_name, ':id' => $category_id]);

                if ($stmt->fetch()) {
                    $_err['update_category_name_' . $category_id] = "Another category with the name '" . htmlspecialchars($new_category_name) . "' already exists.";
                } else {
                    // Perform the update
                    $stmt = $_db->prepare("UPDATE categories SET name = :name WHERE id = :id");
                    $stmt->execute([':name' => $new_category_name, ':id' => $category_id]);

                    // Check if any row was actually updated
                    if ($stmt->rowCount() > 0) {
                        temp('info', "Category updated to '" . htmlspecialchars($new_category_name) . "' successfully!");
                    } else {
                        // Optionally inform if the name was the same
                        temp('info', "Category name was already '" . htmlspecialchars($new_category_name) . "'. No changes made.");
                    }
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Error updating category ID $category_id: " . $e->getMessage());
                $_err['db_error_update'] = "Database error occurred while updating the category.";
            }
        }
    }

    // If errors occurred during create or update, redirect back
    if (!empty($_err)) {
        $_SESSION['form_errors'] = $_err;
        // $_SESSION['form_data'] is already set for create action if needed
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// --- Fetch existing categories for the Edit section ---
try {
    $categories_stmt = $_db->query("SELECT id, name FROM categories ORDER BY name ASC");
    $existing_categories = $categories_stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    error_log("Error fetching existing categories: " . $e->getMessage());
    $existing_categories = [];
    // Set an error message for the user about fetching categories
    $_err['fetch_error'] = "Could not load existing categories for editing.";
}


// --- Include Header AFTER potential redirects ---
include __DIR__ . '/../../_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Categories</title>
    <!-- Use the shared admin create CSS -->
    <link rel="stylesheet" href="../../css/adminCreate.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'> <!-- For icons if needed -->
    <style>
        /* Add any page-specific overrides here if necessary */
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px; /* Adjust spacing */
            margin-bottom: 10px;
            display: block; /* Ensure it takes space */
        }
        .success-message {
             color: green;
             font-weight: bold;
             margin-bottom: 15px;
             text-align: center;
             border: 1px solid #c3e6cb; /* Light green border */
             background-color: #d4edda; /* Light green background */
             padding: 10px;
             border-radius: 4px;
        }
        /* Adjust form container width if needed */
        .form-container {
            max-width: 500px; /* Smaller width for simpler form */
            margin-top: 10px; /* Add space above the create form */
            margin-bottom: 40px; /* Existing space below create form */
        }
        h2 {
            margin-top: 30px; /* Add space above headings */
            margin-bottom: 15px; /* Add space below headings */
        }
        hr {
            margin-top: 40px; /* Space above the separator */
            margin-bottom: 40px; /* Space below the separator */
            border: 0;
            border-top: 1px solid #eee; /* Style the separator */
        }
        .edit-category-list {
            max-width: 600px;
            margin: 10px auto 30px auto; /* Adds 10px top, 30px bottom margin, keeps auto left/right */
        }
        .edit-category-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
         .edit-category-item:last-child {
            border-bottom: none;
        }
        .edit-category-item form {
            display: flex;
            align-items: center;
            gap: 10px; /* Space between input and button */
            margin: 0; /* Remove default form margin */
            padding: 0;
            border: none;
            box-shadow: none;
            background: none;
        }
         .edit-category-item input[type="text"] {
            width: auto; /* Adjust width */
            flex-grow: 1; /* Allow input to grow */
            margin-bottom: 0; /* Remove margin from input */
            padding: 8px; /* Smaller padding */
        }
         .edit-category-item button {
            padding: 8px 15px; /* Smaller padding */
            margin-top: 0; /* Remove margin from button */
            white-space: nowrap; /* Prevent button text wrapping */
        }
        /* Add margin below the last button */
        .right > button[type="button"] {
             margin-top: 30px;
             margin-bottom: 20px; /* Add space at the very bottom */
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Navigation Sidebar -->
        <div class="left">
            <?php include __DIR__ . '/admin_nav.php'; ?>
        </div>

        <div class="divider"></div> <!-- Divider between left and right -->
        <!-- Right Content Area -->
        <div class="right">
            <h1>Manage Categories</h1>

            <?php
            // Display general success/error messages if set
            if (!empty($success_message)) {
                echo "<div class='success-message'>" . htmlspecialchars($success_message) . "</div>";
            }
            if (isset($_err['db_error_create'])) {
                echo "<p class='error-message'>" . htmlspecialchars($_err['db_error_create']) . "</p>";
            }
             if (isset($_err['db_error_update'])) {
                echo "<p class='error-message'>" . htmlspecialchars($_err['db_error_update']) . "</p>";
            }
             if (isset($_err['fetch_error'])) {
                echo "<p class='error-message'>" . htmlspecialchars($_err['fetch_error']) . "</p>";
            }
            ?>

            <!-- Create Category Section -->
            <h2>Create New Category</h2>
            <div class="form-container">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <input type="hidden" name="action" value="create"> <!-- Identify action -->
                    <label for="create_category_name">Category Name:</label>
                    <input type="text" id="create_category_name" name="create_category_name" value="<?php echo htmlspecialchars($create_category_name); ?>" required>
                    <?php if (isset($_err['create_category_name'])): ?><p class="error-message"><?php echo $_err['create_category_name']; ?></p><?php endif; ?>

                    <button type="submit">Create Category</button>
                </form>
            </div>

            <hr> <!-- Separator -->

            <!-- Edit Category Section -->
            <h2>Edit Existing Categories</h2>
            <div class="edit-category-list">
                <?php if (!empty($existing_categories)): ?>
                    <?php foreach ($existing_categories as $category): ?>
                        <div class="edit-category-item">
                            <span><?php echo htmlspecialchars($category->name); ?> (ID: <?php echo $category->id; ?>)</span>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="category_id" value="<?php echo $category->id; ?>">
                                <input type="text" name="update_category_name" value="<?php echo htmlspecialchars($category->name); ?>" required aria-label="New name for <?php echo htmlspecialchars($category->name); ?>">
                                <button type="submit">Update</button>
                                <?php
                                // Display specific update error for this category
                                $update_error_key = 'update_category_name_' . $category->id;
                                if (isset($_err[$update_error_key])):
                                ?>
                                    <p class="error-message" style="flex-basis: 100%; margin-left: 10px;"><?php echo $_err[$update_error_key]; ?></p>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php elseif (empty($_err['fetch_error'])): ?>
                    <p>No categories found.</p>
                <?php endif; ?>
                <button type="button" onclick="window.location.href='adminProduct.php'">Back to Product List</button>

            </div>


            <br><br>
            <!-- Consider linking back to a category management page if one exists -->
        </div> <!-- /right -->
    </div> <!-- /container -->

    <?php include __DIR__ . '/../../_footer.php'; ?>

</body>
</html>