<?php
session_start();
include '../config.php';

// Ensure the logged-in user is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stall_name = trim($_POST['stall_name']);
    $description = trim($_POST['description']);
    
    // Validate stall name
    if (empty($stall_name)) {
        $error = "Stall name is required.";
    } else {
        // Process image upload if provided
        $image_path = "default_stall.jpg"; // default image
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../uploads/stalls/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            // Append time to filename to ensure uniqueness
            $filename = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $target_dir . $filename;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Store the relative path
                    $image_path = "uploads/stalls/" . $filename;
                } else {
                    $error = "Error uploading image.";
                }
            } else {
                $error = "Uploaded file is not an image.";
            }
        }
        
        if (empty($error)) {
            $sql = "INSERT INTO stalls (stall_name, description, image_path) VALUES (?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sss", $stall_name, $description, $image_path);
            if ($stmt->execute()) {
                $success = "Stall added successfully.";
            } else {
                $error = "Error adding stall: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Stall - Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <style>
    body { font-family: 'Poppins', sans-serif; }
    .admin-container { display: flex; }
    .sidebar {
      width: 250px; background: #343a40; color: white; min-height: 100vh;
    }
    .sidebar a { color: white; text-decoration: none; display: block; padding: 10px 15px; }
    .sidebar a:hover { background: #495057; }
    .content { flex-grow: 1; padding: 20px; }
  </style>
</head>
<body>
  <div class="admin-container">
    <!-- Sidebar -->
    <div class="sidebar">
      <h3 class="text-center py-3">Admin Menu</h3>
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="manage_stalls.php">Manage Stalls</a>
      <a href="manage_products.php">Manage Products</a>
      <a href="manage_orders.php">Manage Orders</a>
      <a href="manage_reviews.php">Manage Reviews</a>
      <a href="manage_users.php">Manage Users</a>
    </div>
    <!-- Main Content -->
    <div class="content">
      <h2>Add New Stall</h2>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Stall Name</label>
          <input type="text" name="stall_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Stall Image</label>
          <input type="file" name="image" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Add Stall</button>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
