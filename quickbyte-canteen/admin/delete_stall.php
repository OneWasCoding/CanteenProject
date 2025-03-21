<?php
session_start();
include '../config.php';

// Ensure the logged-in user is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if stall_id is provided
if (!isset($_GET['stall_id'])) {
    header("Location: manage_stalls.php");
    exit();
}

$stall_id = intval($_GET['stall_id']);
$sql = "DELETE FROM stalls WHERE stall_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $stall_id);
if ($stmt->execute()) {
    header("Location: manage_stalls.php");
    exit();
} else {
    echo "Error deleting stall: " . htmlspecialchars($stmt->error);
}
$stmt->close();
?>
