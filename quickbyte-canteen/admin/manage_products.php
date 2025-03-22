<?php
session_start();
include '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}
$sql = "SELECT m.item_id, m.name, m.price, m.category, m.image_path, s.stall_name 
        FROM menu_items m 
        JOIN stalls s ON m.stall_id = s.stall_id 
        ORDER BY m.name ASC";
$result = $con->query($sql);
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Products - Admin Dashboard</title>
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
    <h2>Manage Products</h2>
    <a href="add_product.php" class="btn btn-success mb-3"><i class="bi bi-plus-circle"></i> Add New Product</a>
    <div class="row">
      <?php if(!empty($products)): ?>
        <?php foreach($products as $product): ?>
          <div class="col-md-4 mb-4">
            <div class="card">
              <img src="<?php echo htmlspecialchars($product['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                <p class="card-text"><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
                <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                <p class="card-text"><strong>Stall:</strong> <?php echo htmlspecialchars($product['stall_name']); ?></p>
                <a href="edit_product.php?item_id=<?php echo $product['item_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                <a href="delete_product.php?item_id=<?php echo $product['item_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No products available.</p>
      <?php endif; ?>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
