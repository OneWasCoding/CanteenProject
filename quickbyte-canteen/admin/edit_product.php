<?php
session_start();
include '../config.php';

// Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['item_id']) || empty($_GET['item_id'])) {
    header("Location: manage_products.php");
    exit();
}
$item_id = intval($_GET['item_id']);

// Fetch product details
$sql = "SELECT * FROM menu_items WHERE item_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: manage_products.php");
    exit();
}

// Fetch stalls for dropdown
$sql_stalls = "SELECT stall_id, stall_name FROM stalls ORDER BY stall_name ASC";
$result_stalls = $con->query($sql_stalls);
$stalls = $result_stalls ? $result_stalls->fetch_all(MYSQLI_ASSOC) : [];

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = $_POST['category'] ?? '';
    $availability = isset($_POST['availability']) ? 1 : 0;
    $stall_id = intval($_POST['stall_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if (empty($name) || $price <= 0 || empty($category) || $stall_id <= 0) {
        $error = "Please fill in all required fields correctly.";
    } else {
        // Process image upload if provided
        $image_path = $product['image_path']; // use current image if not changed
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../uploads/products/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($ext, $allowed)) {
                $error = "Invalid image format. Only JPG, JPEG, PNG & GIF allowed.";
            } else {
                $new_filename = uniqid("prod_", true) . "." . $ext;
                $target_file = $target_dir . $new_filename;
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = "uploads/products/" . $new_filename;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }
    }
    if (empty($error)) {
        $sql_update = "UPDATE menu_items SET name = ?, price = ?, category = ?, availability = ?, image_path = ?, stall_id = ?, description = ? WHERE item_id = ?";
        $stmt_update = $con->prepare($sql_update);
        $stmt_update->bind_param("sdsiissi", $name, $price, $category, $availability, $image_path, $stall_id, $description, $item_id);
        if ($stmt_update->execute()) {
            $success = "Product updated successfully.";
            header("Location: manage_products.php?success=1");
            exit();
        } else {
            $error = "Failed to update product: " . htmlspecialchars($stmt_update->error);
        }
        $stmt_update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Product - Admin Dashboard</title>
  <!-- Bootstrap CSS & Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f4f6f9;
    }
    .admin-container {
      margin-left: 260px;
      padding: 20px;
    }
    .form-container {
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      max-width: 600px;
      margin: auto;
    }
    .form-control, .form-select {
      border-radius: 0.5rem;
    }
    .btn-primary {
      border-radius: 0.5rem;
    }
  </style>
</head>
<body>
  <!-- Sidebar Menu -->
  <?php include 'admin_menu.php'; ?>
  <div class="admin-container">
    <h2 class="mb-4">Edit Product</h2>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <div class="form-container">
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="name" class="form-label">Product Name *</label>
          <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="mb-3">
          <label for="price" class="form-label">Price (PHP) *</label>
          <input type="number" step="0.01" class="form-control" id="price" name="price" required value="<?php echo htmlspecialchars($product['price']); ?>">
        </div>
        <div class="mb-3">
          <label for="category" class="form-label">Category *</label>
          <select class="form-select" id="category" name="category" required>
            <option value="">Select Category</option>
            <option value="Snacks" <?php echo ($product['category'] == 'Snacks') ? 'selected' : ''; ?>>Snacks</option>
            <option value="Drinks" <?php echo ($product['category'] == 'Drinks') ? 'selected' : ''; ?>>Drinks</option>
            <option value="Meals" <?php echo ($product['category'] == 'Meals') ? 'selected' : ''; ?>>Meals</option>
          </select>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="availability" name="availability" <?php echo $product['availability'] ? 'checked' : ''; ?>>
          <label class="form-check-label" for="availability">Available</label>
        </div>
        <div class="mb-3">
          <label for="stall_id" class="form-label">Stall *</label>
          <select class="form-select" id="stall_id" name="stall_id" required>
            <option value="">Select Stall</option>
            <?php foreach($stalls as $stall): ?>
              <option value="<?php echo $stall['stall_id']; ?>" <?php echo ($product['stall_id'] == $stall['stall_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($stall['stall_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
        </div>
        <div class="mb-3">
          <label for="image" class="form-label">Product Image</label>
          <input class="form-control" type="file" id="image" name="image">
          <?php if (!empty($product['image_path'])): ?>
            <p class="mt-2">Current Image:</p>
            <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product Image" style="max-width: 150px;">
          <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Update Product</button>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
