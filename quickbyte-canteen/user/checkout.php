<?php
session_start();
include '../config.php';

// Enable detailed error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Function to generate a unique order ID
function generateOrderId() {
    return uniqid('ORDER_');
}

$error = "";
$message = "";

// Fetch cart items with item names, prices, and store (stall) information
$sql = "
    SELECT c.cart_id, m.item_id, m.name AS item_name, m.price, c.quantity, s.stall_name, s.stall_id
    FROM cart c
    JOIN menu_items m ON c.item_id = m.item_id
    JOIN stalls s ON m.stall_id = s.stall_id
    WHERE c.user_id = ?
";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check if cart is empty
if (empty($cart_items)) {
    $error = "Your cart is empty.";
} else {
    // Group cart items by store (using stall_id)
    $grouped_items = [];
    foreach ($cart_items as $item) {
        $storeId   = $item['stall_id'];
        $storeName = $item['stall_name'];
        if (!isset($grouped_items[$storeId])) {
            $grouped_items[$storeId] = [
                'store'       => $storeName,
                'items'       => [],
                'group_total' => 0
            ];
        }
        $grouped_items[$storeId]['items'][] = $item;
        $grouped_items[$storeId]['group_total'] += $item['price'] * $item['quantity'];
    }
    
    // (Optional) Overall total cost
    $total_cost = 0;
    foreach ($grouped_items as $group) {
        $total_cost += $group['group_total'];
    }

    // Handle payment form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $payment_method = $_POST['payment_method'] ?? '';

        if (empty($payment_method)) {
            $error = "Please select a payment method.";
        } elseif ($payment_method === 'gcash') {
            // For GCash, require reference id and an image upload
            $gcash_reference = trim($_POST['gcash_reference'] ?? '');
            if (empty($gcash_reference)) {
                $error = "Please enter your GCash Reference ID.";
            }
            if (isset($_FILES['gcash_image']) && $_FILES['gcash_image']['name'] != "") {
                $target_dir = "images/gcash/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $target_file = $target_dir . uniqid() . "_" . basename($_FILES["gcash_image"]["name"]);
                $uploadOk = 1;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $check = getimagesize($_FILES["gcash_image"]["tmp_name"]);
                if ($check === false) {
                    $uploadOk = 0;
                    $error = "GCash image file is not a valid image.";
                }
                if ($_FILES["gcash_image"]["size"] > 500000) {
                    $uploadOk = 0;
                    $error = "GCash image file is too large.";
                }
                if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $uploadOk = 0;
                    $error = "Only JPG, JPEG, PNG & GIF files are allowed for GCash image.";
                }
                if ($uploadOk == 1) {
                    if (!move_uploaded_file($_FILES["gcash_image"]["tmp_name"], $target_file)) {
                        $error = "There was an error uploading the GCash image.";
                    }
                }
                $gcash_image_path = $uploadOk ? $target_file : "";
            } else {
                $error = "Please upload a GCash payment image.";
            }
        } elseif ($payment_method !== 'in-store') {
            $error = "Invalid payment method selected.";
        }
        
        // If no errors, process the orders
        if (empty($error)) {
            try {
                $con->begin_transaction();

                // Process each store group as a separate order
                foreach ($grouped_items as $store_id => $group) {
                    // Generate a unique order ID for this group
                    $order_id = generateOrderId();
                    
                    // Insert order record with order_status "Pending"
                    $sql_order = "INSERT INTO orders (order_id, user_id, stall_id, total_price, order_status)
                                  VALUES (?, ?, ?, ?, 'Pending')";
                    $stmt_order = $con->prepare($sql_order);
                    $stmt_order->bind_param("siid", $order_id, $user_id, $store_id, $group['group_total']);
                    $stmt_order->execute();
                    $stmt_order->close();
                    
                    // Insert order_details for each item in this group
                    foreach ($group['items'] as $item) {
                        $item_id   = $item['item_id'];
                        $quantity  = $item['quantity'];
                        $price     = $item['price'];
                        $subtotal  = $price * $quantity;
                        
                        $sql_detail = "INSERT INTO order_details (order_id, item_id, quantity, subtotal, unit_price)
                                       VALUES (?, ?, ?, ?, ?)";
                        $stmt_detail = $con->prepare($sql_detail);
                        $stmt_detail->bind_param("siidd", $order_id, $item_id, $quantity, $subtotal, $price);
                        $stmt_detail->execute();
                        $stmt_detail->close();
                    }
                    
                    // Insert payment record into payments table
                    $sql_payment = "INSERT INTO payments (order_id, user_id, amount, payment_method, status)
                                    VALUES (?, ?, ?, ?, 'pending')";
                    $stmt_payment = $con->prepare($sql_payment);
                    $stmt_payment->bind_param("sids", $order_id, $user_id, $group['group_total'], $payment_method);
                    $stmt_payment->execute();
                    $stmt_payment->close();
                    
                    // Insert a receipt record (normalized, no GCash fields here)
                    $sql_receipt = "INSERT INTO receipts (order_id, user_id, total_amount, payment_method)
                                    VALUES (?, ?, ?, ?)";
                    $stmt_receipt = $con->prepare($sql_receipt);
                    $stmt_receipt->bind_param("siis", $order_id, $user_id, $group['group_total'], $payment_method);
                    $stmt_receipt->execute();
                    $stmt_receipt->close();
                    
                    // If GCash, store reference & image in separate table
                    if ($payment_method === 'gcash') {
                        $sql_gcash = "INSERT INTO gcash_payment_details (order_id, gcash_reference, gcash_image_path)
                                      VALUES (?, ?, ?)";
                        $stmt_gcash = $con->prepare($sql_gcash);
                        $stmt_gcash->bind_param("sss", $order_id, $gcash_reference, $gcash_image_path);
                        $stmt_gcash->execute();
                        $stmt_gcash->close();
                    }
                }

                // Clear cart after processing all groups
                $sql_clear = "DELETE FROM cart WHERE user_id = ?";
                $stmt_clear = $con->prepare($sql_clear);
                $stmt_clear->bind_param("i", $user_id);
                $stmt_clear->execute();
                $stmt_clear->close();
                
                $con->commit();

                if ($payment_method === 'gcash') {
                    $message = "Order placed successfully using GCash. Reference: " 
                               . htmlspecialchars($gcash_reference) 
                               . ". Please present the screenshot at the store’s counter.";
                } else {
                    $message = "Order placed successfully. Please proceed to the counter for in-store payment.";
                }
            } catch (mysqli_sql_exception $exception) {
                $con->rollback();
                $error = "Transaction failed: " . $exception->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>QuickByte Canteen - Checkout</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Poppins', sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .navbar-brand { font-weight: bold; }
        .checkout-container {
            max-width: 800px;
            margin: 2rem auto;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: #fff;
            padding: 2rem;
        }
        .checkout-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .group-header {
            margin-top: 1.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #e44d26;
            padding-bottom: 0.5rem;
        }
        .order-summary {
            margin-bottom: 1.5rem;
        }
        table.table {
            margin-bottom: 1rem;
        }
        footer {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <!-- Navbar (unchanged design) -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="bi bi-shop"></i> QuickByte Canteen</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="index.php" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">Home</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="food.php">Food Items</a></li>
                            <li><a class="dropdown-item" href="stalls.php">Stalls</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="cart.php"><i class="bi bi-cart"></i> Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Checkout Content -->
    <div class="container mt-5">
        <div class="checkout-container">
            <div class="checkout-header">
                <h2>Checkout</h2>
                <p>Review your order and proceed to payment.</p>
            </div>

            <!-- Show error or success messages (no big green bar) -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php elseif (!empty($message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <a href="index.php" class="btn btn-primary mt-3">Continue Shopping</a>
            <?php endif; ?>

            <?php if (empty($message)): ?>
                <!-- Display order summary grouped by store -->
                <?php foreach ($grouped_items as $store_id => $group): ?>
                    <div class="group-header"><?php echo htmlspecialchars($group['store']); ?></div>
                    <table class="table table-striped order-summary">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($group['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
                
                <div class="mb-3 text-end">
                    <h4>Total: ₱<?php echo number_format($total_cost, 2); ?></h4>
                </div>

                <!-- Payment Form -->
                <form method="POST" enctype="multipart/form-data">
                    <h4>Payment Details</h4>
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method:</label>
                        <select class="form-select" id="paymentMethod" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="in-store">Proceed to Pay on Store</option>
                            <option value="gcash">GCash Payment</option>
                        </select>
                    </div>

                    <!-- GCash Payment Details (reference + screenshot) -->
                    <div id="gcashDetails" style="display:none;">
                        <div class="mb-3">
                            <label for="gcash_reference" class="form-label">GCash Reference ID:</label>
                            <input type="text" class="form-control" id="gcash_reference" name="gcash_reference">
                        </div>
                        <div class="mb-3">
                            <label for="gcash_image" class="form-label">Upload GCash Payment Image:</label>
                            <input type="file" class="form-control" id="gcash_image" name="gcash_image">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-success w-100 mt-3">
                        <i class="bi bi-credit-card"></i> Proceed to Payment
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <h5>Contact Us</h5>
            <p>
                Email: <a href="mailto:support@quickbyte.com">support@quickbyte.com</a><br>
                Phone: <a href="tel:+1234567890">+123 456 7890</a>
            </p>
            <p>Follow us on social media:</p>
            <div>
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-twitter"></i></a>
            </div>
            <p>&copy; 2025 QuickByte Canteen. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle GCash details based on selected payment method
        document.getElementById('paymentMethod').addEventListener('change', function () {
            const gcashDetailsDiv = document.getElementById('gcashDetails');
            gcashDetailsDiv.style.display = this.value === 'gcash' ? 'block' : 'none';
        });
    </script>
</body>
</html>
