<?php
session_start();
include '../config.php';

// Ensure the logged-in user is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// ---------- Query orders with in‑store payments (receipts) ----------
$sql_instore = "
    SELECT 
        o.order_id, 
        o.order_date, 
        o.total_price, 
        o.order_status, 
        u.name AS customer_name, 
        s.stall_name,
        r.receipt_date,
        r.total_amount
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    JOIN stalls s ON o.stall_id = s.stall_id 
    JOIN receipts r ON o.order_id = r.order_id
    WHERE r.payment_method = 'in-store'
    ORDER BY o.order_date DESC
";
$result_instore = $con->query($sql_instore);
$instore_orders = $result_instore ? $result_instore->fetch_all(MYSQLI_ASSOC) : [];

// ---------- Query orders with gcash payments ----------
$sql_gcash = "
    SELECT 
        o.order_id, 
        o.order_date, 
        o.total_price, 
        o.order_status, 
        u.name AS customer_name, 
        s.stall_name,
        g.gcash_reference, 
        g.gcash_image_path
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    JOIN stalls s ON o.stall_id = s.stall_id 
    JOIN gcash_payment_details g ON o.order_id = g.order_id
    ORDER BY o.order_date DESC
";
$result_gcash = $con->query($sql_gcash);
$gcash_orders = $result_gcash ? $result_gcash->fetch_all(MYSQLI_ASSOC) : [];

function fetchOrderFoodNames($con, $order_id) {
    $sql_food = "SELECT m.name 
                 FROM order_details od 
                 JOIN menu_items m ON od.item_id = m.item_id 
                 WHERE od.order_id = ?";
    $stmt_food = $con->prepare($sql_food);
    $stmt_food->bind_param("s", $order_id);
    $stmt_food->execute();
    $result_food = $stmt_food->get_result();
    $foods = $result_food->fetch_all(MYSQLI_ASSOC);
    $stmt_food->close();
    $foodNames = array_column($foods, 'name');
    return implode(', ', $foodNames);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Orders - Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
    .admin-content { margin-left: 260px; padding: 20px; }
    .table-hover tbody tr:hover { background-color: #f1f1f1; }
    .date-format { font-size: 0.9rem; color: #555; }
    .section-header { margin-top: 2rem; margin-bottom: 1rem; }
    .payment-details { font-size: 0.9rem; color: #333; }
  </style>
</head>
<body>
  <!-- Sidebar Menu -->
  <?php include 'admin_menu.php'; ?>
  
  <div class="admin-content">
    <h2>Manage Orders</h2>
    
    <!-- In-Store Payment Orders Section -->
    <h3 class="section-header">In-Store Payment Orders</h3>
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Stall</th>
          <th>Food Items</th>
          <th>Date</th>
          <th>Total (₱)</th>
          <th>Status</th>
          <th>Receipt Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($instore_orders)): ?>
          <?php foreach ($instore_orders as $order): ?>
            <tr>
              <td><?php echo htmlspecialchars($order['order_id']); ?></td>
              <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
              <td><?php echo htmlspecialchars($order['stall_name']); ?></td>
              <td><?php echo htmlspecialchars(fetchOrderFoodNames($con, $order['order_id'])); ?></td>
              <td class="date-format"><?php echo date('F j, Y, g:i A', strtotime($order['order_date'])); ?></td>
              <td><?php echo number_format($order['total_price'], 2); ?></td>
              <td><?php echo htmlspecialchars($order['order_status']); ?></td>
              <td class="date-format"><?php echo date('F j, Y, g:i A', strtotime($order['receipt_date'])); ?></td>
              <td>
                <a href="edit_order.php?order_id=<?php echo urlencode($order['order_id']); ?>" class="btn btn-primary btn-sm">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="delete_order.php?order_id=<?php echo urlencode($order['order_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this order?');">
                  <i class="bi bi-trash"></i> Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="9" class="text-center">No in-store orders found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    
    <!-- GCash Payment Orders Section -->
    <h3 class="section-header">GCash Payment Orders</h3>
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Stall</th>
          <th>Food Items</th>
          <th>Date</th>
          <th>Total (₱)</th>
          <th>Status</th>
          <th>GCash Reference</th>
          <th>GCash Image</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($gcash_orders)): ?>
          <?php foreach ($gcash_orders as $order): ?>
            <tr>
              <td><?php echo htmlspecialchars($order['order_id']); ?></td>
              <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
              <td><?php echo htmlspecialchars($order['stall_name']); ?></td>
              <td><?php echo htmlspecialchars(fetchOrderFoodNames($con, $order['order_id'])); ?></td>
              <td class="date-format"><?php echo date('F j, Y, g:i A', strtotime($order['order_date'])); ?></td>
              <td><?php echo number_format($order['total_price'], 2); ?></td>
              <td><?php echo htmlspecialchars($order['order_status']); ?></td>
              <td><?php echo htmlspecialchars($order['gcash_reference']); ?></td>
              <td>
                <?php if (!empty($order['gcash_image_path'])): ?>
                  <a href="<?php echo htmlspecialchars($order['gcash_image_path']); ?>" target="_blank">View Image</a>
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </td>
              <td>
                <a href="edit_order.php?order_id=<?php echo urlencode($order['order_id']); ?>" class="btn btn-primary btn-sm">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="delete_order.php?order_id=<?php echo urlencode($order['order_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this order?');">
                  <i class="bi bi-trash"></i> Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="10" class="text-center">No gcash orders found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
