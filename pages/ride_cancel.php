<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: user_payment.php?view=active'); exit; }

$paymentId = isset($_POST['payment_id']) ? (int)$_POST['payment_id'] : 0;
if ($paymentId <= 0) { header('Location: user_payment.php?view=active'); exit; }

// Map to users.id used by payments
$userIdForFk = 0;
$chkUsers = mysqli_query($conn, "SELECT id FROM users WHERE id = $userId");
if ($chkUsers && mysqli_num_rows($chkUsers) === 1) { $userIdForFk = (int)mysqli_fetch_assoc($chkUsers)['id']; }
else {
    $ps = mysqli_query($conn, "SELECT email FROM pessanger WHERE id = $userId");
    if ($ps && mysqli_num_rows($ps) === 1) {
        $emailEsc = mysqli_real_escape_string($conn, (string)mysqli_fetch_assoc($ps)['email']);
        $u2 = mysqli_query($conn, "SELECT id FROM users WHERE email = '$emailEsc'");
        if ($u2 && mysqli_num_rows($u2) === 1) { $userIdForFk = (int)mysqli_fetch_assoc($u2)['id']; }
    }
}

if ($userIdForFk <= 0) { header('Location: user_payment.php?view=active'); exit; }

// Only allow cancel if this payment belongs to the logged user and is pending/active
$auth = mysqli_query($conn, "SELECT ride_status FROM payments WHERE payment_id = $paymentId AND user_id = $userIdForFk");
if (!$auth || mysqli_num_rows($auth) !== 1) { header('Location: user_payment.php?view=active'); exit; }
$status = mysqli_fetch_assoc($auth)['ride_status'];
if (!in_array($status, ['pending','active'], true)) { header('Location: user_payment.php?view=active'); exit; }

mysqli_query($conn, "UPDATE payments SET ride_status = 'canceled' WHERE payment_id = $paymentId AND user_id = $userIdForFk");

header('Location: user_payment.php?view=active');
exit;
?>




