<?php
session_start();
include('driver_index.php');
include('../config.php');

if (!isset($_SESSION['driver_id'])) { header('Location: ../pages/login.php'); exit; }
$driverId = (int)$_SESSION['driver_id'];
$view = isset($_GET['view']) ? strtolower(trim($_GET['view'])) : 'active';
if (!in_array($view, ['active','completed','canceled'], true)) { $view = 'active'; }

// Ensure payments.ride_status exists (backward compatibility on older DBs)
$__col = mysqli_query($conn, "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'ride_status'");
if ($__col && mysqli_num_rows($__col) === 0) {
	mysqli_query($conn, "ALTER TABLE payments ADD COLUMN ride_status ENUM('pending','active','completed','canceled') DEFAULT 'pending'");
}

// Active rides
$activeSql = "SELECT p.payment_id, p.passenger_name, p.car_number_plate, p.pickup, p.drop_location, COALESCE(p.ride_datetime, c.date_time) AS ride_datetime, p.amount, COALESCE(p.payment_mode,'ONLINE') AS payment_mode
              FROM payments p INNER JOIN cars c ON c.car_id = p.car_id
              WHERE c.user_id = $driverId AND p.payment_status='Success' AND p.ride_status='active'";
$activeRes = mysqli_query($conn, $activeSql);

// Completed rides
$completedSql = "SELECT p.payment_id, p.passenger_name, p.car_number_plate, p.pickup, p.drop_location, COALESCE(p.ride_datetime, c.date_time) AS ride_datetime, p.amount, COALESCE(p.payment_mode,'ONLINE') AS payment_mode
                 FROM payments p INNER JOIN cars c ON c.car_id = p.car_id
                 WHERE c.user_id = $driverId AND p.payment_status='Success' AND p.ride_status='completed'";
$completedRes = mysqli_query($conn, $completedSql);

// Create canceledtrip table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS canceledtrip (
    id INT(11) NOT NULL AUTO_INCREMENT,
    payment_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    driver_id INT(11) DEFAULT NULL,
    passenger_name VARCHAR(100) DEFAULT NULL,
    driver_name VARCHAR(100) DEFAULT NULL,
    car_number_plate VARCHAR(20) DEFAULT NULL,
    pickup VARCHAR(255) DEFAULT NULL,
    drop_location VARCHAR(255) DEFAULT NULL,
    amount DECIMAL(10,2) DEFAULT NULL,
    payment_mode VARCHAR(50) DEFAULT NULL,
    canceled_by ENUM('passenger','driver') DEFAULT 'passenger',
    canceled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Canceled rides
$canceledSql = "SELECT ct.payment_id, ct.passenger_name, ct.car_number_plate, ct.pickup, ct.drop_location, ct.canceled_at AS ride_datetime, ct.amount, COALESCE(ct.payment_mode,'ONLINE') AS payment_mode, ct.canceled_by
                FROM canceledtrip ct
                WHERE ct.driver_id = $driverId";
$canceledRes = mysqli_query($conn, $canceledSql);

// Totals
$totalsSql = "SELECT 
  SUM(CASE WHEN p.ride_status='completed' THEN 1 ELSE 0 END) AS total_trips,
  SUM(CASE WHEN p.ride_status='completed' THEN p.amount ELSE 0 END) AS total_amount
