<?php
session_start();
include '../config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch stalls
$sql = "SELECT stall_id, name, description, image_path FROM stalls";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$stalls = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>QuickByte Canteen - Home</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: #e44d26;
            color: white;
        }
        footer {
            background-color: #e44d26;
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: auto;
        }
        .stall-item {
            background-color: rgba(255, 255, 255, 0.92);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .stall-item:hover {
            transform: translateY(-5px);
        }
        .stall-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .stall-item img:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #e44d26;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-shop"></i> QuickByte Canteen</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-house"></i> Home</a>
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
    <div class="container mt-4">
        <h2 class="text-center mb-4">Stalls</h2>
        <div class="row">
            <?php foreach ($stalls as $stall): ?>
                <div class="col-md-4">
                    <div class="stall-item">
                        <img src="<?php echo htmlspecialchars($stall['image_path']); ?>" alt="<?php echo htmlspecialchars($stall['name']); ?>">
                        <h4><?php echo htmlspecialchars($stall['name']); ?></h4>
                        <p><?php echo htmlspecialchars($stall['description']); ?></p>
                        <a href="stall_products.php?stall_id=<?php echo $stall['stall_id']; ?>" class="btn btn-primary">
                            View Menu <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 QuickByte Canteen. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>