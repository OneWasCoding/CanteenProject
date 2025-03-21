<?php
session_start();
include '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}
$sql = "SELECT f.feedback_id, f.rating, f.comment, f.created_at, u.name AS reviewer_name, s.stall_name 
        FROM feedback f 
        JOIN users u ON f.user_id = u.user_id 
        JOIN stalls s ON f.stall_id = s.stall_id 
        ORDER BY f.created_at DESC";
$result = $con->query($sql);
$reviews = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Reviews - Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
    .admin-content { margin-left: 260px; padding: 20px; }
    .card { margin-bottom: 15px; }
    .card:hover { box-shadow: 0 0 15px rgba(0,0,0,0.3); }
  </style>
</head>
<body>
  <?php include 'admin_menu.php'; ?>
  <div class="admin-content">
    <h2>Manage Reviews</h2>
    <?php if(!empty($reviews)): ?>
      <?php foreach($reviews as $review): ?>
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($review['reviewer_name']); ?> - <?php echo htmlspecialchars($review['stall_name']); ?></h5>
            <p class="card-text">
              Rating: <?php echo htmlspecialchars($review['rating']); ?>/5 <br>
              <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
            </p>
            <p class="text-muted" style="font-size: 0.8rem;"><?php echo htmlspecialchars($review['created_at']); ?></p>
            <a href="edit_review.php?feedback_id=<?php echo urlencode($review['feedback_id']); ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
            <a href="delete_review.php?feedback_id=<?php echo urlencode($review['feedback_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No reviews found.</p>
    <?php endif; ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
