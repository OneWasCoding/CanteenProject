<?php
session_start();
include '../config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1) Fetch all reviews made by the user
$sql_reviews = "
    SELECT f.feedback_id, f.rating, f.comment, f.created_at, s.stall_name, f.user_id
    FROM feedback f
    JOIN stalls s ON f.stall_id = s.stall_id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
";
$stmt_reviews = $con->prepare($sql_reviews);
$stmt_reviews->bind_param("i", $user_id);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();
$user_reviews = $result_reviews->fetch_all(MYSQLI_ASSOC);
$stmt_reviews->close();

$countReviews = count($user_reviews);

// 2) Always fetch completed orders that haven’t been reviewed (based on stall review)
$sql_notReviewed = "
    SELECT o.order_id, o.order_date, o.stall_id, s.stall_name, o.total_price
    FROM orders o
    JOIN stalls s ON o.stall_id = s.stall_id
    LEFT JOIN feedback f ON f.user_id = o.user_id AND f.stall_id = o.stall_id
    WHERE o.user_id = ?
      AND o.order_status = 'Completed'
      AND f.feedback_id IS NULL
    ORDER BY o.order_date DESC
";
$stmt_notReviewed = $con->prepare($sql_notReviewed);
$stmt_notReviewed->bind_param("i", $user_id);
$stmt_notReviewed->execute();
$result_notReviewed = $stmt_notReviewed->get_result();
$notReviewedOrders = $result_notReviewed->fetch_all(MYSQLI_ASSOC);
$stmt_notReviewed->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Reviews - QuickByte Canteen</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css">
    <!-- Font Awesome for star icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .reviews-container {
            max-width: 900px;
            margin: 5rem auto 2rem;
            padding: 2rem;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .reviews-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .review-card {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
        }
        .review-card:last-child {
            border-bottom: none;
        }
        .reviewer-stall {
            font-weight: bold;
        }
        .review-rating {
            color: #ffcc00;
        }
        .review-comment {
            margin-top: 0.5rem;
        }
        .btn-group .btn {
            margin-right: 0.5rem;
        }
        .orders-list {
            margin-top: 2rem;
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
    <!-- Navbar (same as user_profile.php) -->
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
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Home
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="food.php">Food Items</a></li>
                            <li><a class="dropdown-item" href="stalls.php">Stalls</a></li>
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

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="reviews-container">
            <div class="reviews-header">
                <h2>My Reviews</h2>
            </div>

            <?php if (!empty($user_reviews)): ?>
                <!-- Display existing reviews -->
                <?php foreach ($user_reviews as $review): ?>
                    <div class="review-card">
                        <div class="reviewer-stall">
                            <?php echo htmlspecialchars($review['stall_name']); ?>
                        </div>
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
                        <div class="review-comment">
                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.8rem;">
                            <?php echo htmlspecialchars($review['created_at']); ?>
                        </div>
                        <!-- Button group for Edit and Delete -->
                        <div class="btn-group mt-2">
                            <a href="edit_review.php?feedback_id=<?php echo urlencode($review['feedback_id']); ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="delete_review.php" onsubmit="return confirm('Are you sure you want to delete this review?');" style="display:inline;">
                                <input type="hidden" name="feedback_id" value="<?php echo htmlspecialchars($review['feedback_id']); ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">You haven't reviewed any products yet.</p>
            <?php endif; ?>

            <!-- Always display the list of completed orders not yet reviewed -->
            <?php if (!empty($notReviewedOrders)): ?>
                <div class="orders-list">
                    <h4 class="text-center">Completed Orders Not Yet Reviewed</h4>
                    <ul class="list-group">
                        <?php foreach ($notReviewedOrders as $order): ?>
                            <li class="list-group-item">
                                <strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?><br>
                                <strong>Stall:</strong> <?php echo htmlspecialchars($order['stall_name']); ?><br>
                                <strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?><br>
                                <strong>Total Price:</strong> ₱<?php echo number_format($order['total_price'], 2); ?><br>
                                <a href="add_review.php?stall_id=<?php echo urlencode($order['stall_id']); ?>&order_id=<?php echo urlencode($order['order_id']); ?>" class="btn btn-primary btn-sm mt-2">
                                    <i class="bi bi-pencil-square"></i> Review Now
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <p class="text-center">No completed orders available for review.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 QuickByte Canteen. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome for star icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
