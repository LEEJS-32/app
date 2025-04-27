<?php
require '../../_base.php';
auth_user();
auth('admin');

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user = $_SESSION['user'];
$user_id = $user->user_id;
$name = $user->name;
$role = $user->role;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = [];
$params = [];

if ($search) {
    $where[] = "(name LIKE ? OR email LIKE ? OR user_id LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
}

try {
    $sql = "SELECT user_id, name, email, role, status, avatar FROM users";
    if ($where) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    $stm = $_db->prepare($sql);
    if ($params) {
        $stm->execute($params);
    } else {
        $stm->execute();
    }
    $users = $stm->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $users = [];
}
?>

<head>
    <title>Member Maintenance</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="../../css/style2.css">
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
            <img src="../../img/avatar/<?= htmlspecialchars($avatar) ?>" alt="Profile" class="profile-avatar" />
                <div class="profile-text">
                    <h3><?= htmlspecialchars($name) ?></h3>
                    <p><?= htmlspecialchars($role) ?></p>
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
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user->user_id) ?></td>
                        <td><?= htmlspecialchars($user->name) ?></td>
                        <td><?= htmlspecialchars($user->email) ?></td>
                        <td><?= htmlspecialchars($user->role) ?></td>
                        <td><?= ucfirst($user->status) ?></td>
                        <td>
                            <?php if (!empty($user->profile_photo)): ?>
                                <img src="../../img/avatar/<?= htmlspecialchars($user->avatar) ?>" 
                                     class="thumbnail popup-thumb"
                                     alt="User Photo">
                            <?php else: ?>
                                <span class="no-photo">No Photo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin_member_detail.php?id=<?= $user->user_id ?>" class="btn-view">View Details</a>
                            
                            <!-- Block/Unblock User Form -->
                            <form method="post" action="block_unblock_user.php" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="user_id" value="<?= $user->user_id ?>">
                                
                                <?php if ($user->status === 'active'): ?>
                                    <button type="submit" name="action" value="block" class="btn-block">Block</button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="unblock" class="btn-unblock">Unblock</button>
                                <?php endif; ?>
                            </form>

                            <!-- Delete User Form -->
                            <form method="post" action="delete_user.php" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="user_id" value="<?= $user->user_id ?>">
                                <button type="submit" name="action" value="delete" class="btn delete">Delete</button>
                            </form>
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