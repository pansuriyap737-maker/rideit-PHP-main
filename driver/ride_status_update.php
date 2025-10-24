<?php
session_start();
include('../config.php');

if (!isset($_SESSION['driver_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: driver_bookings.php');
    exit();
}

$payment_id = (int)($_POST['payment_id'] ?? 0);
$action = $_POST['action'] ?? '';
$driver_id = (int)$_SESSION['driver_id'];

if ($payment_id <= 0 || $action !== 'complete') {
    header('Location: driver_bookings.php?error=invalid');
    exit();
}

// Create completedtrip table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS completedtrip (
    id INT(11) NOT NULL AUTO_INCREMENT,
    payment_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    driver_id INT(11) DEFAULT NULL,
    passenger_name VARCHAR(100) DEFAULT NULL,
    driver_name VARCHAR(100) DEFAULT NULL,
    car_number_plate VARCHAR(20) DEFAULT NULL,
    pickup VARCHAR(255) DEFAULT NULL,
    drop_location VARCHAR(255) DEFAULT NULL,
    amount DECIMAL(10,2) DEFAULT NULL,
    payment_mode VARCHAR(50) DEFAULT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Get payment details and verify ownership
$paymentQuery = "SELECT p.*, c.user_id as driver_id FROM payments p 
                INNER JOIN cars c ON p.car_id = c.car_id 
                WHERE p.payment_id = $payment_id AND c.user_id = $driver_id AND p.ride_status IN ('pending','active')";
$paymentRes = mysqli_query($conn, $paymentQuery);

if (!$paymentRes || mysqli_num_rows($paymentRes) === 0) {
    header('Location: driver_bookings.php?error=notfound');
    exit();
}

$payment = mysqli_fetch_assoc($paymentRes);

// Insert into completedtrip table
$insertSql = "INSERT INTO completedtrip (payment_id, user_id, driver_id, passenger_name, driver_name, car_number_plate, pickup, drop_location, amount, payment_mode) 
              VALUES ($payment_id, {$payment['user_id']}, $driver_id, ?, ?, ?, ?, ?, {$payment['amount']}, ?)";
$stmt = mysqli_prepare($conn, $insertSql);
mysqli_stmt_bind_param($stmt, 'ssssss', 
    $payment['passenger_name'], 
    $payment['driver_name'], 
    $payment['car_number_plate'], 
    $payment['pickup'], 
    $payment['drop_location'], 
    $payment['payment_mode']
);
mysqli_stmt_execute($stmt);

// Update payment status to completed
mysqli_query($conn, "UPDATE payments SET ride_status = 'completed' WHERE payment_id = $payment_id");

header('Location: driver_bookings.php?view=completed&success=1');
exit();
?>