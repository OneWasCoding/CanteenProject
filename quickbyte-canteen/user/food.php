<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$sql_categories = "SELECT DISTINCT category FROM menu_items ORDER BY category ASC";
$stmt_categories = $con->prepare($sql_categories);
$stmt_categories->execute();
$result_categories = $stmt_categories->get_result();
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);
$stmt_categories->close();

$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Fetch and process menu items
$sql = "
    SELECT 
        m.item_id, 
        m.name, 
        m.price, 
        m.category, 
        m.image_path,
        m.description, 
        COALESCE(fs.quantity, 0) AS quantity_in_stock
    FROM 
        menu_items m
    LEFT JOIN 
        food_storage fs ON m.item_id = fs.item_id
    WHERE 
        m.availability = 'Available'
";

if ($category_filter !== 'all') {
    $sql .= " AND m.category = ?";
}

$sql .= " ORDER BY m.category ASC, m.name ASC";

$stmt = $con->prepare($sql);
if ($category_filter !== 'all') {
    $stmt->bind_param("s", $category_filter);
}
$stmt->execute();
$result = $stmt->get_result();
$menuItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Menu - QuickByte Canteen</title>
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .navbar {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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

        .menu-container {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem auto;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            max-width: 1200px;
        }

        .menu-title {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
            font-weight: 700;
            font-size: 2.5rem;
        }

        .menu-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            margin: 10px auto;
            border-radius: 2px;
        }

        .filters-container {
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
        }

        .filter-btn {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            margin: 5px;
            display: inline-block;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(228, 77, 38, 0.2);
            color: white;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #ff7f50, #e44d26);
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.2);
        }

        .food-card {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(255,255,255,0.2);
            margin: 0 auto;
            max-width: 350px;
        }

        .food-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .food-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .food-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .food-card:hover .food-image img {
            transform: scale(1.1);
        }

        .category-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(231, 76, 60, 0.9);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(5px);
        }

        .food-info {
            padding: 1.5rem;
            text-align: center;
        }

        .food-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .food-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #e44d26;
            margin-bottom: 1rem;
        }

        .stock-info {
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-align: center;
        }

        .in-stock { 
            color: #2ecc71; 
            font-weight: 600;
        }

        .out-of-stock { 
            color: #e74c3c;
            font-weight: 600;
        }

        .btn-add-cart, .btn-view-details {
            width: 100%;
            padding: 10px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-add-cart {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            border: none;
        }

        .btn-view-details {
            background: transparent;
            border: 2px solid #e44d26;
            color: #e44d26;
        }

        .btn-add-cart:hover, .btn-view-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(228, 77, 38, 0.2);
            color: white;
        }

        .btn-view-details:hover {
            background: #e44d26;
        }

        .row {
            justify-content: center;
        }

        @media (max-width: 768px) {
            .menu-title {
                font-size: 2rem;
            }
            
            .filters-container {
                padding: 15px;
            }
            
            .filter-btn {
                width: calc(50% - 10px);
                margin: 5px;
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
    <div class="container menu-container">
        <h1 class="menu-title">Our Menu</h1>
        
        <!-- Filters -->
        <div class="filters-container">
            <div class="text-center">
                <a href="?category=all" class="filter-btn <?php echo $category_filter === 'all' ? 'active' : ''; ?>">
                    All Items
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="?category=<?php echo urlencode($category['category']); ?>" 
                       class="filter-btn <?php echo $category_filter === $category['category'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['category']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Menu Items Grid -->
        <div class="row g-4">
            <?php if (!empty($menuItems)): ?>
                <?php foreach ($menuItems as $item): ?>
                    <?php
                    // Process the image_path for the current item
                    $image_path = str_replace("../../", "../", $item['image_path']);
                    ?>
                    <div class="col-md-4">
                        <div class="food-card">
                            <div class="food-image">
                                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="category-badge">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </div>
                            </div>
                            <div class="food-info">
                                <h3 class="food-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="food-price">₱<?php echo number_format($item['price'], 2); ?></div>
                                <div class="stock-info">
                                    <?php if ($item['quantity_in_stock'] > 0): ?>
                                        <span class="in-stock">
                                            <i class="bi bi-check-circle-fill"></i> 
                                            Available Stock: <?php echo $item['quantity_in_stock']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="out-of-stock">
                                            <i class="bi bi-x-circle-fill"></i> Out of Stock
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <button onclick="addToCart(<?php echo $item['item_id']; ?>)" 
                                        class="btn-add-cart"
                                        <?php echo $item['quantity_in_stock'] <= 0 ? 'disabled' : ''; ?>>
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                                <a href="product_details.php?item_id=<?php echo $item['item_id']; ?>" 
                                   class="btn-view-details">
                                    <i class="bi bi-info-circle"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No menu items available for this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>