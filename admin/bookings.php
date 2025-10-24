<?php
session_start();
include('admin_header.php');
include('../config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get today's date in proper format
$today = date("Y-m-d");

// Search logic
$search = '';
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM cars 
              WHERE date_time > '$today' AND (
                    car_id LIKE '%$search%' OR 
                    pickup_location LIKE '%$search%' OR 
                    drop_location LIKE '%$search%' OR 
                    user_id LIKE '%$search%' OR 
                    seating LIKE '%$search%'
              )
              ORDER BY date_time ASC";
} else {
    $query = "SELECT * FROM cars 
              WHERE date_time > '$today' 
              ORDER BY date_time ASC";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upcoming Bookings</title>
    <style>
        .main-content {
            margin-left: 220px;
            padding: 30px;
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            min-height: 100vh;
            width: 80%;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar input[type="text"] {
            padding: 10px;
            width: 300px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .search-bar button {
            padding: 10px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .search-bar button:hover {
            background-color: #0056b3;
        }

        table {
            width: 90%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="main-content">
 <center>   <h2>📅 Upcoming Car Bookings</h2> </center>

    <!-- Search -->
    <form class="search-bar" method="GET">
        <input type="text" name="search" placeholder="Search by car ID, location, user ID..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <!-- Table -->
    <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Car ID</th>
                    <th>Pickup Location</th>
                    <th>Drop Location</th>
                    <th>User ID</th>
                    <th>Seating</th>
                    <th>Date Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['car_id']) ?></td>
                        <td><?= htmlspecialchars($row['pickup_location']) ?></td>
                        <td><?= htmlspecialchars($row['drop_location']) ?></td>
                        <td><?= htmlspecialchars($row['user_id']) ?></td>
                        <td><?= htmlspecialchars($row['seating']) ?></td>
                        <td><?= htmlspecialchars($row['date_time']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">No upcoming bookings found.</div>
    <?php endif; ?>
</div>

</body>
</html>
