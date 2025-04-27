
<link rel="stylesheet" href="../../css/style.css">
<link rel="stylesheet" href="../../css/admin_profile.css">
<link rel="stylesheet" href="../../css/nav.css">

<div class="left">
    <button id="navToggle" class="nav-toggle">
        <i class='bx bx-menu'></i>
    </button>
    <div class="profile">
        <img src="../../img/avatar/avatar.jpg" alt="User Avatar">
        <div class="profile-text">
            <h3><?php echo htmlspecialchars($name); ?></h3>
            <p><?php echo htmlspecialchars($role); ?></p>
        </div>
    </div>

    <ul class="nav">
        <li><a href="admin_profile.php"><i class='bx bxs-dashboard'></i>DashBoard</a></li>
        <li><a href="admin_members.php"><i class='bx bxs-user-account'></i>Members</a></li>
        <li><a href="adminProduct.php" class="active"><i class='bx bx-chair'></i>Products</a></li>
        <li><a href="#"><i class='bx bx-food-menu'></i>Orders</a></li>
    </ul>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const navToggle = document.getElementById('navToggle');
        const leftContainer = document.querySelector('.left');

        navToggle.addEventListener('click', function () {
            leftContainer.classList.toggle('collapsed');
        });
    });
</script>