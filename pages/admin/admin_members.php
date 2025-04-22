<?php

include ('../../_base.php');

// ----------------------------------------------------------------------------

// Verify admin privileges
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user['user_id'];
$name = $user['name'];
$role = $user['role'];

// Database connection
require '../../db/db_connect.php';

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = [];
$params = [];

if ($search) {
    $where[] = "(name LIKE ? OR email LIKE ? OR user_id LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
}

$sql = "SELECT user_id, name, email, role, status, avatar FROM users"; // Added photo field
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
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
    <header>
        <?php include __DIR__ . '/../../_header.php'; ?>
    </header>

    <main>
    <div class="container">
        <div class="left">
            <div class="profile">
                <img src="../../img/avatar/avatar.jpg" alt="User Avatar">
                <div class="profile-text">
                    <h3><?= $name ?></h3>
                    <p><?= $role ?></p>
                </div>
            </div>

            <ul class="nav">
                <li><a href="admin_profile.php"><i class='bx bxs-dashboard'></i>DashBoard</a></li>
                <li><a href="admin_members.php" class="active"><i class='bx bxs-user-account'></i>Members</a></li>
                <li><a href="products.php"><i class='bx bx-chair'></i>Products</a></li>
                <li><a href="#"><i class='bx bx-food-menu'></i>Orders</a></li>
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
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['user_id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= $row['role'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td>
                            <?php if (!empty($row['profile_photo'])): ?>
                                <img src="../../uploads/profile_photos/<?= htmlspecialchars($row['profile_photo']) ?>" 
                                     class="thumbnail popup-thumb"
                                     alt="User Photo">
                            <?php else: ?>
                                <span class="no-photo">No Photo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin_member_detail.php?id=<?= $row['user_id'] ?>" class="btn-view">View Details</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
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