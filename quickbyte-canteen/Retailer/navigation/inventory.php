<?php
ob_start();
session_start();
include '../sidepanel.php';
include '../../config.php';

// Check if the user is logged in and has the correct role and stall ID
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['stall_id']) || $_SESSION['role'] != 'Retailer') {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stall_id = $_SESSION['stall_id'];

// Handle search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "AND (p.product_name LIKE ? OR p.category LIKE ?)";
}

// Fetch inventory data for the specific stall with optional search
$inventory_sql = "SELECT i.*, p.product_name, p.category, p.unit 
                  FROM inventory i 
                  JOIN products p ON i.product_id = p.product_id 
                  WHERE i.stall_id = ? $search_condition";
$inventory_stmt = $con->prepare($inventory_sql);

if (!empty($search)) {
    $search_param = "%$search%";
    $inventory_stmt->bind_param("iss", $stall_id, $search_param, $search_param);
} else {
    $inventory_stmt->bind_param("i", $stall_id);
}

$inventory_stmt->execute();
$inventory_result = $inventory_stmt->get_result();
$inventory_data = [];
while ($row = $inventory_result->fetch_assoc()) {
    $inventory_data[] = $row;
}
$inventory_stmt->close();

// Fetch all products for the dropdown
$products_sql = "SELECT * FROM products";
$products_result = $con->query($products_sql);
$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

// Handle form submission for adding a new product and adding it to inventory
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $unit = $_POST['unit'];
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date'];

    // Validate input
    if (empty($product_name) || empty($category) || empty($unit) || empty($quantity) || empty($expiry_date)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif ($quantity <= 0) {
        $_SESSION['error'] = "Quantity must be a positive number.";
    } else {
        // Check if the product already exists in the products table
        $check_product_sql = "SELECT product_id FROM products WHERE product_name = ? AND category = ? AND unit = ?";
        $check_product_stmt = $con->prepare($check_product_sql);
        $check_product_stmt->bind_param("sss", $product_name, $category, $unit);
        $check_product_stmt->execute();
        $check_product_result = $check_product_stmt->get_result();

        if ($check_product_result->num_rows > 0) {
            // Product already exists, get its ID
            $product = $check_product_result->fetch_assoc();
            $product_id = $product['product_id'];
        } else {
            // Insert new product into the products table
            $insert_product_sql = "INSERT INTO products (product_name, category, unit) VALUES (?, ?, ?)";
            $insert_product_stmt = $con->prepare($insert_product_sql);
            $insert_product_stmt->bind_param("sss", $product_name, $category, $unit);

            if ($insert_product_stmt->execute()) {
                $product_id = $insert_product_stmt->insert_id; // Get the ID of the newly inserted product
            } else {
                $_SESSION['error'] = "Failed to add product. Please try again.";
                $insert_product_stmt->close();
                header("Location: inventory.php");
                exit();
            }

            $insert_product_stmt->close();
        }

        // Insert into inventory table
        $insert_inventory_sql = "INSERT INTO inventory (stall_id, product_id, quantity, expiry_date, created_at) VALUES (?, ?, ?, ?, now())";
        $insert_inventory_stmt = $con->prepare($insert_inventory_sql);
        $insert_inventory_stmt->bind_param("iiis", $stall_id, $product_id, $quantity, $expiry_date);

        if ($insert_inventory_stmt->execute()) {
            $_SESSION['success'] = "Product and inventory added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add inventory. Please try again.";
        }

        $insert_inventory_stmt->close();
    }

    // Refresh the page to reflect the new product and inventory
    header("Location: inventory.php");
    exit();
}

// Handle form submission for updating inventory
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_inventory'])) {
    $inventory_id = $_POST['inventory_id'];
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date'];

    // Validate input
    if (empty($quantity) || empty($expiry_date)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif ($quantity <= 0) {
        $_SESSION['error'] = "Quantity must be a positive number.";
    } else {
        $update_sql = "UPDATE inventory SET quantity = ?, expiry_date = ?, last_updated = now() WHERE inventory_id = ?";
        $update_stmt = $con->prepare($update_sql);
        $update_stmt->bind_param("isi", $quantity, $expiry_date, $inventory_id);

        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Inventory updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update inventory. Please try again.";
        }

        $update_stmt->close();
    }

    // Refresh the page to reflect the updated inventory
    header("Location: inventory.php");
    exit();
}

