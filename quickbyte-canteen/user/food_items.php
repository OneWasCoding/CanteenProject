<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php"); 
    exit();
}

if (!isset($_GET['stall_id']) || empty($_GET['stall_id'])) {
    header("Location: index.php");
    exit(); 
}
$stall_id = $_GET['stall_id'];

function getStallDetails($con, $stall_id) {
    $sql_stall = "SELECT stall_name, description, image_path FROM stalls WHERE stall_id = ?";
    $stmt_stall = $con->prepare($sql_stall);
    $stmt_stall->bind_param("i", $stall_id);
    $stmt_stall->execute();
    $result_stall = $stmt_stall->get_result();
    $stall = $result_stall->fetch_assoc();
    $stmt_stall->close();
    return $stall;
}

function getCategories($con, $stall_id) {
    $sql_categories = "SELECT DISTINCT category FROM menu_items WHERE stall_id = ? ORDER BY category ASC";
    $stmt_categories = $con->prepare($sql_categories);
    $stmt_categories->bind_param("i", $stall_id);
    $stmt_categories->execute();
    $result_categories = $stmt_categories->get_result();
    $categories = $result_categories->fetch_all(MYSQLI_ASSOC);
    $stmt_categories->close();
    return $categories;
}

$stall = getStallDetails($con, $stall_id);
if (!$stall) {
    header("Location: index.php");
    exit();
}

$categories = getCategories($con, $stall_id);

