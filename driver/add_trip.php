<?php
session_start();
include('driver_index.php');
include('../config.php');

// Get logged-in driver info
$driverId = isset($_SESSION['driver_id']) ? (int)$_SESSION['driver_id'] : 0;
$user_name = isset($_SESSION['driver_name']) ? $_SESSION['driver_name'] : 'Driver';
if ($driverId > 0) {
	$resDriver = mysqli_query($conn, "SELECT name FROM drivers WHERE id = $driverId");
	if ($resDriver && mysqli_num_rows($resDriver) === 1) {
		$rowDriver = mysqli_fetch_assoc($resDriver);
		$user_name = $rowDriver['name'];
	}
}

// Gujarat cities for dropdown (not used anymore, but kept for reference if needed)
$gujarat_cities = [
    'Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar',
    'Gandhinagar', 'Junagadh', 'Anand', 'Nadiad', 'Bharuch', 'Mehsana',
    'Morbi', 'Gondal', 'Navsari', 'Vapi', 'Veraval', 'Botad', 'Porbandar'
];

// Seating capacity dropdown values
$seating_options = range(1, 8);

// Limit booking date-time range
$minDate = date('Y-m-d\TH:i');
$maxDate = date('Y-m-d\T23:59', strtotime('+90 day'));

$result = mysqli_query($conn, "SELECT * FROM cars WHERE user_id = $driverId ORDER BY date_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Trip</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            margin: 0;
            padding: 0;
        }

        .shareride-container {
            margin-top: 40px;
        }

        .shareride-form {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        #addtrip-form {
            width: 900px;
            height: auto;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
            font-family: 'Poppins', sans-serif;
        }

        label {
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        #sharepickup, #sharedrop, #amountshare, #dateandtime, #sharecarimg, #sharecarename, #sharecarnumberplate, #seatingCapacity {
            width: 100%;
            height: 45px;
            border: 0.5px solid gray;
            border-radius: 10px;
            margin-bottom: 15px; /* Increased margin for spacing */
            padding: 10px; /* Added padding for internal spacing */
            font-size: 16px;
        }

        .addtripsame {
            margin-top: 5px;
            width: 100%;
            height: 45px;
            border: 0.5px solid gray;
            border-radius: 10px;
            margin-bottom: 15px; /* Increased margin for spacing */
            padding: 10px; /* Added padding for internal spacing */
            font-size: 16px;
        }

        select.addtripsame {
            font-size: 16px;
            padding: 10px; /* Added padding for selects as well */
        }

        #addtrip-btn {
            padding: 5px;
            font-size: 16px;
            width: 30%;
            height: 40px;
            margin-top: 20px;
            background-color: green;
            color: white;
            border: none;
            border-radius: 5px;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        #addtrip-btn:hover {
            transform: scale(105%);
            background-color: #8000ff;
            color: white;
        }

        #ride-share-heading {
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
            font-family: 'Poppins', sans-serif;
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

<div class="shareride-container">
    <h2 id="ride-share-heading">ADD TRIP</h2>
    <div class="shareride-form">
        <form id="addtrip-form" method="POST" action="insert_trip.php" enctype="multipart/form-data">
            <label for="shareuser">Username</label>
            <input type="text" id="shareuser" name="username" class="addtripsame" value="<?= htmlspecialchars($user_name) ?>" readonly>

            <label for="sharepickup">Pick-Up</label>
            <input type="text" id="sharepickup" name="pickup_location" class="addtripsame" required> <!-- Changed to text input -->

            <label for="sharedrop">Drop</label>
            <input type="text" id="sharedrop" name="drop_location" class="addtripsame" required> <!-- Changed to text input -->

            <label for="amountshare">Amount</label>
            <input type="number" id="amountshare" name="amount" class="addtripsame" required>

            <label for="dateandtime">Booking Date & Time</label>
            <input type="datetime-local" id="dateandtime" name="date_time" class="addtripsame" min="<?= $minDate ?>" max="<?= $maxDate ?>" required>

            <label for="sharecarimg">Car Image</label>
            <input type="file" id="sharecarimg" name="car_image" class="addtripsame" required>

            <label for="sharecarename">Car Name</label>
            <input type="text" id="sharecarename" name="car_name" class="addtripsame" required>

            <label for="sharecarnumberplate">Car Number Plate</label>
            <input type="text" id="sharecarnumberplate" name="number_plate" class="addtripsame" required>

            <label for="seatingCapacity">Seating Capacity</label>
            <select id="seatingCapacity" name="seating" class="addtripsame" required>
                <option value="">Select</option>
                <?php foreach ($seating_options as $seat): ?>
                    <option value="<?= $seat ?>"><?= $seat ?></option>
                <?php endforeach; ?>
            </select>

            <button id="addtrip-btn" type="submit">Add Trip</button>
        </form>
    </div>
</div>

 <div class="custom-footer">
        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>
