<?php
ob_start();
session_start();
include '../sidepanel.php';
include '../../config.php';

// Check if the user is logged in as a retailer
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['stall_id']) || $_SESSION['role'] != 'Retailer') {
    header("Location: ../../auth/login.php");
    exit();
}

$stall_id = $_SESSION['stall_id'];

// Fetch stall details
$stall_sql = "SELECT stall_name, description, image_path, status FROM stalls WHERE stall_id = ?";
$stall_stmt = $con->prepare($stall_sql);
$stall_stmt->bind_param("i", $stall_id);
$stall_stmt->execute();
$stall_result = $stall_stmt->get_result();
$stall = $stall_result->fetch_assoc();
$stall_stmt->close();

if (!$stall) {
    echo "Stall not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stall_name = $_POST['stall_name'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $image_path = $stall['image_path']; // Default to current image path

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../images/";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            }
        }
    }

    // Update stall details
    $update_sql = "UPDATE stalls SET stall_name = ?, description = ?, image_path = ?, status = ? WHERE stall_id = ?";
    $update_stmt = $con->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $stall_name, $description, $image_path, $status, $stall_id);
    $update_stmt->execute();
    $update_stmt->close();

    header("Location: settings.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stall Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group input[type="file"] {
            padding: 5px;
        }
        .btn-primary {
            background-color: #2c3e50;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #1a252f;
        }
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            border-radius: 0.25rem;
        }
        .status-open {
            background-color: #d4edda;
            color: #155724;
        }
        .status-closed {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="form-container">
            <h2>Stall Settings</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="stall_name">Stall Name</label>
                    <input type="text" id="stall_name" name="stall_name" value="<?php echo htmlspecialchars($stall['stall_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($stall['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Stall Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if ($stall['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($stall['image_path']); ?>" alt="Stall Image" style="max-width: 100px; margin-top: 10px;">
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="status">Stall Status</label>
                    <select id="status" name="status" required>
                        <option value="Open" <?php echo $stall['status'] === 'Open' ? 'selected' : ''; ?>>Open</option>
                        <option value="Closed" <?php echo $stall['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>