<?php
session_start();
include('driver_index.php');
include('../config.php'); // Assuming this has your database connection

$driverId = isset($_SESSION['driver_id']) ? (int)$_SESSION['driver_id'] : 0;
if ($driverId <= 0) {
	header('Location: ../pages/login.php');
	exit;
}

// Fetch latest paid bookings for this driver using denormalized fields in payments
$sql = "SELECT p.payment_id, p.passenger_name, p.car_number_plate, p.pickup, p.drop_location, COALESCE(p.ride_datetime, c.date_time) AS ride_datetime, p.amount, COALESCE(p.payment_mode, 'ONLINE') AS payment_mode, p.ride_status
        FROM payments p
        INNER JOIN cars c ON c.car_id = p.car_id
        WHERE c.user_id = $driverId AND p.payment_status = 'Success' AND p.ride_status IN ('pending','active')
        ORDER BY p.payment_date DESC";
$ridesRes = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pending Rides</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        .pending-rides-container {
            width: 1400px;
            margin: 40px auto 40px;
            font-family: Arial, sans-serif;
            height: 100vh;
        }

        .pending-rides-heading {
            font-size: 30px;
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* Important to avoid double borders */
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #c5c4c4ff;
            /* Add borders to all sides */
        }

         tr:hover {
            background-color: #e9e7e7ff;
        }

        th {
            font-size: 17px;
            background-color: #6a0fe0;
            color: white;
        }

        td {
            font-size: 16px;
            align-items: center;
        }

        .ride-accept {
            padding: 5px;
            font-size: 15px;
            border: 2px solid #6a0fe0;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(58, 58, 58, 0.1);
            color: #6a0fe0;
            transition: 0.3s;
            width: 90px;
            margin-bottom: 10px;
        }

        .ride-accept:hover {
            transform: scale(105%);
        }

        .ride-cancel {
            padding: 5px;
            font-size: 15px;
            border: 2px solid #ff0000;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(58, 58, 58, 0.1);
            color: #ff0000;
            transition: 0.3s;
            width: 90px;
        }

        .ride-cancel:hover {
            transform: scale(105%);
        }

        .action-rides-pending {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .custom-footer {
            background-color: #6a0fe0;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="pending-rides-container">
        <h2 class="pending-rides-heading">Pending Rides</h2>
        <table>
            <thead>
                <tr>
                    <th>Full Name(Passenger)</th>
                    <th>Number Plate</th>
                    <th>Pickup</th>
                    <th>Drop</th>
                    <th>Booking DateTime</th>
                    <th>Amount (₹)</th>
                    <th>Payment Mode</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ridesRes && mysqli_num_rows($ridesRes) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($ridesRes)): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['passenger_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($r['car_number_plate'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($r['pickup'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($r['drop_location'] ?? '-') ?></td>
                            <td><?= $r['ride_datetime'] ? date('d/m/Y H:i', strtotime($r['ride_datetime'])) : '-' ?></td>
                            <td><?= number_format((float)$r['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($r['payment_mode']) ?></td>
                            <td class="action-rides-pending">
                                <form method="POST" action="ride_status_update.php" style="margin:0">
                                    <input type="hidden" name="payment_id" value="<?= (int)$r['payment_id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button class="ride-accept" type="submit">Accept</button>
                                </form>
                                <form method="POST" action="ride_status_update.php" style="margin:0">
                                    <input type="hidden" name="payment_id" value="<?= (int)$r['payment_id'] ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <button class="ride-cancel" type="submit">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No pending rides.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="custom-footer">
        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>
