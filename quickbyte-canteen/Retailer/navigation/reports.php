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

// Fetch total sales revenue (completed orders)
$total_revenue_sql = "SELECT SUM(total_price) AS total_revenue FROM orders WHERE stall_id = ? AND order_status = 'Completed'";
$total_revenue_stmt = $con->prepare($total_revenue_sql);
$total_revenue_stmt->bind_param("i", $stall_id);
$total_revenue_stmt->execute();
$total_revenue_result = $total_revenue_stmt->get_result();
$total_revenue = $total_revenue_result->fetch_assoc()['total_revenue'] ?? 0;
$total_revenue_stmt->close();

// Fetch total orders count
$total_orders_sql = "SELECT COUNT(*) AS total_orders FROM orders WHERE stall_id = ?";
$total_orders_stmt = $con->prepare($total_orders_sql);
$total_orders_stmt->bind_param("i", $stall_id);
$total_orders_stmt->execute();
$total_orders_result = $total_orders_stmt->get_result();
$total_orders = $total_orders_result->fetch_assoc()['total_orders'] ?? 0;
$total_orders_stmt->close();

// Fetch top-selling food items
$top_food_sql = "
    SELECT mi.name, COUNT(od.item_id) AS total_orders
    FROM order_details od
    INNER JOIN menu_items mi ON od.item_id = mi.item_id
    INNER JOIN orders o ON od.order_id = o.order_id
    WHERE o.stall_id = ? and mi.stall_id = ?
    GROUP BY od.item_id
    ORDER BY total_orders DESC
    LIMIT 5";
$top_food_stmt = $con->prepare($top_food_sql);
$top_food_stmt->bind_param("ii", $stall_id, $stall_id);
$top_food_stmt->execute();
$top_food_result = $top_food_stmt->get_result();
$top_food_items = [];
while ($row = $top_food_result->fetch_assoc()) {
    $top_food_items[] = $row;
}
$top_food_stmt->close();

// Fetch sales trends (Daily Sales for the last 7 days)
$sales_trend_sql = "
    SELECT DATE(order_date) AS order_day, SUM(total_price) AS daily_sales
    FROM orders
    WHERE stall_id = ? AND order_status = 'Completed' 
    GROUP BY order_day
    ORDER BY order_day DESC
    LIMIT 7";
$sales_trend_stmt = $con->prepare($sales_trend_sql);
$sales_trend_stmt->bind_param("i", $stall_id);
$sales_trend_stmt->execute();
$sales_trend_result = $sales_trend_stmt->get_result();
$sales_trends = [];
while ($row = $sales_trend_result->fetch_assoc()) {
    $sales_trends[] = $row;
}
$sales_trend_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics Dashboard</title>
    <link href="../../css/styles.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e44d26;
            --background-color: #f8f9fa;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .metric-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
        }

        .metric-card h3 {
            color: var(--secondary-color);
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
        }

        .metric-card p {
            color: #666;
            margin: 0;
            font-weight: 500;
        }

        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .chart-title {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--secondary-color);
            display: inline-block;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background-color: var(--primary-color);
            color: white;
        }

        .data-table tr:hover {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Sales Analytics Dashboard</h1>
            <div class="time-range">
                <small>Last updated: <?php echo date('M j, Y H:i'); ?></small>
            </div>
        </div>

        <div class="metric-grid">
            <div class="metric-card">
                <h3>PHP <?php echo number_format($total_revenue, 2); ?></h3>
                <p>Total Revenue</p>
                <small class="text-muted">Completed orders only</small>
            </div>
            <div class="metric-card">
                <h3><?php echo $total_orders; ?></h3>
                <p>Total Orders</p>
                <small class="text-muted">All-time orders processed</small>
            </div>
        </div>

        <div class="chart-container">
            <h2 class="chart-title">Sales Trends (Last 7 Days)</h2>
            <canvas id="salesTrendChart"></canvas>
        </div>

        <div class="chart-container">
            <h2 class="chart-title">Top Performing Items</h2>
            <canvas id="topItemsChart"></canvas>
        </div>

        <div class="chart-container">
            <h2 class="chart-title">Detailed Sales Data</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Daily Sales</th>
                        <th>Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales_trends as $trend): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($trend['order_day']); ?></td>
                        <td>PHP <?php echo number_format($trend['daily_sales'], 2); ?></td>
                        <td><?php echo '-'; // Add actual order count if available ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Sales Trend Line Chart
        const salesData = {
            labels: [<?php echo '"' . implode('","', array_column($sales_trends, 'order_day')) . '"'; ?>],
            datasets: [{
                label: 'Daily Sales',
                data: [<?php echo implode(',', array_column($sales_trends, 'daily_sales')); ?>],
                borderColor: '#e44d26',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(228, 77, 38, 0.05)'
            }]
        };

        new Chart(document.getElementById('salesTrendChart'), {
            type: 'line',
            data: salesData,
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'PHP ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Top Items Bar Chart
        const topItemsData = {
            labels: [<?php echo '"' . implode('","', array_column($top_food_items, 'name')) . '"'; ?>],
            datasets: [{
                label: 'Number of Orders',
                data: [<?php echo implode(',', array_column($top_food_items, 'total_orders')); ?>],
                backgroundColor: '#2c3e50',
                borderColor: '#1a252f',
                borderWidth: 1
            }]
        };

        new Chart(document.getElementById('topItemsChart'), {
            type: 'bar',
            data: topItemsData,
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
