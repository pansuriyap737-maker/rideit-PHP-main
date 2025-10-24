<?php
session_start();
include('../config.php');

// Ensure the car_id is passed and it's valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $car_id = $_GET['id'];
    
    // Start a transaction to make sure both delete operations succeed or fail together
    mysqli_begin_transaction($conn);

    try {
        // First, delete dependent rows to satisfy FKs
        mysqli_query($conn, "DELETE FROM bookings WHERE car_id = $car_id");
        mysqli_query($conn, "DELETE FROM payments WHERE car_id = $car_id");

        // Then, delete the car
        mysqli_query($conn, "DELETE FROM cars WHERE car_id = $car_id");

        // Commit the transaction
        mysqli_commit($conn);
        echo "<script>alert('Trip deleted successfully!'); window.location.href = 'add_trip.php';</script>";
    } catch (Exception $e) {
        // If any error occurs, rollback the transaction
        mysqli_rollback($conn);
        echo "<script>alert('Error deleting trip.'); window.location.href = 'add_trip.php';</script>";
    }
} else {
    echo "<script>alert('Invalid car ID.'); window.location.href = 'add_trip.php';</script>";
}
?>
