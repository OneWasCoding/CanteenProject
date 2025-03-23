<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        .reviews-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 900px;
        }

        .reviews-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .reviews-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .review-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(228,77,38,0.2);
        }

        .reviewer-stall {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .review-rating {
            color: #ffc107;
            font-size: 1.1rem;
            margin: 0.5rem 0;
        }

        .review-comment {
            color: #666;
            margin: 1rem 0;
            line-height: 1.6;
        }

        .btn-group {
            margin-top: 1rem;
        }

        .btn-group .btn {
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(228,77,38,0.3);
        }

        .btn-danger {
            background: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
        }

        .orders-list {
            margin-top: 3rem;
        }

        .orders-list h4 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .list-group-item {
            border-radius: 15px;
            margin-bottom: 1rem;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        footer {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: auto;
        }

        .social-icons {
            margin: 1rem 0;
        }

        .social-icons a {
            color: white;
            margin: 0 10px;
            font-size: 1.2rem;
            transition: opacity 0.3s ease;
        }

        .social-icons a:hover {
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .reviews-container {
                margin: 1rem;
                padding: 1rem;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-group .btn {
                width: 100%;
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

    <!-- Main Content -->
    <div class="container">
        <div class="reviews-container">
            <div class="reviews-header">
                <h2><i class="bi bi-star"></i> My Reviews</h2>
                <p>Your feedback matters to us!</p>
            </div>

            <?php if (!empty($user_reviews)): ?>
                <?php foreach ($user_reviews as $review): ?>
                    <div class="review-card">
                        <div class="reviewer-stall">
                            <i class="bi bi-shop"></i> <?php echo htmlspecialchars($review['stall_name']); ?>
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
                            <span class="ms-2">(<?php echo htmlspecialchars($review['rating']); ?>/5)</span>
                        </div>
                        <div class="review-comment">
                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.9rem;">
                            <i class="bi bi-calendar"></i> <?php echo date('F j, Y, g:i a', strtotime($review['created_at'])); ?>
                        </div>
                        <div class="btn-group">
                            <a href="edit_review.php?feedback_id=<?php echo urlencode($review['feedback_id']); ?>" 
                               class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="delete_review.php" 
                                  onsubmit="return confirm('Are you sure you want to delete this review?');" 
                                  style="display:inline;">
                                <input type="hidden" name="feedback_id" 
                                       value="<?php echo htmlspecialchars($review['feedback_id']); ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center mb-4">
                    <i class="bi bi-chat-square-text" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="mt-3">You haven't reviewed any products yet.</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($notReviewedOrders)): ?>
                <div class="orders-list">
                    <h4 class="text-center">Pending Reviews</h4>
                    <div class="list-group">
                        <?php foreach ($notReviewedOrders as $order): ?>
                            <div class="list-group-item">
                                <h5 class="mb-2"><?php echo htmlspecialchars($order['stall_name']); ?></h5>
                                <p class="mb-1"><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                                <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                                <p class="mb-2"><strong>Total:</strong> ₱<?php echo number_format($order['total_price'], 2); ?></p>
                                <a href="add_review.php?stall_id=<?php echo urlencode($order['stall_id']); ?>&order_id=<?php echo urlencode($order['order_id']); ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-star"></i> Write a Review
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
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
            <div class="social-icons">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-twitter"></i></a>
            </div>
            <p>&copy; 2024 QuickByte Canteen. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>