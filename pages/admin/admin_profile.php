<?php

include ('../../_base.php');

// ----------------------------------------------------------------------------

//member role
auth_user();
auth('admin');

$user = $_SESSION['user'];
$user_id = $user['user_id'];
$name = $user['name'];
$role = $user['role'];

// ----------------------------------------------------------------------------
?>
</script>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin_profile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script defer src="../../js/webcam.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <header>
        <?php 
            include __DIR__ . '/../../_header.php'; 
        ?>
    </header>

    <main>
    <?php
        require '../../db/db_connect.php';
    
        // Fetch avatar from database
        $sql = "SELECT avatar FROM users WHERE user_id = '$user_id'";
        $result = $conn->query($sql);
        $imageUrl = __DIR__ . "/../../img/avatar/avatar.jpg"; // Default avatar
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
        // If avatar exists, update the image URL
            if (!empty($row["avatar"])) {
                $imageUrl = $row["avatar"];
            }
        }

        // Total active products
        $result_products = $conn->query("SELECT COUNT(*) AS total_products FROM products WHERE status = 'active'");
        $total_products = $result_products->fetch_assoc()['total_products'];

        // Total members
        $result_members = $conn->query("SELECT COUNT(*) AS total_members FROM users WHERE role = 'member'");
        $total_members = $result_members->fetch_assoc()['total_members'];

        // Total revenue from all completed orders
        $result_revenue = $conn->query("SELECT SUM(amount) AS total_revenue FROM payments WHERE payment_status IN ('Completed')");
        $total_revenue = $result_revenue->fetch_assoc()['total_revenue'] ?? 0;

        // Current month (YYYY-MM)
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));

        // Total products sold this month
        $res = $conn->query("
            SELECT SUM(oi.quantity) AS total_sold
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            WHERE DATE_FORMAT(o.order_date, '%Y-%m') = '$currentMonth'
            AND o.status NOT IN ('pending', 'cancelled')
        ");
        $total_sold = (int)($res->fetch_assoc()['total_sold'] ?? 0);

        // Total products sold last month
        $res = $conn->query("
            SELECT SUM(oi.quantity) AS total_sold
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            WHERE DATE_FORMAT(o.order_date, '%Y-%m') = '$lastMonth'
            AND o.status NOT IN ('pending', 'cancelled')
        ");
        $last_month_sold = (int)($res->fetch_assoc()['total_sold'] ?? 0);

        // Revenue this month
        $res = $conn->query("
            SELECT SUM(amount) AS total_revenue
            FROM payments p
            JOIN orders o ON p.order_id = o.order_id
            WHERE DATE_FORMAT(o.order_date, '%Y-%m') = '$currentMonth'
            AND p.payment_status = 'Completed'
        ");
        $revenue = (float)($res->fetch_assoc()['total_revenue'] ?? 0);

        // Revenue last month
        $res = $conn->query("
            SELECT SUM(amount) AS total_revenue
            FROM payments p
            JOIN orders o ON p.order_id = o.order_id
            WHERE DATE_FORMAT(o.order_date, '%Y-%m') = '$lastMonth'
            AND p.payment_status = 'Completed'
        ");
        $last_revenue = (float)($res->fetch_assoc()['total_revenue'] ?? 0);

        // Percentage change helpers
        function percentChange($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return round((($current - $previous) / $previous) * 100, 1);
        }

        $sold_change = percentChange($total_sold, $last_month_sold);
        $revenue_change = percentChange($revenue, $last_revenue);

    ?>
    <div class="container">
        <div class="left">
            <div class="profile">
                <img src="../../img/avatar/avatar.jpg" alt="User Avatar">
                <div class="profile-text">
                    <h3><?php echo ($name); ?></h3>
                    <p><?php echo ($role); ?></p>
                </div>
            </div>

            <ul class="nav">
                <li><a href="admin_profile.php" class="active"><i class='bx bxs-dashboard'></i>DashBoard</a></li>
                <li><a href="admin_members.php"><i class='bx bxs-user-account' ></i>Members</a></li>
                <li><a href="products.php"><i class='bx bx-chair'></i>Products</a></li>
                <li><a href="#"><i class='bx bx-food-menu'></i>Orders</a></li>
            </ul>
        </div>

        <div class="divider"></div>

        <div class="right">
            <h1>Dashboard</h1>
            <div class="categories">
                <div class="category">
                    <ul>
                        <li>Selling Products</li>
                        <li><?= $total_products ?></li>
                    </ul>
                </div>
                <div class="category">
                    <ul>
                        <li>Members</li>
                        <li><?= $total_members ?></li>
                    </ul>
                </div>
                <div class="category">
                    <ul>
                        <li>Total Revenue</li>
                        <li>RM <?= number_format($total_revenue, 2) ?></li>
                    </ul>
                </div>
            </div>

            <div class="overview">
                <div class="total">
                    <div class="section1">
                        <h2>Financial Income</h2>
                    </div>
                    <div class="section2">
                        <h3>Total Products Sold</h3>
                        <h1><?= $total_sold ?></h1>
                        <p><?= ($sold_change >= 0 ? "Increased" : "Decreased") ?> <?= abs($sold_change) ?>% from last month</p>
                    </div>
                    <div class="section3">
                        <h3>Total Revenue</h3>
                        <h1>RM <?= number_format($revenue, 2) ?></h1>
                        <p><?= ($revenue_change >= 0 ? "Increased" : "Decreased") ?> <?= abs($revenue_change) ?>% from last month</p>
                    </div>
                </div>

                <div class="chart">
                    <canvas id="myChart"></canvas> <!-- Canvas to render the line chart -->
                </div>
            </div>
        </div>
    </div>

    <?php
    // Members registered by month (last 6 months)
$members_per_month = [];
$sales_per_month = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    
    // Members count
    $res = $conn->query("SELECT COUNT(*) AS count FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'");
    $members_per_month[] = (int)$res->fetch_assoc()['count'];

    // Products sold
    $res = $conn->query("
        SELECT SUM(order_items.quantity) AS sold 
        FROM orders 
        JOIN order_items ON orders.order_id = order_items.order_id 
        WHERE DATE_FORMAT(orders.order_date, '%Y-%m') = '$month'
        AND orders.status NOT IN ('pending', 'cancelled')
    ");
    $sales_per_month[] = (int)($res->fetch_assoc()['sold'] ?? 0);
}

$month_labels = [];
for ($i = 5; $i >= 0; $i--) {
    $month_labels[] = date('M', strtotime("-$i months"));
}
?>

    <script>
    const months = <?= json_encode($month_labels) ?>;
    const membersData = <?= json_encode($members_per_month) ?>;
    const productsData = <?= json_encode($sales_per_month) ?>;

    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Members Registered',
                    data: membersData,
                    borderColor: 'red',
                    fill: false,
                    tension: 0.1,
                    borderWidth: 2
                },
                {
                    label: 'Products Sold',
                    data: productsData,
                    borderColor: 'blue',
                    fill: false,
                    tension: 0.1,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>


    </main>
    <footer>
        <?php
            include __DIR__ . '/../../_footer.php';
        ?>
    </footer>
</body>
    