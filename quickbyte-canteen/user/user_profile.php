<?php
session_start();
include '../config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT name, email, role, balance, phone, address, image_path FROM users WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Ensure default values for optional fields
$user['phone'] = $user['phone'] ?? 'Not Provided';
$user['address'] = $user['address'] ?? 'Not Provided';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>QuickByte Canteen - User Profile</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:600,300" rel="stylesheet">
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
        .frame {
            position: relative;
            max-width: 800px; /* Adjusted width */
            margin: 2rem auto;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            background: linear-gradient(to top right, #EEBE6C 0%, #CA7C4E 100%);
            color: #786450;
            font-family: 'Open Sans', Helvetica, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow: hidden;
        }
        .center {
            display: grid;
            grid-template-columns: 1fr 2fr; /* Profile on the left, stats/details on the right */
            align-items: center;
            gap: 2rem;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
        }
        .profile {
            text-align: center;
        }
        .image {
            position: relative;
            width: 150px; /* Larger image */
            height: 150px;
            margin: 0 auto 1rem auto;
        }
        .circle-1, .circle-2 {
            position: absolute;
            box-sizing: border-box;
            width: 156px;
            height: 156px;
            top: -3px;
            left: -3px;
            border-width: 2px;
            border-style: solid;
            border-color: #786450 #786450 #786450 transparent;
            border-radius: 50%;
            transition: all 1.5s ease-in-out;
        }
        .circle-2 {
            width: 162px;
            height: 162px;
            top: -6px;
            left: -6px;
            border-color: #786450 transparent #786450 #786450;
        }
        .image img {
            display: block;
            border-radius: 50%;
            background: #F5E8DF;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .image:hover {
            cursor: pointer;
        }
        .image:hover .circle-1 {
            transform: rotate(360deg);
        }
        .image:hover .circle-2 {
            transform: rotate(-360deg);
        }
        .name {
            font-size: 22px; /* Larger font */
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .job {
            font-size: 16px;
            line-height: 18px;
            margin-bottom: 1rem;
        }
        .actions .btn {
            display: inline-block;
            margin: 0 0.5rem 0.5rem 0;
            padding: 0.5rem 1rem;
            background: none;
            border: 2px solid #786450;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            color: #786450;
            transition: all 0.3s ease-in-out;
        }
        .actions .btn:hover {
            background: #786450;
            color: #fff;
        }
        .details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .details div {
            background: #F5E8DF;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.4s ease-in-out;
        }
        .details div:hover {
            background: #E1CFC2;
            cursor: pointer;
        }
        .details span {
            display: block;
        }
        .details .value {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .details .parameter {
            font-size: 14px;
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
                        <a class="nav-link" href="../user/index.php"><i class="bi bi-house"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php"><i class="bi bi-cart"></i> Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="user_profile.php"><i class="bi bi-person-circle"></i> Profile</a>
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
    <div class="frame">
        <div class="center">
            <!-- Profile Section -->
            <div class="profile">
                <div class="image">
                    <div class="circle-1"></div>
                    <div class="circle-2"></div>
                    <img src="<?php echo htmlspecialchars($user['image_path']); ?>" alt="Profile Picture">
                </div>
                <div class="name"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="job"><?php echo htmlspecialchars($user['role']); ?></div>
                <div class="actions">
                    <a href="update_profile.php" class="btn">Edit Profile</a>
                    <a href="order_history.php" class="btn">Order History</a>
                    <button class="btn" onclick="addBalance()">Add Balance</button>
                </div>
            </div>
            <!-- Details Section -->
            <div class="details">
                <div>
                    <span class="value" id="current-balance">$<?php echo number_format($user['balance'], 2); ?></span>
                    <span class="parameter">Balance</span>
                </div>
                <div>
                    <span class="value"><?php echo htmlspecialchars($user['email']); ?></span>
                    <span class="parameter">Email</span>
                </div>
                <div>
                    <span class="value"><?php echo htmlspecialchars($user['phone']); ?></span>
                    <span class="parameter">Phone</span>
                </div>
                <div>
                    <span class="value"><?php echo htmlspecialchars($user['address']); ?></span>
                    <span class="parameter">Address</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 QuickByte Canteen. All rights reserved.</p>
    </footer>

    <!-- JavaScript for Adding Balance -->
    <script>
        function addBalance() {
            const amount = prompt('Enter the amount you want to add:');
            if (amount && !isNaN(amount) && parseFloat(amount) > 0) {
                // Send the amount to the server using AJAX
                fetch('update_balance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ amount: parseFloat(amount) })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`You have added $${parseFloat(amount).toFixed(2)} to your balance.`);
                        // Update the balance in the UI
                        const currentBalanceElement = document.getElementById('current-balance');
                        const currentBalance = parseFloat(currentBalanceElement.textContent.replace('$', ''));
                        currentBalanceElement.textContent = `$${(currentBalance + parseFloat(amount)).toFixed(2)}`;
                    } else {
                        alert('Failed to update balance.');
                    }
                });
            } else {
                alert('Invalid amount. Please enter a valid positive number.');
            }
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>