<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retailer Dashboard</title>
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

        /* Navigation Bar */
        .navbar {
            background-color: #004466;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            width: 100%;
        }

        .navbar .logo {
            font-size: 22px;
            font-weight: bold;
        }

        .nav-links {
            list-style: none;
            display: flex;
        }

        .nav-links li {
            margin-left: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: white;
            font-size: 16px;
            padding: 10px;
            transition: 0.3s;
        }

        .nav-links a:hover {
            background-color: #006699;
            border-radius: 5px;
        }

        /* Main Content */
        .main-content {
            text-align: center;
            margin: 60px auto;
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #004466;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #006699;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-links {
                margin-top: 10px;
            }

            .nav-links li {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">Retailer Panel</div>
        <ul class="nav-links">
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="#">Logout</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <h1>Welcome, Retailer!</h1>
        <p>Select the stall you want to manage:</p>
        <select>
            <option value="" disabled selected>Choose a stall</option>
            <option value="stall1">Stall 1 - Burger Hub</option>
            <option value="stall2">Stall 2 - Fresh Juices</option>
            <option value="stall3">Stall 3 - Rice Meals</option>
        </select>
        <button onclick="alert('Redirecting to stall management...')">Manage Stall</button>
    </div>

</body>
</html>
