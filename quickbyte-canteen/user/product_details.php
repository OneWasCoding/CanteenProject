<?php
session_start();
include '../config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get the item ID from the query string
if (!isset($_GET['item_id']) || empty($_GET['item_id'])) {
    header("Location: index.php");
    exit();
}

$item_id = intval($_GET['item_id']);

// Fetch product details and stock information using the updated SQL
$sql = "
    SELECT m.item_id, m.name, m.price, m.category, m.image_path, m.description, m.stall_id, i.quantity AS quantity_in_stock
    FROM menu_items m
    LEFT JOIN inventory i ON m.item_id = i.product_id
    WHERE m.item_id = ?
";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: index.php");
    exit();
}

// Fetch reviews for the stall offering this product.
// Note: Since your feedback table does not include a product (item) column,
// we fetch reviews based on the stall_id. This means that reviews are linked
// to the stall, not directly to the product.
$sql_reviews = "
    SELECT f.feedback_id, f.rating, f.comment, f.created_at, f.user_id, u.name AS reviewer_name 
    FROM feedback f 
    JOIN users u ON f.user_id = u.user_id 
    WHERE f.stall_id = ?
    ORDER BY f.created_at DESC
";
$stmt_reviews = $con->prepare($sql_reviews);
$stmt_reviews->bind_param("i", $product['stall_id']);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();
$reviews = $result_reviews->fetch_all(MYSQLI_ASSOC);
$stmt_reviews->close();

$countReviews = count($reviews);

// Calculate average rating
$totalRating = 0;
foreach ($reviews as $review) {
    $totalRating += $review['rating'];
}
$averageRating = $countReviews > 0 ? round($totalRating / $countReviews, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - <?php echo htmlspecialchars($product['name']); ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css">
    <!-- Font Awesome for star icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }
        /* Navbar (same as reviews.php) */
        .navbar {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .navbar-brand { font-weight: bold; }
        /* Product card styling */
        .product-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            padding: 2rem;
            text-align: center;
            max-width: 600px;
            margin: 2rem auto;
        }
        .product-image img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .product-image img:hover {
            transform: scale(1.05);
        }
        .product-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .product-price {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 1rem;
        }
        .product-description {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .stock-info {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .btn-add-cart {
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }
        .btn-add-cart:hover {
            opacity: 0.8;
        }
        .btn-sold-out {
            background-color: transparent;
            border: 1px solid #ccc;
            color: #ccc;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: not-allowed;
        }
        /* Reviews section styling */
        .reviews-section {
            margin-top: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        .review-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .reviewer-name {
            font-weight: bold;
        }
        .review-rating {
            color: #ffcc00;
        }
        .review-comment {
            margin-top: 0.5rem;
        }
        footer {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar (Same as reviews.php) -->
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

    <!-- Product Details -->
    <div class="container mt-5 pt-5">
        <div class="product-card">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
            <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
            <div class="product-description"><?php echo htmlspecialchars($product['description'] ?? 'No description available.'); ?></div>
            <p class="stock-info">
                <?php if ($product['quantity_in_stock'] > 0): ?>
                    <strong>In Stock:</strong> <?php echo $product['quantity_in_stock']; ?> left
                <?php else: ?>
                    <strong>Sold Out</strong>
                <?php endif; ?>
            </p>
            <button
                class="<?php echo $product['quantity_in_stock'] > 0 ? 'btn-add-cart' : 'btn-sold-out'; ?>"
                onclick="addToCart(<?php echo $product['item_id']; ?>)"
                <?php echo $product['quantity_in_stock'] <= 0 ? 'disabled' : ''; ?>
            >
                <?php echo $product['quantity_in_stock'] > 0 ? '<i class="fas fa-cart-plus"></i> Add to Cart' : 'Sold Out'; ?>
            </button>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="container reviews-section">
        <h3 class="text-center">Reviews</h3>
        <?php if ($countReviews > 0): ?>
            <p class="text-center">Average Rating: <?php echo $averageRating; ?> / 5</p>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="reviewer-name"><?php echo htmlspecialchars($review['reviewer_name']); ?></div>
                    <div class="review-rating">
                        <?php 
                            $fullStars = floor($review['rating']);
                            for ($i = 0; $i < $fullStars; $i++) {
                                echo '<i class="fas fa-star"></i> ';
                            }
                            for ($i = $fullStars; $i < 5; $i++) {
                                echo '<i class="far fa-star"></i> ';
                            }
                        ?>
                        (<?php echo htmlspecialchars($review['rating']); ?>/5)
                    </div>
                    <div class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></div>
                    <div class="text-muted" style="font-size: 0.8rem;"><?php echo htmlspecialchars($review['created_at']); ?></div>
                    <!-- If this review belongs to the logged-in user, show Edit and Delete buttons -->
                    <?php if ($review['user_id'] == $_SESSION['user_id']): ?>
                        <div class="mt-2">
                            <a href="edit_review.php?feedback_id=<?php echo urlencode($review['feedback_id']); ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="delete_review.php?feedback_id=<?php echo urlencode($review['feedback_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this review?');">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 QuickByte Canteen. All rights reserved.</p>
    </footer>

    <!-- JavaScript for Add to Cart -->
    <script>
        function addToCart(itemId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_id: itemId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Item added to cart!');
                } else {
                    alert(data.message || 'Failed to add item to cart.');
                }
            });
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
