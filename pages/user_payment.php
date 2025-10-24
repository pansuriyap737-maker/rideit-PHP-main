<?php
session_start();
include('uder_index.php'); // Include header or navigation
include('../config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$session_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$view = isset($_GET['view']) ? strtolower(trim($_GET['view'])) : 'active';
if (!in_array($view, ['active','completed','canceled'], true)) { $view = 'active'; }

// Ensure users table exists (in case only `pessanger` was used earlier)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    contact VARCHAR(15) DEFAULT NULL,
    password VARCHAR(255) DEFAULT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Map current passenger to a users.id used in payments FK
$userIdForFk = 0;
$chkUsers = mysqli_query($conn, "SELECT id FROM users WHERE id = $user_id");
if ($chkUsers && mysqli_num_rows($chkUsers) === 1) {
    $userIdForFk = (int)mysqli_fetch_assoc($chkUsers)['id'];
} else {
    $ps = mysqli_query($conn, "SELECT email FROM pessanger WHERE id = $user_id");
    if ($ps && mysqli_num_rows($ps) === 1) {
        $emailRow = mysqli_fetch_assoc($ps);
        $emailEsc = mysqli_real_escape_string($conn, (string)$emailRow['email']);
        $u2 = mysqli_query($conn, "SELECT id FROM users WHERE email = '$emailEsc'");
        if ($u2 && mysqli_num_rows($u2) === 1) {
            $userIdForFk = (int)mysqli_fetch_assoc($u2)['id'];
        }
    }
}

// Fixed SQL query (removed phone column)
$activeQ = "
    SELECT p.payment_id, COALESCE(p.driver_name, d.name) AS driver_name,
           COALESCE(p.car_number_plate, c.number_plate) AS car_number_plate,
           COALESCE(p.pickup, c.pickup_location) AS pickup,
           COALESCE(p.drop_location, c.drop_location) AS drop_location,
           p.amount AS amount, COALESCE(p.payment_mode, 'ONLINE') AS payment_mode,
           p.payment_status, p.payment_date, p.ride_status
    FROM payments p
    INNER JOIN cars c ON p.car_id = c.car_id
    LEFT JOIN drivers d ON d.id = c.user_id
    WHERE p.user_id = $userIdForFk AND p.payment_status='Success' AND p.ride_status IN ('pending','active')
    ORDER BY p.payment_date DESC";

// Check if completedtrip table exists before querying it
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'completedtrip'");
$completedRes = false;

if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    // Try to get completed trips from completedtrip table
    $completedQ = "
        SELECT ct.id AS payment_id, 
               (SELECT name FROM drivers WHERE id = ct.driver_id) AS driver_name,
               ct.car_number_plate,
               ct.pickup,
               ct.drop_location,
               ct.amount, 
               ct.payment_mode,
               'Success' AS payment_status, 
               ct.completed_at AS payment_date, 
               'completed' AS ride_status
        FROM completedtrip ct
        WHERE ct.passenger_id = $userIdForFk
        ORDER BY ct.completed_at DESC";

    $completedRes = mysqli_query($conn, $completedQ);
}

// Fallback to payments table if completedtrip table doesn't exist yet
if (!$completedRes) {
    $completedQ = "
        SELECT p.payment_id, COALESCE(p.driver_name, d.name) AS driver_name,
               COALESCE(p.car_number_plate, c.number_plate) AS car_number_plate,
               COALESCE(p.pickup, c.pickup_location) AS pickup,
               COALESCE(p.drop_location, c.drop_location) AS drop_location,
               p.amount AS amount, COALESCE(p.payment_mode, 'ONLINE') AS payment_mode,
               p.payment_status, p.payment_date, p.ride_status
        FROM payments p
        INNER JOIN cars c ON p.car_id = c.car_id
        LEFT JOIN drivers d ON d.id = c.user_id
        WHERE p.user_id = $userIdForFk AND p.payment_status='Success' AND p.ride_status='completed'
        ORDER BY p.payment_date DESC";
    $completedRes = mysqli_query($conn, $completedQ);
}

// Check if canceledtrip table exists before querying it
$canceledTableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'canceledtrip'");
$canceledRes = false;

