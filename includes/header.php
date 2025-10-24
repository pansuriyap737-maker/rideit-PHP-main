<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rideit</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Your existing CSS link remains unchanged -->
    
    <!-- Adding the provided CSS inside a <style> tag as requested -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        /* CSS Variables for easier maintenance */
        :root {
            --primary-color: #6a0fe0;
            --text-color: white;
            --hover-bg: white;
            --hover-text: var(--primary-color);
        }

        .nav {
            font-family: "Poppins", sans-serif;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 20px;
            margin: 10px;
            transition: all 0.3s ease;
            display: inline-block;
            transform-origin: center;
        }

        .nav:hover {
            transform: scale(105%);
            background-color: var(--hover-bg);
            color: var(--hover-text);
        }

        .nav.active {
            background: #ffffff;
            color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .header {
            background-color: var(--primary-color);
            color: var(--text-color);
            padding: 0 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            height: 100px;
        }

        .logo {
            width: 150px;
            height: 100px;
            object-fit: cover;
            transition: 0.3s;
        }

        .logo:hover {
            transform: scale(105%);
        }

        .user-profile-logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            cursor: pointer;
            transition: 0.3s;
            margin: 10px;
        }

        .user-profile-logo:hover {
            transform: scale(105%);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .user-menu {
            position: absolute;
            top: 65px;
            right: 10px;
            background: #ffffff;
            color: #333;
            border-radius: 8px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            padding: 6px;
            min-width: 160px;
            z-index: 1000;
        }

        .user-menu-item {
            display: block;
            padding: 10px 12px;
            color: #333;
            text-decoration: none;
            font-family: "Poppins", sans-serif;
            font-size: 16px;
            border-radius: 6px;
            font-weight: 500;
        }

        .user-menu-item:hover {
            background: #f3e9ff;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <header class="header">  <!-- Added class="header" to match the CSS. You can remove or adjust if needed -->
        <div class="logo-section">
            <a href="/rideit/pages/home.php" class="logo-link">
                <img src="/rideit/assets/images/rideitlogo.png" alt="rideit Logo" class="logo-img logo">  <!-- Added class="logo" to match the CSS -->
            </a>
        </div>
        <?php $currentPath = $_SERVER['REQUEST_URI'] ?? ''; ?>
        <nav>
            <ul>
                <li><a href="/rideit/pages/home.php" class="nav <?= strpos($currentPath, '/rideit/pages/home.php') !== false ? 'active' : '' ?>">Home</a></li>  <!-- Added class="nav" to the <a> tags -->
                <li><a href="/rideit/pages/about.php" class="nav <?= strpos($currentPath, '/rideit/pages/about.php') !== false ? 'active' : '' ?>">About</a></li>
                <li><a href="/rideit/pages/contact.php" class="nav <?= strpos($currentPath, '/rideit/pages/contact.php') !== false ? 'active' : '' ?>">Contact</a></li>
                <!-- <li><a href="/rideit/pages/donate.php" class="nav">Donate Now</a></li> -->  <!-- Added class="nav" if you uncomment -->
                <li><a href="/rideit/pages/login.php" class="nav <?= strpos($currentPath, '/rideit/pages/login.php') !== false ? 'active' : '' ?>">Login</a></li>
                <li><a href="/rideit/pages/register.php" class="nav <?= strpos($currentPath, '/rideit/pages/register.php') !== false ? 'active' : '' ?>">Register</a></li>
            </ul>
        </nav>
    </header>
</body>
</html>
