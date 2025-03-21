<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
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
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-ready {
            background-color: #cce5ff;
            color: #004085;
        }
        .action-links .btn {
            margin-right: 8px;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.03);
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
    </style>
</head>
<body>
    <?php 
    ob_start();
    session_start();
    include '../sidepanel.php'; 
    include '../../config.php';

    // Check if the user is logged in as a retailer
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['stall_id']) || $_SESSION['role'] != 'Retailer') {
        header("Location: ../../auth/login.php");
        exit();
    }

    $stall_id = $_SESSION['stall_id'];

    // Handle Accept, Deny, or Mark as Ready
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        $order_id = $_POST['order_id'];
        $action = $_POST['action']; // 'accept', 'deny', or 'ready'

        if ($action === 'accept') {
            $status = 'Preparing';
        } elseif ($action === 'deny') {
            $status = 'Cancelled';
        } elseif ($action === 'ready') {
            $status = 'Ready for Pickup'; // Update status to "Ready"
        }

        $update_sql = "UPDATE orders SET order_status = ? WHERE order_id = ? AND stall_id = ?";
        $update_stmt = $con->prepare($update_sql);
        $update_stmt->bind_param("ssi", $status, $order_id, $stall_id);
        $update_stmt->execute();
        $update_stmt->close();

        header("Location: vorder.php");
        exit();
    }

    // Fetch all orders for the retailer
    $orders_sql = "SELECT * FROM orders WHERE stall_id = ?";
    $orders_stmt = $con->prepare($orders_sql);
    $orders_stmt->bind_param("i", $stall_id);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    $orders = [];
    while ($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
    $orders_stmt->close();

    // Filter pending orders
    $pending_orders = array_filter($orders, function($order) {
        return $order['order_status'] === 'Pending';
    });

    // Filter preparing orders
    $preparing_orders = array_filter($orders, function($order) {
        return $order['order_status'] === 'Preparing';
    });

    // Filter ready orders
    $ready_orders = array_filter($orders, function($order) {
        return $order['order_status'] === 'Ready for Pickup';
    });
    ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h1 class="h2 mb-0">Manage Orders</h1>
                    <p class="text-muted">Total Orders: <?php echo count($orders); ?></p>
                </div>
            </div>

            <!-- Pending Orders Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Pending Orders</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>User ID</th>
                                    <th>Total Price</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_id'], ENT_QUOTES); ?></td>
                                        <td><?php echo htmlspecialchars($order['user_id'], ENT_QUOTES); ?></td>
                                        <td>PHP <?php echo number_format($order['total_price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_date'], ENT_QUOTES); ?></td>
                                        <td>
                                            <span class="status-badge status-pending">
                                                <?php echo htmlspecialchars($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td class="action-links">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="btn btn-sm btn-outline-success">Accept</button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <input type="hidden" name="action" value="deny">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Deny</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Preparing Orders Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Preparing Orders</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>User ID</th>
                                    <th>Total Price</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($preparing_orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_id'], ENT_QUOTES); ?></td>
                                        <td><?php echo htmlspecialchars($order['user_id'], ENT_QUOTES); ?></td>
                                        <td>PHP <?php echo number_format($order['total_price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_date'], ENT_QUOTES); ?></td>
                                        <td>
                                            <span class="status-badge status-preparing">
                                                <?php echo htmlspecialchars($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td class="action-links">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <input type="hidden" name="action" value="ready">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Mark as Ready</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Ready to Pickup Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Ready to Pickup</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>User ID</th>
                                    <th>Total Price</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ready_orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_id'], ENT_QUOTES); ?></td>
                                        <td><?php echo htmlspecialchars($order['user_id'], ENT_QUOTES); ?></td>
                                        <td>PHP <?php echo number_format($order['total_price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_date'], ENT_QUOTES); ?></td>
                                        <td>
                                            <span class="status-badge status-ready">
                                                <?php echo htmlspecialchars($order['order_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- All Orders Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">All Orders</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>User ID</th>
                                    <th>Total Price</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_id'], ENT_QUOTES); ?></td>
                                        <td><?php echo htmlspecialchars($order['user_id'], ENT_QUOTES); ?></td>
                                        <td>PHP <?php echo number_format($order['total_price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_date'], ENT_QUOTES); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo 'status-' . strtolower($order['order_status']); ?>">
                                                <?php echo htmlspecialchars($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td class="action-links">
                                            <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" 
                                               class="btn btn-sm btn-outline-info">
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>