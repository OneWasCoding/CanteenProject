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
if (!isset($_SESSION['user_id']) && $_SESSION['role'] != 'Retailer' && $_SESSION['stall_id'] != 9) {
    header("Location: ../../auth/login.php");
    exit();
}
?>

<div class="main-content">
        <!-- Your main content goes here -->
        <h1>Welcome to Stall Management</h1>
        <p>This is the main content area.</p>
        <h1><?php echo $_SESSION['stall_id']; echo $_SESSION['user_id'] ?></h1>
    </div>

</body>
</html>
