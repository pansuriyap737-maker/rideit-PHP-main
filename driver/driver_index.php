<?php
// session_start();
include('../config.php');

// Check driver login
if (!isset($_SESSION['driver_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

// Fetch driver name (for welcome)
$driverId = (int)$_SESSION['driver_id'];
$query = mysqli_query($conn, "SELECT name FROM drivers WHERE id = $driverId");
$drv = mysqli_fetch_assoc($query);
$userName = $drv ? $drv['name'] : 'Driver';
?>

<style>
    /* Existing styles from your PHP file and the integrated React CSS */

    * {
        margin: 0;
        padding: 0;
    }

    /* Importing font from React CSS */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

    /* Styles from your React CSS file */
    .nav {
        font-family: "Poppins", sans-serif;
        color: white;
        text-decoration: none;
        font-weight: 600;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 20px;
        margin: 10px;
        transition: all 0.3s ease;
        display: inline-block;
        transform-origin: center;
    }

    .nav:hover {
        transform: scale(105%) !important;
        background-color: white !important;
        color: #6a0fe0 !important;
    }
    .nav.active {
        background:#ffffff !important;
        color:#6a0fe0 !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    .header {
        background-color: #6a0fe0;
        color: white;
        padding: 0px 10px 0 2px;
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
        color: white;
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
        margin-right: 30px;
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
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        padding: 6px;
        min-width: 160px;
        z-index: 1000;
        display: none;
        /* Initially hidden */
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
        cursor: pointer;
    }

    .user-menu-item:hover {
        background: #f3e9ff;
        color: #6a0fe0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const userLogo = document.getElementById('user-logo');
        const userMenu = document.getElementById('user-menu');

        if (userLogo && userMenu) {
            userLogo.addEventListener('click', function (e) {
                e.stopPropagation();  // Prevent immediate closure
                userMenu.style.display = (userMenu.style.display === 'block') ? 'none' : 'block';
            });

            // Close menu when clicking outside
            document.addEventListener('click', function () {
                if (userMenu.style.display === 'block') {
                    userMenu.style.display = 'none';
                }
            });
        }
    });
</script>

<!-- Navigation Bar -->
<?php $currentPath = $_SERVER['REQUEST_URI'] ?? ''; ?>
<div class="header">
    <!-- Main logo on the left -->
    <div>
        <a href="add_trip.php"><img src="/rideit/assets/images/rideitlogo.png" alt="" class="logo"></a>
    </div>

    <!-- Right-side content -->
    <div class="header-right">
        <a href="add_trip.php" class="nav <?= strpos($currentPath, 'add_trip.php') !== false ? 'active' : '' ?>">Ride Share</a>
        <a href="view_information.php" class="nav <?= strpos($currentPath, 'view_information.php') !== false ? 'active' : '' ?>">Ride Details</a>
        <a href="pending_rides.php" class="nav <?= strpos($currentPath, 'pending_rides.php') !== false ? 'active' : '' ?>">Pending Rides</a>
        <a href="driver_bookings.php" class="nav <?= strpos($currentPath, 'driver_bookings.php') !== false ? 'active' : '' ?>">My Bookings</a>


        <!-- User menu wrapper -->
        <div class="user-menu-wrapper" style="position: relative; display: inline-block;">
            <img src="/rideit/assets/images/userlogo.png" alt="User menu" class="user-profile-logo" id="user-logo">

            <!-- Dropdown menu -->
            <div id="user-menu" class="user-menu">
                <a href="driver_profile.php" class="user-menu-item">My Profile</a>
                <a href="logout.php" class="user-menu-item">Log Out</a>
            </div>
        </div>
    </div>
</div>

<!-- Rest of your PHP content
<div class="container">
    <h2>Welcome, </h2>
     Other content here -->
<!-- </div> -->