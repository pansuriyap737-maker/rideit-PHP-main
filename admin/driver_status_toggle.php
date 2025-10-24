<?php
session_start();
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: manage_driver.php');
	exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$newStatus = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';

if ($id <= 0 || ($newStatus !== 'inactive' && $newStatus !== 'active')) {
	header('Location: manage_driver.php');
	exit;
}

function redirect_back() {
	$target = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'manage_driver.php';
	header('Location: ' . $target);
	exit;
}

function ensure_deactivated_drivers_exists($conn) {
	$createSql = "CREATE TABLE IF NOT EXISTS `deactivateddrivers` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`driver_id` INT(11) NOT NULL,
		`name` VARCHAR(100) DEFAULT NULL,
		`email` VARCHAR(100) DEFAULT NULL,
		`contact` VARCHAR(15) DEFAULT NULL,
		`password` VARCHAR(255) DEFAULT NULL,
		`license_no` VARCHAR(100) DEFAULT NULL,
		`created_at` TIMESTAMP NULL DEFAULT NULL,
		`deactivated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(),
		PRIMARY KEY (`id`),
		KEY `driver_id` (`driver_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

	mysqli_query($conn, $createSql);
}

if ($newStatus === 'inactive') {
	// Deactivate: move from drivers -> deactivateddrivers
	ensure_deactivated_drivers_exists($conn);
	$drvRes = mysqli_query($conn, "SELECT id, name, email, contact, password, license_no, created_at FROM drivers WHERE id = $id");
	if ($drvRes && mysqli_num_rows($drvRes) === 1) {
		$drv = mysqli_fetch_assoc($drvRes);
		$name = mysqli_real_escape_string($conn, $drv['name']);
		$email = mysqli_real_escape_string($conn, $drv['email']);
		$contact = mysqli_real_escape_string($conn, $drv['contact']);
		$password = mysqli_real_escape_string($conn, $drv['password']);
		$license = mysqli_real_escape_string($conn, $drv['license_no']);
		$createdAt = mysqli_real_escape_string($conn, $drv['created_at']);
		$ins = mysqli_query($conn, "INSERT INTO deactivateddrivers (driver_id, name, email, contact, password, license_no, created_at, deactivated_at) VALUES ($id, '$name', '$email', '$contact', '$password', '$license', '$createdAt', NOW())");
		if ($ins) {
			mysqli_query($conn, "DELETE FROM drivers WHERE id = $id");
		}
	}
	redirect_back();
} else {
	// Activate: move from deactivateddrivers -> drivers (upsert to avoid duplicates)
	ensure_deactivated_drivers_exists($conn);
	$decRes = mysqli_query($conn, "SELECT driver_id, name, email, contact, password, license_no, created_at FROM deactivateddrivers WHERE driver_id = $id");
	if ($decRes && mysqli_num_rows($decRes) === 1) {
		$row = mysqli_fetch_assoc($decRes);
		$name = mysqli_real_escape_string($conn, $row['name']);
		$email = mysqli_real_escape_string($conn, $row['email']);
		$contact = mysqli_real_escape_string($conn, $row['contact']);
		$password = mysqli_real_escape_string($conn, $row['password']);
		$license = mysqli_real_escape_string($conn, $row['license_no']);
		$createdAt = mysqli_real_escape_string($conn, $row['created_at']);

		$upsert = "INSERT INTO drivers (name, email, contact, password, license_no, created_at)
				  VALUES ('$name', '$email', '$contact', '$password', '$license', '$createdAt')
				  ON DUPLICATE KEY UPDATE 
					name = VALUES(name),
					contact = VALUES(contact),
					password = VALUES(password),
					license_no = VALUES(license_no)";
		$ok = mysqli_query($conn, $upsert);
		if ($ok) {
			mysqli_query($conn, "DELETE FROM deactivateddrivers WHERE driver_id = $id");
		}
	}
	redirect_back();
}


