<?php
session_start();
if (isset($_POST['stall_name'])) {
    $_SESSION['stall_name'] = $_POST['stall_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Input</title>
    <link href="../../css/styles.css" rel="stylesheet" />
    <style>
        .password-input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }
        .back-button {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #004466;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .back-button:hover {
            background-color: #006699;
        }
        .main-content {
            position: relative;
        }
    </style>
</head>
<body>
<?php include('header.php'); ?>
    
    <div class="main-content">
        <a href="index.php" class="back-button">Back</a>
        <h1 style="margin-top: 40px;">Enter Password for <?php echo $_SESSION['stall_name']?></h1>
        <form action="redirect.php" method="POST">
            <input type="password" class="password-input" name="password" placeholder="Password" required>
            <?php if (isset($_SESSION['stall_name'])): ?>
                <input type="hidden" name="stall_name" value="<?php echo $_SESSION['stall_name']; ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

</body>
</html>
