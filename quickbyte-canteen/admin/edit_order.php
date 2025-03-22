<?php
session_start();
include '../config.php';

// Ensure the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Check for order_id in query string
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: manage_orders.php");
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order details
$sql = "SELECT order_id, user_id, stall_id, total_price, order_date, order_status 
        FROM orders 
        WHERE order_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: manage_orders.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_status = $_POST['order_status'];
    
    try {
        // Begin transaction
        $con->begin_transaction();
        
        // Update order status
        $sql_update = "UPDATE orders SET order_status = ? WHERE order_id = ?";
        $stmt_update = $con->prepare($sql_update);
        $stmt_update->bind_param("ss", $order_status, $order_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        // If the new status is Completed, deduct stock from food_storage
        if ($order_status === 'Completed') {
            $sql_deduct = "
                UPDATE food_storage fs
                JOIN order_details od ON fs.item_id = od.item_id
                JOIN orders o ON od.order_id = o.order_id
                SET fs.quantity = GREATEST(fs.quantity - od.quantity, 0)
                WHERE o.order_id = ? AND o.order_status = 'Completed'
            ";
            $stmt_deduct = $con->prepare($sql_deduct);
            $stmt_deduct->bind_param("s", $order_id);
            $stmt_deduct->execute();
            $stmt_deduct->close();
        }
        
        $con->commit();
        $success = "Order updated successfully.";
        header("Location: manage_orders.php?success=1");
        exit();
    } catch (mysqli_sql_exception $e) {
        $con->rollback();
        $error = "Failed to update order: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Order - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .admin-content { margin-left: 260px; padding: 20px; }
        .form-container { max-width: 600px; margin: 40px auto; background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <?php include 'admin_menu.php'; ?>
    <div class="admin-content">
        <div class="form-container">
            <h2>Edit Order</h2>
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="order_id" class="form-label">Order ID</label>
                    <input type="text" id="order_id" class="form-control" value="<?php echo htmlspecialchars($order['order_id']); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="order_status" class="form-label">Order Status</label>
                    <select name="order_status" id="order_status" class="form-select" required>
                        <?php
                        // Define the possible order statuses
                        $statuses = ['Pending', 'Completed', 'Cancelled', 'Ready-for-Pickup', 'Partially Completed', 'Preparing'];
                        foreach($statuses as $status) {
                            $selected = ($order['order_status'] === $status) ? "selected" : "";
                            echo "<option value=\"$status\" $selected>$status</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-pencil"></i> Update Order</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
