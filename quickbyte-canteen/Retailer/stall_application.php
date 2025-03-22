<?php
session_start();
include '../config.php'; // Database connection

// Ensure the logged-in user is a Retailer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Retailer') {
    header("Location: ../login.php"); // Redirect to login page if unauthorized
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure form data is received
    if (empty($_POST)) {
        $_SESSION['error'] = "No form data received.";
        header("Location: stall_application.php"); // Redirect back to the form
        exit();
    }

    $stall_name = $_POST['stall_name'];
    $stall_description = $_POST['stall_description'];

    // Define the base upload directory
    $base_upload_dir = '../images/application/';

    // Create necessary subdirectories
    $subdirectories = [
        'birth' => $base_upload_dir . 'birth/',
        'tin' => $base_upload_dir . 'tin/',
        'permit' => $base_upload_dir . 'permit/',
        'id' => $base_upload_dir . 'id/',
    ];

    foreach ($subdirectories as $subdir) {
        if (!is_dir($subdir)) {
            mkdir($subdir, 0777, true);
        }
    }

    // Function to handle file uploads with error handling
    function uploadFile($file, $upload_dir, $allowed_extensions) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ["error" => "File upload error. Code: " . $file['error']];
        }

        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_extensions)) {
            return ["error" => "Invalid file type. Only JPEG and JPG are allowed."];
        }

        $new_file_name = uniqid() . '.' . $file_ext;
        $file_path = $upload_dir . $new_file_name;

        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            return ["error" => "Failed to move uploaded file."];
        }

        return ["path" => str_replace('../', '', $file_path)];
    }

    // Allowed file extensions
    $allowed_extensions = ['jpeg', 'jpg'];

    // Upload files
    $birth_certificate = uploadFile($_FILES['birth_certificate'], $subdirectories['birth'], $allowed_extensions);
    $tin_number = uploadFile($_FILES['tin_number'], $subdirectories['tin'], $allowed_extensions);
    $business_permit = uploadFile($_FILES['business_permit'], $subdirectories['permit'], $allowed_extensions);
    $valid_id = uploadFile($_FILES['valid_id'], $subdirectories['id'], $allowed_extensions);

    // Check if any file failed to upload
    $upload_errors = [];
    foreach ([$birth_certificate, $tin_number, $business_permit, $valid_id] as $file) {
        if (isset($file['error'])) {
            $upload_errors[] = $file['error'];
        }
    }

    if (!empty($upload_errors)) {
        $_SESSION['error'] = implode(", ", $upload_errors);
        header("Location: stall_application.php"); // Redirect back to the form
        exit();
    }

    // Prepare SQL Statement
    $sql = "INSERT INTO stall_application (user_id, stall_name, stall_description, birth_certificate, tin_number, business_permit, valid_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $con->error;
        header("Location: stall_application.php"); // Redirect back to the form
        exit();
    }

    $stmt->bind_param(
        'issssss',
        $user_id,
        $stall_name,
        $stall_description,
        $birth_certificate['path'],
        $tin_number['path'],
        $business_permit['path'],
        $valid_id['path']
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Stall application submitted successfully!";
    } else {
        $_SESSION['error'] = "Database insertion failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: ../auth/login.php"); // Redirect back to the form
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stall Application Form - QuickByte Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
        }
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .success-message {
            color: green;
            font-weight: bold;
        }
        .error-message {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Stall Application Form</h2>

    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="stall_application.php" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="stall_name" class="form-label">Stall Name</label>
            <input type="text" class="form-control" id="stall_name" name="stall_name" required>
        </div>

        <div class="mb-4">
            <label for="stall_description" class="form-label">Stall Description</label>
            <textarea class="form-control" id="stall_description" name="stall_description" rows="4" required></textarea>
        </div>

        <div class="mb-4">
            <label class="form-label"><strong>Birth Certificate</strong> (JPEG/JPG only)</label>
            <input type="file" class="form-control" name="birth_certificate" accept=".jpeg,.jpg" required>
        </div>

        <div class="mb-4">
            <label class="form-label"><strong>TIN Number</strong> (JPEG/JPG only)</label>
            <input type="file" class="form-control" name="tin_number" accept=".jpeg,.jpg" required>
        </div>

        <div class="mb-4">
            <label class="form-label"><strong>Business Permit</strong> (JPEG/JPG only)</label>
            <input type="file" class="form-control" name="business_permit" accept=".jpeg,.jpg" required>
        </div>

        <div class="mb-4">
            <label class="form-label"><strong>Valid ID</strong> (JPEG/JPG only)</label>
            <input type="file" class="form-control" name="valid_id" accept=".jpeg,.jpg" required>
        </div>

        <button type="submit" class="btn btn-primary">Submit Application</button>
    </form>
</div>

</body>
</html>