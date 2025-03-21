<?php
session_start();
include '../config.php';

// Ensure the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: manage_orders.php");
    exit();
}

$order_id = $_GET['order_id'];

// Delete order details (child records)
$sql_delete_details = "DELETE FROM order_details WHERE order_id = ?";
$stmt_details = $con->prepare($sql_delete_details);
$stmt_details->bind_param("s", $order_id);
$stmt_details->execute();
$stmt_details->close();

// Delete receipt record if exists
$sql_delete_receipt = "DELETE FROM receipts WHERE order_id = ?";
$stmt_receipt = $con->prepare($sql_delete_receipt);
$stmt_receipt->bind_param("s", $order_id);
$stmt_receipt->execute();
$stmt_receipt->close();

// Delete gcash payment details if exists
$sql_delete_gcash = "DELETE FROM gcash_payment_details WHERE order_id = ?";
$stmt_gcash = $con->prepare($sql_delete_gcash);
$stmt_gcash->bind_param("s", $order_id);
$stmt_gcash->execute();
$stmt_gcash->close();

// Finally, delete the order record
$sql_delete_order = "DELETE FROM orders WHERE order_id = ?";
$stmt_order = $con->prepare($sql_delete_order);
$stmt_order->bind_param("s", $order_id);
if ($stmt_order->execute()) {
    header("Location: manage_orders.php?deleted=1");
    exit();
} else {
    echo "Error deleting order: " . htmlspecialchars($stmt_order->error);
}
$stmt_order->close();
?>
