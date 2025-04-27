<?php

include ('../../_base.php');

// ----------------------------------------------------------------------------

// Verify admin privileges
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;

// Database connection
require '../../db/db_connect.php';

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = [];
$params = [];

if ($search) {
    $where[] = "(name LIKE :search OR email LIKE :search OR user_id LIKE :search)";
    $params[':search'] = "%$search%";
}

$sql = "SELECT user_id, name, email, role, status, avatar FROM users"; // Added photo field
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

try {
    $stm = $_db->prepare($sql);
    $stm->execute($params);
    $result = $stm->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
// ----------------------------------------------------------------------------
?>
<head>
    <title>Member Maintenance</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="../../css/admin_member.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <div id="info"><?= temp('info') ?></div>
    <header>
        <?php include __DIR__ . '/../../_header.php'; ?>
    </header>

    <?php
        try {
            // Fetch avatar from database
            $stm = $_db->prepare("SELECT * FROM users WHERE user_id = :user_id");
            $stm->execute([':user_id' => $user_id]);
            $row = $stm->fetch(PDO::FETCH_OBJ);
            
            $imageUrl = __DIR__ . "/../../img/avatar/avatar.jpg"; // Default avatar
            
            if ($row) {
                $email = $row->email;
                $name = $row->name;
                
                // If avatar exists, update the image URL
                if (!empty($row->avatar)) {
                    $imageUrl = $row->avatar;
                }
            }
        } catch (PDOException $e) {
            // Log error and continue with default avatar
            error_log("Error fetching avatar: " . $e->getMessage());
        }
    ?>

    <main>
    <div class="container">
        <div class="left">
            <div class="profile">
            <img src="../../img/avatar/<?= htmlspecialchars($imageUrl) ?>" alt="Profile" class="profile-avatar" />
                <div class="profile-text">
                    <h3><?= $name ?></h3>
                    <p><?= $role ?></p>
                </div>
            </div>

            <ul class="nav">
                <li><a href="admin_profile.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
                <li><a href="admin_members.php" class="active"><i class='bx bxs-user-account' ></i>Members</a></li>
                <li><a href="adminProduct.php"><i class='bx bx-chair'></i>Products</a></li>
                <li><a href="adminOrder.php"><i class='bx bx-food-menu'></i>Orders</a></li>
                <hr>
                <li><a href="admin_edit_profile.php"><i class='bx bxs-user-detail' ></i>Edit Profile</a></li>
                <li><a href="admin_reset_password.php"><i class='bx bx-lock-alt' ></i>Password</a></li>
            </ul>
        </div>

        <div class="divider"></div>

        <div class="right">
            <h1>Member Maintenance</h1>
            
            <!-- Search Form -->
            <form method="get" class="search-form">
                <input type="text" name="search" placeholder="Search by name, email or ID" value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>

            <!-- Member List -->
            <table class="member-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Photo</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?= $row->user_id ?></td>
                        <td><?= htmlspecialchars($row->name) ?></td>
                        <td><?= htmlspecialchars($row->email) ?></td>
                        <td><?= $row->role ?></td>
                        <td><?= $row->status ?></td>
                        <td>
                            <?php if (!empty($row->avatar)): ?>
                                <img src="../../img/avatar/<?= htmlspecialchars($row->avatar) ?>" 
                                     class="thumbnail popup-thumb"
                                     alt="User Photo">
                            <?php else: ?>
                                <span class="no-photo">No Photo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin_member_detail.php?id=<?= $row->user_id ?>" class="btn-view">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>  
    </div>

    <!-- Photo Modal -->
    <div id="popupModal" class="popup-modal">
        <span class="close">&times;</span>
        <img class="popup-modal-content" id="popupImage">
    </div>

    <script>
        // Add click handlers for all thumbnails
        document.querySelectorAll('.popup-thumb').forEach(img => {
            img.onclick = function() {
                const modal = document.getElementById('popupModal');
                const modalImg = document.getElementById('popupImage');
                modal.style.display = "block";
                modalImg.src = this.src;
            }
        });

        // Close modal handlers
        document.querySelector('.close').onclick = function() {
            document.getElementById('popupModal').style.display = "none";
        };

        window.onclick = function(event) {
            const modal = document.getElementById('popupModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
    </script>
    </main>

    <footer>
        <?php include __DIR__ . '/../../_footer.php'; ?>
    </footer>
</body>
</html>