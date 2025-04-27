<?php
require '../_base.php';
auth_user();

// ----------------------------------------------------------------------------
?>
<head>
    <link rel="stylesheet" href="../css/aboutus.css">
</head>

<body>
<header>
    <?php include '../_header.php'; ?>
</header>

<main>
    <section class="about">
        <div class="about-content">
            <div class="about-image">
                <img src="../img/Aboutus.jpg" alt="Furniture" />
            </div>
            <div class="about-text">
                <h2>About Us</h2>
                <p>At <strong>Furniture</strong>, we believe that furniture should not only be functional, but also inspiring. We're an online furniture store dedicated to helping you create beautiful, comfortable spaces—whether it's your home, office, or a cozy corner you love.</p>

                <h2>What We Offer</h2>
                <p>From <em>modern sofas</em> to <em>classic wooden dining sets</em>, our collection is carefully curated to bring you :</p>
                <ul>
                    <li>Quality craftsmanship</li>
                    <li>Affordable pricing</li>
                    <li>Stylish designs for every space</li>
                </ul>

                <h2>Our Mission</h2>
                <p>To make quality furniture accessible to everyone, delivered right to your doorstep, with hassle-free service and guaranteed satisfaction.</p>

                <h2>Why Shop With Us</h2>
                <ul>
                    <li><strong>Affordable Luxury</strong> : Designer-quality pieces without the luxury price tag</li>
                    <li><strong>Trusted Delivery</strong> : Fast and safe shipping nationwide</li>
                    <li><strong>Customer Care</strong> : Friendly, responsive support before and after purchase</li>
                    <li><strong>Eco-Friendly Options</strong> : We support sustainable and eco-conscious production</li>
                </ul>

                <h2>Join Our Community</h2>
                <p>Thousands of customers have made us their go-to furniture store. Let us help you make your space truly yours.</p>
            </div>
        </div>
    </section>
</main>

<footer>
    <?php include '../_footer.php'; ?>
</footer>
</body>
