<?php
session_start();
include('../config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $car_name = mysqli_real_escape_string($conn, $_POST['car_name']);
    $seating = (int)$_POST['seating'];
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $pickup_location = mysqli_real_escape_string($conn, $_POST['pickup_location']);
    $drop_location = mysqli_real_escape_string($conn, $_POST['drop_location']);
    $user_id = (int)$_POST['user_id'];
    $amount = (int)$_POST['amount'];
    $number_plate = mysqli_real_escape_string($conn, $_POST['number_plate']);
    $date_time = mysqli_real_escape_string($conn, $_POST['date_time']);

    // ✅ Validate date_time (only allow today or tomorrow)
  

    // ✅ Upload image
    if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === 0) {
        $image_name = time() . '_' . basename($_FILES['car_image']['name']);
        $target_dir = "../uploads/";
        $target_file = $target_dir . $image_name;

        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!in_array($_FILES['car_image']['type'], $allowed_types)) {
            $_SESSION['error'] = "Only JPG, PNG, JPEG, and WEBP images are allowed.";
            header("Location: manage_cars.php");
            exit;
        }

        if (!move_uploaded_file($_FILES['car_image']['tmp_name'], $target_file)) {
            $_SESSION['error'] = "Failed to upload car image.";
            header("Location: manage_cars.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Please select a valid image file.";
        header("Location: manage_cars.php");
        exit;
    }

    // ✅ Check for duplicate number plate
    $check = mysqli_query($conn, "SELECT * FROM cars WHERE number_plate='$number_plate'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Car with this number plate already exists.";
        header("Location: manage_cars.php");
        exit;
    }

    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;

    // ✅ Insert into DB
    $query = "INSERT INTO cars (
                car_image, car_name, seating, city, 
                pickup_location, drop_location, user_id, 
                amount, number_plate, created_at, updated_at, date_time
              ) VALUES (
                '$image_name', '$car_name', '$seating', '$city', 
                '$pickup_location', '$drop_location', '$user_id', 
                '$amount', '$number_plate', '$created_at', '$updated_at', '$date_time'
              )";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Car added successfully.";
    } else {
        $_SESSION['error'] = "Something went wrong. Please try again.";
    }

    header("Location: manage_cars.php");
    exit;

} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: manage_cars.php");
    exit;
}
