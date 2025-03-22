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

// Fetch the stall name and status from the `stalls` table
$stall_name = "Unknown Stall"; // Default value
$stall_status = "Closed"; // Default value
$stall_sql = "SELECT s.stall_name, s.status FROM stalls s INNER JOIN retailers r ON s.stall_id = r.stall_id WHERE r.user_id = ?";
$stall_stmt = $con->prepare($stall_sql);
$stall_stmt->bind_param("i", $user_id);
$stall_stmt->execute();
$stall_result = $stall_stmt->get_result();

if ($stall_result->num_rows > 0) {
    $stall = $stall_result->fetch_assoc();
    $stall_name = $stall['stall_name']; 
    $stall_status = $stall['status']; // Fetch the current stall status
}
$stall_stmt->close();

// Handle Toggle Stall Status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_stall_status'])) {
    $new_status = ($stall_status == 'Open') ? 'Closed' : 'Open';

    // Update the stall status in the database
    $update_sql = "UPDATE stalls SET status = ? WHERE stall_id = ?";
    $update_stmt = $con->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $stall_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Refresh the page to reflect the updated status
    header("Location: ../stalls/stall_{$stall_id}.php");
    exit();
}

// Fetch total orders for the stall
$total_orders_sql = "SELECT COUNT(*) AS total_orders FROM orders WHERE stall_id = ?";
$total_orders_stmt = $con->prepare($total_orders_sql);
$total_orders_stmt->bind_param("i", $stall_id);
$total_orders_stmt->execute();
$total_orders_result = $total_orders_stmt->get_result();
$total_orders = $total_orders_result->fetch_assoc()['total_orders'] ?? 0;
$total_orders_stmt->close();

// Fetch total revenue (only completed orders)
$total_revenue_sql = "SELECT SUM(total_price) AS total_revenue FROM orders WHERE stall_id = ? AND order_status = 'Completed'";
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

// Fetch recent activity
$recent_activity_sql = "
    (SELECT 'order' AS type, order_id AS id, order_status AS status, order_date AS timestamp 
     FROM orders 
     WHERE stall_id = ?)
    UNION
    (SELECT 'inventory' AS type, inventory_id AS id, 'Item Added' AS status, created_at AS timestamp 
     FROM inventory 
     WHERE stall_id = ?)
    ORDER BY timestamp DESC
    LIMIT 5"; // Fetch the 5 most recent activities
$recent_activity_stmt = $con->prepare($recent_activity_sql);
$recent_activity_stmt->bind_param("ii", $stall_id, $stall_id);
$recent_activity_stmt->execute();
$recent_activity_result = $recent_activity_stmt->get_result();
$recent_activities = [];
while ($row = $recent_activity_result->fetch_assoc()) {
    $recent_activities[] = $row;
}
$recent_activity_stmt->close();

// Fetch top 5 best-selling food items for the specific stall
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

// Bind the session stall_id to the query
$top_food_stmt->bind_param("ii", $_SESSION['stall_id'], $_SESSION['stall_id']);

// Execute the query
$top_food_stmt->execute();

// Fetch the results
$top_food_result = $top_food_stmt->get_result();
$top_food_items = [];
while ($row = $top_food_result->fetch_assoc()) {
    $top_food_items[] = $row;
}

// Close the statement
$top_food_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stall Management</title>
    <link href="../../css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .stall-name {
            font-size: 1.2em;
            font-weight: bold;
        }

        .username {
            font-size: 1.1em;
        }

        .main-content {
            margin-top: 80px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-metrics {
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

        .quick-actions {
            margin-bottom: 2rem;
        }

        .action-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s ease;
        }

        .action-btn:hover {
            background-color: #d13d17;
        }

        .recent-activity {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .recent-activity h2 {
            margin-top: 0;
        }

        .recent-activity ul {
            list-style: none;
            padding: 0;
        }

        .recent-activity ul li {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .recent-activity ul li:last-child {
            border-bottom: none;
        }

        .session-data {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .session-data h3 {
            margin-top: 0;
        }

        .session-data ul {
            list-style: none;
            padding: 0;
        }

        .session-data ul li {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .session-data ul li:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Metrics -->
        <div class="dashboard-metrics">
            <div class="metric-card">
                <h3><?php echo $total_orders; ?></h3>
                <p>Total Orders</p>
                <small class="text-muted">All-time orders processed</small>
            </div>
            <div class="metric-card">
                <h3>PHP <?php echo number_format($total_revenue, 2); ?></h3>
                <p>Total Revenue</p>
                <small class="text-muted">Completed orders only</small>
            </div>
            <div class="metric-card">
                <h3><?php echo $pending_orders; ?></h3>
                <p>Pending Orders</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="../navigation/manage_menu.php"><button class="action-btn">Add New Item</button></a>
            <a href="../navigation/reports.php"><button class="action-btn">View Reports</button></a>
            <!-- Stall Status Toggle Button -->
            <form method="POST" action="" style="display: inline;">
                <button type="submit" name="toggle_stall_status" class="btn <?php echo ($stall_status == 'Open') ? 'btn-danger' : 'btn-success'; ?>">
                    <?php echo ($stall_status == 'Open') ? 'Close Stall' : 'Open Stall'; ?>
                </button>
            </form>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <ul>
                <?php if (!empty($recent_activities)): ?>
                    <?php foreach ($recent_activities as $activity): ?>
                        <li>
                            <?php
                            $type = $activity['type'];
                            $id = $activity['id'];
                            $status = $activity['status'];
                            $timestamp = date("M j, Y h:i A", strtotime($activity['timestamp']));

                            if ($type === 'order') {
                                echo "Order #$id: $status (at $timestamp)";
                            } elseif ($type === 'inventory') {
                                echo "Inventory Item #$id: $status (at $timestamp)";
                            }
                            ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No recent activity.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Top 5 Best-Selling Food Items -->
        <div class="session-data">
            <h3>Top 5 Best-Selling Food Items</h3>
            <?php if (!empty($top_food_items)): ?>
                <ul>
                    <?php foreach ($top_food_items as $item): ?>
                        <li>
                            <?php echo htmlspecialchars($item['name']); ?> - 
                            <strong><?php echo $item['total_orders']; ?></strong> orders
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No data available.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>