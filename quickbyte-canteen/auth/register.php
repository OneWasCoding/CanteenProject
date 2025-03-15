<?php
session_start();
include '../config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = isset($_POST["role"]) ? trim($_POST["role"]) : '';
    $stall_id = isset($_POST["stall_id"]) ? trim($_POST["stall_id"]) : '';

    if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all fields.";
    } elseif ($role === "Retailer" && empty($stall_id)) {
        $error = "Please select a stall.";
    } else {
        $check_email_sql = "SELECT user_id FROM users WHERE email = ?";
        $check_email_stmt = $con->prepare($check_email_sql);
        $check_email_stmt->bind_param("s", $email);
        $check_email_stmt->execute();
        $check_email_stmt->store_result();

        if ($check_email_stmt->num_rows > 0) {
            $error = "This email address is already registered. Please use a different email or log in.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert user data into the users table
            $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ssss", $full_name, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                $user_id = $con->insert_id; // Get the newly inserted user ID

                // If the user is a retailer, store additional data in the retailers table
                if ($role === "Retailer") {
                    $retailer_sql = "INSERT INTO retailers (user_id, stall_id) VALUES (?, ?)";
                    $retailer_stmt = $con->prepare($retailer_sql);
                    $retailer_stmt->bind_param("ii", $user_id, $stall_id);

                    if ($retailer_stmt->execute()) {
                        header("Location: login.php");
                        exit();
                    } else {
                        $error = "Retailer registration failed. Please try again.";
                    }
                    $retailer_stmt->close();
                } else {
                    header("Location: login.php");
                    exit();
                }
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $check_email_stmt->close();
    }
}
?>
<script>
        function checkRole() {
            let roleField = document.getElementById("role");
            let stallField = document.getElementById("stall-select");

            if (roleField.value === "Retailer") {
                if (!stallField) {
                    // Create stall select field if it doesn't exist
                    stallField = document.createElement("div");
                    stallField.id = "stall-select";
                    stallField.innerHTML = `
                        <div class="form-group">
                            <i class="fas fa-store"></i>
                            <select class="form-select" id="stall_id" name="stall_id" required>
                                <option value="">Select Stall</option>
                                <?php
                                $stallSql = "SELECT stall_id, name FROM stalls";
                                $stallStmt = $con->prepare($stallSql);
                                $stallStmt->execute();
                                $stallResult = $stallStmt->get_result();

                                while ($stallRow = $stallResult->fetch_assoc()) {
                                    echo "<option value='" . $stallRow['stall_id'] . "'>" . $stallRow['name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    `;
                    roleField.parentNode.parentNode.appendChild(stallField);
                }
            } else {
                if (stallField) {
                    stallField.remove(); // Remove stall select if role changes
                }
            }
        }
    </script>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - QuickByte Canteen</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
    <style>
        
        body {
    background-color: #f8f9fa;
    background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                     url('../assets/img/canteen-background.jpg');
    background-size: cover;
    background-position: center;
    min-height: 100vh;
    display: block;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 15px;
    
}

.register-container {
    background-color: rgba(255, 255, 255, 0.92);
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    width: 100%;
    max-width: 400px;
    max-height: 90vh; 
    position: relative;
    display: block;
    flex-direction: column;
}

.register-container::-webkit-scrollbar {
    width: 8px; /* Thin scrollbar */
}

.register-container::-webkit-scrollbar-track {
    background: #f1f1f1; /* Light gray track */
    border-radius: 10px;
}

.register-container::-webkit-scrollbar-thumb {
    background: #e44d26; /* Bootstrap primary color */
    border-radius: 10px;
}

.register-container::-webkit-scrollbar-thumb:hover {
    background:rgb(44, 37, 35); /* Slightly darker on hover */
}

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

        .form-control, .form-select {
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

        .form-content {
            flex-grow: 1;
            max-height: 50vh; /* Limits the scrollable height */
             overflow-y: auto; /* Enables vertical scrolling */
            padding-right: 10px; /* Prevents content from overlapping scrollbar */
            scrollbar-width: thin;
            scrollbar-color: #e44d26 #f1f1f1;
        }

        
    </style>

    
</head>
<body>

<div class="register-container">
    <div class="logo-container">
        <div class="logo-circle">
            <i class="fas fa-user-plus"></i>
        </div>
        <h2 class="text-center mb-4">Create Account</h2>
    </div>

    <form id="register-form" method="POST" class="form-content">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <i class="fas fa-user"></i>
            <input type="text" class="form-control" id="full_name" name="full_name"
                   placeholder="Full Name" required>
        </div>

        <div class="form-group">
            <i class="fas fa-envelope"></i>
            <input type="email" class="form-control" id="email" name="email"
                   placeholder="Email Address" required>
        </div>

        <div class="form-group">
            <i class="fas fa-lock"></i>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Password" required>
        </div>

        <div class="form-group">
            <i class="fas fa-user-tag"></i>
            <select class="form-select" id="role" name="role" required onchange="checkRole()">
                <option value="">Select Role</option>
                <option value="Student">Student</option>
                <option value="Retailer">Retailer</option>
            </select>
        </div>

    </form>

    <div class="mt-auto">
        <button type="submit" form="register-form" class="btn btn-primary btn-block">Register</button>

        <div class="text-center mt-4">
            <p class="mb-0">Already have an account?
                <a href="login.php" class="text-primary">Login here</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