FROM payments p INNER JOIN cars c ON c.car_id = p.car_id
WHERE c.user_id = $driverId AND p.payment_status='Success'";
$totals = mysqli_fetch_assoc(mysqli_query($conn, $totalsSql));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Bookings</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; background:#f6f7fb; margin:0; }
        .wrap { max-width: 1200px; margin: 30px auto; padding: 0 16px; }
        h2 { margin: 24px 0 12px; font-weight:600; }
        .card { background:#fff; border-radius:14px; box-shadow:0 8px 24px rgba(0,0,0,0.06); padding:18px; margin-top:12px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 12px 14px; border-bottom: 1px solid #eceef4; text-align: center; }
        th { background: #6a0fe0; color: #fff; font-weight:600; }
        tr:nth-child(even) td { background:#fafbfe; }
        .actions form { display:inline-block; margin: 0 5px; }
        .btn { padding: 8px 12px; border-radius: 10px; border: 2px solid; cursor: pointer; background:#fff; font-weight:600; }
        .btn-complete { border-color: #22a06b; color: #22a06b; }
        .btn-complete:hover { background:#eaf7f1; }
        .btn-filter { border-color:#ccc; color:#444; }
        .btn-filter.active { border-color:#6a0fe0; color:#6a0fe0; background:#f3e9ff; }
        .metric { display:flex; gap:20px; margin: 16px 0; }
        .metric .card { flex:1; background:#fff; padding:16px; border-radius:14px; box-shadow:0 8px 24px rgba(0,0,0,0.06); }
        .badge { display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:600; }
        .badge-active { color:#b35a00; background:#fff4e6; border:1px solid #ffd7a8; }
        .badge-completed { color:#176b45; background:#eaf7f1; border:1px solid #bfe6d3; }
        .badge-canceled { color:#b42318; background:#fee4e2; border:1px solid #f7b4ae; }
    </style>
</head>
<body>
<div class="wrap">
    <h2>Totals</h2>
    <div class="metric">
        <div class="card">Total Trips Completed: <b><?= (int)($totals['total_trips'] ?? 0) ?></b></div>
        <div class="card">Total Amount Received: <b>₹<?= number_format((float)($totals['total_amount'] ?? 0), 2) ?></b></div>
    </div>

    <h2 style="display:flex; align-items:center; gap:12px;">Bookings
        <span style="margin-left:auto; display:flex; gap:8px;">
            <a href="driver_bookings.php?view=active" style="text-decoration:none;">
                <button class="btn btn-filter <?= $view==='active' ? 'active' : '' ?>">Active</button>
            </a>
            <a href="driver_bookings.php?view=completed" style="text-decoration:none;">
                <button class="btn btn-filter <?= $view==='completed' ? 'active' : '' ?>">Completed</button>
            </a>
            <a href="driver_bookings.php?view=canceled" style="text-decoration:none;">
                <button class="btn btn-filter <?= $view==='canceled' ? 'active' : '' ?>">Canceled</button>
            </a>
        </span>
    </h2>

    <?php if ($view === 'active'): ?>
    <div class="card">
    <h2 style="margin-top:0;">Active Trips <span class="badge badge-active">Active</span></h2>
    <table>
        <thead>
            <tr>
                <th>Passenger</th><th>Number Plate</th><th>Pickup</th><th>Drop</th><th>DateTime</th><th>Amount</th><th>Mode</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($activeRes && mysqli_num_rows($activeRes) > 0): while ($r = mysqli_fetch_assoc($activeRes)): ?>
                <tr>
                    <td><?= htmlspecialchars($r['passenger_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['car_number_plate'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['pickup'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['drop_location'] ?? '-') ?></td>
                    <td><?= $r['ride_datetime'] ? date('d/m/Y H:i', strtotime($r['ride_datetime'])) : '-' ?></td>
                    <td><?= number_format((float)$r['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($r['payment_mode']) ?></td>
                    <td class="actions">
                        <form method="POST" action="ride_status_update.php" style="display:inline;">
                            <input type="hidden" name="payment_id" value="<?= (int)$r['payment_id'] ?>">
                            <input type="hidden" name="action" value="complete">
                            <button type="submit" class="btn btn-complete">Complete</button>
                        </form>
                        <form method="POST" action="driver_cancel.php" style="display:inline;">
                            <input type="hidden" name="payment_id" value="<?= (int)$r['payment_id'] ?>">
                            <button type="submit" class="btn" style="border-color:#c00;color:#c00;" onclick="return confirm('Cancel this trip?')">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="8">No active trips.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <?php elseif ($view === 'completed'): ?>
    <div class="card">
    <h2 style="margin-top:0;">Completed Trips <span class="badge badge-completed">Completed</span></h2>
    <table>
        <thead>
            <tr>
                <th>Passenger</th><th>Number Plate</th><th>Pickup</th><th>Drop</th><th>DateTime</th><th>Amount</th><th>Mode</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($completedRes && mysqli_num_rows($completedRes) > 0): while ($r = mysqli_fetch_assoc($completedRes)): ?>
                <tr>
                    <td><?= htmlspecialchars($r['passenger_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['car_number_plate'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['pickup'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['drop_location'] ?? '-') ?></td>
                    <td><?= $r['ride_datetime'] ? date('d/m/Y H:i', strtotime($r['ride_datetime'])) : '-' ?></td>
                    <td><?= number_format((float)$r['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($r['payment_mode']) ?></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="7">No completed trips.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <?php else: ?>
    <div class="card">
    <h2 style="margin-top:0;">Canceled Trips <span class="badge badge-canceled">Canceled</span></h2>
    <table>
        <thead>
            <tr>
                <th>Passenger</th><th>Number Plate</th><th>Pickup</th><th>Drop</th><th>Canceled Date</th><th>Amount</th><th>Mode</th><th>Canceled By</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($canceledRes && mysqli_num_rows($canceledRes) > 0): while ($r = mysqli_fetch_assoc($canceledRes)): ?>
                <tr>
                    <td><?= htmlspecialchars($r['passenger_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['car_number_plate'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['pickup'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['drop_location'] ?? '-') ?></td>
                    <td><?= $r['ride_datetime'] ? date('d/m/Y H:i', strtotime($r['ride_datetime'])) : '-' ?></td>
                    <td><?= number_format((float)$r['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($r['payment_mode']) ?></td>
                    <td><?= ucfirst($r['canceled_by']) ?></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="8">No canceled trips.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
</body>
</html>