if ($canceledTableCheck && mysqli_num_rows($canceledTableCheck) > 0) {
    // Try to get canceled trips from canceledtrip table
    $canceledQ = "
        SELECT ct.id AS payment_id, 
               (SELECT name FROM drivers WHERE id = ct.driver_id) AS driver_name,
               ct.car_number_plate,
               ct.pickup,
               ct.drop_location,
               ct.amount, 
               ct.payment_mode,
               'Success' AS payment_status, 
               ct.canceled_at AS payment_date, 
               'canceled' AS ride_status,
               ct.canceled_by
        FROM canceledtrip ct
        WHERE ct.passenger_id = $userIdForFk
        ORDER BY ct.canceled_at DESC";

    $canceledRes = mysqli_query($conn, $canceledQ);
}

// Fallback to payments table if canceledtrip table doesn't exist yet
if (!$canceledRes) {
    $canceledQ = "
        SELECT p.payment_id, COALESCE(p.driver_name, d.name) AS driver_name,
               COALESCE(p.car_number_plate, c.number_plate) AS car_number_plate,
               COALESCE(p.pickup, c.pickup_location) AS pickup,
               COALESCE(p.drop_location, c.drop_location) AS drop_location,
               p.amount AS amount, COALESCE(p.payment_mode, 'ONLINE') AS payment_mode,
               p.payment_status, p.payment_date, p.ride_status,
               'unknown' AS canceled_by
        FROM payments p
        INNER JOIN cars c ON p.car_id = c.car_id
        LEFT JOIN drivers d ON d.id = c.user_id
        WHERE p.user_id = $userIdForFk AND p.payment_status='Success' AND p.ride_status='canceled'
        ORDER BY p.payment_date DESC";
    $canceledRes = mysqli_query($conn, $canceledQ);
}

$activeRes = mysqli_query($conn, $activeQ);
$completedRes = mysqli_query($conn, $completedQ);

// Totals
$totalsSql = "SELECT 
  SUM(CASE WHEN p.ride_status='completed' THEN 1 ELSE 0 END) AS total_trips,
  SUM(CASE WHEN p.ride_status='completed' THEN p.amount ELSE 0 END) AS total_amount
FROM payments p WHERE p.user_id = $userIdForFk AND p.payment_status='Success'";
$totals = mysqli_fetch_assoc(mysqli_query($conn, $totalsSql));

