<?php
session_start();
include '../sidepanel.php';
include '../../config.php';

// Check if the user is logged in as a retailer
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['stall_id']) || $_SESSION['role'] != 'Retailer') {
    header("Location: ../../auth/login.php");
    exit();
}

$stall_id = $_SESSION['stall_id'];

// Fetch feedback data for the specific stall
$feedback_sql = "SELECT * FROM feedback WHERE stall_id = ?";
$feedback_stmt = $con->prepare($feedback_sql);
$feedback_stmt->bind_param("i", $stall_id);
$feedback_stmt->execute();
$feedback_result = $feedback_stmt->get_result();
$feedback_data = [];
while ($row = $feedback_result->fetch_assoc()) {
    $feedback_data[] = $row;
}
$feedback_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            border-radius: 0.25rem;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-preparing {
            background-color: #d4edda;
            color: #155724;
        }
        .status-ready {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h1 class="h2 mb-0">Feedback Details</h1>
                </div>
            </div>

            <!-- Feedback Summary -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Feedback Summary</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Stall ID:</strong> <?php echo htmlspecialchars($stall_id); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback Items -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 card-title mb-4">Feedback Items</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Feedback ID</th>
                                    <th>User ID</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($feedback_data)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No feedback found for this stall.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($feedback_data as $feedback): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($feedback['feedback_id']); ?></td>
                                            <td><?php echo htmlspecialchars($feedback['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($feedback['rating']); ?></td>
                                            <td><?php echo htmlspecialchars($feedback['comment']); ?></td>
                                            <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="text-center">
                <a href="vorder.php" class="btn btn-secondary">Back to Orders</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>