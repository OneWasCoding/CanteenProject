<?php
include '../../config.php'; // Include the database connection

// Check if the user is logged in and has the correct role and stall ID
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['stall_id']) || $_SESSION['role'] != 'Retailer') {
    header("Location: ../../auth/login.php");
    exit();
}

// Fetch the stall name from the `stalls` table
$stall_name = "Stall Manager"; // Default value
$stall_sql = "SELECT stall_name FROM stalls WHERE stall_id = ?";
$stall_stmt = $con->prepare($stall_sql);
$stall_stmt->bind_param("i", $_SESSION['stall_id']);
$stall_stmt->execute();
$stall_result = $stall_stmt->get_result();

if ($stall_result->num_rows > 0) {
    $stall = $stall_result->fetch_assoc();
    $stall_name = $stall['stall_name']; // Get the stall name
}
$stall_stmt->close();

// Generate the stall landing page URL
$stall_landing_page = "../stalls/stall_{$_SESSION['stall_id']}.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stall Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../../css/styles.css" rel="stylesheet" />
    <style>
        /* Custom styles for the side panel */
        .side-panel {
            height: 100vh;
            width: 60px;
            /* Small width when not hovered */
            position: fixed;
            top: 0;
            left: 0;
            background-color: #f8f9fa;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
            /* Smooth transition */
            overflow: hidden;
            /* Hide overflow content */
        }

        .side-panel:hover {
            width: 250px;
            /* Expanded width on hover */
        }

        .side-panel .header {
            display: flex;
            align-items: center;
            padding: 20px 10px;
            background-color: #e9ecef;
            border-bottom: 1px solid #ddd;
        }

        .side-panel .header i {
            font-size: 1.5em;
            /* Icon size */
            color: #333;
            /* Icon color */
        }

        .side-panel .header span {
            opacity: 0;
            /* Hide text when not hovered */
            transition: opacity 0.3s ease;
            margin-left: 10px;
            /* Space between icon and text */
            font-size: 1.2em;
            font-weight: bold;
        }

        .side-panel:hover .header span {
            opacity: 1;
            /* Show text on hover */
        }

        .management-options {
            padding: 10px;
        }

        .management-options .option {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 10px 0;
            text-decoration: none;
            color: #333;
            background-color: #e9ecef;
            border-radius: 5px;
            transition: background-color 0.3s;
            white-space: nowrap;
            /* Prevent text from wrapping */
        }

        .management-options .option span {
            opacity: 0;
            /* Hide text when not hovered */
            transition: opacity 0.3s ease;
        }

        .side-panel:hover .management-options .option span {
            opacity: 1;
            /* Show text on hover */
        }

        .management-options .option:hover {
            background-color: #ced4da;
        }

        .management-options .option i {
            margin-right: 10px;
            /* Space between icon and text */
            font-size: 1.2em;
            /* Icon size */
            opacity: 1;
            /* Always show icons */
        }

        .main-content {
            margin-left: 60px;
            /* Adjust based on the small width of the side panel */
            padding: 20px;
            transition: margin-left 0.3s ease;
            /* Smooth transition */
        }

        .side-panel:hover~.main-content {
            margin-left: 250px;
            /* Adjust based on the expanded width of the side panel */
        }

        /* Make the stall name clickable */
        .stall-name-link {
            text-decoration: none; /* Remove underline */
            color: inherit; /* Inherit the color from parent */
        }

        .stall-name-link:hover {
            color: #007bff; /* Change color on hover */
        }
    </style>
</head>

<body>

    <!-- Side Panel -->
    <div class="side-panel">
        <!-- Header Section -->
        <div class="header">
            <i class="fas fa-store"></i> <!-- Icon for the stall -->
            <a href="<?php echo $stall_landing_page; ?>" class="stall-name-link">
                <span><?php echo htmlspecialchars($stall_name); ?></span> <!-- Dynamic stall name -->
            </a>
        </div>

        <!-- Management Options -->
        <div class="management-options">
            <a href="#" class="option">
                <i class="fas fa-utensils"></i> <!-- Icon for Manage Menu -->
                <span>Manage Menu</span>
            </a>

            <a href="../navigation/reports.php" class="option">
                <i class="fa-duotone fa-solid fa-chart-column"></i>
                <span>Reports</span>
            </a>

            <a href="#" class="option">
                <i class="fas fa-list-alt"></i> <!-- Icon for View Orders -->
                <span>View Orders</span>
            </a>

            <a href="#" class="option">
                <i class="fa-solid fa-warehouse"></i> <!-- Icon for Inventory -->
                <span>Inventory</span>
            </a>

            <a href="#" class="option">
                <i class="fa-solid fa-message"></i>
                <span>Feedback</span>
            </a>

            <a href="#" class="option">
                <i class="fas fa-cog"></i> <!-- Icon for Update Settings -->
                <span>Update Settings</span>
            </a>

            <a href="../../auth/logout.php" class="option">
                <i class="fa-duotone fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>