// no single query now; we use view-specific result sets
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Car Bookings</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body { font-family: 'Poppins', sans-serif; background:#f6f7fb; margin:0; }

        .payment-container { min-height: 80vh; display:flex; align-items: center; flex-direction: column; }

        .userpanel-tabel { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        .userpanel-tabel tr:nth-child(even) td { background:#fafbfe; }

        .user-payment,
        .user-ride-data { font-size: 16px; padding: 12px 12px; border-bottom: 1px solid #eceef4; text-align: center; }
        .user-payment { background-color: #6a0fe0; color: white; border-bottom: none; }

        .wrap { width: 90%; max-width: 1200px; margin: 0 auto; }
        #payment-heading { margin: 20px 0; font-weight: 600; }

        .cancel-passenger { padding: 8px 12px; font-size: 15px; border: 2px solid #c00; border-radius: 10px; background: #fff; color: #c00; transition: 0.2s; cursor: pointer; }
        .cancel-passenger:hover { background:#fdecec; transform: translateY(-1px); }

        .toolbar { display:flex; align-items:center; width:100%; }
        .filters { display:flex; gap:8px; margin-left:auto; }
        .btn { padding:8px 12px; border-radius:10px; border:2px solid #ccc; background:#fff; cursor:pointer; font-weight:600; }
        .btn.active { border-color:#6a0fe0; color:#6a0fe0; background:#f3e9ff; }

        .metrics { display:flex; gap:20px; width:100%; margin:30px 0 20px 0; }
        .metric-card { flex:1; background:#fff; padding:12px; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.06); }
        .card { background:#fff; padding:18px; border-radius:14px; box-shadow:0 8px 24px rgba(0,0,0,0.06); width:100%; }

        .custom-footer { background-color: #6a0fe0; color: white; text-align: center; padding: 15px 0; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="wrap">
            

            <div class="metrics">
                <div class="metric-card">Total Trips Completed: <b><?= (int)($totals['total_trips'] ?? 0) ?></b></div>
                <div class="metric-card">Total Amount Paid: <b>₹<?= number_format((float)($totals['total_amount'] ?? 0), 2) ?></b></div>
            </div>

            <div class="toolbar">
                <h1 id="payment-heading">My Bookings</h1>
                <div class="filters">
                    <a href="user_payment.php?view=active"><button class="btn <?= $view==='active' ? 'active' : '' ?>">Active</button></a>
                    <a href="user_payment.php?view=completed"><button class="btn <?= $view==='completed' ? 'active' : '' ?>">Completed</button></a>
                    <a href="user_payment.php?view=canceled"><button class="btn <?= $view==='canceled' ? 'active' : '' ?>">Canceled</button></a>
                </div>
            </div>

        <?php if ($view === 'active'): ?>
        <div class="card">
        <table class="userpanel-tabel">
            <tr>
                <th class="user-payment">Full Name (Driver)</th>
                <th class="user-payment">Car No. Plate</th>
                <th class="user-payment">Pick-up</th>
                <th class="user-payment">Drop</th>
                <th class="user-payment">Amount</th>
                <th class="user-payment">Payment Mode</th>
                <th class="user-payment">Action</th>
            </tr>
            <?php if ($activeRes && mysqli_num_rows($activeRes) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($activeRes)): ?>
                    <tr>
                        <td class="user-ride-data"><?= htmlspecialchars($row['driver_name']) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['car_number_plate']) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['pickup']) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['drop_location']) ?></td>
                        <td class="user-ride-data">₹<?= number_format($row['amount'], 2) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['payment_mode']) ?></td>
                        <td class="user-ride-data">
                            <form method="POST" action="ride_cancel.php" style="margin:0;">
                                <input type="hidden" name="payment_id" value="<?= (int)$row['payment_id'] ?>">
                                <button class="cancel-passenger" type="submit">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="user-ride-data">No bookings found.</td></tr>
            <?php endif; ?>
        </table>
        </div>
        <?php elseif ($view === 'completed'): ?>
        <div class="card">
        <table class="userpanel-tabel">
            <tr>
                <th class="user-payment">Full Name (Driver)</th>
                <th class="user-payment">Car No. Plate</th>
                <th class="user-payment">Pick-up</th>
                <th class="user-payment">Drop</th>
                <th class="user-payment">Amount</th>
                <th class="user-payment">Payment Mode</th>
            </tr>
            <?php if ($completedRes && mysqli_num_rows($completedRes) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($completedRes)): ?>
                    <tr>
                        <td class="user-ride-data"><?= htmlspecialchars($row['driver_name']) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['car_number_plate']) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['pickup']) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['drop_location']) ?></td>
                        <td class="user-ride-data">₹<?= number_format($row['amount'], 2) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['payment_mode']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="user-ride-data">No completed bookings.</td></tr>
            <?php endif; ?>
        </table>
        </div>
        <?php else: ?>
        <div class="card">
        <table class="userpanel-tabel">
            <tr>
                <th class="user-payment">Full Name (Driver)</th>
                <th class="user-payment">Car No. Plate</th>
                <th class="user-payment">Pick-up</th>
                <th class="user-payment">Drop</th>
                <th class="user-payment">Amount</th>
                <th class="user-payment">Payment Mode</th>
            </tr>
            <?php if ($canceledRes && mysqli_num_rows($canceledRes) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($canceledRes)): ?>
                    <tr>
                        <td class="user-ride-data"><?= htmlspecialchars($row['driver_name']) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['car_number_plate']) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['pickup']) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['drop_location']) ?></td>
                        <td class="user-ride-data">₹<?= number_format($row['amount'], 2) ?></td>
                        <td class="user-ride-data"><?= htmlspecialchars($row['payment_mode']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="user-ride-data">No canceled bookings.</td></tr>
            <?php endif; ?>
        </table>
        </div>
        <?php endif; ?>
        </div>
    </div>

       <div class="custom-footer">
    <?php include('../includes/footer.php'); ?>
</div>
</body>
</html>
