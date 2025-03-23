<?php
session_start();
include '../config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

/* --- Queries for Top Elements --- */

// Top Feedback
$sql_top_feedback = "
    SELECT f.feedback_id, u.name AS user_name, s.stall_name, f.rating, f.comment
    FROM feedback f
    JOIN users u ON f.user_id = u.user_id
    JOIN stalls s ON f.stall_id = s.stall_id
    ORDER BY f.rating DESC
    LIMIT 3
";
$stmt_top_feedback = $con->prepare($sql_top_feedback);
$stmt_top_feedback->execute();
$result_top_feedback = $stmt_top_feedback->get_result();
$top_feedback = $result_top_feedback->fetch_all(MYSQLI_ASSOC);
$stmt_top_feedback->close();

// Top Stalls
$sql_top_stalls = "
    SELECT s.stall_id, s.stall_name, COUNT(o.order_id) AS total_orders
    FROM stalls s
    JOIN orders o ON s.stall_id = o.stall_id
    GROUP BY s.stall_id
    ORDER BY total_orders DESC
    LIMIT 3
";
$stmt_top_stalls = $con->prepare($sql_top_stalls);
$stmt_top_stalls->execute();
$result_top_stalls = $stmt_top_stalls->get_result();
$top_stalls = $result_top_stalls->fetch_all(MYSQLI_ASSOC);
$stmt_top_stalls->close();

// Top Products
$sql_top_products = "
    SELECT m.item_id, m.name, m.price, m.category, m.image_path, SUM(od.quantity) AS total_sold
    FROM order_details od
    JOIN menu_items m ON od.item_id = m.item_id
    GROUP BY m.item_id
    ORDER BY total_sold DESC
    LIMIT 3
";
$stmt_top_products = $con->prepare($sql_top_products);
$stmt_top_products->execute();
$result_top_products = $stmt_top_products->get_result();
$top_products = $result_top_products->fetch_all(MYSQLI_ASSOC);
$stmt_top_products->close();

// Top Users
$sql_top_users = "
    SELECT u.user_id, u.name, COUNT(o.order_id) AS total_orders
    FROM users u
    JOIN orders o ON u.user_id = o.user_id
    GROUP BY u.user_id
    ORDER BY total_orders DESC
    LIMIT 3
";
$stmt_top_users = $con->prepare($sql_top_users);
$stmt_top_users->execute();
$result_top_users = $stmt_top_users->get_result();
$top_users = $result_top_users->fetch_all(MYSQLI_ASSOC);
$stmt_top_users->close();

// Most Rated Products
$sql_most_rated_products = "
    SELECT 
        m.item_id, 
        m.name, 
        m.price, 
        m.category, 
        m.image_path, 
        AVG(f.rating) AS avg_rating
    FROM 
        menu_items m
    LEFT JOIN 
        feedback f ON m.stall_id = f.stall_id  
    GROUP BY 
        m.item_id
    ORDER BY 
        avg_rating DESC
    LIMIT 3
";
$stmt_most_rated_products = $con->prepare($sql_most_rated_products);
$stmt_most_rated_products->execute();
$result_most_rated_products = $stmt_most_rated_products->get_result();
$most_rated_products = $result_most_rated_products->fetch_all(MYSQLI_ASSOC);
$stmt_most_rated_products->close();

// Featured Products
$sql_featured_products = "
    SELECT 
        m.item_id, 
        m.name, 
        m.price, 
        m.category, 
        m.image_path, 
        COALESCE(fs.quantity, 0) AS quantity_in_stock
    FROM 
        menu_items m
    LEFT JOIN 
        food_storage fs ON m.item_id = fs.item_id
    WHERE 
        m.availability = 'Available'
    ORDER BY 
        RAND() 
    LIMIT 6
