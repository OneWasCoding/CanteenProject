<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: order_history.php"); 
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

$sql_order = "SELECT order_id, total_price, order_status, order_date, stall_id FROM orders WHERE order_id = ? AND user_id = ?";
$stmt_order = $con->prepare($sql_order);
$stmt_order->bind_param("si", $order_id, $user_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();
$stmt_order->close();

if (!$order) {
    header("Location: order_history.php");
    exit();
}

$sql_stall = "SELECT stall_name, description FROM stalls WHERE stall_id = ?";
$stmt_stall = $con->prepare($sql_stall);
$stmt_stall->bind_param("i", $order['stall_id']);
$stmt_stall->execute();
$result_stall = $stmt_stall->get_result();
$stall = $result_stall->fetch_assoc();
$stmt_stall->close();

$sql_items = "SELECT m.item_id, m.name AS product_name, m.category, m.image_path, od.quantity, od.unit_price, od.subtotal 
              FROM order_details od 
              JOIN menu_items m ON od.item_id = m.item_id 
              WHERE od.order_id = ?";
$stmt_items = $con->prepare($sql_items);
$stmt_items->bind_param("s", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$order_items = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

$order_date = date('F j, Y, g:i A', strtotime($order['order_date']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Details - <?php echo htmlspecialchars($order['order_id']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 60px;
        }
        .navbar {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
        }
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            color: white !important;
        }
        .nav-link:hover {
            transform: translateY(-2px);
        }
        .order-details-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 800px;
        }
        .order-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }
        .order-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }
        .back-btn {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(228,77,38,0.3);
            color: white;
        }
        .order-status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            margin: 1rem 0;
        }
        .status-pending { background: #ffc107; color: #000; }
        .status-completed { background: #28a745; color: #fff; }
        .status-cancelled { background: #dc3545; color: #fff; }
        .status-partial { background: #17a2b8; color: #fff; }
        .order-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .order-info p {
            margin: 0.5rem 0;
            color: #666;
        }
        .order-items {
            margin-top: 2rem;
        }
        .item-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(228,77,38,0.2);
        }
        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .item-details {
            padding: 1.5rem;
        }
        .item-details h5 {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .item-price {
            color: #e44d26;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .total-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 2rem;
        }
        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e44d26;
        }
        footer {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: auto;
        }
        .social-icons {
            margin: 1rem 0;
        }
        .social-icons a {
            color: white;
            margin: 0 10px;
            font-size: 1.2rem;
            transition: opacity 0.3s ease;
        }
        .social-icons a:hover {
            opacity: 0.8;
        }
        @media (max-width: 768px) {
            .order-details-container {
                margin: 1rem;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
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

    <!-- Main Content -->
    <div class="container">
        <div class="order-details-container">
            <a href="order_history.php" class="back-btn">
                <i class="bi bi-arrow-left"></i> Back to Order History
            </a>

            <div class="order-header">
                <h2>Order Details</h2>
                <div class="order-status status-<?php echo strtolower($order['order_status']); ?>">
                    <?php echo htmlspecialchars($order['order_status']); ?>
                </div>
            </div>

            <div class="order-info">
                <p><strong><i class="bi bi-receipt"></i> Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                <p><strong><i class="bi bi-calendar-event"></i> Date:</strong> <?php echo $order_date; ?></p>
                <?php if ($stall): ?>
                    <p><strong><i class="bi bi-shop"></i> Stall:</strong> <?php echo htmlspecialchars($stall['stall_name']); ?></p>
                <?php endif; ?>
            </div>

            <div class="order-items">
                <h4 class="mb-4">Ordered Items</h4>
                <div class="row">
                    <?php if (!empty($order_items)): ?>
                        <?php foreach ($order_items as $item): ?>
                            <?php 
                            // Process the image path for each order item
                            $image_path = !empty($item['image_path'])
                                ? htmlspecialchars(str_replace("../../", "../", $item['image_path']))
                                : '../assets/images/default-food.jpg';
                            ?>
                            <div class="col-md-6 mb-4">
                                <div class="item-card">
                                    <img src="<?php echo $image_path; ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         class="item-image">
                                    <div class="item-details">
                                        <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                        <p class="text-muted"><?php echo htmlspecialchars($item['category']); ?></p>
                                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                                        <p>Unit Price: ₱<?php echo number_format($item['unit_price'], 2); ?></p>
                                        <p class="item-price">Subtotal: ₱<?php echo number_format($item['subtotal'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-center">No items found for this order.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="total-section text-end">
                <p class="mb-2">Total Amount:</p>
                <p class="total-amount">₱<?php echo number_format($order['total_price'], 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <h5>Contact Us</h5>
            <p>
                Email: <a href="mailto:support@quickbyte.com">support@quickbyte.com</a><br>
                Phone: <a href="tel:+1234567890">+123 456 7890</a>
            </p>
            <p>Follow us on social media:</p>
            <div class="social-icons">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-twitter"></i></a>
            </div>
            <p>&copy; 2024 QuickByte Canteen. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
