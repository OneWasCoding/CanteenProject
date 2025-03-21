<?php
session_start();
include '../config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all categories from the menu_items table
$sql_categories = "SELECT DISTINCT category FROM menu_items ORDER BY category ASC";
$stmt_categories = $con->prepare($sql_categories);
$stmt_categories->execute();
$result_categories = $stmt_categories->get_result();
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);
$stmt_categories->close();

// Get the selected category from the query string (default to 'all')
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Get the selected sort order from the query string (default to 'default')
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'default';

// Build the SQL query based on the selected category and sort order
$sql = "
    SELECT m.item_id, m.name, m.price, m.category, m.image_path, i.quantity AS quantity_in_stock
    FROM menu_items m
    LEFT JOIN inventory i ON m.item_id = i.product_id
    WHERE m.availability = 1
";

if ($category_filter !== 'all') {
    $sql .= " AND m.category = ?";
}

// Add sorting options
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
        $sql .= " ORDER BY m.category ASC"; // Default sort by category
        break;
}

// Prepare and execute the query
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
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .dropdown-menu {
            background-color: #fff;
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .dropdown-item {
            color: #333;
            transition: all 0.3s ease;
        }
        .dropdown-item:hover {
            background-color: #e44d26;
            color: white;
        }
        .filter-buttons {
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .filter-button {
            background-color: #e44d26;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            transition: opacity 0.3s ease;
            text-decoration: none;
        }
        .filter-button:hover {
            opacity: 0.8;
        }
        .filter-button.active {
            background-color: #c63e1e;
        }
        .menu-item {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .menu-item:hover {
            transform: translateY(-5px);
        }
        .menu-item img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .menu-item img:hover {
            transform: scale(1.05);
        }
        .menu-item .card-body {
            flex-grow: 1;
            text-align: center;
        }
        .stock-info {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }
        .btn-add-cart {
            background-color: #e44d26;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            transition: opacity 0.3s ease;
            width: 100%;
            margin-top: auto;
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
            width: 100%;
            margin-top: auto;
        }
        .sorting-options {
            margin-bottom: 2rem;
            text-align: center;
        }
        .sorting-options a {
            margin: 0 10px;
            text-decoration: none;
            color: #333;
            transition: color 0.3s ease;
        }
        .sorting-options a:hover {
            color: #e44d26;
        }
        .sorting-options a.active {
            color: #e44d26;
            font-weight: bold;
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
    <nav class="navbar navbar-expand-lg navbar-dark">
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

    <!-- Filter & Sorting Section -->
    <section class="container mt-4">
        <h2 class="text-center mb-4">Food Menu</h2>
        <div class="filter-buttons">
            <a href="?category=all" class="filter-button <?php echo $category_filter === 'all' ? 'active' : ''; ?>">All</a>
            <?php foreach ($categories as $category): ?>
                <a href="?category=<?php echo htmlspecialchars($category['category']); ?>" class="filter-button <?php echo $category_filter === $category['category'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category['category']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Sorting Options -->
        <div class="sorting-options">
            <span>Sort by:</span>
            <a href="?category=<?php echo $category_filter; ?>&sort=default" class="<?php echo $sort_order === 'default' ? 'active' : ''; ?>">Default</a>
            <a href="?category=<?php echo $category_filter; ?>&sort=price_low_to_high" class="<?php echo $sort_order === 'price_low_to_high' ? 'active' : ''; ?>">Price (Low to High)</a>
            <a href="?category=<?php echo $category_filter; ?>&sort=price_high_to_low" class="<?php echo $sort_order === 'price_high_to_low' ? 'active' : ''; ?>">Price (High to Low)</a>
            <a href="?category=<?php echo $category_filter; ?>&sort=name_a_to_z" class="<?php echo $sort_order === 'name_a_to_z' ? 'active' : ''; ?>">Name (A to Z)</a>
            <a href="?category=<?php echo $category_filter; ?>&sort=name_z_to_a" class="<?php echo $sort_order === 'name_z_to_a' ? 'active' : ''; ?>">Name (Z to A)</a>
        </div>
    </section>

    <!-- Menu Items -->
    <section class="container mt-4">
        <div class="row">
            <?php if (!empty($menuItems)): ?>
                <?php foreach ($menuItems as $item): ?>
                    <div class="col-md-4 mb-4">
                        <div class="menu-item <?php echo $item['quantity_in_stock'] <= 0 ? 'sold-out' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="card-body">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p><strong>Price:</strong> $<?php echo number_format($item['price'], 2); ?></p>
                                <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
                                <p class="stock-info">
                                    <?php if ($item['quantity_in_stock'] > 0): ?>
                                        <strong>In Stock:</strong> <?php echo $item['quantity_in_stock']; ?>
                                    <?php else: ?>
                                        <strong>Sold Out</strong>
                                    <?php endif; ?>
                                </p>
                                <button
                                    class="<?php echo $item['quantity_in_stock'] > 0 ? 'btn-add-cart' : 'btn-sold-out'; ?>"
                                    onclick="addToCart(<?php echo $item['item_id']; ?>)"
                                    <?php echo $item['quantity_in_stock'] <= 0 ? 'disabled' : ''; ?>
                                >
                                    <?php echo $item['quantity_in_stock'] > 0 ? '<i class="bi bi-cart-plus"></i> Add to Cart' : 'Sold Out'; ?>
                                </button>
                                <!-- Added View Details button -->
                                <a href="product_details.php?item_id=<?php echo $item['item_id']; ?>" class="btn btn-outline-secondary w-100 mt-2">
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
    </section>

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