";
$stmt_featured_products = $con->prepare($sql_featured_products);
$stmt_featured_products->execute();
$result_featured_products = $stmt_featured_products->get_result();
$featured_products = $result_featured_products->fetch_all(MYSQLI_ASSOC);
$stmt_featured_products->close();
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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            transform: translateY(-2px);
        }
        .hero-section {
            height: 85vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            padding-top: 70px;
        }
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 1.5rem;
            animation: fadeIn 1.5s ease-in;
        }
        .hero-section p {
            color: white;
            font-weight: 500;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7);
            font-size: 1.5rem;
            margin-bottom: 2rem;
            animation: fadeIn 2s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-cta {
            background-color: #e44d26;
            color: white;
            border-radius: 30px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: none;
        }
        .btn-cta:hover {
            background-color: #ff7f50;
            transform: translateY(-5px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.3);
            color: white;
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
        
        /* Redesigned elements */
        .page-section {
            padding: 80px 0;
            position: relative;
        }
        .page-section:nth-child(even) {
            background-color: #fff;
        }
        .section-title {
            position: relative;
            margin-bottom: 50px;
            font-weight: 700;
            font-size: 2.2rem;
            color: #333;
            text-align: center;
        }
        .section-title:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            margin: 20px auto 0;
            border-radius: 2px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 25px;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .stats-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .stats-card h3 {
            color: #e44d26;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
        }
        .stats-card h3:after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            margin-top: 10px;
            border-radius: 2px;
        }
        .stats-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .stats-list li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95rem;
        }
        .stats-list li:last-child {
            border-bottom: none;
        }
        .stats-list .item-name {
            font-weight: 500;
            flex-grow: 1;
        }
        .stats-list .item-value {
            font-weight: 600;
            color: #e44d26;
            padding-left: 10px;
        }
        .rating-stars {
            color: #FFD700;
            font-size: 0.8rem;
        }
        
        /* Featured Products */
        .product-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            background: white;
            border: none;
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .product-card .card-img-container {
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        .product-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .product-card:hover img {
            transform: scale(1.1);
        }
        .product-category {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 127, 80, 0.9);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .product-card .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        .product-price {
            color: #e44d26;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        .product-stock {
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #666;
        }
        .stock-high {
            color: #28a745;
        }
        .stock-medium {
            color: #ffc107;
        }
        .stock-low {
            color: #dc3545;
        }
        .btn-view {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: auto;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        .btn-view:hover {
            background: linear-gradient(135deg, #ff7f50, #e44d26);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        /* Quick Stats */
        .quick-stats {
            background: linear-gradient(135deg, rgba(228, 77, 38, 0.9), rgba(255, 127, 80, 0.9));
            padding: 60px 0;
            color: white;
            text-align: center;
        }
        .stat-counter {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 1.1rem;
            font-weight: 500;
            opacity: 0.9;
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }
        
        /* Footer */
        footer {
            background: linear-gradient(135deg, #333, #222);
            color: white;
            padding: 40px 0 20px;
        }
        .footer-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #fff;
        }
        .footer-title:after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            margin-top: 10px;
        }
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-links li {
            margin-bottom: 10px;
        }
        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer-links a:hover {
            color: #ff7f50;
        }
        .social-links {
            font-size: 1.5rem;
            margin-top: 20px;
        }
        .social-links a {
            color: white;
            margin-right: 15px;
            transition: color 0.3s ease;
        }
        .social-links a:hover {
            color: #ff7f50;
        }
        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5rem;
            }
            .hero-section p {
                font-size: 1.2rem;
            }
            .section-title {
                font-size: 1.8rem;
            }
            .page-section {
                padding: 60px 0;
            }
            .stats-card {
                margin-bottom: 30px;
            }
            /* Add this to your existing CSS */
.btn-view {
    background: linear-gradient(135deg, #e44d26, #ff7f50);
    color: white;
    border: none;
    border-radius: 30px;
    padding: 10px 20px;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-top: auto;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    width: 100%;
}

.btn-view:hover {
    background: linear-gradient(135deg, #ff7f50, #e44d26);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
    text-decoration: none;
}
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

    <!-- Quick Stats Section -->
    <div class="quick-stats">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-4">
                    <i class="bi bi-shop stat-icon"></i>
                    <div class="stat-counter"><?php echo count($top_stalls); ?>+</div>
                    <div class="stat-label">Popular Stalls</div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <i class="bi bi-egg-fried stat-icon"></i>
                    <div class="stat-counter"><?php echo count($featured_products); ?>+</div>
                    <div class="stat-label">Menu Items</div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <i class="bi bi-people stat-icon"></i>
                    <div class="stat-counter"><?php echo count($top_users); ?>+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <i class="bi bi-star stat-icon"></i>
                    <div class="stat-counter"><?php echo count($top_feedback); ?>+</div>
                    <div class="stat-label">5-Star Reviews</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Stats Section -->
    <section class="page-section">
        <div class="container">
            <h2 class="section-title">What's Trending</h2>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3><i class="bi bi-star me-2"></i>Top Feedback</h3>
                        <ul class="stats-list">
                            <?php if ($top_feedback): ?>
                                <?php foreach ($top_feedback as $feedback): ?>
                                    <li>
                                        <span class="item-name"><?php echo htmlspecialchars($feedback['user_name']); ?> - <?php echo htmlspecialchars($feedback['stall_name']); ?></span>
                                        <span class="item-value">
                                            <span class="rating-stars">
                                                <?php for($i = 0; $i < $feedback['rating']; $i++): ?>
                                                    <i class="bi bi-star-fill"></i>
                                                <?php endfor; ?>
                                            </span>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span class="item-name">No feedback yet</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3><i class="bi bi-shop-window me-2"></i>Top Stalls</h3>
                        <ul class="stats-list">
                            <?php if ($top_stalls): ?>
                                <?php foreach ($top_stalls as $stall): ?>
                                    <li>
                                        <span class="item-name"><?php echo htmlspecialchars($stall['stall_name']); ?></span>
                                        <span class="item-value"><?php echo $stall['total_orders']; ?> orders</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span class="item-name">No top stalls yet</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3><i class="bi bi-egg-fried me-2"></i>Top Products</h3>
                        <ul class="stats-list">
                            <?php if ($top_products): ?>
                                <?php foreach ($top_products as $product): ?>
                                    <li>
                                        <span class="item-name"><?php echo htmlspecialchars($product['name']); ?></span>
                                        <span class="item-value"><?php echo $product['total_sold']; ?> sold</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span class="item-name">No top products yet</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3><i class="bi bi-person-check me-2"></i>Top Users</h3>
                        <ul class="stats-list">
                            <?php if ($top_users): ?>
                                <?php foreach ($top_users as $user): ?>
                                    <li>
                                        <span class="item-name"><?php echo htmlspecialchars($user['name']); ?></span>
                                        <span class="item-value"><?php echo $user['total_orders']; ?> orders</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span class="item-name">No top users yet</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3><i class="bi bi-star-half me-2"></i>Most Rated</h3>
                        <ul class="stats-list">
                            <?php if ($most_rated_products): ?>
                                <?php foreach ($most_rated_products as $product): ?>
                                    <li>
                                        <span class="item-name"><?php echo htmlspecialchars($product['name']); ?></span>
                                        <span class="item-value">
                                            <?php echo number_format($product['avg_rating'], 1); ?>
                                            <i class="bi bi-star-fill" style="font-size: 0.8rem; color: #FFD700;"></i>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span class="item-name">No rated products yet</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="stats-card">
                        <h3><i class="bi bi-award me-2"></i>Bestsellers</h3>
                        <ul class="stats-list">
                                <?php if ($featured_products): ?>
    <?php for($i = 0; $i < min(3, count($featured_products)); $i++): ?>
        <li>
            <span class="item-name"><?php echo htmlspecialchars($featured_products[$i]['name']); ?></span>
            <span class="item-value">$<?php echo number_format($featured_products[$i]['price'], 2); ?></span>
        </li>
    <?php endfor; ?>
<?php else: ?>
    <li><span class="item-name">No bestsellers yet</span></li>
<?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Featured Products Section -->
<section class="page-section">
    <div class="container">
        <h2 class="section-title">Featured Menu Items</h2>
        <div class="row">
            <?php if ($featured_products): ?>
                <?php foreach ($featured_products as $product): ?>
                    <?php
                    // Process the image_path for the current product with path adjustment
                    $image_path = !empty($product['image_path']) 
                        ? htmlspecialchars(str_replace("../../", "../", $product['image_path'])) 
                        : '../assets/images/default-food.jpg';
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="product-card card">
                            <div class="card-img-container">
                                <img src="<?php echo $image_path; ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="product-category">
                                    <?php echo htmlspecialchars($product['category']); ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-stock">
                                    <?php 
                                    $stock = isset($product['quantity_in_stock']) ? $product['quantity_in_stock'] : 0;
                                    if ($stock > 20) {
                                        echo '<span class="stock-high"><i class="bi bi-check-circle-fill"></i> In Stock</span>';
                                    } elseif ($stock > 5) {
                                        echo '<span class="stock-medium"><i class="bi bi-exclamation-circle-fill"></i> Limited Stock</span>';
                                    } else {
                                        echo '<span class="stock-low"><i class="bi bi-x-circle-fill"></i> Low Stock</span>';
                                    }
                                    ?>
                                </div>
                                <a href="product_details.php?item_id=<?php echo $product['item_id']; ?>" 
                                   class="btn-view">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No featured products available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="food.php" class="btn btn-cta">View All Menu Items</a>
        </div>
    </div>
</section>


    <!-- Call to Action Section -->
    <section class="page-section quick-stats">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Order?</h2>
            <p class="mb-4">Skip the line and order your favorite meals directly from our platform.</p>
            <a href="food.php" class="btn btn-cta">Start Ordering Now</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">QuickByte Canteen</h5>
                    <p>Delicious food, delivered fast. Our platform connects you with the best food vendors on campus.</p>
                    <div class="social-links">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="food.php">Menu</a></li>
                        <li><a href="stalls.php">Stalls</a></li>
                        <li><a href="cart.php">Cart</a></li>
                        <li><a href="user_profile.php">My Account</a></li>
                    </ul>
                </div>
                <div class="col-md-2 col-6 mb-4">
                    <h5 class="footer-title">Categories</h5>
                    <ul class="footer-links">
                        <li><a href="food.php?category=Breakfast">Breakfast</a></li>
                        <li><a href="food.php?category=Lunch">Lunch</a></li>
                        <li><a href="food.php?category=Dinner">Dinner</a></li>
                        <li><a href="food.php?category=Snacks">Snacks</a></li>
                        <li><a href="food.php?category=Beverages">Beverages</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Contact Us</h5>
                    <ul class="footer-links">
                        <li><i class="bi bi-geo-alt me-2"></i> 123 Campus Ave, University Town</li>
                        <li><i class="bi bi-telephone me-2"></i> (123) 456-7890</li>
                        <li><i class="bi bi-envelope me-2"></i> info@quickbytecanteen.com</li>
                        <li><i class="bi bi-clock me-2"></i> Mon-Fri: 7am-10pm, Sat-Sun: 8am-8pm</li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> QuickByte Canteen. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>