function getMenuItems($con, $stall_id, $category_filter, $sort_order) {
    $sql = "
        SELECT 
            m.item_id, 
            m.name, 
            m.price, 
            m.category, 
            m.image_path,
            COALESCE(SUM(fs.quantity), 0) AS quantity_in_stock,
            CASE 
                WHEN COALESCE(SUM(fs.quantity), 0) > 0 THEN 'Available'
                ELSE 'Out Of Stock'
            END AS availability_status
        FROM menu_items m
        LEFT JOIN food_storage fs ON m.item_id = fs.item_id
        WHERE m.stall_id = ?";

    if ($category_filter !== 'all') {
        $sql .= " AND m.category = ?";
    }

    $sql .= " GROUP BY m.item_id, m.name, m.price, m.category, m.image_path";

    switch ($sort_order) {
        case 'price_low_to_high':
            $sql .= " ORDER BY m.price ASC";
            break;
        case 'price_high_to_low':
            $sql .= " ORDER BY m.price DESC";
            break;
        case 'name_a_to_z':
            $sql .= " ORDER BY m.name ASC";
            break;
        case 'name_z_to_a':
            $sql .= " ORDER BY m.name DESC";
            break;
        default:
            $sql .= " ORDER BY m.category ASC";
            break;
    }

    $stmt = $con->prepare($sql);
    if ($category_filter !== 'all') {
        $stmt->bind_param("is", $stall_id, $category_filter);
    } else {
        $stmt->bind_param("i", $stall_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $menuItems = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $menuItems;
}

$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$menuItems = getMenuItems($con, $stall_id, $category_filter, $sort_order);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($stall['stall_name']); ?> - QuickByte Canteen</title>
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

        .stall-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
        }

        .stall-header {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            text-align: center;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .stall-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .filter-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin: 2rem 0;
        }

        .filter-button {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }

        .filter-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(228,77,38,0.3);
            color: white;
        }

        .filter-button.active {
            background: #c63e1e;
            transform: scale(1.05);
        }

        .sorting-options {
            background: white;
            padding: 1rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            text-align: center;
        }

        .sorting-options a {
            color: #666;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .sorting-options a:hover {
            color: #e44d26;
            transform: translateY(-2px);
        }

        .sorting-options a.active {
            color: #e44d26;
            font-weight: 600;
        }

        .menu-item {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            overflow: hidden;
        }

        .menu-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(228,77,38,0.2);
        }

        .menu-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .menu-item .card-body {
            padding: 1.5rem;
        }

        .menu-item h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.75rem;
        }

        .btn-add-cart {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(228,77,38,0.3);
        }

        .btn-sold-out {
            background: #f8f9fa;
            color: #999;
            border: 2px solid #ddd;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            cursor: not-allowed;
        }

        .stock-info {
            color: #666;
            font-size: 0.9rem;
            margin: 1rem 0;
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
            .filter-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .sorting-options {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .menu-item {
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

    <!-- Stall Header -->
    <section class="stall-header">
        <div class="container">
            <h2><?php echo htmlspecialchars($stall['stall_name']); ?></h2>
            <p><?php echo htmlspecialchars($stall['description']); ?></p>
        </div>
    </section>

    <div class="container">
        <div class="stall-container">
            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <a href="?stall_id=<?php echo $stall_id; ?>&category=all" 
                   class="filter-button <?php echo $category_filter === 'all' ? 'active' : ''; ?>">
                    All Items
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="?stall_id=<?php echo $stall_id; ?>&category=<?php echo htmlspecialchars($category['category']); ?>" 
                       class="filter-button <?php echo $category_filter === $category['category'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['category']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Sorting Options -->
            <div class="sorting-options">
                <span>Sort by:</span>
                <a href="?stall_id=<?php echo $stall_id; ?>&category=<?php echo $category_filter; ?>&sort=default" 
                   class="<?php echo $sort_order === 'default' ? 'active' : ''; ?>">Default</a>
                <a href="?stall_id=<?php echo $stall_id; ?>&category=<?php echo $category_filter; ?>&sort=price_low_to_high" 
                   class="<?php echo $sort_order === 'price_low_to_high' ? 'active' : ''; ?>">Price (Low to High)</a>
                <a href="?stall_id=<?php echo $stall_id; ?>&category=<?php echo $category_filter; ?>&sort=price_high_to_low" 
                   class="<?php echo $sort_order === 'price_high_to_low' ? 'active' : ''; ?>">Price (High to Low)</a>
                <a href="?stall_id=<?php echo $stall_id; ?>&category=<?php echo $category_filter; ?>&sort=name_a_to_z" 
                   class="<?php echo $sort_order === 'name_a_to_z' ? 'active' : ''; ?>">Name (A to Z)</a>
                <a href="?stall_id=<?php echo $stall_id; ?>&category=<?php echo $category_filter; ?>&sort=name_z_to_a" 
                   class="<?php echo $sort_order === 'name_z_to_a' ? 'active' : ''; ?>">Name (Z to A)</a>
            </div>

            <!-- Menu Items Grid -->
            <div class="row">
                <?php if (!empty($menuItems)): ?>
                    <?php foreach ($menuItems as $item): ?>
                        <div class="col-md-4 mb-4">
                            <div class="menu-item">
                                <?php 
                                $image_path = !empty($item['image_path'])
                                    ? htmlspecialchars(str_replace("../../", "../", $item['image_path']))
                                    : '../assets/images/default-food.jpg';
                                ?>
                                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="card-body">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p><strong>Price:</strong> ₱<?php echo number_format($item['price'], 2); ?></p>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
                                    <p class="stock-info">
                                        <?php if ($item['quantity_in_stock'] > 0): ?>
                                            <strong>In Stock:</strong> <?php echo $item['quantity_in_stock']; ?>
                                        <?php else: ?>
                                            <strong>Sold Out</strong>
                                        <?php endif; ?>
                                    </p>
                                    <button class="<?php echo $item['quantity_in_stock'] > 0 ? 'btn-add-cart' : 'btn-sold-out'; ?>"
                                            onclick="addToCart(<?php echo $item['item_id']; ?>)" 
                                            <?php echo $item['quantity_in_stock'] <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo $item['quantity_in_stock'] > 0 ? '<i class="bi bi-cart-plus"></i> Add to Cart' : 'Sold Out'; ?>
                                    </button>
                                    <a href="product_details.php?item_id=<?php echo $item['item_id']; ?>" 
                                       class="btn btn-primary w-100 mt-2">
                                        <i class="bi bi-info-circle"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No menu items available for this stall.</p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>