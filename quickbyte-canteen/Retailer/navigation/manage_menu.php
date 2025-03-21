<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            border-radius: 0.25rem;
        }
        .status-available {
            background-color: #d4edda;
            color: #155724;
        }
        .status-out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
        }
        .action-links .btn {
            margin-right: 8px;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.03);
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .modal-content {
            border-radius: 0.5rem;
        }
        .modal-header {
            background-color: #f0f0f0;
            border-bottom: 1px solid #ddd;
        }
        .modal-footer {
            background-color: #f0f0f0;
            border-top: 1px solid #ddd;
        }
        .modal-title {
            font-weight: bold;
        }
        .form-label {
            font-weight: bold;
        }
        .food-image {
            max-width: 100px;
            max-height: 100px;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <?php 
    ob_start();
    session_start();
    include '../sidepanel.php'; 
    include '../../config.php';

    // Check if the user is logged in as a retailer
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['stall_id']) || $_SESSION['role'] != 'Retailer') {
        header("Location: ../../auth/login.php");
        exit();
    }

    $stall_id = $_SESSION['stall_id'];

    // Handle Add Item
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $availability = $_POST['availability'];
        $quantity = $_POST['quantity'];
        $expiration = $_POST['expiration_day'];
        $image_path = ''; // Initialize image path
    
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../../images/";
            $target_file = $target_dir . basename($_FILES['image']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
            // Check if the file is an image
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check !== false) {
                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = $target_file;
                }
            }
        }
    
        // Insert into menu_items
        $add_sql = "INSERT INTO menu_items (stall_id, name, price, availability, image_path) VALUES (?, ?, ?, ?, ?)";
        $add_stmt = $con->prepare($add_sql);
        $add_stmt->bind_param("isdss", $stall_id, $name, $price, $availability, $image_path);
        $add_stmt->execute();
        $item_id = $con->insert_id; // Get the last inserted item_id
        $add_stmt->close();
    
        // Insert into food_storage with stall_id
        $storage_sql = "INSERT INTO food_storage (item_id, stall_id, quantity, expiration_day) VALUES (?, ?, ?, ?)";
        $storage_stmt = $con->prepare($storage_sql);
        $storage_stmt->bind_param("iiis", $item_id, $stall_id, $quantity, $expiration);
        $storage_stmt->execute();
        $storage_stmt->close();
    
        header("Location: manage_menu.php");
        exit();
    }

    // Handle Edit Item
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_item'])) {
        $id = $_POST['item_id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $availability = $_POST['availability'];
        $quantity = $_POST['quantity'];
        $expiration = $_POST['expiration'];
        $current_image = $_POST['current_image']; // Current image path from the hidden input
    
        // Initialize image_path with the current image
        $image_path = $current_image;
    
        // Handle image upload if a new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../../images/";
            $target_file = $target_dir . basename($_FILES['image']['name']);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
            // Check if the file is an image
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check !== false) {
                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = $target_file; // Update image_path with the new image
                }
            }
        }
    
        // Update menu_items
        $edit_sql = "UPDATE menu_items SET name = ?, price = ?, availability = ?, image_path = ? WHERE item_id = ? AND stall_id = ?";
        $edit_stmt = $con->prepare($edit_sql);
        $edit_stmt->bind_param("sdssii", $name, $price, $availability, $image_path, $id, $stall_id);
        $edit_stmt->execute();
        $edit_stmt->close();
    
        // Update food_storage
        $storage_sql = "UPDATE food_storage SET quantity = ?, expiration_day = ? WHERE item_id = ?";
        $storage_stmt = $con->prepare($storage_sql);
        $storage_stmt->bind_param("isi", $quantity, $expiration, $id);
        $storage_stmt->execute();
        $storage_stmt->close();
    
        header("Location: manage_menu.php");
        exit();
    }

    // Handle Delete Item
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Delete from food_storage first
    $delete_storage_sql = "DELETE FROM food_storage WHERE item_id = ?";
    $delete_storage_stmt = $con->prepare($delete_storage_sql);
    $delete_storage_stmt->bind_param("i", $id);
    $delete_storage_stmt->execute();
    $delete_storage_stmt->close();

    // Delete from menu_items
    $delete_menu_sql = "DELETE FROM menu_items WHERE item_id = ? AND stall_id = ?";
    $delete_menu_stmt = $con->prepare($delete_menu_sql);
    $delete_menu_stmt->bind_param("ii", $id, $stall_id);
    $delete_menu_stmt->execute();
    $delete_menu_stmt->close();

    header("Location: manage_menu.php");
    exit();
}

    // Fetch menu items with quantity, expiration, and image_path
    $menu_sql = "
        SELECT mi.item_id, mi.name, mi.price, mi.availability, mi.image_path, fs.quantity, fs.expiration_day 
        FROM menu_items mi
        LEFT JOIN food_storage fs ON mi.item_id = fs.item_id
        WHERE mi.stall_id = ?
    ";
    $menu_stmt = $con->prepare($menu_sql);
    $menu_stmt->bind_param("i", $stall_id);
    $menu_stmt->execute();
    $menu_result = $menu_stmt->get_result();
    $menu_items = [];
    while ($row = $menu_result->fetch_assoc()) {
        $menu_items[] = $row;
    }
    $menu_stmt->close();
    ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h1 class="h2 mb-0">Manage Menu</h1>
                    <p class="text-muted">Total Items: <?php echo count($menu_items); ?></p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Menu Items</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Item Name</th>
                                    <th>Price</th>
                                    <th>Availability</th>
                                    <th>Quantity</th>
                                    <th>Expiration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menu_items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="food-image" alt="Food Image">
                                            <?php else: ?>
                                                <span>No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?></td>
                                        <td>PHP <?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo ($item['availability'] === 'Available') ? 'status-available' : 'status-out-of-stock'; ?>">
                                                <?php echo htmlspecialchars($item['availability']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($item['expiration_day']); ?></td>
                                        <td class="action-links">
                                            <button onclick="editItem('<?php echo $item['item_id']; ?>', 
                                                      '<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>', 
                                                      '<?php echo $item['price']; ?>', 
                                                      '<?php echo $item['availability']; ?>', 
                                                      '<?php echo $item['quantity']; ?>', 
                                                      '<?php echo $item['expiration_day']; ?>', 
                                                      '<?php echo $item['image_path']; ?>')"
                                                class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </button>
                                            <a href="manage_menu.php?delete_id=<?php echo $item['item_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this item?')">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Add New Item</h3>
                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="name" class="form-control" placeholder="Item Name" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="price" class="form-control" placeholder="Price" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <select name="availability" class="form-select">
                                <option value="Available">Available</option>
                                <option value="Out Of Stock">Out Of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="quantity" class="form-control" placeholder="Quantity" required>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="expiration_day" class="form-control" placeholder="Expiration" required>
                        </div>
                        <div class="col-md-3">
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" name="add_item" class="btn btn-success w-100">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bootstrap Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="item_id" id="edit_item_id">
                            <div class="mb-3">
                                <label class="form-label">Item Name</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" name="price" id="edit_price" step="0.01" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Availability</label>
                                <select name="availability" id="edit_availability" class="form-select">
                                    <option value="Available">Available</option>
                                    <option value="Out Of Stock">Out Of Stock</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" id="edit_quantity" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Expiration</label>
                                <input type="date" name="expiration" id="edit_expiration" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <input type="hidden" name="current_image" id="edit_current_image">
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="food-image mt-2" alt="Current Image">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="edit_item" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editItem(id, name, price, availability, quantity, expiration, image_path) {
            document.getElementById('edit_item_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_availability').value = availability;
            document.getElementById('edit_quantity').value = quantity;
            document.getElementById('edit_expiration').value = expiration;
            document.getElementById('edit_current_image').value = image_path;

            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }
    </script>
</body>
</html>