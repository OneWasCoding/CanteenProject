<?php
session_start();
include '../config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch order history for the current user
$sql = "
    SELECT o.order_id, o.order_date, o.total_price, o.status,
           GROUP_CONCAT(m.name SEPARATOR ', ') AS items,
           GROUP_CONCAT(od.quantity SEPARATOR ', ') AS quantities,
           GROUP_CONCAT(od.price SEPARATOR ', ') AS prices
    FROM orders o
    JOIN order_details od ON o.order_id = od.order_id
    JOIN menu_items m ON od.item_id = m.item_id
    WHERE o.user_id = ?
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>QuickByte Canteen - Order History</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Open Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: #e44d26;
            color: white;
        }
        footer {
            background-color: #e44d26;
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: auto;
        }
        .order-history-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .order-history-container h2 {
            margin-bottom: 1.5rem;
            text-align: center;
            color: #333;
        }
        .no-orders {
            text-align: center;
            margin-top: 2rem;
            font-size: 1.2rem;
            color: #6c757d;
        }
        .order-card {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 1rem;
            padding: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .order-header .order-id {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
        }
        .order-header .order-date {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .order-status {
            font-size: 0.9rem;
            font-weight: bold;
        }
        .order-status.completed {
            color: #198754;
        }
        .order-status.pending {
            color: #ffc107;
        }
        .order-status.cancelled, .order-status.failed {
            color: #dc3545;
        }
        .item-details {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .total-price {
            font-size: 1rem;
            font-weight: bold;
            color: #333;
            text-align: right;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #e44d26;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-shop"></i> QuickByte Canteen</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house"></i> Home</a>
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
    <div class="order-history-container">
        <h2>Order History</h2>
        <?php if (empty($orders)): ?>
            <p class="no-orders">You have not placed any orders yet.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">Order #<?php echo htmlspecialchars($order['order_id']); ?></div>
                        <div class="order-date"><?php echo htmlspecialchars($order['order_date']); ?></div>
                    </div>
                    <div class="order-status <?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                        Status: <?php echo htmlspecialchars($order['status']); ?>
                    </div>
                    <div class="item-list">
                        <?php
                        $items = explode(',', $order['items']);
                        $quantities = explode(',', $order['quantities']);
                        $prices = explode(',', $order['prices']);
                        foreach ($items as $index => $item) {
                            echo "<div class='item-details'>$item (Qty: {$quantities[$index]}, Price: $" . number_format($prices[$index], 2) . ")</div>";
                        }
                        ?>
                    </div>
                    <div class="total-price">
                        Total: $<?php echo number_format($order['total_price'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 QuickByte Canteen. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>