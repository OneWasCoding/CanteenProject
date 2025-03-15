<?php
session_start();
include '../config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback_id'])) {
    // Sanitize feedback_id as integer
    $feedback_id = intval($_POST['feedback_id']);
    
    // Prepare DELETE query
    $sql = "DELETE FROM feedback WHERE feedback_id = ? AND user_id = ?";
    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param("ii", $feedback_id, $user_id);
        if ($stmt->execute()) {
            header("Location: manage_reviews.php");
            exit();
        } else {
            echo "Error deleting review: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . htmlspecialchars($con->error);
    }
} else {
    header("Location: manage_reviews.php");
    exit();
}
?>
