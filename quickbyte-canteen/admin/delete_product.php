<?php
session_start();
include '../config.php';

// Ensure the logged-in user is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if product id is provided
if (!isset($_GET['item_id'])) {
    header("Location: manage_products.php");
    exit();
}

$item_id = intval($_GET['item_id']);

$sql = "DELETE FROM menu_items WHERE item_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $item_id);
if ($stmt->execute()) {
    header("Location: manage_products.php");
    exit();
} else {
    echo "Error deleting product: " . htmlspecialchars($stmt->error);
}
$stmt->close();
?>
