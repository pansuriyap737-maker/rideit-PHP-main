<?php
session_start();
include('driver_index.php');
include('../config.php'); // Assuming this has your database connection
?>
<?php
$driverId = isset($_SESSION['driver_id']) ? (int)$_SESSION['driver_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// First, delete cars with past dates automatically
$deletePastCars = "DELETE FROM cars WHERE user_id = $driverId AND date_time < NOW()";
mysqli_query($conn, $deletePastCars);

// Get all cars for this driver
$allCarsQuery = "SELECT c.* FROM cars c WHERE c.user_id = $driverId ORDER BY c.date_time DESC";
$allCarsResult = mysqli_query($conn, $allCarsQuery);

$cars = [];
$totalCars = 0;

if ($allCarsResult) {
    while ($car = mysqli_fetch_assoc($allCarsResult)) {
        // Double-check if car date is in the past (in case the DELETE didn't catch it)
        if ($car['date_time'] && strtotime($car['date_time']) < time()) {
            // Delete past cars immediately
            $deleteCar = "DELETE FROM cars WHERE car_id = " . (int)$car['car_id'];
            mysqli_query($conn, $deleteCar);
            continue; // Skip this car
        }
        
        // Apply search filter if provided
        if ($search !== '') {
            $searchLower = strtolower($search);
            $carName = strtolower($car['car_name']);
            $numberPlate = strtolower($car['number_plate']);
            $pickupLocation = strtolower($car['pickup_location']);
            $dropLocation = strtolower($car['drop_location']);
            
            if (strpos($carName, $searchLower) === false && 
                strpos($numberPlate, $searchLower) === false && 
                strpos($pickupLocation, $searchLower) === false && 
                strpos($dropLocation, $searchLower) === false) {
                continue; // Skip this car if it doesn't match search
            }
        }
        
        // Check if car is fully booked
        $bookingsQuery = "
            SELECT COUNT(*) as booked_count 
            FROM payments p 
            WHERE p.car_id = " . (int)$car['car_id'] . " 
            AND UPPER(p.payment_status) = 'SUCCESS'
        ";
        $bookingsResult = mysqli_query($conn, $bookingsQuery);
        $bookedSeats = 0;
        if ($bookingsResult && $bookingRow = mysqli_fetch_assoc($bookingsResult)) {
            $bookedSeats = (int)$bookingRow['booked_count'];
        }
        
        // Only include cars with available seats (completely hide fully booked cars)
        if ($car['seating'] > $bookedSeats) {
            $car['booked_seats'] = $bookedSeats;
            $cars[] = $car;
            $totalCars++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Ride Details</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
        }

        .ride-list-container {
            width: 1400px;
            margin: 40px auto 40px;
            height: 100vh;
        }

        #ride-list-heading {
            font-size: 30px;
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* Important to avoid double borders */
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #c5c4c4ff;
            /* Add borders to all sides */
        }


        th {
            font-size: 16px;
            background-color: #6a0fe0;
            color: white;
        }

        td {
            font-size: 15px;
        }

        tr:hover {
            background-color: #e9e7e7ff;
        }

        td img.car-image {
            width: 80px;
            border-radius: 5px;
            object-fit: cover;
        }

        .no-cars {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 16px;
        }

        .edit-btn-rides {
            padding: 5px;
            font-size: 15px;
            border: 2px solid #6a0fe0;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(58, 58, 58, 0.1);
            color: #6a0fe0;
            transition: 0.3s;
            width: 60px;
            margin-right: 10px;
        }

        .edit-btn-rides:hover {
            transform: scale(105%);
        }

        .delete-btn-rides {
            padding: 5px;
            font-size: 15px;
            border: 2px solid #ff0000;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(58, 58, 58, 0.1);
            color: #ff0000;
            transition: 0.3s;
            width: 60px;
        }

        .delete-btn-rides:hover {
            transform: scale(105%);
        }

        .ride-search {
            width: 250px;
            height: 50px;
            border-radius: 10px;
            padding: 10px;
            font-size: 16px;
            border: 0.5px solid gray;
        }

        .ride-search-btn {
            margin-bottom: 20px;
            margin-left: 15px;
            width: 150px;
            height: 40px;
            border-radius: 10px;
            background-color: green;
            color: white;
            border: none;
            transition: 0.3s;
        }

        .ride-search-btn:hover {
            transform: scale(105%);
            background-color: #6a0fe0;
        }

        .custom-footer {
            background-color: #6a0fe0;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <div class="ride-list-container">
        <h3 id="ride-list-heading">Available Cars (Total: <?php echo $totalCars; ?>)</h3>
        
        <form method="get" style="margin-bottom: 15px;">
            <input type="search" placeholder="Search by Location, Number Plate and Car Name" class="ride-search"
                id="search-input" name="search" value="<?php echo htmlspecialchars($search); ?>" />
            <button class="ride-search-btn" type="submit">Search</button>
        </form>

        <table id="trips-table"> <!-- ID for JavaScript filtering -->
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Car Name</th>
                    <th>Number Plate</th>
                    <th>Seating Capacity</th>

                    <th>Pickup</th>
                    <th>Drop</th>
                    <th>Booking DateTime</th>
                    <th>Amount (₹)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cars)): ?>
                    <?php foreach ($cars as $car): ?>
                        <tr>
                            <td>
                                <?php if (!empty($car['car_image'])): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($car['car_image']); ?>" class="car-image" alt="Car">
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($car['car_name']); ?></td>
                            <td><?php echo htmlspecialchars($car['number_plate']); ?></td>
                            <td><?php echo htmlspecialchars($car['seating']); ?></td>

                            <td><?php echo htmlspecialchars($car['pickup_location']); ?></td>
                            <td><?php echo htmlspecialchars($car['drop_location']); ?></td>
                            <td><?php echo $car['date_time'] ? date('d/m/Y H:i', strtotime($car['date_time'])) : ''; ?></td>
                            <td>₹<?php echo number_format($car['amount'], 2); ?></td>
                            <td>
                                <a class="edit-btn-rides" href="edit_trip.php?id=<?php echo (int)$car['car_id']; ?>">Edit</a>
                                <a class="delete-btn-rides" href="delete_trip.php?id=<?php echo (int)$car['car_id']; ?>" onclick="return confirm('Delete this trip?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="no-cars">No available trips found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script></script>
</body>
<div class="custom-footer">
        <?php include('../includes/footer.php'); ?>
    </div>
</html>