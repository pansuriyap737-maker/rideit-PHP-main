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

if ($newStatus === 'completed') {
	// Optionally, more actions (e.g., tally totals) can be added here
}

// Redirect back appropriately
if ($newStatus === 'active') {
	header('Location: driver_bookings.php');
} else {
	header('Location: pending_rides.php');
}
exit;
?>


