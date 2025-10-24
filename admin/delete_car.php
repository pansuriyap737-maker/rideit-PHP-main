<?php
session_start();
include('../config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Support both POST from forms and GET fallback
$car_id = 0;
if (isset($_POST['car_id'])) { $car_id = (int)$_POST['car_id']; }
elseif (isset($_GET['id'])) { $car_id = (int)$_GET['id']; }

if ($car_id > 0) {

    // Delete image file if exists
    $image_result = mysqli_query($conn, "SELECT car_image FROM cars WHERE car_id = $car_id");
    $image = mysqli_fetch_assoc($image_result);
    if ($image && isset($image['car_image']) && $image['car_image'] !== '' && file_exists(__DIR__ . '/../uploads/' . $image['car_image'])) {
        unlink(__DIR__ . '/../uploads/' . $image['car_image']);
    }

    // Cascade delete dependent rows first to satisfy FK constraints
    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn, "DELETE FROM bookings WHERE car_id = $car_id");
        mysqli_query($conn, "DELETE FROM payments WHERE car_id = $car_id");
        mysqli_query($conn, "DELETE FROM cars WHERE car_id = $car_id");
        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
    }
}

header("Location: manage_cars.php");
exit;
