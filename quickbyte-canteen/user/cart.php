<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if (isset($_POST['add_to_cart'])) {
    $item = [
        'id' => $_POST['item_id'],
        'name' => $_POST['item_name'],
        'price' => $_POST['item_price'],
        'quantity' => $_POST['quantity']
    ];
    $_SESSION['cart'][] = $item;
}

// Remove item from cart
if (isset($_POST['remove_item'])) {
    $index = $_POST['index'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
}

// Clear cart
if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Shopping Cart</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                foreach ($_SESSION['cart'] as $index => $item) { 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?= $item['name']; ?></td>
                    <td>₱<?= number_format($item['price'], 2); ?></td>
                    <td><?= $item['quantity']; ?></td>
                    <td>₱<?= number_format($subtotal, 2); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="index" value="<?= $index; ?>">
                            <button type="submit" name="remove_item" class="btn btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        
        <h4>Total: ₱<?= number_format($total, 2); ?></h4>
        <form method="POST">
            <button type="submit" name="clear_cart" class="btn btn-warning">Clear Cart</button>
        </form>
        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
