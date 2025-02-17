<?php
include '../config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
        $user_id = $_SESSION['user_id'];
        $total_amount = 0;

        foreach ($_SESSION['cart'] as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        // Insert order
        $sql = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $user_id, $total_amount);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        // Insert order details
        foreach ($_SESSION['cart'] as $item) {
            $sql = "INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }

        // Clear cart after checkout
        $_SESSION['cart'] = [];
        echo "<script>alert('Order placed successfully!'); window.location.href='order_history.php';</script>";
    } else {
        echo "<script>alert('Your cart is empty!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Checkout</h2>
        <h4>Total: ₱<?= number_format($total_amount, 2); ?></h4>
        <form method="POST">
            <button type="submit" class="btn btn-success">Place Order</button>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
