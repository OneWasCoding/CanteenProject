<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
        }

        /* Navbar Styling */
        .navbar {
            background-color: #004466;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
        }

        .navbar .logo {
            font-size: 22px;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
        }

        .nav-links {
            list-style: none;
            display: flex;
        }

        .nav-links li {
            position: relative;
        }

        .nav-links a {
            text-decoration: none;
            color: white;
            padding: 12px 18px;
            display: block;
            transition: 0.3s;
        }

        .nav-links a:hover {
            background-color: #006699;
        }

        /* Dropdown Styling */
        .dropdown-menu {
            position: absolute;
            background-color: #004466;
            min-width: 180px;
            top: 100%;
            left: 0;
            display: none;
            flex-direction: column;
            z-index: 100;
        }

        .dropdown-menu a {
            padding: 12px;
            white-space: nowrap;
        }

        .nav-links li:hover .dropdown-menu {
            display: flex;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
            }

            .nav-links li {
                width: 100%;
            }

            .nav-links a {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="logo">Canteen Management</a>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="#">Orders</a></li>
            <li>
                <a href="#">Menu ▼</a>
                <ul class="dropdown-menu">
                    <li><a href="#">Breakfast</a></li>
                    <li><a href="#">Lunch</a></li>
                    <li><a href="#">Snacks</a></li>
                </ul>
            </li>
            <li><a href="#">Transactions</a></li>
            <li><a href="#">Reports</a></li>
            <li><a href="#">Logout</a></li>
        </ul>
    </nav>

</body>
</html>
