<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

<style>
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
</style>

</head>
<body>
<nav class="navbar">
    <div class="logo">Retailer Panel</div>
    <ul class="nav-links">
        <li><a href="#">Dashboard</a></li>
        <li><a href="#">Settings</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>
</nav>
</body>
</html>