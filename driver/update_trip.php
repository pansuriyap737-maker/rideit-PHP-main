<?php
session_start();
include('../config.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the form data
    $car_id = $_POST['car_id'];
    $car_name = $_POST['car_name'];
    $seating = $_POST['seating'];
    $city = $_POST['city'];
    $amount = $_POST['amount'];
    $number_plate = $_POST['number_plate'];
    $pickup_location = $_POST['pickup_location'];
    $drop_location = $_POST['drop_location'];
    $date_time = $_POST['date_time'];

    // Check if the number plate already exists for another car
    $query = "SELECT car_id FROM cars WHERE number_plate = '$number_plate' AND car_id != '$car_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // If the number plate already exists, show an error message
        echo "<script>alert('The number plate is already in use. Please choose a different one.'); window.history.back();</script>";
        exit;
    }

    // If number plate is unique, proceed with updating the trip
    $updateQuery = "UPDATE cars SET 
                        car_name = '$car_name', 
                        seating = '$seating', 
                        city = '$city', 
                        amount = '$amount', 
                        number_plate = '$number_plate', 
                        pickup_location = '$pickup_location', 
                        drop_location = '$drop_location', 
                        date_time = '$date_time' 
                    WHERE car_id = '$car_id'";

    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Trip updated successfully.'); window.location.href = 'add_trip.php';</script>";
    } else {
        echo "<script>alert('Failed to update trip. Please try again.'); window.history.back();</script>";
    }
}
?>
