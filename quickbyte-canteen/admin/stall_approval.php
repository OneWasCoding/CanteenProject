<?php
session_start();
include '../config.php'; // Database connection
include 'admin_menu.php'; // Ensure this includes Bootstrap and JS

// Ensure the logged-in user is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php"); // Redirect to login if unauthorized
    exit();
}

// Function to send email
function sendEmail($to, $subject, $message) {
    $headers = "From: no-reply@quickbyte-canteen.com\r\n";
    $headers .= "Reply-To: no-reply@quickbyte-canteen.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}

// Handle approval or denial
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $application_id = $_POST['application_id'];
        $action = $_POST['action']; // 'approve' or 'deny'

        // Fetch application details and retailer email
        $sql = "
            SELECT sa.*, u.email 
            FROM stall_application sa
            JOIN users u ON sa.user_id = u.user_id
            WHERE sa.application_id = ?
        ";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('i', $application_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $application = $result->fetch_assoc();

        if ($application) {
            $user_id = $application['user_id'];
            $stall_name = $application['stall_name'];
            $stall_description = $application['stall_description'];
            $email = $application['email']; // Fetch email from users table

            if ($action === 'approve') {
                // Insert into stalls table
                $sql = "INSERT INTO stalls (stall_name, description, image_path) VALUES (?, ?, ?)";
                $stmt = $con->prepare($sql);

                // Use a default image path or fetch from the application if available
                $image_path = 'default_stall.jpg'; // Default image path
                $stmt->bind_param('sss', $stall_name, $stall_description, $image_path);

                if ($stmt->execute()) {
                    // Update application status to 'Approved'
                    $sql = "UPDATE stall_application SET status = 'Approved', verification_date = NOW() WHERE application_id = ?";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param('i', $application_id);
                    $stmt->execute();

                    // Send approval email
                    $subject = "Your Stall Application Has Been Approved";
                    $message = "Dear Retailer,<br><br>Your stall application for <strong>$stall_name</strong> has been approved. You can now start managing your stall.<br><br>Best regards,<br>QuickByte Canteen";
                    if (sendEmail($email, $subject, $message)) {
                        $_SESSION['success'] = "Application approved and email sent.";
                    } else {
                        $_SESSION['error'] = "Application approved, but email could not be sent.";
                    }
                } else {
                    $_SESSION['error'] = "Failed to create stall.";
                }
            } elseif ($action === 'deny') {
                // Update application status to 'Denied'
                $sql = "UPDATE stall_application SET status = 'Denied', verification_date = NOW() WHERE application_id = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param('i', $application_id);
                $stmt->execute();

                // Send denial email
                $subject = "Your Stall Application Has Been Denied";
                $message = "Dear Retailer,<br><br>We regret to inform you that your stall application for <strong>$stall_name</strong> has been denied.<br><br>Best regards,<br>QuickByte Canteen";
                if (sendEmail($email, $subject, $message)) {
                    $_SESSION['success'] = "Application denied and email sent.";
                } else {
                    $_SESSION['error'] = "Application denied, but email could not be sent.";
                }
            }
        } else {
            $_SESSION['error'] = "Application not found.";
        }
    }
}

// Fetch all pending applications
$sql = "SELECT * FROM stall_application WHERE status = 'Pending'";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stall Application Approval - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        .admin-content { margin-left: 260px; padding: 20px; }
        .form-container { max-width: 1200px; margin: 40px auto; background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .table th, .table td { vertical-align: middle; }
        .btn-approve { background-color: #28a745; color: white; }
        .btn-deny { background-color: #dc3545; color: white; }
        .btn-approve:hover { background-color: #218838; }
        .btn-deny:hover { background-color: #c82333; }
    </style>
</head>
<body>
    <div class="admin-content">
        <div class="form-container">
            <h2>Stall Application Approval</h2>

            <!-- Display success or error messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Table to display pending applications -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Application ID</th>
                        <th>Stall Name</th>
                        <th>Stall Description</th>
                        <th>Application Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['application_id']; ?></td>
                            <td><?php echo $row['stall_name']; ?></td>
                            <td><?php echo $row['stall_description']; ?></td>
                            <td><?php echo $row['application_date']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-approve btn-sm">
                                        <i class="bi bi-check-circle"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>">
                                    <button type="submit" name="action" value="deny" class="btn btn-deny btn-sm">
                                        <i class="bi bi-x-circle"></i> Deny
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>