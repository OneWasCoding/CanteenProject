<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT c.cart_id, m.name, m.price, m.image_path, c.quantity
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

$totalPrice = 0;
foreach ($cartItems as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - QuickByte Canteen</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 60px;
        }
        .navbar {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
        }
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            color: white !important;
        }
        .nav-link:hover {
            transform: translateY(-2px);
        }
        .dropdown-menu {
            background-color: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .dropdown-item {
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        .dropdown-item:hover {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            transform: translateX(5px);
        }
        .cart-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
        }
        .cart-header {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
            margin-bottom: 2rem;
        }
        .cart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 15px;
        }
        .cart-table th {
            padding: 1rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            border-bottom: 2px solid #f1f1f1;
        }
        .cart-table td {
            padding: 1rem;
            vertical-align: middle;
            background: #f8f9fa;
        }
        .cart-table tr:hover td {
            background: #f1f1f1;
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
        .cart-table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .quantity-input {
            width: 80px;
            text-align: center;
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 20px;
            font-weight: 500;
        }
        .remove-btn {
            background: transparent;
            border: none;
            color: #dc3545;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 50%;
        }
        .remove-btn:hover {
            background: #fff1f1;
            transform: scale(1.1);
        }
        .cart-summary {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 2rem;
            text-align: right;
            font-size: 1.3rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(228, 77, 38, 0.2);
        }
        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1rem;
        }
        .continue-shopping,
        .checkout-btn {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .continue-shopping {
            background: transparent;
            color: #666;
            border: 2px solid #ddd;
        }
        .continue-shopping:hover {
            background: #f8f9fa;
            transform: translateX(-5px);
            color: #333;
        }
        .checkout-btn {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            border: none;
        }
        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(228, 77, 38, 0.3);
        }
        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        .empty-cart a {
            color: #e44d26;
            text-decoration: none;
            font-weight: 600;
        }
        .empty-cart a:hover {
            text-decoration: underline;
        }
        footer {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: auto;
        }
        @media (max-width: 768px) {
            .cart-container {
                margin: 1rem;
                padding: 1rem;
            }
            .cart-table {
                display: block;
                overflow-x: auto;
            }
            .cart-actions {
                flex-direction: column;
                gap: 1rem;
            }
            .continue-shopping,
            .checkout-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="bi bi-shop"></i> QuickByte Canteen</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Home
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="food.php"><i class="bi bi-egg-fried"></i> Food Items</a></li>
                            <li><a class="dropdown-item" href="stalls.php"><i class="bi bi-shop-window"></i> Stalls</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="cart.php"><i class="bi bi-cart"></i> Cart</a>
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
    <div class="container">
        <div class="cart-container">
            <div class="cart-header">
                Shopping Cart (<?php echo count($cartItems); ?> item<?php echo count($cartItems) !== 1 ? 's' : ''; ?>)
            </div>

            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <i class="bi bi-cart-x"></i>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added anything to your cart yet.</p>
                    <a href="food.php">Browse our menu</a>
                </div>
            <?php else: ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <?php 
                            // Process the image_path similarly to other pages
                            $image_path = !empty($item['image_path']) 
                                ? htmlspecialchars(str_replace("../../", "../", $item['image_path'])) 
                                : '../assets/images/default-food.jpg';
                            ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $image_path; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>
                                    <input type="number" class="quantity-input form-control" 
                                           value="<?php echo $item['quantity']; ?>" min="1" 
                                           onchange="updateQuantity(<?php echo $item['cart_id']; ?>, this.value)">
                                </td>
                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td>
                                    <button class="remove-btn" onclick="removeFromCart(<?php echo $item['cart_id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-summary">
                    Total Amount: ₱<?php echo number_format($totalPrice, 2); ?>
                </div>

                <div class="cart-actions">
                    <a href="food.php" class="continue-shopping">
                        <i class="bi bi-arrow-left"></i> Continue Shopping
                    </a>
                    <button class="checkout-btn" onclick="checkout()">
                        Proceed to Checkout <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 QuickByte Canteen. All rights reserved.</p>
    </footer>

    <script>
        function updateQuantity(cartId, quantity) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_id: cartId, quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to update quantity.');
                }
            });
        }

        function removeFromCart(cartId) {
            if (confirm('Are you sure you want to remove this item?')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cart_id: cartId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to remove item.');
                    }
                });
            }
        }

        function checkout() {
            window.location.href = 'checkout.php';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
