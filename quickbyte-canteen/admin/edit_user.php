<?php
session_start();
include '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id_to_edit = intval($_GET['user_id']);

$sql = "SELECT user_id, name, email, role, phone, address FROM users WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id_to_edit);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: manage_users.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $balance = floatval($_POST['balance']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (empty($name) || empty($email)) {
        $error = "Name and Email are required.";
    } else {
        $sql_update = "UPDATE users SET name = ?, email = ?, role = ?, balance = ?, phone = ?, address = ? WHERE user_id = ?";
        $stmt_update = $con->prepare($sql_update);
        $stmt_update->bind_param("sssdsii", $name, $email, $role, $balance, $phone, $address, $user_id_to_edit);
        if ($stmt_update->execute()) {
            $success = "User updated successfully.";
        } else {
            $error = "Failed to update user.";
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
  <title>Edit User - Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <style>
    body { font-family: 'Poppins', sans-serif; }
    .sidebar { height: 100vh; position: fixed; top: 0; left: 0; width: 220px; background: #343a40; padding-top: 20px; }
    .sidebar a { color: #ddd; padding: 10px 15px; text-decoration: none; display: block; }
    .sidebar a:hover { background: #495057; color: #fff; }
    .main-content { margin-left: 240px; padding: 20px; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h4 class="text-center text-white">Admin Dashboard</h4>
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="manage_users.php"><i class="bi bi-people"></i> Manage Users</a>
    <a href="manage_stalls.php"><i class="bi bi-shop"></i> Manage Stalls</a>
    <a href="manage_products.php"><i class="bi bi-box-seam"></i> Manage Products</a>
    <a href="manage_orders.php"><i class="bi bi-receipt"></i> Manage Orders</a>
    <a href="manage_reviews.php"><i class="bi bi-chat-left-text"></i> Manage Reviews</a>
    <a href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>

  <div class="main-content">
    <h2>Edit User</h2>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label for="name" class="form-label">Name:</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">Role:</label>
        <select class="form-select" id="role" name="role" required>
          <option value="Student" <?php echo ($user['role'] == 'Student') ? 'selected' : ''; ?>>Student</option>
          <option value="Retailer" <?php echo ($user['role'] == 'Retailer') ? 'selected' : ''; ?>>Retailer</option>
          <option value="Admin" <?php echo ($user['role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
        </select>
      </div>
     
      <div class="mb-3">
        <label for="phone" class="form-label">Phone:</label>
        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
      </div>
      <div class="mb-3">
        <label for="address" class="form-label">Address:</label>
        <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
      </div>
      <button type="submit" class="btn btn-success"><i class="bi bi-pencil"></i> Update User</button>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
