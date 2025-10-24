<?php
$host = "localhost";        // or 127.0.0.1
$db_user = "root";          // change if you have a different DB user
$db_pass = "";              // enter password if set
$db_name = "rideit_db";     // your database name

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// You can enable error reporting during development
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>
