<?php
session_start();
include '../config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if feedback_id is provided
if (!isset($_GET['feedback_id']) || empty($_GET['feedback_id'])) {
    header("Location: manage_reviews.php");
    exit();
}

$feedback_id = intval($_GET['feedback_id']); // Ensure it's an integer

// Fetch existing review (item_id removed)
$sql = "SELECT feedback_id, stall_id, rating, comment FROM feedback WHERE feedback_id = ? AND user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $feedback_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();
$stmt->close();

// Redirect if no matching review is found
if (!$review) {
    header("Location: manage_reviews.php");
    exit();
}

$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = trim($_POST['comment'] ?? '');

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $error = "Please provide a rating between 1 and 5.";
    } else {
        // Update feedback (item_id removed)
        $sql_update = "UPDATE feedback SET rating = ?, comment = ? WHERE feedback_id = ? AND user_id = ?";
        $stmt_update = $con->prepare($sql_update);
        $stmt_update->bind_param("isii", $rating, $comment, $feedback_id, $user_id);

        if ($stmt_update->execute()) {
            $success = "Review updated successfully.";
            header("Location: edit_review.php?feedback_id=$feedback_id&success=1");
            exit();
        } else {
            $error = "Failed to update review.";
        }
        $stmt_update->close();
    }
}

// If redirected after update, set success message
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = "Review updated successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Review - QuickByte Canteen</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
        .navbar-brand { font-weight: bold; }
        .review-container {
            max-width: 600px;
            margin: 4rem auto;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .review-header {
            text-align: center;
            margin-bottom: 2rem;
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

    <div class="container">
        <div class="review-container">
            <div class="review-header">
                <h2>Edit Review</h2>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="rating" class="form-label">Rating (1 to 5):</label>
                    <select class="form-select" id="rating" name="rating" required>
                        <option value="">Select Rating</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($review['rating'] == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="comment" class="form-label">Your Review:</label>
                    <textarea class="form-control" id="comment" name="comment" rows="5" required><?php echo htmlspecialchars($review['comment'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100"><i class="bi bi-pencil"></i> Update Review</button>
            </form>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 QuickByte Canteen. All rights reserved.</p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
