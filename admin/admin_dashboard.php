<?php
session_start();
include('admin_header.php');
include('../config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch totals from the database
$passengers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pessanger"))['total'];
$drivers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM drivers"))['total'];
$total_users = $passengers + $drivers;
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS total FROM completedtrip"))['total'] ?? 0;
$total_cars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM cars"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    <!-- Replace this section in your existing code -->
<style>
    .main-content {
        margin-left: 220px;
        padding: 30px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        min-height: 100vh;
    }

    .welcome-message {
        font-size: 26px;
        font-weight: bold;
        margin-bottom: 30px;
        color: #333;
        text-align: center;
    }

    .stats-container {
        display: flex;
        justify-content: center;
        margin-bottom: 40px;
        gap: 30px;
        flex-wrap: wrap;
    }

    .stat-box {
        width: 220px;
        background-color: #007bff;
        color: white;
        padding: 25px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }

    .stat-box:hover {
        transform: translateY(-5px);
    }

    .stat-box h3 {
        margin-bottom: 10px;
        font-size: 20px;
        font-weight: 600;
    }

    .stat-box p {
        font-size: 24px;
        font-weight: bold;
    }

    .chart-container {
        width: 100%;
        max-width: 700px;
        margin: 0 auto 40px;
        padding: 20px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .admin-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .admin-buttons a {
        padding: 12px 24px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        font-weight: bold;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }

    .admin-buttons a:hover {
        background-color: #0056b3;
    }
</style>

    </style>
</head>
<body>

<div class="main-content">
    <div class="welcome-message">
        👋 Welcome back, Admin!
    </div>

    <!-- Stats Boxes -->
    <div class="stats-container">
        <div class="stat-box">
            <h3>Total Users</h3>
            <p><?= $total_users ?></p>
        </div>
        <div class="stat-box">
            <h3>Total Payments</h3>
            <p><?= $total_payments ?></p>
        </div>
        <div class="stat-box">
            <h3>Total Cars</h3>
            <p><?= $total_cars ?></p>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-container">
        <canvas id="growthChart"></canvas>
    </div>

    <!-- Buttons -->
    <div class="admin-buttons">
        <a href="admin_profile.php">👤 Profile</a>
        <a href="manage_users.php">👥 Manage Users</a>
        <a href="user_contact.php">📨 Inquiry</a>
        <a href="user_payment.php">💳 Verify Payment</a>
    </div>
</div>

<script>
    const ctx = document.getElementById('growthChart').getContext('2d');
    const growthChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            datasets: [{
                label: 'Bookings Growth',
                data: [12, 19, 25, 30, 45], // Example data
                backgroundColor: 'rgba(0, 123, 255, 0.7)',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
