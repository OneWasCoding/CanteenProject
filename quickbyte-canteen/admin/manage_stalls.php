<?php
session_start();
include '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}
$sql = "SELECT stall_id, stall_name, description, image_path FROM stalls ORDER BY stall_name ASC";
$result = $con->query($sql);
$stalls = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Stalls - Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; margin: 0; }
    .admin-content { margin-left: 260px; padding: 20px; }
    .card:hover { box-shadow: 0 0 15px rgba(0,0,0,0.3); }
  </style>
</head>
<body>
  <?php include 'admin_menu.php'; ?>
  <div class="admin-content">
    <h2>Manage Stalls</h2>
    <a href="add_stall.php" class="btn btn-success mb-3"><i class="bi bi-plus-circle"></i> Add New Stall</a>
    <div class="row">
      <?php if(!empty($stalls)): ?>
        <?php foreach($stalls as $stall): ?>
          <div class="col-md-4 mb-4">
            <div class="card">
              <img src="<?php echo htmlspecialchars($stall['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($stall['stall_name']); ?>">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($stall['stall_name']); ?></h5>
                <p class="card-text"><?php echo htmlspecialchars($stall['description']); ?></p>
                <a href="edit_stall.php?stall_id=<?php echo $stall['stall_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                <a href="delete_stall.php?stall_id=<?php echo $stall['stall_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No stalls available.</p>
      <?php endif; ?>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
include '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}
$sql = "SELECT stall_id, stall_name, description, image_path FROM stalls ORDER BY stall_name ASC";
$result = $con->query($sql);
$stalls = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Stalls - Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; margin: 0; }
    .admin-content { margin-left: 260px; padding: 20px; }
    .card:hover { box-shadow: 0 0 15px rgba(0,0,0,0.3); }
  </style>
</head>
<body>
  <?php include 'admin_menu.php'; ?>
  <div class="admin-content">
    <h2>Manage Stalls</h2>
    <a href="add_stall.php" class="btn btn-success mb-3"><i class="bi bi-plus-circle"></i> Add New Stall</a>
    <div class="row">
      <?php if(!empty($stalls)): ?>
        <?php foreach($stalls as $stall): ?>
          <div class="col-md-4 mb-4">
            <div class="card">
              <img src="<?php echo htmlspecialchars($stall['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($stall['stall_name']); ?>">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($stall['stall_name']); ?></h5>
                <p class="card-text"><?php echo htmlspecialchars($stall['description']); ?></p>
                <a href="edit_stall.php?stall_id=<?php echo $stall['stall_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                <a href="delete_stall.php?stall_id=<?php echo $stall['stall_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No stalls available.</p>
      <?php endif; ?>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
