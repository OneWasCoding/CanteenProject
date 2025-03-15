<?php
session_start();
include '../config.php';

// Redirect to login if user is not logged in
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

// Fetch order details (including stall_id)
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

// Fetch stall details for the order
$sql_stall = "SELECT stall_name, description FROM stalls WHERE stall_id = ?";
$stmt_stall = $con->prepare($sql_stall);
$stmt_stall->bind_param("i", $order['stall_id']);
$stmt_stall->execute();
$result_stall = $stmt_stall->get_result();
$stall = $result_stall->fetch_assoc();
$stmt_stall->close();

// Fetch order items along with product details
$sql_items = "SELECT m.item_id, m.name AS product_name, m.category, od.quantity, od.unit_price, od.subtotal 
              FROM order_details od 
              JOIN menu_items m ON od.item_id = m.item_id 
              WHERE od.order_id = ?";
$stmt_items = $con->prepare($sql_items);
$stmt_items->bind_param("s", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$order_items = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

// Format date
$order_date = date('F j, Y, g:i A', strtotime($order['order_date']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order Details - <?php echo htmlspecialchars($order['order_id']); ?></title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
    }
    .navbar {
      background: linear-gradient(135deg, #e44d26, #ff7f50);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .navbar-brand {
      font-weight: bold;
    }
    .order-details {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.15);
      padding: 2rem;
      margin: 2rem auto;
      max-width: 800px;
    }
    .order-details h3 {
      margin-bottom: 1rem;
      text-align: center;
    }
    .order-info p {
      margin: 0.5rem 0;
    }
    .item-row {
      display: flex;
      justify-content: space-between;
      padding: 0.5rem 0;
      border-bottom: 1px solid #eee;
    }
    .item-row:last-child {
      border-bottom: none;
    }
    .total-row {
      font-weight: bold;
      font-size: 1.1rem;
      text-align: right;
      margin-top: 1rem;
    }
    .back-btn {
      display: inline-block;
      margin-bottom: 1rem;
      background-color: transparent;
      border: 1px solid #333;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      text-decoration: none;
      color: #333;
      transition: background 0.3s ease;
    }
    .back-btn:hover {
      background-color: #333;
      color: #fff;
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
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php"><i class="bi bi-shop"></i> QuickByte Canteen</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house"></i> Home</a></li>
          <li class="nav-item"><a class="nav-link" href="cart.php"><i class="bi bi-cart"></i> Cart</a></li>
          <li class="nav-item"><a class="nav-link" href="user_profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
          <li class="nav-item"><a class="nav-link" href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container order-details">
    <a href="order_history.php" class="back-btn"><i class="bi bi-arrow-left"></i> Back to Order History</a>
    <h3>Order Details</h3>
    <div class="order-info">
      <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
      <p><strong>Status:</strong> <?php echo htmlspecialchars($order['order_status']); ?></p>
      <p><strong>Date:</strong> <?php echo $order_date; ?></p>
      <p><strong>Total Price:</strong> ₱<?php echo number_format($order['total_price'], 2); ?></p>
      <?php if ($stall): ?>
        <p><strong>Stall:</strong> <?php echo htmlspecialchars($stall['stall_name']); ?></p>
      <?php endif; ?>
    </div>

    <h4 class="mt-4">Items Ordered</h4>
    <?php if (!empty($order_items)): ?>
      <?php foreach ($order_items as $item): ?>
        <div class="item-row">
          <span>
            <?php echo htmlspecialchars($item['product_name']); ?>
            <small class="text-muted">(<?php echo htmlspecialchars($item['category']); ?>)</small>
            - Qty: <?php echo $item['quantity']; ?>
          </span>
          <span>₱<?php echo number_format($item['subtotal'], 2); ?></span>
        </div>
      <?php endforeach; ?>
      <div class="total-row">
        <span>Total: ₱<?php echo number_format($order['total_price'], 2); ?></span>
      </div>
    <?php else: ?>
      <p>No items found for this order.</p>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container">
      <p>&copy; 2025 QuickByte Canteen. All rights reserved.</p>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
