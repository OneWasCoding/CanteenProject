<?php
session_start();
include '../sidepanel.php';
include '../../config.php';

// Check if the user is logged in as a retailer
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['stall_id']) || $_SESSION['role'] != 'Retailer') {
    header("Location: ../../auth/login.php");
    exit();
}

$stall_id = $_SESSION['stall_id'];

// Get the order ID from the URL
if (!isset($_GET['order_id'])) {
    header("Location: vorder.php");
    exit();
}
$order_id = $_GET['order_id'];

// Fetch order details
$order_sql = "SELECT * FROM orders WHERE order_id = ? AND stall_id = ?";
$order_stmt = $con->prepare($order_sql);
$order_stmt->bind_param("si", $order_id, $stall_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();
$order_stmt->close();

if (!$order) {
    echo "Order not found.";
    exit();
}

// Fetch order items with status
$items_sql = "
    SELECT od.item_id, od.quantity, od.unit_price, od.subtotal, od.status, mi.name 
    FROM order_details od
    JOIN menu_items mi ON od.item_id = mi.item_id
    WHERE od.order_id = ?
";
$items_stmt = $con->prepare($items_sql);
$items_stmt->bind_param("s", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}
$items_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            border-radius: 0.25rem;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-preparing {
            background-color: #d4edda;
            color: #155724;
        }
        .status-ready {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h1 class="h2 mb-0">Order Details</h1>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Order Summary</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                            <p><strong>User ID:</strong> <?php echo htmlspecialchars($order['user_id']); ?></p>
                            <p><strong>Total Price:</strong> PHP <?php echo number_format($order['total_price'], 2); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="status-badge <?php echo 'status-' . strtolower($order['order_status']); ?>">
                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Order Items</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No items found for this order.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                            <td>PHP <?php echo number_format($item['unit_price'], 2); ?></td>
                                            <td>PHP <?php echo number_format($item['subtotal'], 2); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo 'status-' . strtolower($item['status']); ?>">
                                                    <?php echo htmlspecialchars($item['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="text-center">
                <a href="vorder.php" class="btn btn-secondary">Back to Orders</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>