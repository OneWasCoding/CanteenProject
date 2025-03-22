<?php
session_start();
include '../config.php';

// (Optional) Ensure the logged-in user is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Query top user by number of orders
$sql_top_user = "
    SELECT u.name, COUNT(o.order_id) AS order_count 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id
    GROUP BY o.user_id
    ORDER BY order_count DESC
    LIMIT 1
";
$result_top_user = $con->query($sql_top_user);
$top_user = $result_top_user ? $result_top_user->fetch_assoc() : null;

// Query top stalls by number of orders
$sql_top_stalls = "
    SELECT s.stall_name, COUNT(o.order_id) AS order_count
    FROM orders o 
    JOIN stalls s ON o.stall_id = s.stall_id
    GROUP BY o.stall_id
    ORDER BY order_count DESC
    LIMIT 5
";
$result_top_stalls = $con->query($sql_top_stalls);
$top_stalls = $result_top_stalls ? $result_top_stalls->fetch_all(MYSQLI_ASSOC) : [];

// Query top products by quantity sold
$sql_top_products = "
    SELECT m.name, SUM(od.quantity) AS total_quantity
    FROM order_details od 
    JOIN menu_items m ON od.item_id = m.item_id
    GROUP BY od.item_id
    ORDER BY total_quantity DESC
    LIMIT 5
";
$result_top_products = $con->query($sql_top_products);
$top_products = $result_top_products ? $result_top_products->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard - QuickByte Canteen</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f4f6f9;
    }
    /* Content area left margin for sidebar */
    .admin-content {
      margin-left: 260px;
      padding: 20px;
    }
    .card {
      margin-bottom: 20px;
    }
    .chart-container {
      position: relative;
      margin: auto;
      height: 300px;
      width: 100%;
    }
    .dashboard-header {
      margin-bottom: 2rem;
    }
  </style>
</head>
<body>
  <!-- Include sidebar menu -->
  <?php include 'admin_menu.php'; ?>

  <!-- Main Content -->
  <div class="admin-content">
    <div class="dashboard-header">
      <h2>Welcome, Admin</h2>
      <p>Overview of key metrics</p>
    </div>

    <div class="row">
      <!-- Top User Card -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Top User</h5>
            <?php if ($top_user): ?>
              <p class="card-text"><?php echo htmlspecialchars($top_user['name']); ?> with <?php echo $top_user['order_count']; ?> orders</p>
            <?php else: ?>
              <p class="card-text">No data available</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <!-- Top Stalls Chart -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Top Stalls</h5>
            <div class="chart-container">
              <canvas id="topStallsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Top Products Chart -->
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Top Products</h5>
            <div class="chart-container">
              <canvas id="topProductsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and Chart JS initialization -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Top Stalls Chart
    const topStallsData = {
      labels: [
        <?php foreach($top_stalls as $stall) { echo '"' . htmlspecialchars($stall['stall_name']) . '",'; } ?>
      ],
      datasets: [{
        label: 'Orders',
        data: [
          <?php foreach($top_stalls as $stall) { echo $stall['order_count'] . ','; } ?>
        ],
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
      }]
    };

    const topStallsConfig = {
      type: 'bar',
      data: topStallsData,
      options: {
        scales: {
          y: { beginAtZero: true }
        },
        plugins: {
          legend: { display: false }
        }
      }
    };

    new Chart(document.getElementById('topStallsChart'), topStallsConfig);

    // Top Products Chart
    const topProductsData = {
      labels: [
        <?php foreach($top_products as $product) { echo '"' . htmlspecialchars($product['name']) . '",'; } ?>
      ],
      datasets: [{
        label: 'Quantity Sold',
        data: [
          <?php foreach($top_products as $product) { echo $product['total_quantity'] . ','; } ?>
        ],
        backgroundColor: 'rgba(153, 102, 255, 0.6)',
        borderColor: 'rgba(153, 102, 255, 1)',
        borderWidth: 1
      }]
    };

    const topProductsConfig = {
      type: 'pie',
      data: topProductsData,
      options: {
        plugins: { legend: { position: 'bottom' } }
      }
    };

    new Chart(document.getElementById('topProductsChart'), topProductsConfig);
  </script>
</body>
</html>