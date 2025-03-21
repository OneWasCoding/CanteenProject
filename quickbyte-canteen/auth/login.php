<<?php
session_start();
include '../config.php';

// Check if config.php is working and the database connection is established
if (!isset($con)) {
    die("Database connection is NOT established. Check your database credentials.");
}

$error = ""; // Initialize error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare the SQL statement
    $sql = "SELECT user_id, name, password, role FROM users WHERE email = ? AND status = 1";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on user role
            switch ($user['role']) {
                case 'Admin':
                    header("Location: ../admin/admin_dashboard.php");
                    exit();

                case 'Retailer':
                    // Fetch stall_id from the retailers table
                    $retailer_sql = "SELECT stall_id FROM retailers WHERE user_id = ?";
                    $retailer_stmt = $con->prepare($retailer_sql);
                    $retailer_stmt->bind_param("i", $user['user_id']);
                    $retailer_stmt->execute();
                    $retailer_result = $retailer_stmt->get_result();
                    $retailer = $retailer_result->fetch_assoc();
                    $retailer_stmt->close();

                    if ($retailer && !empty($retailer['stall_id'])) {
                        $_SESSION['stall_id'] = $retailer['stall_id']; // Store stall_id in session
                        header("Location: ../retailer/stalls/stall_{$retailer['stall_id']}.php");
                        exit();
                    } else {
                        // If no stall is assigned, redirect to stall registration
                        header("Location: ../Retailer/stall_application.php");
                        exit();
                    }
                    break;

                case 'Customer':
                default:
                    header("Location: ../user/index.php");
                    exit();
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Account does not exist, please register!";
    }
}

$pageTitle = "QuickByte Canteen - Login";
include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

<style>
    /* Custom canteen-themed styles */
    body {
        background-color: #f8f9fa;
        background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                         url('../assets/img/canteen-background.jpg');
        background-size: cover;
        background-position: center;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 15px;
        overflow: hidden;
    }

    .login-container {
        background-color: rgba(255, 255, 255, 0.92);
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        width: 100%;
        max-width: 400px;
        margin: auto;
        position: relative;
        transition: all 0.3s ease;
        z-index: 10;
    }

    /* Logo styles */
    .logo-container {
        text-align: center;
        margin-bottom: 2rem;
    }

    .logo-circle {
        background-color: #e44d26;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        margin-bottom: 1rem;
    }

    .logo-circle i {
        font-size: 2rem;
        color: white;
    }

    /* Form styles */
    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-group i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #e44d26;
    }

    .form-control {
        padding-left: 2.5rem;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .btn-primary {
        background-color: #e44d26;
        border: none;
        padding: 0.8rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #d13d17;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(228, 77, 38, 0.2);
    }

    /* Floating icons */
    .floating-icons {
        position: fixed;
        width: 100vw;
        height: 100vh;
        top: 0;
        left: 0;
        pointer-events: none;
        z-index: 1;
    }

    .floating-icon {
        position: absolute;
        font-size: 1.2rem;
        color: #e44d26;
        opacity: 0.4;
        animation: floatIcon 4s ease-in-out infinite;
    }

    @keyframes floatIcon {
        0%, 100% {
            transform: translate(0, 0) rotate(0deg);
            opacity: 0.4;
        }
        50% {
            transform: translate(5px, -5px) rotate(10deg);
            opacity: 0.6;
        }
    }

    /* Animation delays for icons */
    .floating-icons i:nth-child(1) { animation-delay: 0s; }
    .floating-icons i:nth-child(2) { animation-delay: 0.5s; }
    .floating-icons i:nth-child(3) { animation-delay: 1s; }
    .floating-icons i:nth-child(4) { animation-delay: 1.5s; }
    .floating-icons i:nth-child(5) { animation-delay: 2s; }
    .floating-icons i:nth-child(6) { animation-delay: 2.5s; }
    .floating-icons i:nth-child(7) { animation-delay: 3s; }
    .floating-icons i:nth-child(8) { animation-delay: 3.5s; }
    .floating-icons i:nth-child(9) { animation-delay: 4s; }
    .floating-icons i:nth-child(10) { animation-delay: 4.5s; }
    .floating-icons i:nth-child(11) { animation-delay: 5s; }
    .floating-icons i:nth-child(12) { animation-delay: 5.5s; }

    /* Responsive text */
    @media (max-width: 576px) {
        .login-container {
            padding: 1.5rem;
        }
    }
</style>

<div class="floating-icons">
    <i class="bi bi-egg-fried floating-icon" style="top: 20%; left: 10%;"></i>
    <i class="bi bi-cup-hot floating-icon" style="top: 15%; right: 15%;"></i>
    <i class="bi bi-basket floating-icon" style="bottom: 20%; left: 15%;"></i>
    <i class="bi bi-cup-straw floating-icon" style="bottom: 25%; right: 10%;"></i>
    <i class="bi bi-burger floating-icon" style="top: 35%; left: 25%;"></i>
    <i class="bi bi-cake floating-icon" style="top: 45%; right: 30%;"></i>
    <i class="bi bi-cookie floating-icon" style="bottom: 40%; left: 35%;"></i>
    <i class="bi bi-pie-chart floating-icon" style="top: 60%; right: 40%;"></i>
    <i class="bi bi-cup floating-icon" style="bottom: 15%; right: 25%;"></i>
    <i class="bi bi-egg floating-icon" style="top: 75%; left: 45%;"></i>
    <i class="bi bi-dice-6 floating-icon" style="bottom: 35%; right: 45%;"></i>
    <i class="bi bi-bag-check floating-icon" style="top: 85%; right: 15%;"></i>
</div>

<div class="login-container">
    <div class="logo-container">
        <div class="logo-circle">
            <i class="bi bi-shop"></i>
        </div>
    </div>

    <h2 class="text-center mb-4">QuickByte Canteen</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <i class="bi bi-envelope"></i>
            <input type="email" class="form-control" id="email" name="email"
                   placeholder="Enter email" required>
        </div>
        <div class="form-group">
            <i class="bi bi-lock"></i>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block w-100">Login</button>
    </form>

    <p class="text-center mt-4 mb-0">
        Don't have an account?
        <a href="register.php" class="text-primary fw-bold">Register here</a>
    </p>
</div>

<?php include '../includes/footer.php'; ?>