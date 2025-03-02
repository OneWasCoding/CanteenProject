<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["stall_name"]) && !empty($_POST["password"])) {
        $stall = $_POST["stall_name"]; // Get stall name
        $password = $_POST["password"]; // Get password

        // Define a mapping of stall names to their respective PHP pages
        $stallPages = [
            "Kael Food Store" => "kael.php",
            "Bonapetite" => "bonapetite.php"
        ];

        // Verify the password here
        // For demonstration purposes, assume the password is correct
        if (true) { // Replace with actual password verification logic
            if (array_key_exists($stall, $stallPages)) {
                $redirectPage = $stallPages[$stall];
                header("Location: $redirectPage?stall=" . urlencode($stall));
            } else {
                // Redirect to a default page if stall is not found
                header("Location: index.php");
            }
        } else {
            echo "Incorrect password.";
        }
    } else {
        // Redirect back if no stall or password is provided
        header("Location: index.php");
    }
    exit();
    }   
    ?>
