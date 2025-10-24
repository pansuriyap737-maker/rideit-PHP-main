<?php
session_start();
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: manage_users.php');
	exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$newStatus = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';

if ($id <= 0 || ($newStatus !== 'inactive' && $newStatus !== 'active')) {
	header('Location: manage_users.php');
	exit;
}

// Helper to redirect back
function redirect_back() {
	$target = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'manage_users.php';
	header('Location: ' . $target);
	exit;
}

// Ensure the deactivated table exists
function ensure_deactivated_table_exists($conn) {
	$createSql = "CREATE TABLE IF NOT EXISTS `deactivatedpesenger` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`user_id` INT(11) NOT NULL,
		`name` VARCHAR(100) DEFAULT NULL,
		`email` VARCHAR(100) DEFAULT NULL,
		`contact` VARCHAR(15) DEFAULT NULL,
		`password` VARCHAR(255) DEFAULT NULL,
		`created_at` TIMESTAMP NULL DEFAULT NULL,
		`deactivated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(),
		PRIMARY KEY (`id`),
		KEY `user_id` (`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

	mysqli_query($conn, $createSql);
}

if ($newStatus === 'inactive') {
    // Deactivate: move from pessanger -> deactivatedpesenger
    ensure_deactivated_table_exists($conn);
    $userRes = mysqli_query($conn, "SELECT id, name, email, contact, password, created_at FROM pessanger WHERE id = $id");
	if ($userRes && mysqli_num_rows($userRes) === 1) {
		$user = mysqli_fetch_assoc($userRes);
		$name = mysqli_real_escape_string($conn, $user['name']);
		$email = mysqli_real_escape_string($conn, $user['email']);
		$contact = mysqli_real_escape_string($conn, $user['contact']);
		$password = mysqli_real_escape_string($conn, $user['password']);
		$createdAt = mysqli_real_escape_string($conn, $user['created_at']);

		// Ensure table deactivatedpesenger exists with expected columns
        $insert = mysqli_query($conn, "INSERT INTO deactivatedpesenger (user_id, name, email, contact, password, created_at, deactivated_at) VALUES ($id, '$name', '$email', '$contact', '$password', '$createdAt', NOW())");
        if ($insert) {
            mysqli_query($conn, "DELETE FROM pessanger WHERE id = $id");
		}
	}
	redirect_back();
} else {
    // Activate: move from deactivatedpesenger -> pessanger
    ensure_deactivated_table_exists($conn);
    $decRes = mysqli_query($conn, "SELECT user_id, name, email, contact, password, created_at FROM deactivatedpesenger WHERE user_id = $id");
    if ($decRes && mysqli_num_rows($decRes) === 1) {
        $row = mysqli_fetch_assoc($decRes);
        $name = mysqli_real_escape_string($conn, $row['name']);
        $email = mysqli_real_escape_string($conn, $row['email']);
        $contact = mysqli_real_escape_string($conn, $row['contact']);
        $password = mysqli_real_escape_string($conn, $row['password']);
        $createdAt = mysqli_real_escape_string($conn, $row['created_at']);

        // Upsert into pessanger to avoid duplicate email error
        $upsertSql = "INSERT INTO pessanger (name, email, contact, password, created_at)
                      VALUES ('$name', '$email', '$contact', '$password', '$createdAt')
                      ON DUPLICATE KEY UPDATE 
                        name = VALUES(name),
                        contact = VALUES(contact),
                        password = VALUES(password)";
        $ok = mysqli_query($conn, $upsertSql);
        if ($ok) {
            mysqli_query($conn, "DELETE FROM deactivatedpesenger WHERE user_id = $id");
        }
    }
    redirect_back();
}


