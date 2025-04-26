<?php
include ('../../_base.php');
include ('../../db/db_connect.php'); // Include the database connection file
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user['user_id'];
$name = $user['name'];
$role = $user['role'];


$user_id = req('id');

try {
    $stm = $_db->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stm->execute([':user_id' => $user_id]);
    $member = $stm->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        die("Member not found");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<head>
    <title>Member Detail</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
<div id="info"><?= temp('info') ?></div>
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
                <h1>Member Detail</h1>
                <a href="admin_members.php" class="btn-back">← Back to List</a>

                <div class="member-detail">
                    <div class="detail-item">
                        <label>User ID:</label>
                        <span><?= htmlspecialchars($member['user_id']) ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Name:</label>
                        <span><?= htmlspecialchars($member['name']) ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label>
                        <span><?= htmlspecialchars($member['email']) ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Role:</label>
                        <span><?= htmlspecialchars($member['role']) ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Status:</label>
                        <span><?= htmlspecialchars($member['status']) ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Gender:</label>
                        <span><?= htmlspecialchars($member['gender']) ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Phone Number:</label>
                        <span><?= htmlspecialchars($member['phonenum']) ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Occupation:</label>
                        <span><?= htmlspecialchars($member['occupation']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <?php include __DIR__ . '/../../_footer.php'; ?>
    </footer>
</body>
</html>