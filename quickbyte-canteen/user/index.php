<?php
session_start();
include '../config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

/* --- Existing Featured Products Query --- */
$sql_featured = "
    SELECT m.item_id, m.name, m.price, m.category, m.image_path, i.quantity AS quantity_in_stock
    FROM menu_items m
    LEFT JOIN inventory i ON m.item_id = i.product_id
    WHERE m.availability = 1
    ORDER BY RAND() LIMIT 6
";
$stmt_featured = $con->prepare($sql_featured);
$stmt_featured->execute();
$result_featured = $stmt_featured->get_result();
$featured_products = $result_featured->fetch_all(MYSQLI_ASSOC);
$stmt_featured->close();

/* --- New Query: Top Seller of Retailer --- */
/* This query selects the retailer (user) whose stall has the highest number of orders */
$sql_top_seller = "
    SELECT u.user_id, u.name, COUNT(o.order_id) AS total_orders
    FROM retailers r
    JOIN orders o ON r.stall_id = o.stall_id
    JOIN users u ON r.user_id = u.user_id
    GROUP BY u.user_id
    ORDER BY total_orders DESC
    LIMIT 1
";
$stmt_top_seller = $con->prepare($sql_top_seller);
$stmt_top_seller->execute();
$result_top_seller = $stmt_top_seller->get_result();
$top_seller = $result_top_seller->fetch_assoc();
$stmt_top_seller->close();

/* --- New Query: Top Store --- */
/* This query selects the stall with the highest number of orders */
$sql_top_store = "
    SELECT s.stall_id, s.stall_name, COUNT(o.order_id) AS total_orders
    FROM stalls s
    JOIN orders o ON s.stall_id = o.stall_id
    GROUP BY s.stall_id
    ORDER BY total_orders DESC
    LIMIT 1
";
$stmt_top_store = $con->prepare($sql_top_store);
$stmt_top_store->execute();
$result_top_store = $stmt_top_store->get_result();
$top_store = $result_top_store->fetch_assoc();
$stmt_top_store->close();

/* --- New Query: Top Product --- */
/* This query selects the product with the highest total quantity sold */
$sql_top_product = "
    SELECT m.item_id, m.name, m.price, m.category, m.image_path, SUM(od.quantity) AS total_sold
    FROM order_details od
    JOIN menu_items m ON od.item_id = m.item_id
    GROUP BY m.item_id
    ORDER BY total_sold DESC
    LIMIT 1
";
$stmt_top_product = $con->prepare($sql_top_product);
$stmt_top_product->execute();
$result_top_product = $stmt_top_product->get_result();
$top_product = $result_top_product->fetch_assoc();
$stmt_top_product->close();

/* --- New Query: Featured Product from Different Stall --- */
/* This query selects one product per stall (using the minimum item_id per stall) */
$sql_featured_diff = "
    SELECT m.item_id, m.name, m.price, m.category, m.image_path, i.quantity AS quantity_in_stock, m.stall_id
    FROM menu_items m
    LEFT JOIN inventory i ON m.item_id = i.product_id
    WHERE m.availability = 1
      AND m.item_id IN (
          SELECT MIN(m2.item_id)
          FROM menu_items m2
          WHERE m2.availability = 1
          GROUP BY m2.stall_id
      )
    LIMIT 6
