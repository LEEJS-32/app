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
                        <li>3,000</li>
                    </ul>
                </div>
                <div class="category">
                    <ul>
                        <li>Members</li>
                        <li>2,000</li>
                    </ul>
                </div>
                <div class="category">
                    <ul>
                        <li>Total Revenue</li>
                        <li>RM 100,000</li>
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
                    <h1>227</h1>
                    <p>Increased 10% from last month</p>
                    </div>
                    <div class="section3">
                    <h3>Total Revenue</h3>
                    <h1>RM 35,300</h1>
                    <p>Increased 15% from last month</p>
                    </div>
                </div>
                <div class="chart">
                    <canvas id="myChart"></canvas> <!-- Canvas to render the line chart -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Generate last 6 months dynamically
        function getLastSixMonths() {
            let months = [];
            let currentMonth = new Date();
            for (let i = 0; i < 6; i++) {
                months.unshift(currentMonth.toLocaleString('default', { month: 'short' }));
                currentMonth.setMonth(currentMonth.getMonth() - 1); // Move to previous month
            }
            return months;
        }

        var months = getLastSixMonths(); // Get the last 6 months    

        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line', // Line chart type
            data: {
                labels: months, // X-axis labels (months)
                datasets: [{
                    label: 'Members Registered', // First line label
                    data: [50, 100, 150, 200, 250, 300], // Example data for members registered
                    borderColor: 'red', // Line color for members registered
                    backgroundColor: 'red',
                    fill: false, // Fill the area under the line
                    tension: 0.1, // Curve the line slightly
                    borderWidth: 2 // Line width
                },
                {
                    label: 'Products Sold', // Second line label
                    data: [30, 80, 120, 170, 220, 280], // Example data for products sold
                    borderColor: 'blue', // Line color for products sold
                    backgroundColor: 'blue',
                    fill: false, // Fill the area under the line
                    tension: 0.1, // Curve the line slightly
                    borderWidth: 2 // Line width
                }]
            },
            options: {
                responsive: true, // Make the chart responsive
                scales: {
                    y: {
                        beginAtZero: true // Start Y-axis at 0
                    }
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
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
    