// Handle form submission for deleting inventory
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_inventory'])) {
    $inventory_id = $_POST['inventory_id'];

    $delete_sql = "DELETE FROM inventory WHERE inventory_id = ?";
    $delete_stmt = $con->prepare($delete_sql);
    $delete_stmt->bind_param("i", $inventory_id);

    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Inventory deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete inventory. Please try again.";
    }

    $delete_stmt->close();

    // Refresh the page to reflect the deleted inventory
    header("Location: inventory.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .table-responsive {
            margin-top: 20px;
        }
        .search-form {
            margin-bottom: 20px;
            max-width: 300px; /* Limit the width of the search bar */
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h1 class="h2 mb-0">Inventory Management</h1>
                </div>
            </div>

            <!-- Display Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Compact Search Form -->
            <div class="search-form">
                <form method="GET" action="" class="d-flex">
                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search by product or category" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </form>
            </div>

            <!-- Inventory List -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Inventory List</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Inventory ID</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Expiry Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($inventory_data)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No inventory found for this stall.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($inventory_data as $inventory): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($inventory['inventory_id']); ?></td>
                                            <td><?php echo htmlspecialchars($inventory['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($inventory['category']); ?></td>
                                            <td><?php echo htmlspecialchars($inventory['unit']); ?></td>
                                            <td><?php echo htmlspecialchars($inventory['quantity']); ?></td>
                                            <td><?php echo htmlspecialchars($inventory['expiry_date']); ?></td>
                                            <td>
                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" 
                                                    data-inventory-id="<?php echo $inventory['inventory_id']; ?>"
                                                    data-quantity="<?php echo $inventory['quantity']; ?>"
                                                    data-expiry-date="<?php echo $inventory['expiry_date']; ?>">
                                                    Edit
                                                </button>
                                                <!-- Delete Button -->
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                                    data-inventory-id="<?php echo $inventory['inventory_id']; ?>">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add New Product Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Add New Product</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="product_name">Product Name</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="Condiment">Condiment</option>
                                <option value="Beverage">Beverage</option>
                                <option value="Eating Essential">Eating Essential</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="unit">Unit</label>
                            <select class="form-control" id="unit" name="unit" required>
                                <option value="Can">Can</option>
                                <option value="Bottle">Bottle</option>
                                <option value="Pack">Pack</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Initial Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                        </div>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Inventory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="inventory_id" id="editInventoryId">
                        <div class="form-group">
                            <label for="editQuantity">Quantity</label>
                            <input type="number" class="form-control" id="editQuantity" name="quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="editExpiryDate">Expiry Date</label>
                            <input type="date" class="form-control" id="editExpiryDate" name="expiry_date" required>
                        </div>
                        <button type="submit" name="update_inventory" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Inventory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this inventory item?</p>
                    <form method="POST" action="">
                        <input type="hidden" name="inventory_id" id="deleteInventoryId">
                        <button type="submit" name="delete_inventory" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript to handle the edit modal
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = document.getElementById('editModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var inventoryId = button.getAttribute('data-inventory-id');
                var quantity = button.getAttribute('data-quantity');
                var expiryDate = button.getAttribute('data-expiry-date');

                // Update the modal's content
                var modalInputId = editModal.querySelector('#editInventoryId');
                var modalInputQuantity = editModal.querySelector('#editQuantity');
                var modalInputExpiryDate = editModal.querySelector('#editExpiryDate');

                modalInputId.value = inventoryId;
                modalInputQuantity.value = quantity;
                modalInputExpiryDate.value = expiryDate;
            });

            // JavaScript to handle the delete modal
            var deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var inventoryId = button.getAttribute('data-inventory-id');

                // Update the modal's content
                var modalInputId = deleteModal.querySelector('#deleteInventoryId');
                modalInputId.value = inventoryId;
            });
        });
    </script>
</body>
</html>