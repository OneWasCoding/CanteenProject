<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    
    $sql_cancel = "UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ? AND user_id = ? AND order_status = 'Pending'";
    $stmt_cancel = $con->prepare($sql_cancel);
    $stmt_cancel->bind_param("si", $order_id, $user_id);

    if ($stmt_cancel->execute()) {
        echo '<script>alert("Order cancelled successfully!"); window.location.href = "order_history.php";</script>';
        exit();
    } else {
        echo '<script>alert("Failed to cancel the order.");</script>';
    }
    $stmt_cancel->close();
}

$sql = "
    SELECT o.order_id, o.order_date, o.total_price, o.order_status,
           GROUP_CONCAT(m.name SEPARATOR ', ') AS items,
           GROUP_CONCAT(od.quantity SEPARATOR ', ') AS quantities,
           GROUP_CONCAT(od.unit_price SEPARATOR ', ') AS prices
    FROM orders o
    LEFT JOIN order_details od ON o.order_id = od.order_id
    LEFT JOIN menu_items m ON od.item_id = m.item_id
    WHERE o.user_id = ? 
      AND o.order_status IN ('Pending','Completed','Partially Completed','Cancelled')
";

if ($filter_status !== 'all') {
    $sql .= " AND o.order_status = ?";
}

$sql .= " GROUP BY o.order_id ORDER BY o.order_date DESC";

$stmt = $con->prepare($sql);

if ($filter_status !== 'all') {
    $stmt->bind_param("is", $user_id, $filter_status);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - QuickByte Canteen</title>
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

        .dropdown-menu {
            background-color: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .dropdown-item {
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            transform: translateX(5px);
        }

        .history-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
        }

        .history-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .history-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .filters {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 2rem;
        }

        .filters a {
            background: white;
            padding: 10px 20px;
            border-radius: 25px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid #eee;
        }

        .filters a:hover,
        .filters a.active {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            transform: translateY(-2px);
            border-color: transparent;
        }

        .order-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(228,77,38,0.2);
        }

        .order-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .order-header p {
            margin: 0;
            font-size: 1.1rem;
        }

        .order-details {
            padding: 1rem 0;
        }

        .order-details p {
            margin: 0.5rem 0;
            color: #666;
        }

        .order-details i {
            color: #e44d26;
            margin-right: 5px;
        }

        .progress-tracker {
            margin: 2rem 0;
            position: relative;
            display: flex;
            justify-content: space-between;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            border: 2px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .circle.active {
            background: #e44d26;
            border-color: #e44d26;
            color: white;
        }

        .progress-bar {
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #ddd;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-view {
            background: #17a2b8;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-view:hover {
            background: #138496;
            transform: translateY(-2px);
            color: white;
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
            .history-container {
                margin: 1rem;
                padding: 1rem;
            }

            .filters {
                flex-direction: column;
            }

            .filters a {
                width: 100%;
                text-align: center;
            }

            .actions {
                flex-direction: column;
            }

            .btn-cancel,
            .btn-view {
                width: 100%;
                text-align: center;
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
        <div class="history-container">
            <div class="history-header">
                <h2><i class="bi bi-clock-history"></i> Order History</h2>
                <p>Track and manage your orders</p>
            </div>

            <div class="filters">
                <a href="?status=all" class="<?php echo $filter_status === 'all' ? 'active' : ''; ?>">
                    <i class="bi bi-collection"></i> All Orders
                </a>
                <a href="?status=Pending" class="<?php echo $filter_status === 'Pending' ? 'active' : ''; ?>">
                    <i class="bi bi-hourglass-split"></i> Pending
                </a>
                <a href="?status=Completed" class="<?php echo $filter_status === 'Completed' ? 'active' : ''; ?>">
                    <i class="bi bi-check-circle"></i> Completed
                </a>
                <a href="?status=Partially Completed" class="<?php echo $filter_status === 'Partially Completed' ? 'active' : ''; ?>">
                    <i class="bi bi-clock"></i> Partially Completed
                </a>
                <a href="?status=Cancelled" class="<?php echo $filter_status === 'Cancelled' ? 'active' : ''; ?>">
                    <i class="bi bi-x-circle"></i> Cancelled
                </a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="text-center">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="mt-3">No orders found for the selected status.</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo $order['order_status'] === 'Completed' ? 'success' : 
                                        ($order['order_status'] === 'Pending' ? 'warning' : 
                                        ($order['order_status'] === 'Cancelled' ? 'danger' : 'info')); 
                                ?>">
                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                </span>
                            </p>
                            <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                        </div>
                        
                        <div class="order-details">
                            <?php
                            $items = explode(',', $order['items']);
                            $quantities = explode(',', $order['quantities']);
                            $prices = explode(',', $order['prices']);
                            foreach ($items as $index => $item) {
                                echo "<p><i class='bi bi-dot'></i> " . htmlspecialchars(trim($item)) . 
                                     " (Qty: " . htmlspecialchars($quantities[$index]) . 
                                     ", Price: ₱" . number_format((float)$prices[$index], 2) . ")</p>";
                            }
                            ?>
                            <p class="mt-3"><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_price'], 2); ?></p>
                        </div>

                        <div class="progress-tracker">
                            <div class="progress-bar"></div>
                            <div class="step">
                                <div class="circle <?php echo in_array($order['order_status'], ['Pending', 'Completed', 'Partially Completed']) ? 'active' : ''; ?>">
                                    <i class="bi bi-cart-check"></i>
                                </div>
                                <p>Order Placed</p>
                            </div>
                            <div class="step">
                                <div class="circle <?php echo in_array($order['order_status'], ['Completed', 'Partially Completed']) ? 'active' : ''; ?>">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <p>Processing</p>
                            </div>
                            <div class="step">
                                <div class="circle <?php echo $order['order_status'] === 'Completed' ? 'active' : ''; ?>">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <p>Completed</p>
                            </div>
                        </div>

                        <div class="actions">
                            <?php if ($order['order_status'] === 'Pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                    <button type="submit" name="cancel_order" class="btn-cancel">
                                        <i class="bi bi-x-circle"></i> Cancel Order
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="view_details.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>" class="btn-view">
                                <i class="bi bi-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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