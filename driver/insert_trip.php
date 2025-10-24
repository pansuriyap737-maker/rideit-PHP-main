<?php
session_start();
include('../config.php');

if (!isset($_SESSION['driver_id'])) {
	header('Location: ../pages/login.php');
	exit;
}

$driverId = (int)$_SESSION['driver_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: add_trip.php');
	exit;
}

// Simple required validation
$required = ['pickup_location','drop_location','amount','date_time','car_name','number_plate','seating','username'];
foreach ($required as $field) {
	if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
		die('All fields are required.');
	}
}

$pickup = mysqli_real_escape_string($conn, trim($_POST['pickup_location']));
$drop = mysqli_real_escape_string($conn, trim($_POST['drop_location']));
$amount = (float)$_POST['amount'];
$dateTime = mysqli_real_escape_string($conn, trim($_POST['date_time']));
$carName = mysqli_real_escape_string($conn, trim($_POST['car_name']));
$numberPlate = mysqli_real_escape_string($conn, trim($_POST['number_plate']));
$seating = (int)$_POST['seating'];
// Driver name from form (read-only) or session fallback
$driverName = mysqli_real_escape_string($conn, trim($_POST['username']));
if ($driverName === '' && isset($_SESSION['driver_name'])) {
    $driverName = mysqli_real_escape_string($conn, $_SESSION['driver_name']);
}

// Ensure cars table exists
$createSql = "CREATE TABLE IF NOT EXISTS cars (
	car_id INT(11) NOT NULL AUTO_INCREMENT,
	car_image VARCHAR(255) NOT NULL,
	car_name VARCHAR(100) NOT NULL,
	seating INT(11) NOT NULL,
	city VARCHAR(100) NOT NULL DEFAULT '',
	amount DECIMAL(10,2) NOT NULL,
	number_plate VARCHAR(50) NOT NULL,
	pickup_location VARCHAR(100) DEFAULT NULL,
	drop_location VARCHAR(100) DEFAULT NULL,
	driver_name VARCHAR(100) DEFAULT NULL,
	user_id INT(11) DEFAULT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	updated_at DATETIME DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	date_time DATETIME DEFAULT NULL,
	PRIMARY KEY (car_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
mysqli_query($conn, $createSql);

// Ensure driver_name column exists (older tables won't have it)
$colCheck = mysqli_query($conn, "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cars' AND COLUMN_NAME = 'driver_name'");
if ($colCheck && mysqli_num_rows($colCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE cars ADD COLUMN driver_name VARCHAR(100) DEFAULT NULL AFTER drop_location");
}

// Handle image upload (basic)
$imagePath = '';
if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === UPLOAD_ERR_OK) {
	$uploadDir = __DIR__ . '/../uploads/';
	if (!is_dir($uploadDir)) {
		mkdir($uploadDir, 0777, true);
	}
	$base = basename($_FILES['car_image']['name']);
	$target = $uploadDir . time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $base);
	if (move_uploaded_file($_FILES['car_image']['tmp_name'], $target)) {
		$imagePath = basename($target);
	}
}

if ($imagePath === '') {
	die('Image upload failed.');
}

$sql = "INSERT INTO cars (car_image, car_name, seating, city, amount, number_plate, pickup_location, drop_location, driver_name, user_id, date_time)
		VALUES ('$imagePath', '$carName', $seating, '', $amount, '$numberPlate', '$pickup', '$drop', '$driverName', $driverId, '$dateTime')";
$ok = mysqli_query($conn, $sql);

if ($ok) {
	header('Location: add_trip.php');
} else {
	echo 'Failed to add trip.';
}


