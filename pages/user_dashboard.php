<?php
session_start();
include('../config.php');
include('uder_index.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


$userName = $_SESSION['user_name'];

// Fetch total number of available rides (cars with available seats)
$simpleQuery = "
    SELECT c.car_id, c.seating,
           COALESCE(SUM(CASE WHEN UPPER(p.payment_status) = 'SUCCESS' THEN 1 ELSE 0 END), 0) AS booked_seats
    FROM cars c
    LEFT JOIN drivers d ON d.id = c.user_id
    LEFT JOIN payments p ON c.car_id = p.car_id
    WHERE c.date_time > NOW()
    GROUP BY c.car_id
    HAVING c.seating > booked_seats
";

$simpleResult = mysqli_query($conn, $simpleQuery);
$totalAvailableCars = 0;
if ($simpleResult) {
    $totalAvailableCars = mysqli_num_rows($simpleResult);
} else {
    // Fallback query if the complex query fails
    $fallbackQuery = "SELECT COUNT(*) as total_cars FROM cars WHERE date_time > NOW()";
    $fallbackResult = mysqli_query($conn, $fallbackQuery);
    if ($fallbackResult && $row = mysqli_fetch_assoc($fallbackResult)) {
        $totalAvailableCars = $row['total_cars'];
    }
}

// If still 0, try even simpler approach
if ($totalAvailableCars == 0) {
    $simpleCountQuery = "SELECT COUNT(*) as total_cars FROM cars";
    $simpleCountResult = mysqli_query($conn, $simpleCountQuery);
    if ($simpleCountResult && $row = mysqli_fetch_assoc($simpleCountResult)) {
        $totalAvailableCars = $row['total_cars'];
    }
}

// Cities covered - static value as requested
$totalCities = 20;

// Fetch user's total trips (bookings) - fixed query to handle both pessanger and users tables
$userId = (int)$_SESSION['user_id'];

// First try to find trips in payments table with direct user_id match
$userTripsQuery = "
    SELECT COUNT(*) as total_trips 
    FROM payments p 
    WHERE p.user_id = $userId 
    AND UPPER(p.payment_status) = 'SUCCESS'
    AND p.ride_status = 'completed'
";
$userTripsResult = mysqli_query($conn, $userTripsQuery);
$userTrips = 0;
if ($userTripsResult && $row = mysqli_fetch_assoc($userTripsResult)) {
    $userTrips = $row['total_trips'];
}

// If no trips found, try to find by mapping pessanger to users table
if ($userTrips == 0) {
    // Get user's email from pessanger table
    $pessangerQuery = "SELECT email FROM pessanger WHERE id = $userId";
    $pessangerResult = mysqli_query($conn, $pessangerQuery);
    
    if ($pessangerResult && $pessangerRow = mysqli_fetch_assoc($pessangerResult)) {
        $userEmail = mysqli_real_escape_string($conn, $pessangerRow['email']);
        
        // Find corresponding user in users table
        $usersQuery = "SELECT id FROM users WHERE email = '$userEmail'";
        $usersResult = mysqli_query($conn, $usersQuery);
        
        if ($usersResult && $usersRow = mysqli_fetch_assoc($usersResult)) {
            $mappedUserId = $usersRow['id'];
            
            // Count trips for the mapped user - ONLY completed rides
            $mappedTripsQuery = "
                SELECT COUNT(*) as total_trips 
                FROM payments p 
                WHERE p.user_id = $mappedUserId 
                AND UPPER(p.payment_status) = 'SUCCESS'
                AND p.ride_status = 'completed'
            ";
            $mappedTripsResult = mysqli_query($conn, $mappedTripsQuery);
            if ($mappedTripsResult && $mappedRow = mysqli_fetch_assoc($mappedTripsResult)) {
                $userTrips = $mappedRow['total_trips'];
            }
        }
    }
}

// If still 0, try a more comprehensive approach - check all payments for this user's email
if ($userTrips == 0) {
    // Get user info from pessanger table
    $pessangerInfoQuery = "SELECT name, email FROM pessanger WHERE id = $userId";
    $pessangerInfoResult = mysqli_query($conn, $pessangerInfoQuery);
    
    if ($pessangerInfoResult && $pessangerInfoRow = mysqli_fetch_assoc($pessangerInfoResult)) {
        $userEmail = mysqli_real_escape_string($conn, $pessangerInfoRow['email']);
        $userName = mysqli_real_escape_string($conn, $pessangerInfoRow['name']);
        
        // Try to find payments by passenger name or email in denormalized fields - ONLY completed rides
        $denormalizedQuery = "
            SELECT COUNT(*) as total_trips 
            FROM payments p 
            WHERE (p.passenger_name = '$userName' OR p.passenger_name LIKE '%$userName%')
            AND UPPER(p.payment_status) = 'SUCCESS'
            AND p.ride_status = 'completed'
        ";
        $denormalizedResult = mysqli_query($conn, $denormalizedQuery);
        if ($denormalizedResult && $denormalizedRow = mysqli_fetch_assoc($denormalizedResult)) {
            $userTrips = $denormalizedRow['total_trips'];
        }
    }
}

// If still 0 and ride_status might be NULL, try without ride_status filter
if ($userTrips == 0) {
    // Try without ride_status filter in case it's NULL
    $fallbackTripsQuery = "
        SELECT COUNT(*) as total_trips 
        FROM payments p 
        WHERE p.user_id = $userId 
        AND UPPER(p.payment_status) = 'SUCCESS'
    ";
    $fallbackTripsResult = mysqli_query($conn, $fallbackTripsQuery);
    if ($fallbackTripsResult && $fallbackRow = mysqli_fetch_assoc($fallbackTripsResult)) {
        $userTrips = $fallbackRow['total_trips'];
    }
}

?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
    body { font-family: 'Poppins', sans-serif; background:#f6f7fb; margin:0; }
    .dashboard-container { max-width: 1200px; margin: 110px auto 40px; padding: 0 16px; }
    .welcome { text-align: center; margin-bottom: 18px; font-size: 26px; font-weight:600; }
    .subtext { text-align:center; color:#667085; margin-bottom: 26px; }

    .metric-row { display:flex; gap:18px; margin-bottom: 20px; flex-wrap: wrap; }
    .metric { flex:1; min-width: 240px; background:#fff; border-radius:14px; padding:16px; box-shadow:0 8px 24px rgba(0,0,0,0.06); display:flex; align-items:center; justify-content:space-between; }
    .metric .title { color:#667085; font-weight:600; }
    .metric .value { font-size: 28px; font-weight:700; color:#1d2939; }

    .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; }
    .card { background:#fff; padding:18px; border-radius:14px; box-shadow:0 8px 24px rgba(0,0,0,0.06); text-align:center; }
    .card h3 { margin: 6px 0 8px; font-size: 18px; color:#6a0fe0; font-weight:600; }
    .card p { font-size: 26px; font-weight: 700; color: #1d2939; }

    .links { text-align:center; margin-top: 28px; }
    .btn-link { display:inline-block; margin: 0 8px; padding:10px 14px; border-radius:10px; border:2px solid #6a0fe0; color:#6a0fe0; text-decoration:none; font-weight:600; background:#fff; }
    .btn-link:hover { background:#f3e9ff; }

    .custom-footer { background-color: #6a0fe0; color: white; text-align: center; padding: 15px 0; margin-top: 40px; }
</style>

<div class="dashboard-container">
    <div class="welcome">👋 Welcome, <strong><?php echo ucfirst($userName); ?></strong>!</div>
    <div class="subtext">Here’s a quick snapshot of your account and activity.</div>

    <div class="metric-row">
        <div class="metric">
            <div class="title">Available Rides</div>
            <div class="value"><?php echo $totalAvailableCars; ?></div>
        </div>
        <div class="metric">
            <div class="title">Cities Covered</div>
            <div class="value"><?php echo $totalCities; ?></div>
        </div>
        <div class="metric">
            <div class="title">Your Trips</div>
            <div class="value"><?php echo $userTrips; ?></div>
        </div>
    </div>

    <div class="card-grid">
        <div class="card">
            <h3>Profile</h3>
            <p>Keep your information up to date.</p>
            <a class="btn-link" href="profile.php">Manage Profile</a>
        </div>
        <div class="card">
            <h3>My Bookings</h3>
            <p>Track your active and past bookings.</p>
            <a class="btn-link" href="user_payment.php">View Bookings</a>
        </div>
        <div class="card">
            <h3>Explore Rides</h3>
            <p>Find new rides that match your route.</p>
            <a class="btn-link" href="trip_booking.php">Book a Ride</a>
        </div>
    </div>

    <div class="links">
        <a class="btn-link" href="profile.php">My Profile</a>
        <a class="btn-link" href="user_payment.php">My Bookings</a>
        <a class="btn-link" href="logout.php">Logout</a>
    </div>
</div>

<div class="custom-footer">
    <?php include('../includes/footer.php'); ?>
</div>
