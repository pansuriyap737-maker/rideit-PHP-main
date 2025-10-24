<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('admin_header.php');
include('../config.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Cars</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
        .container { max-width: 1200px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);}    
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: center; }
        th { background: #007bff; color: #fff; }
        .no { color: #777; padding: 20px; }
        img { width: 80px; border-radius: 6px; object-fit: cover; }
    </style>
</head>
<body>
<div class="container">
    <?php $totalCarsRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM cars"); $totalCars = 0; if ($totalCarsRes) { $row = mysqli_fetch_assoc($totalCarsRes); $totalCars = (int)$row['cnt']; } ?>
    <h2>Manage Cars (Total: <?php echo $totalCars; ?>)</h2>
    <form method="GET" style="margin:10px 0; display:flex; gap:8px; align-items:center;">
        <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Search by driver, car, plate, pickup, drop" style="flex:1; padding:8px; border:1px solid #ccc; border-radius:6px;">
        <button type="submit" style="padding:8px 12px; border:none; background:#007bff; color:#fff; border-radius:6px;">Search</button>
        <a href="manage_cars.php" style="padding:8px 12px; border:1px solid #ccc; border-radius:6px; text-decoration:none; color:#333; background:#f7f7f7;">Reset</a>
    </form>
    <?php
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $where = '';
    if ($q !== '') {
        $esc = mysqli_real_escape_string($conn, $q);
        $where = "WHERE c.car_name LIKE '%$esc%' OR c.number_plate LIKE '%$esc%' OR c.pickup_location LIKE '%$esc%' OR c.drop_location LIKE '%$esc%' OR COALESCE(c.driver_name, d.name) LIKE '%$esc%'";
    }
    $res = mysqli_query($conn, "SELECT c.*, COALESCE(c.driver_name, d.name) AS driver_name FROM cars c LEFT JOIN drivers d ON d.id = c.user_id $where ORDER BY c.created_at DESC");
    ?>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Driver</th>
                <th>Car Name</th>
                <th>Number Plate</th>
                <th>Seating</th>
                <th>Pickup</th>
                <th>Drop</th>
                <th>Amount</th>
                <th>Date/Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && mysqli_num_rows($res) > 0): ?>
            <?php while($car = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?php if($car['car_image']) { ?><img src="../uploads/<?php echo htmlspecialchars($car['car_image']); ?>"><?php } ?></td>
                    <td><?php echo htmlspecialchars($car['driver_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($car['car_name']); ?></td>
                    <td><?php echo htmlspecialchars($car['number_plate']); ?></td>
                    <td><?php echo (int)$car['seating']; ?></td>
                    <td><?php echo htmlspecialchars($car['pickup_location']); ?></td>
                    <td><?php echo htmlspecialchars($car['drop_location']); ?></td>
                    <td><?php echo number_format($car['amount'],2); ?></td>
                    <td><?php echo $car['date_time'] ? date('d/m/Y H:i', strtotime($car['date_time'])) : ''; ?></td>
                    <td>
                        <form method="POST" action="delete_car.php" style="margin:0;">
                            <input type="hidden" name="car_id" value="<?php echo (int)$car['car_id']; ?>">
                            <button type="submit" style="padding:6px 10px; border:2px solid #c00; color:#c00; background:#fff; border-radius:6px; cursor:pointer;">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9" class="no">No cars found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
