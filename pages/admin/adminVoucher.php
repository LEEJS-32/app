<?php
include ('../../_base.php');

// ----------------------------------------------------------------------------
// Member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;

// Fetch vouchers
try {
    $stm = $_db->prepare("SELECT v.voucher_id, v.code, v.type, v.value, v.quantity, v.start_date, v.end_date, v.description, u.name AS user_name
                          FROM vouchers v
                          LEFT JOIN users u ON v.user_id = u.user_id
                          ORDER BY v.start_date DESC");
    $stm->execute();
    $vouchers = $stm->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Error fetching vouchers: " . $e->getMessage());
}

// Handle form submission for creating a new voucher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $code = $_POST['code'];
    $type = $_POST['type'];
    $value = $_POST['value'];
    $quantity = $_POST['quantity'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'];
    $user_id = $_POST['user_id'] ?? null; // Optional: Assign to a specific user

    try {
        if (empty($user_id)) {
            // If "Public" is selected, create the voucher for all members
            $stm_users = $_db->query("SELECT user_id FROM users WHERE role = 'member'");
            $members = $stm_users->fetchAll(PDO::FETCH_OBJ);

            foreach ($members as $member) {
                // Append user_id and a timestamp to the code to make it unique
                $unique_code = $code . '-' . $member->user_id . '-' . time();

                $stm = $_db->prepare("INSERT INTO vouchers (code, type, value, quantity, start_date, end_date, description, user_id)
                                      VALUES (:code, :type, :value, :quantity, :start_date, :end_date, :description, :user_id)");
                $stm->execute([
                    ':code' => $unique_code,
                    ':type' => $type,
                    ':value' => $value,
                    ':quantity' => $quantity,
                    ':start_date' => $start_date,
                    ':end_date' => $end_date,
                    ':description' => $description,
                    ':user_id' => $member->user_id
                ]);
            }
        } else {
            // Create the voucher for a specific user
            $stm = $_db->prepare("INSERT INTO vouchers (code, type, value, quantity, start_date, end_date, description, user_id)
                                  VALUES (:code, :type, :value, :quantity, :start_date, :end_date, :description, :user_id)");
            $stm->execute([
                ':code' => $code,
                ':type' => $type,
                ':value' => $value,
                ':quantity' => $quantity,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
                ':description' => $description,
                ':user_id' => $user_id
            ]);
        }

        header("Location: adminVoucher.php");
        exit();
    } catch (PDOException $e) {
        die("Error creating voucher: " . $e->getMessage());
    }
}

// Handle voucher deletion via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $voucher_id = $_POST['voucher_id'];

    try {
        // Delete the voucher
        $stm = $_db->prepare("DELETE FROM vouchers WHERE voucher_id = :voucher_id");
        $stm->execute([':voucher_id' => $voucher_id]);

        // Fetch the updated vouchers
        $stm = $_db->prepare("SELECT v.voucher_id, v.code, v.type, v.value, v.quantity, v.start_date, v.end_date, v.description, u.name AS user_name
                              FROM vouchers v
                              LEFT JOIN users u ON v.user_id = u.user_id
                              ORDER BY v.start_date DESC");
        $stm->execute();
        $vouchers = $stm->fetchAll(PDO::FETCH_OBJ);

        // Return the updated table rows
        foreach ($vouchers as $voucher) {
            echo "<tr>
                    <td>" . htmlspecialchars($voucher->code) . "</td>
                    <td>" . htmlspecialchars($voucher->type === 'rm' ? 'RM' : '%') . "</td>
                    <td>" . htmlspecialchars($voucher->value) . "</td>
                    <td>" . htmlspecialchars($voucher->quantity) . "</td>
                    <td>" . htmlspecialchars($voucher->start_date) . "</td>
                    <td>" . htmlspecialchars($voucher->end_date) . "</td>
                    <td>" . htmlspecialchars($voucher->description) . "</td>
                    <td>" . htmlspecialchars($voucher->user_name ?? 'Public') . "</td>
                    <td>
                        <button class='delete-btn' onclick='deleteVoucher(" . htmlspecialchars($voucher->voucher_id) . ")'>Delete</button>
                    </td>
                  </tr>";
        }
        exit(); // Stop further processing
    } catch (PDOException $e) {
        echo "Error deleting voucher: " . $e->getMessage();
        exit();
    }
}
?>

<head>
    <title>Admin Voucher Management</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="../../css/member/member.css">
    <link rel="stylesheet" href="../../css/admin/adminVoucher.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <header>
        <?php include __DIR__ . '/../../_header.php'; ?>
    </header>

    <main>
        <div class="container">
            <div class="left">
                <div class="profile">
                    <img src="../../img/avatar/<?= htmlspecialchars($imageUrl) ?>" alt="Profile" class="profile-avatar" />
                    <div class="profile-text">
                        <h3><?= htmlspecialchars($name); ?></h3>
                        <p><?= htmlspecialchars($role); ?></p>
                    </div>
                </div>

                <ul class="nav">
                    <li><a href="admin_profile.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
                    <li><a href="admin_members.php"><i class='bx bxs-user-account'></i>Members</a></li>
                    <li><a href="products.php"><i class='bx bx-chair'></i>Products</a></li>
                    <li><a href="adminOrder.php"><i class='bx bx-food-menu'></i>Orders</a></li>
                    <li><a href="adminVoucher.php" class="active"><i class='bx bx-gift'></i>Vouchers</a></li>
                    <hr>
                    <li><a href="admin_edit_profile.php"><i class='bx bxs-user-detail' ></i>Edit Profile</a></li>
                    <li><a href="admin_reset_password.php"><i class='bx bx-lock-alt' ></i>Password</a></li>
                </ul>
            </div>

            <div class="divider"></div>

            <div class="right">
                <h1>Voucher Management</h1>

                <!-- Buttons to toggle between table and form -->
                <div class="actions">
                    <button id="show-table-btn">View Vouchers</button>
                    <button id="show-form-btn">Create New Voucher</button>
                </div>

                <!-- Voucher Table -->
                <div id="voucher-table" style="display: none;">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Quantity</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Description</th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="voucher-table-body">
                            <?php foreach ($vouchers as $voucher): ?>
                                <tr>
                                    <td><?= htmlspecialchars($voucher->code); ?></td>
                                    <td><?= htmlspecialchars($voucher->type === 'rm' ? 'RM' : '%'); ?></td>
                                    <td><?= htmlspecialchars($voucher->value); ?></td>
                                    <td><?= htmlspecialchars($voucher->quantity); ?></td>
                                    <td><?= htmlspecialchars($voucher->start_date); ?></td>
                                    <td><?= htmlspecialchars($voucher->end_date); ?></td>
                                    <td><?= htmlspecialchars($voucher->description); ?></td>
                                    <td><?= htmlspecialchars($voucher->user_name ?? 'Public'); ?></td>
                                    <td>
                                        <button class="delete-btn" onclick="deleteVoucher(<?= htmlspecialchars($voucher->voucher_id); ?>)">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Create Voucher Form -->
                <div id="voucher-form" style="display: none;">
                    <h2>Create New Voucher</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        <label>Code:</label>
                        <input type="text" name="code" required>

                        <label>Type:</label>
                        <select name="type" required>
                            <option value="rm">RM</option>
                            <option value="percent">%</option>
                        </select>

                        <label>Value:</label>
                        <input type="number" name="value" step="0.01" required>

                        <label>Quantity:</label>
                        <input type="number" name="quantity" required>

                        <label>Start Date:</label>
                        <input type="date" name="start_date" required>

                        <label>End Date:</label>
                        <input type="date" name="end_date" required>

                        <label>Description:</label>
                        <textarea name="description" required></textarea>

                        <label>Assign to User (Optional):</label>
                        <select name="user_id">
                            <option value="">Public</option>
                            <?php
                            // Fetch only users with the role 'member'
                            $users = $_db->query("SELECT user_id, name FROM users WHERE role = 'member'")->fetchAll(PDO::FETCH_OBJ);
                            foreach ($users as $user) {
                                echo "<option value='" . htmlspecialchars($user->user_id) . "'>" . htmlspecialchars($user->name) . "</option>";
                            }
                            ?>
                        </select>

                        <button type="submit">Create Voucher</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <?php include __DIR__ . '/../../_footer.php'; ?>
    </footer>

    <script>
        // JavaScript to toggle between table and form
        document.getElementById('show-table-btn').addEventListener('click', function () {
            const table = document.getElementById('voucher-table');
            const form = document.getElementById('voucher-form');

            if (table.style.display === 'block') {
                table.style.display = 'none'; // Collapse the table
            } else {
                table.style.display = 'block'; // Show the table
                form.style.display = 'none';  // Hide the form
            }
        });

        document.getElementById('show-form-btn').addEventListener('click', function () {
            const table = document.getElementById('voucher-table');
            const form = document.getElementById('voucher-form');

            if (form.style.display === 'block') {
                form.style.display = 'none'; // Collapse the form
            } else {
                form.style.display = 'block'; // Show the form
                table.style.display = 'none'; // Hide the table
            }
        });

        // AJAX-based deletion
        function deleteVoucher(voucherId) {
            if (confirm("Are you sure you want to delete this voucher?")) {
                $.ajax({
                    url: "adminVoucher.php",
                    type: "POST",
                    data: { action: "delete", voucher_id: voucherId },
                    success: function(response) {
                        // Update the table body with the new rows
                        $("#voucher-table-body").html(response);
                    },
                    error: function() {
                        alert("Error deleting voucher. Please try again.");
                    }
                });
            }
        }

        // Ensure neither section is displayed by default
        window.onload = function () {
            document.getElementById('voucher-table').style.display = 'none';
            document.getElementById('voucher-form').style.display = 'none';
        };
    </script>
</body>
</html>