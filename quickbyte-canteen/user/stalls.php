<?php
session_start();
include '../config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all stalls using the correct column name "stall_name"
$sql = "SELECT stall_id, stall_name, description, image_path FROM stalls";
$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Error preparing SQL statement: " . htmlspecialchars($con->error));
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error fetching results: " . htmlspecialchars($stmt->error));
}

$stalls = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stalls - QuickByte Canteen</title>
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

        .hero-section {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            text-align: center;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .hero-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .stall-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
        }

        .stall-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            overflow: hidden;
        }

        .stall-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(228,77,38,0.2);
        }

        .stall-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .stall-card .card-body {
            padding: 1.5rem;
        }

        .stall-card h5 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.75rem;
        }

        .stall-card p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .btn-view-menu {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            width: 100%;
            text-align: center;
            border: none;
        }

        .btn-view-menu:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(228,77,38,0.3);
            color: white;
        }

        footer {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: auto;
        }

        footer p {
            margin: 0;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 0;
            }
            
            .hero-section h2 {
                font-size: 2rem;
            }

            .stall-container {
                margin: 1rem;
                padding: 1rem;
            }

            .stall-card {
                margin-bottom: 1.5rem;
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h2>Discover Our Stalls</h2>
            <p class="lead">Explore a variety of delicious options from our food stalls</p>
        </div>
    </section>

    <!-- Stalls Section -->
    <div class="container">
        <div class="stall-container">
            <div class="row">
                <?php if (!empty($stalls)): ?>
                    <?php foreach ($stalls as $stall): ?>
                        <div class="col-md-4 mb-4">
                            <div class="stall-card">
                                <img src="<?php echo htmlspecialchars($stall['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($stall['stall_name']); ?>">
                                <div class="card-body">
                                    <h5><?php echo htmlspecialchars($stall['stall_name']); ?></h5>
                                    <p><?php echo htmlspecialchars($stall['description'] ?? 'No description available.'); ?></p>
                                    <a href="food_items.php?stall_id=<?php echo $stall['stall_id']; ?>" class="btn-view-menu">
                                        <i class="bi bi-utensils"></i> View Menu
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No stalls available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
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