";
$stmt_featured_diff = $con->prepare($sql_featured_diff);
$stmt_featured_diff->execute();
$result_featured_diff = $stmt_featured_diff->get_result();
$featured_diff_products = $result_featured_diff->fetch_all(MYSQLI_ASSOC);
$stmt_featured_diff->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickByte Canteen</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .navbar {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .hero-section {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
        }
        .hero-section h1 {
            font-size: 4rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        .hero-section p {
            color: white;
            font-weight: bold;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7);
            font-size: 1.5rem;
            margin-top: 1rem;
        }
        .btn-cta {
            background-color: #e44d26;
            color: white;
            border-radius: 30px;
            padding: 10px 20px;
            font-size: 1.2rem;
            transition: all 0.3s ease-in-out;
        }
        .btn-cta:hover {
            background-color: #ff7f50;
        }
        svg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: -1;
        }
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .out-top {
            animation: rotate 20s linear infinite;
            transform-origin: 13px 25px;
        }
        .in-top {
            animation: rotate 10s linear infinite;
            transform-origin: 13px 25px;
        }
        .out-bottom {
            animation: rotate 25s linear infinite;
            transform-origin: 84px 93px;
        }
        .in-bottom {
            animation: rotate 15s linear infinite;
            transform-origin: 84px 93px;
        }
        .blob-path-1 {
            fill: #e44d26;
        }
        .blob-path-2 {
            fill: #ff7f50;
        }
        .blob-path-3 {
            fill: #ff7f50;
        }
        .blob-path-4 {
            fill: #e44d26;
        }
        /* Section styles */
        .section-title {
            margin-top: 2rem;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
        }
        .info-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .info-card h4 {
            margin-bottom: 0.5rem;
        }
        .info-card p {
            margin: 0;
            font-size: 1rem;
            color: #666;
        }
        /* Featured Products Section (existing) */
        .featured-products {
            padding-top: 4rem;
            padding-bottom: 4rem;
        }
        .product-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-10px);
        }
        .product-card img {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .product-card .card-body {
            padding: 1rem;
        }
        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .card-text {
            font-size: 1.1rem;
        }
        .btn-view {
            background-color: #e44d26;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }
        .btn-view:hover {
            opacity: 0.8;
        }
        footer {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <!-- Blob Animation SVG -->
    <svg preserveAspectRatio="xMidYMid slice" viewBox="10 10 80 80">
        <defs>
            <style>
                @keyframes rotate {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .out-top {
                    animation: rotate 20s linear infinite;
                    transform-origin: 13px 25px;
                }
                .in-top {
                    animation: rotate 10s linear infinite;
                    transform-origin: 13px 25px;
                }
                .out-bottom {
                    animation: rotate 25s linear infinite;
                    transform-origin: 84px 93px;
                }
                .in-bottom {
                    animation: rotate 15s linear infinite;
                    transform-origin: 84px 93px;
                }
            </style>
        </defs>
        <path class="blob-path-1 out-top" d="M37-5C25.1-14.7,5.7-19.1-9.2-10-28.5,1.8-32.7,31.1-19.8,49c15.5,21.5,52.6,22,67.2,2.3C59.4,35,53.7,8.5,37-5Z"/>
        <path class="blob-path-2 in-top" d="M20.6,4.1C11.6,1.5-1.9,2.5-8,11.2-16.3,23.1-8.2,45.6,7.4,50S42.1,38.9,41,24.5C40.2,14.1,29.4,6.6,20.6,4.1Z"/>
        <path class="blob-path-3 out-bottom" d="M105.9,48.6c-12.4-8.2-29.3-4.8-39.4.8-23.4,12.8-37.7,51.9-19.1,74.1s63.9,15.3,76-5.6c7.6-13.3,1.8-31.1-2.3-43.8C117.6,63.3,114.7,54.3,105.9,48.6Z"/>
        <path class="blob-path-4 in-bottom" d="M102,67.1c-9.6-6.1-22-3.1-29.5,2-15.4,10.7-19.6,37.5-7.6,47.8s35.9,3.9,44.5-12.5C115.5,92.6,113.9,74.6,102,67.1Z"/>
    </svg>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="bi bi-shop"></i> QuickByte Canteen</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="index.php" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Home
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="food.php"><i class="bi bi-egg-fried"></i> Food Items</a></li>
                            <li><a class="dropdown-item" href="stalls.php"><i class="bi bi-shop-window"></i> Stalls</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php"><i class="bi bi-cart"></i> Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_profile.php"><i class="bi bi-person-circle"></i> Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Welcome to QuickByte Canteen</h1>
            <p>Delicious food, delivered fast!</p>
            <a href="food.php" class="btn btn-cta">Explore Now</a>
        </div>
    </section>

    <!-- New Section: Top Seller of Retailer -->
    <section class="container mt-5">
        <div class="info-card">
            <h4>Top Seller</h4>
            <?php if ($top_seller): ?>
                <p><?php echo htmlspecialchars($top_seller['name']); ?> (<?php echo $top_seller['total_orders']; ?> orders)</p>
            <?php else: ?>
                <p>No data available.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- New Section: Top Store -->
    <section class="container mt-5">
        <div class="info-card">
            <h4>Top Store</h4>
            <?php if ($top_store): ?>
                <p><?php echo htmlspecialchars($top_store['stall_name']); ?> (<?php echo $top_store['total_orders']; ?> orders)</p>
            <?php else: ?>
                <p>No data available.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- New Section: Top Product -->
    <section class="container mt-5">
        <div class="info-card">
            <h4>Top Product</h4>
            <?php if ($top_product): ?>
                <p><?php echo htmlspecialchars($top_product['name']); ?> (Sold: <?php echo $top_product['total_sold']; ?>)</p>
                <img src="<?php echo htmlspecialchars($top_product['image_path']); ?>" alt="<?php echo htmlspecialchars($top_product['name']); ?>" style="max-width:200px; margin-top:10px;">
            <?php else: ?>
                <p>No data available.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- New Section: Featured Products from Different Stall -->
    <section class="featured-products">
        <div class="container">
            <h2 class="text-center mb-5">Featured Products from Different Stalls</h2>
            <div class="row">
                <?php if (!empty($featured_diff_products)): ?>
                    <?php foreach ($featured_diff_products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card product-card">
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text"><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                                    <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                                    <a href="product_details.php?item_id=<?php echo $product['item_id']; ?>" class="btn btn-view">View Product</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No featured products available from different stalls.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Existing Section: Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2 class="text-center mb-5">Featured Products</h2>
            <div class="row">
                <?php foreach ($featured_products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card">
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                                <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                                <a href="product_details.php?item_id=<?php echo $product['item_id']; ?>" class="btn btn-view">View Product</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 QuickByte Canteen. All rights reserved.</p>
    </footer>

    <!-- JavaScript for Add to Cart -->
    <script>
        function addToCart(itemId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_id: itemId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Item added to cart!');
                } else {
                    alert(data.message || 'Failed to add item to cart.');
                }
            });
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
