<?php 
ob_start();
session_start();
include '../sidepanel.php'; 
include '../../config.php';

// Check if the user is logged in and has the correct role and stall ID
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['stall_id']) || $_SESSION['role'] != 'Retailer') {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stall_id = $_SESSION['stall_id']; 

// Fetch the stall name from the `stalls` table
$stall_name = "Unknown Stall"; // Default value
$stall_sql = "SELECT s.stall_name FROM stalls s INNER JOIN retailers r ON s.stall_id = r.stall_id WHERE r.user_id = ?";
$stall_stmt = $con->prepare($stall_sql);
$stall_stmt->bind_param("i", $user_id);
$stall_stmt->execute();
$stall_result = $stall_stmt->get_result();

if ($stall_result->num_rows > 0) {
    $stall = $stall_result->fetch_assoc();
    $stall_name = $stall['stall_name']; 
}
$stall_stmt->close();

// Fetch total orders for the stall
$total_orders_sql = "SELECT COUNT(*) AS total_orders FROM orders WHERE stall_id = ?";
$total_orders_stmt = $con->prepare($total_orders_sql);
$total_orders_stmt->bind_param("i", $stall_id);
$total_orders_stmt->execute();
$total_orders_result = $total_orders_stmt->get_result();
$total_orders = $total_orders_result->fetch_assoc()['total_orders'] ?? 0;
$total_orders_stmt->close();

// Fetch total revenue (only completed orders)
$total_revenue_sql = "SELECT SUM(total_price    ) AS total_revenue FROM orders WHERE stall_id = ? AND order_status = 'Completed'";
$total_revenue_stmt = $con->prepare($total_revenue_sql);
$total_revenue_stmt->bind_param("i", $stall_id);
$total_revenue_stmt->execute();
$total_revenue_result = $total_revenue_stmt->get_result();
$total_revenue = $total_revenue_result->fetch_assoc()['total_revenue'] ?? 0;
$total_revenue_stmt->close();

// Fetch pending orders
$pending_orders_sql = "SELECT COUNT(*) AS pending_orders FROM orders WHERE stall_id = ? AND order_status = 'Pending'";
$pending_orders_stmt = $con->prepare($pending_orders_sql);
$pending_orders_stmt->bind_param("i", $stall_id);
$pending_orders_stmt->execute();
$pending_orders_result = $pending_orders_stmt->get_result();
$pending_orders = $pending_orders_result->fetch_assoc()['pending_orders'] ?? 0;
$pending_orders_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stall Management</title>
    <link href="../../css/styles.css" rel="stylesheet" />
    <style>
        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #343a40;
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            z-index: 1000;
        }
        .header-left {
            display: flex;
            align-items: center;
        }
        .stall-name {
            font-size: 1.2em;
            font-weight: bold;
        }
        .header-right {
            display: flex;
            align-items: center;
        }
        .username {
            font-size: 1.1em;
        }

        /* Main Content Styles */
        .main-content {
            margin-top: 80px;
            padding: 20px;
        }

        /* Dashboard Metrics */
        .dashboard-metrics {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .dashboard-metrics .metric-card {
            flex: 1;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .dashboard-metrics .metric-card h3 {
            margin: 0;
            font-size: 1.5em;
        }
        .dashboard-metrics .metric-card p {
            margin: 5px 0 0;
            color: #666;
        }

        /* Quick Actions */
        .quick-actions {
            margin-bottom: 20px;
        }
        .quick-actions .action-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        /* Recent Activity */
        .recent-activity {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .recent-activity h2 {
            margin-top: 0;
        }
        .recent-activity ul {
            list-style: none;
            padding: 0;
        }
        .recent-activity ul li {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .recent-activity ul li:last-child {
            border-bottom: none;
        }

        /* Debug Session Data */
        .session-data {
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <span class="stall-name"><?php echo htmlspecialchars($stall_name); ?></span>
        </div>
        <div class="header-right">
            <span class="username"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Metrics -->
        <div class="dashboard-metrics">
            <div class="metric-card">
                <h3><?php echo $total_orders; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="metric-card">
                <h3>PHP <?php echo number_format($total_revenue, 2); ?></h3>
                <p>Total Revenue</p>
            </div>
            <div class="metric-card">
                <h3><?php echo $pending_orders; ?></h3>
                <p>Pending Orders</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="action-btn">Add New Item</button>
            <button class="action-btn">View Reports</button>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <ul>
                <li>Order #1234 placed by John Doe</li>
                <li>Order #1235 placed by Jane Smith</li>
                <li>New menu item added: Burger</li>
                <li>Settings updated: Payment methods</li>
            </ul>
        </div>

        <!-- Debug Session Data -->
        <div class="session-data">
            <h3>Session Data</h3>
            <p>Stall ID: <?php echo htmlspecialchars($stall_id); ?></p>
            <p>User ID: <?php echo htmlspecialchars($user_id); ?></p>
        </div>
    </div>

</body>
</html>
