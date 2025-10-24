<?php
session_start();
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $user_id = $_SESSION['user_id'] ?? 0;
    $car_name = $_POST['car_name'];
    $seating = $_POST['seating'];
    $city = $_POST['city'];
    $amount = $_POST['amount'];
    $number_plate = $_POST['number_plate'];
    $pickup_location = $_POST['pickup_location'];
    $drop_location = $_POST['drop_location'];
    $date_time = $_POST['date_time'];

    // Handle image upload
    $car_image = $_FILES['car_image']['name'];
    $image_tmp = $_FILES['car_image']['tmp_name'];
    $image_folder = "../uploads/";

    // Create upload folder if not exists
    if (!file_exists($image_folder)) {
        mkdir($image_folder, 0777, true);
    }

    // Rename image to avoid duplicates
    $image_ext = pathinfo($car_image, PATHINFO_EXTENSION);
    $new_image_name = 'car_' . time() . '.' . $image_ext;
    $image_path = $image_folder . $new_image_name;

    if (move_uploaded_file($image_tmp, $image_path)) {
        // Insert into database
        $query = "INSERT INTO cars 
            (user_id, car_image, car_name, seating, city, amount, number_plate, pickup_location, drop_location, date_time) 
            VALUES 
            ('$user_id', '$new_image_name', '$car_name', '$seating', '$city', '$amount', '$number_plate', '$pickup_location', '$drop_location', '$date_time')";

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Trip added successfully!'); window.location.href = 'add_trip.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location.href = 'add_trip.php';</script>";
        }
    } else {
        echo "<script>alert('Failed to upload car image.'); window.location.href = 'add_trip.php';</script>";
    }
} else {
    header("Location: add_trip.php");
    exit;
}
?>
