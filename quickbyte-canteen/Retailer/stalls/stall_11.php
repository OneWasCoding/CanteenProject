<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stall Management</title>
    <link href="../../css/styles.css" rel="stylesheet" />
</head>

<?php 
session_start();
include '../rheader.php'; 
include '../../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Retailer') {
    header("Location: ../../auth/login.php");
    exit();
}


$stall_name = "Unknown Stall";

if ($_SESSION['stall_id'] > 0) {
    // Prepare SQL statement to fetch stall name
    $sql = "SELECT name FROM stalls WHERE stall_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $_SESSION['stall_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stall_name = htmlspecialchars($row['name']); // Corrected column name
    }

    $stmt->close();
}

?>

<body>

    <div class="main-content">
        <h1>Welcome to 
            <?php echo $stall_name; ?>!
        </h1>
        <p>Manage your stall efficiently with the options below.</p>

        <div class="management-options">
            <a href="#" class="option">Manage Menu</a>
            <a href="#" class="option">View Orders</a>
            <a href="#" class="option">Update Settings</a>
        </div>
    </div>

</body>
</html>
