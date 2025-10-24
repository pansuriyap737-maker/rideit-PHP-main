<?php
session_start();
include('../config.php');

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];  // The unique payment_id from the form

    // Validate payment_id
    if (empty($payment_id)) {
        echo "Invalid request!";
        exit();
    }

    // Delete the payment record based on the unique payment_id
    $query = "DELETE FROM payments WHERE payment_id = $payment_id AND user_id = {$_SESSION['user_id']}";

    if (mysqli_query($conn, $query)) {
        // Redirect after successful deletion
        header('Location: bookings.php'); // Redirect to the bookings page after successful delete
    } else {
        echo "Error deleting booking: " . mysqli_error($conn);
    }
}
?>
