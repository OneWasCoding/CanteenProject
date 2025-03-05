<?php
session_start();
include '../config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items for the current user
$sql = "
    SELECT c.cart_id, m.item_id, m.name, m.price, c.quantity
    FROM cart c
    JOIN menu_items m ON c.item_id = m.item_id
    WHERE c.user_id = ?
";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate total price
$totalPrice = 0;
foreach ($cartItems as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

// Fetch user's current balance
$sql = "SELECT balance FROM users WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$currentBalance = $user['balance'];
$stmt->close();

// Handle checkout process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'];

    // Check if the user has selected "Balance" and validate the balance
    if ($paymentMethod === 'balance' && $currentBalance < $totalPrice) {
        echo "<script>alert('Insufficient balance! Please add funds to your account.');</script>";
    } else {
        // Start transaction to ensure atomicity
        $con->begin_transaction();

        try {
            // Generate a unique order ID
            $order_id = uniqid('ORDER_', true);

            // Insert the order into the orders table
            $sql = "INSERT INTO orders (order_id, user_id, total_price, status) VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $status = 'Pending'; // Default status for new orders
            $stmt->bind_param("sids", $order_id, $user_id, $totalPrice, $status);
            $stmt->execute();
            $stmt->close();

            // Deduct stock levels
            foreach ($cartItems as $item) {
                $sql = "UPDATE inventory SET quantity_in_stock = quantity_in_stock - ? WHERE item_id = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("ii", $item['quantity'], $item['item_id']);
                $stmt->execute();
                $stmt->close();
            }

            // Deduct balance if the payment method is "Balance"
            if ($paymentMethod === 'balance') {
                $newBalance = $currentBalance - $totalPrice;
                $sql = "UPDATE users SET balance = ? WHERE user_id = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("di", $newBalance, $user_id);
                $stmt->execute();
                $stmt->close();
            }

            // Insert payment record into the payments table
            $paymentStatus = 'completed'; // Default status for successful payments
            $sql = "INSERT INTO payments (order_id, user_id, amount, payment_method, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sdsss", $order_id, $user_id, $totalPrice, $paymentMethod, $paymentStatus);
            $stmt->execute();
            $stmt->close();

            // Clear the cart after successful purchase
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Commit the transaction
            $con->commit();

            // Success message
            if ($paymentMethod === 'balance') {
                echo "<script>alert('Order placed successfully! Your new balance is $" . number_format($newBalance, 2) . ".');</script>";
            } else {
                echo "<script>alert('Order placed successfully! Payment method: " . ucfirst($paymentMethod) . ".');</script>";
            }
            echo "<script>window.location.href = 'index.php';</script>";
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $con->rollback();
            echo "<script>alert('An error occurred while processing your order. Please try again.');</script>";
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
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: #e44d26;
            color: white;
        }
        footer {
            background-color: #e44d26;
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: auto;
        }
        .checkout-container {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .checkout-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        .checkout-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 1rem;
        }
        .checkout-total {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .insufficient-balance {
            color: red;
            font-weight: bold;
        }
        .payment-methods {
            margin-top: 1rem;
        }
        .payment-methods label {
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #e44d26;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-shop"></i> QuickByte Canteen</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php"><i class="bi bi-cart"></i> Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_profile.php"><i class="bi bi-person-circle"></i> Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <h2 class="text-center mb-4">Checkout</h2>

        <?php if (empty($cartItems)): ?>
            <p class="text-center text-muted">Your cart is empty. <a href="index.php">Start shopping!</a></p>
        <?php else: ?>
            <div class="checkout-container">
                <h4>Order Summary</h4>
                <?php foreach ($cartItems as $item): ?>
                    <div class="checkout-item">
                        <img src="images/default-product.jpg" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div>
                            <p><strong><?php echo htmlspecialchars($item['name']); ?></strong></p>
                            <p>$<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="checkout-total">
                    <h4>Total: $<?php echo number_format($totalPrice, 2); ?></h4>

                    <!-- Payment Method Selection -->
                    <form method="POST">
                        <div class="payment-methods">
                            <label>
                                <input type="radio" name="payment_method" value="gcash" required> GCash
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="paymaya"> PayMaya
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="bank_transfer"> Bank Transfer
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="balance"> Balance ($<?php echo number_format($currentBalance, 2); ?>)
                            </label>
                        </div>

                        <?php if ($currentBalance < $totalPrice): ?>
                            <p class="insufficient-balance">Insufficient Balance! Your current balance is $<?php echo number_format($currentBalance, 2); ?>.</p>
                            <a href="user_profile.php" class="btn btn-success">Add Balance</a>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary mt-3">Place Order</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 QuickByte Canteen. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>