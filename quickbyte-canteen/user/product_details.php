<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['item_id']) || empty($_GET['item_id'])) {
    header("Location: index.php");
    exit();
}

$item_id = intval($_GET['item_id']);

$sql = "
    SELECT 
        m.item_id, 
        m.name, 
        m.price, 
        m.category, 
        m.image_path, 
        m.description, 
        m.stall_id, 
        COALESCE(i.quantity, 0) AS inventory_stock,
        COALESCE(fs.quantity, 0) AS food_storage_stock,
        (COALESCE(i.quantity, 0) + COALESCE(fs.quantity, 0)) AS total_stock
    FROM menu_items m
    LEFT JOIN inventory i ON m.item_id = i.product_id
    LEFT JOIN food_storage fs ON m.item_id = fs.item_id
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

// Process the image path similar to food.php
$image_path = str_replace("../../", "../", $product['image_path']);

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .product-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 800px;
        }
        .product-image {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .product-image img {
            width: 100%;
            height: auto;
            transition: transform 0.5s ease;
        }
        .product-image:hover img {
            transform: scale(1.05);
        }
        .product-info {
            text-align: center;
        }
        .product-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }
        .product-category {
            display: inline-block;
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .product-price {
            font-size: 1.8rem;
            font-weight: 600;
            color: #e44d26;
            margin-bottom: 1.5rem;
        }
        .product-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .stock-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .in-stock {
            color: #28a745;
            font-weight: 600;
        }
        .out-of-stock {
            color: #dc3545;
            font-weight: 600;
        }
        .btn-add-cart {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 300px;
        }
        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(228, 77, 38, 0.3);
        }
        .btn-add-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .reviews-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-top: 3rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .reviews-title {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #333;
        }
        .review-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }
        .review-card:hover {
            transform: translateY(-5px);
        }
        .reviewer-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .review-rating {
            color: #ffc107;
            margin-bottom: 0.5rem;
        }
        .review-comment {
            color: #666;
            line-height: 1.6;
        }
        .review-actions {
            margin-top: 1rem;
        }
        .btn-edit-review,
        .btn-delete-review {
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .btn-edit-review:hover,
        .btn-delete-review:hover {
            transform: translateY(-2px);
        }
        .average-rating {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.2rem;
            color: #333;
        }
        .average-rating .stars {
            color: #ffc107;
            font-size: 1.5rem;
        }
        footer {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: auto;
        }
        @media (max-width: 768px) {
            .product-container {
                margin: 1rem;
                padding: 1rem;
            }
            .product-title {
                font-size: 1.5rem;
            }
            .product-price {
                font-size: 1.5rem;
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

    <!-- Product Details -->
    <div class="container">
        <div class="product-container">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                <p class="product-description">
                    <?php echo htmlspecialchars($product['description'] ?? 'No description available.'); ?>
                </p>
                <div class="stock-info">
                    <?php if ($product['total_stock'] > 0): ?>
                        <span class="in-stock">
                            <i class="bi bi-check-circle-fill"></i> 
                            In Stock: <?php echo $product['total_stock']; ?> available
                        </span>
                    <?php else: ?>
                        <span class="out-of-stock">
                            <i class="bi bi-x-circle-fill"></i> Currently Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                <button class="btn-add-cart" 
                        onclick="addToCart(<?php echo $product['item_id']; ?>)"
                        <?php echo $product['total_stock'] <= 0 ? 'disabled' : ''; ?>>
                    <?php if ($product['total_stock'] > 0): ?>
                        <i class="bi bi-cart-plus"></i> Add to Cart
                    <?php else: ?>
                        <i class="bi bi-x-circle"></i> Sold Out
                    <?php endif; ?>
                </button>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <h2 class="reviews-title">Customer Reviews</h2>
            <?php if ($countReviews > 0): ?>
                <div class="average-rating">
                    <div class="stars">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $averageRating) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($i - 0.5 <= $averageRating) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <div><?php echo $averageRating; ?> out of 5 (<?php echo $countReviews; ?> reviews)</div>
                </div>

                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="reviewer-name"><?php echo htmlspecialchars($review['reviewer_name']); ?></div>
                        <div class="review-rating">
                            <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $review['rating']) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <div class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></div>
                        
                        <?php if ($review['user_id'] == $_SESSION['user_id']): ?>
                            <div class="review-actions">
                                <a href="edit_review.php?feedback_id=<?php echo urlencode($review['feedback_id']); ?>" 
                                   class="btn btn-primary btn-edit-review">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="delete_review.php?feedback_id=<?php echo urlencode($review['feedback_id']); ?>" 
                                   class="btn btn-danger btn-delete-review"
                                   onclick="return confirm('Are you sure you want to delete this review?');">
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
    </div>

    <footer>
        <p>&copy; 2024 QuickByte Canteen. All rights reserved.</p>
    </footer>

    <script>
        function addToCart(itemId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ item_id: itemId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Item added to cart successfully!');
                } else {
                    alert(data.message || 'Failed to add item to cart.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the item to cart.');
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
