<?php
session_start();
include('../config.php');

if (!isset($_SESSION['driver_id'])) { header('Location: ../pages/login.php'); exit; }
$driverId = (int)$_SESSION['driver_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: pending_rides.php'); exit; }

$paymentId = isset($_POST['payment_id']) ? (int)$_POST['payment_id'] : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($paymentId <= 0 || !in_array($action, ['accept','cancel','complete'], true)) {
	header('Location: pending_rides.php');
	exit;
}

// Authorize driver owns the ride (via cars.user_id)
$auth = mysqli_query($conn, "SELECT c.user_id FROM payments p INNER JOIN cars c ON c.car_id = p.car_id WHERE p.payment_id = $paymentId");
if (!$auth || mysqli_num_rows($auth) !== 1) { header('Location: pending_rides.php'); exit; }
$owner = (int)mysqli_fetch_assoc($auth)['user_id'];
if ($owner !== $driverId) { header('Location: pending_rides.php'); exit; }

$newStatus = $action === 'accept' ? 'active' : ($action === 'cancel' ? 'canceled' : 'completed');
mysqli_query($conn, "UPDATE payments SET ride_status = '$newStatus' WHERE payment_id = $paymentId");

if ($newStatus === 'canceled') {
	// Check if canceledtrip table exists, create if not
	$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'canceledtrip'");
	if (mysqli_num_rows($tableCheck) == 0) {
		$createTable = "CREATE TABLE IF NOT EXISTS `canceledtrip` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`payment_id` int(11) NOT NULL,
			`passenger_id` int(11) NOT NULL,
			`driver_id` int(11) NOT NULL,
			`car_id` int(11) NOT NULL,
			`passenger_name` varchar(100) NOT NULL,
			`car_name` varchar(100) NOT NULL,
			`car_number_plate` varchar(50) NOT NULL,
			`pickup` varchar(100) NOT NULL,
			`drop_location` varchar(100) NOT NULL,
			`ride_datetime` datetime NOT NULL,
			`amount` decimal(10,2) NOT NULL,
			`payment_mode` varchar(50) NOT NULL,
			`canceled_at` timestamp NOT NULL DEFAULT current_timestamp(),
			`canceled_by` enum('driver','passenger') NOT NULL,
			PRIMARY KEY (`id`),
			KEY `payment_id` (`payment_id`),
			KEY `driver_id` (`driver_id`),
			KEY `passenger_id` (`passenger_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
		mysqli_query($conn, $createTable);
	}
	
	// Get all necessary data for the canceled trip
	$tripData = mysqli_query($conn, "SELECT 
		p.payment_id, 
		p.user_id AS passenger_id, 
		c.user_id AS driver_id,
		p.car_id,
		c.car_name,
		p.passenger_name, 
		p.car_number_plate, 
		p.pickup, 
		p.drop_location, 
		COALESCE(p.ride_datetime, c.date_time) AS ride_datetime, 
		p.amount, 
		COALESCE(p.payment_mode,'ONLINE') AS payment_mode
	FROM payments p 
	INNER JOIN cars c ON c.car_id = p.car_id 
	WHERE p.payment_id = $paymentId");
	
	if ($tripData && $row = mysqli_fetch_assoc($tripData)) {
		$passengerId = (int)$row['passenger_id'];
		$driverId = (int)$row['driver_id'];
		$carId = (int)$row['car_id'];
		$carName = mysqli_real_escape_string($conn, $row['car_name']);
		$passengerName = mysqli_real_escape_string($conn, $row['passenger_name']);
		$carNumberPlate = mysqli_real_escape_string($conn, $row['car_number_plate']);
		$pickup = mysqli_real_escape_string($conn, $row['pickup']);
		$dropLocation = mysqli_real_escape_string($conn, $row['drop_location']);
		$rideDateTime = $row['ride_datetime'];
		$amount = (float)$row['amount'];
		$paymentMode = mysqli_real_escape_string($conn, $row['payment_mode']);
		
		// Insert into canceledtrip table
		$insertQuery = "INSERT INTO canceledtrip (
			payment_id, passenger_id, driver_id, car_id, passenger_name, car_name,
			car_number_plate, pickup, drop_location, ride_datetime, 
			amount, payment_mode, canceled_by
		) VALUES (
			$paymentId, $passengerId, $driverId, $carId, '$passengerName', '$carName',
			'$carNumberPlate', '$pickup', '$dropLocation', '$rideDateTime', 
			$amount, '$paymentMode', 'driver'
		)";
		mysqli_query($conn, $insertQuery);
	}
}

// Redirect back appropriately
if ($newStatus === 'active') {
	header('Location: driver_bookings.php');
} else {
	header('Location: pending_rides.php');
}
exit;
?>


