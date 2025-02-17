<?php
include '../config.php';
session_start();

// Fetch menu items from database
$sql = "SELECT * FROM menu_items";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>QuickByte Canteen</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Menu</h2>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="col-md-4">
                    <div class="card">
                        <img src="../uploads/menu_items/<?= $row['image']; ?>" class="card-img-top" alt="Food Image">
                        <div class="card-body">
                            <h5 class="card-title"><?= $row['name']; ?></h5>
                            <p class="card-text">₱<?= number_format($row['price'], 2); ?></p>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="item_id" value="<?= $row['id']; ?>">
                                <input type="hidden" name="item_name" value="<?= $row['name']; ?>">
                                <input type="hidden" name="item_price" value="<?= $row['price']; ?>">
                                <input type="number" name="quantity" value="1" min="1" class="form-control mb-2">
                                <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
