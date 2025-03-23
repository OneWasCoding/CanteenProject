<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['change_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            $password_error = "Please fill in all password fields.";
        } elseif ($new_password !== $confirm_password) {
            $password_error = "New password and confirm password do not match.";
        } else {
            $check_sql = "SELECT password FROM users WHERE user_id = ?";
            $stmt_check = $con->prepare($check_sql);
            $stmt_check->bind_param("i", $user_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $user_password = $result_check->fetch_assoc();
            $stmt_check->close();

            if (password_verify($old_password, $user_password['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_password_sql = "UPDATE users SET password = ? WHERE user_id = ?";
                $stmt_update_password = $con->prepare($update_password_sql);
                $stmt_update_password->bind_param("si", $hashed_password, $user_id);

                if ($stmt_update_password->execute()) {
                    $password_message = "Password updated successfully.";
                } else {
                    $password_error = "Error updating password: " . htmlspecialchars($stmt_update_password->error);
                }
                $stmt_update_password->close();
            } else {
                $password_error = "Old password is incorrect.";
            }
        }
    }

    if (isset($_POST['deactivate_account'])) {
        $deactivate_sql = "UPDATE users SET status = 0 WHERE user_id = ?";
        $stmt_deactivate = $con->prepare($deactivate_sql);
        $stmt_deactivate->bind_param("i", $user_id);

        if ($stmt_deactivate->execute()) {
            $deactivate_message = "Account deactivated successfully.";
            session_destroy();
            header("Location: ../auth/login.php");
            exit();
        } else {
            $deactivate_error = "Error deactivating account: " . htmlspecialchars($stmt_deactivate->error);
        }
        $stmt_deactivate->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>QuickByte Canteen - Settings</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .settings-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 800px;
        }

        .settings-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .settings-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .settings-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .settings-section h4 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #e44d26;
            box-shadow: 0 0 0 0.2rem rgba(228,77,38,0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #e44d26, #ff7f50);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(228,77,38,0.3);
        }

        .btn-danger {
            background: #dc3545;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
            background: #c82333;
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
            .settings-container {
                margin: 1rem;
                padding: 1rem;
            }
            
            .settings-section {
                padding: 1rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
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
                        <a class="nav-link active" href="settings.php"><i class="bi bi-gear"></i> Settings</a>
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
        <div class="settings-container">
            <div class="settings-header">
                <h2><i class="bi bi-gear"></i> Account Settings</h2>
                <p class="text-muted">Manage your account security and preferences</p>
            </div>

            <!-- Password Change Section -->
            <div class="settings-section">
                <h4><i class="bi bi-lock"></i> Change Password</h4>
                <form method="post">
                    <div class="mb-3">
                        <label for="old_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <?php if (isset($password_error)): ?>
                        <div class="alert alert-danger"><?php echo $password_error; ?></div>
                    <?php endif; ?>
                    <?php if (isset($password_message)): ?>
                        <div class="alert alert-success"><?php echo $password_message; ?></div>
                    <?php endif; ?>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Update Password
                    </button>
                </form>
            </div>

            <!-- Account Management Section -->
            <div class="settings-section">
                <h4><i class="bi bi-person-x"></i> Account Management</h4>
                <p class="text-muted mb-4">Deactivating your account will remove your profile and all associated data.</p>
                <form method="post" onsubmit="return confirm('Are you sure you want to deactivate your account? This action cannot be undone.');">
                    <button type="submit" name="deactivate_account" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Deactivate Account
                    </button>
                    <?php if (isset($deactivate_error)): ?>
                        <div class="alert alert-danger mt-3"><?php echo $deactivate_error; ?></div>
                    <?php endif; ?>
                </form>
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