<?php if (!isset($disable_admin_header_styles)): ?>
<!-- Sidebar, header CSS, menu, or anything else -->
<link rel="stylesheet" href="admin_sidebar.css">
<div class="sidebar">...</div>
<?php endif; ?>


<?php
// session_start();
include('../config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            /* display: flex; */
        }

        .sidebar {
            width: 220px;
            background-color: #343a40;
            height: 100vh;
            position: fixed;
            top: 0;
            left: -220px;
            transition: left 0.3s ease;
            padding-top: 30px;
            z-index: 999;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar a {
            display: block;
            padding: 15px 20px;
            color: #fff;
            text-decoration: none;
            border-bottom: 1px solid #495057;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .main-content {
            margin-left: 0;
            padding: 30px;
            width: 100%;
            transition: margin-left 0.3s ease;
        }

        .main-content.shifted {
            margin-left: 220px;
        }

        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1000;
            background: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        h1 {
            color: #007bff;
        }

        .card-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>


<!-- Toggle Button -->
<button class="toggle-btn" id="toggleBtn">☰ Menu</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <br> <br>
    <a href="admin_dashboard.php">📊 Dashboard</a>
    <a href="manage_users.php">👥 Manage Passenger</a>
    <a href="deactivated_passengers.php">🚫 Deactivated Passenger</a>
    <a href="manage_driver.php">👥 Manage Drivers</a>
    <a href="deactivated_drivers.php">🚫 Deactivated Drivers</a>
    <a href="manage_cars.php">🚗 Manage Cars</a>
    <a href="manage_payments.php">💳 User Payments</a>
    <a href="logout.php">🚪 Logout</a>
</div>


<script>
    const toggleBtn = document.getElementById("toggleBtn");
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("mainContent");

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("active");
        mainContent.classList.toggle("shifted");
    });

    // Close sidebar when clicking outside of it
    document.addEventListener("click", function(event) {
        const isClickInside = sidebar.contains(event.target) || toggleBtn.contains(event.target);
        if (!isClickInside) {
            sidebar.classList.remove("active");
            mainContent.classList.remove("shifted");
        }
    });
</script>

</body>
</html>
