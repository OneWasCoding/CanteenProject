<?php
session_start();
include '../config.php';

// Ensure the logged-in user is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if feedback_id is provided
if (!isset($_GET['feedback_id']) || empty($_GET['feedback_id'])) {
    header("Location: manage_reviews.php");
    exit();
}

$feedback_id = intval($_GET['feedback_id']);

// Fetch existing review
$sql = "SELECT feedback_id, rating, comment FROM feedback WHERE feedback_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $feedback_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();
$stmt->close();

if (!$review) {
    header("Location: manage_reviews.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $error = "Please provide a rating between 1 and 5.";
    } else {
        $sql_update = "UPDATE feedback SET rating = ?, comment = ? WHERE feedback_id = ?";
        $stmt_update = $con->prepare($sql_update);
        $stmt_update->bind_param("isi", $rating, $comment, $feedback_id);
        if ($stmt_update->execute()) {
            $success = "Review updated successfully.";
            // Optionally, redirect back to manage_reviews.php after update
            header("Location: manage_reviews.php");
            exit();
        } else {
            $error = "Failed to update review.";
        }
        $stmt_update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Review - Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css">
  <style>
    body { 
      font-family: 'Poppins', sans-serif; 
      background-color: #f4f6f9; 
    }
    .admin-content { 
      margin-left: 260px; 
      padding: 20px; 
    }
    .form-container { 
      max-width: 600px; 
      margin: auto; 
      background: #fff; 
      padding: 20px; 
      border-radius: 10px; 
      box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
    }
  </style>
</head>
<body>
  <?php include 'admin_menu.php'; ?>
  <div class="admin-content">
    <div class="form-container">
      <h2>Edit Review</h2>
      <?php if ($error): ?>
         <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
         <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      <form method="POST">
         <div class="mb-3">
            <label for="rating" class="form-label">Rating (1-5):</label>
            <select class="form-select" name="rating" id="rating" required>
               <option value="">Select rating</option>
               <?php for ($i = 1; $i <= 5; $i++): ?>
                   <option value="<?php echo $i; ?>" <?php echo ($review['rating'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
               <?php endfor; ?>
            </select>
         </div>
         <div class="mb-3">
            <label for="comment" class="form-label">Comment:</label>
            <textarea class="form-control" name="comment" id="comment" rows="5" required><?php echo htmlspecialchars($review['comment']); ?></textarea>
         </div>
         <button type="submit" class="btn btn-primary"><i class="bi bi-pencil"></i> Update Review</button>
         <a href="manage_reviews.php" class="btn btn-secondary">Cancel</a>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
