<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id_to_delete = intval($_GET['user_id']);

$sql = "DELETE FROM users WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id_to_delete);
if ($stmt->execute()) {
    header("Location: manage_users.php");
    exit();
} else {
    echo "Error deleting user: " . htmlspecialchars($stmt->error);
}
$stmt->close();
?>
