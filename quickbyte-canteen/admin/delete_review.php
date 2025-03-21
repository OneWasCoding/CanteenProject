<?php
session_start();
include '../config.php';

// Ensure the logged-in user is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['feedback_id']) || empty($_GET['feedback_id'])) {
    header("Location: manage_reviews.php");
    exit();
}

$feedback_id = intval($_GET['feedback_id']);

$sql = "DELETE FROM feedback WHERE feedback_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $feedback_id);

if ($stmt->execute()) {
    header("Location: manage_reviews.php");
    exit();
} else {
    echo "Error deleting review: " . htmlspecialchars($stmt->error);
}

$stmt->close();
?>
