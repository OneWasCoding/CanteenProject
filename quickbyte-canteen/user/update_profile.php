<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$error = "";
$message = "";
$imageUploadMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['name'] != "") {
        $target_dir = "images/profiles/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
        if ($check === false) {
            $uploadOk = 0;
            $imageUploadMessage = "File is not an image.";
        }
        if ($_FILES["profile_image"]["size"] > 500000) {
            $uploadOk = 0;
            $imageUploadMessage = "Sorry, your file is too large.";
        }
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $uploadOk = 0;
            $imageUploadMessage = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $sql = "UPDATE users SET image_path = ? WHERE user_id = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("si", $target_file, $user_id);
                if ($stmt->execute()) {
                    $imageUploadMessage = "The file " . htmlspecialchars(basename($_FILES["profile_image"]["name"])) . " has been uploaded.";
                } else {
                    $imageUploadMessage = "Sorry, there was an error updating your profile image.";
                }
                $stmt->close();
            } else {
                $imageUploadMessage = "Sorry, there was an error uploading your file.";
            }
        }
    }

    if (isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['address'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        if (empty($name) || empty($email) || empty($phone) || empty($address)) {
            $error = "Please fill in all fields.";
        } else {
            $update_sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?";
            $stmt_update = $con->prepare($update_sql);
            $stmt_update->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
            if ($stmt_update->execute()) {
                $message = "Profile updated successfully.";
            } else {
                $error = "Error updating profile: " . htmlspecialchars($stmt_update->error);
            }
            $stmt_update->close();
        }
    }
}

$sql = "SELECT name, email, phone, address, image_path FROM users WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>QuickByte Canteen - Update Profile</title>
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

        .update-profile-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 600px;
        }

        .update-profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 5px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(228,77,38,0.3);
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
            padding: 1rem;
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
            .update-profile-container {
                margin: 1rem;
                padding: 1rem;
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
    <div class="container">
        <div class="update-profile-container">
            <div class="update-profile-header">
                <h2>Update Profile</h2>
                <p>Customize your profile information</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (!empty($imageUploadMessage)): ?>
                <div class="alert alert-info"><?php echo $imageUploadMessage; ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <img src="<?php echo htmlspecialchars($user['image_path']); ?>" alt="Profile Picture" class="profile-image">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Delivery Address</label>
                    <input type="text" class="form-control" id="address" name="address" 
                           value="<?php echo htmlspecialchars($user['address']); ?>" required>
                </div>

                <div class="mb-4">
                    <label for="profile_image" class="form-label">Profile Picture</label>
                    <input class="form-control" type="file" id="profile_image" name="profile_image">
                    <small class="text-muted">Maximum file size: 500KB. Supported formats: JPG, JPEG, PNG, GIF</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle"></i> Save Changes
                </button>
            </form>
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