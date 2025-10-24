<?php
session_start();
include('uder_index.php');  // Assuming this is correct; adjust if needed
include('../config.php');

// Get unique pickup and drop locations (though not used directly now, keeping for reference if needed)
$pickup_result = mysqli_query($conn, "SELECT DISTINCT pickup_location FROM cars");
$drop_result = mysqli_query($conn, "SELECT DISTINCT drop_location FROM cars");

// Handle filters
$pickup = $_GET['pickup'] ?? '';
$drop = $_GET['drop'] ?? '';
$date = $_GET['date'] ?? '';

$conditions = [];

if (!empty($pickup)) {
    $conditions[] = "c.pickup_location LIKE '%" . mysqli_real_escape_string($conn, $pickup) . "%'";
}
if (!empty($drop)) {
    $conditions[] = "c.drop_location LIKE '%" . mysqli_real_escape_string($conn, $drop) . "%'";
}
if (!empty($date)) {
    $conditions[] = "DATE(c.date_time) = '" . mysqli_real_escape_string($conn, $date) . "'";
}

$where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
$query = "
    SELECT 
        c.*, COALESCE(c.driver_name, d.name) AS driver_name,
        COALESCE(SUM(CASE WHEN UPPER(p.payment_status) = 'SUCCESS' AND (p.ride_status IN ('pending','active')) THEN 1 ELSE 0 END), 0) AS booked_seats
    FROM 
        cars c
    LEFT JOIN drivers d ON d.id = c.user_id
    LEFT JOIN 
        payments p ON c.car_id = p.car_id
    $where
    GROUP BY 
        c.car_id
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Your Trip</title>
    <style>
        /* Importing font from React CSS */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        /* Styles from your React Bookrides.css */
        body { background:#f6f7fb; margin:0; font-family:'Poppins', sans-serif; }
        .wrap { width: 90%; max-width: 1200px; margin: 0 auto; }
        .rides-container { margin-top: 0; display: flex; justify-content: center; align-items: center; flex-direction: column; }

        #rides-search { display: flex; justify-content: space-between; flex-wrap: wrap; align-items: center; box-shadow: 0 8px 24px rgba(0,0,0,0.06); margin-top: 40px; padding: 12px; background:#fff; border-radius:12px; }

        .location { margin: 10px; height: 48px; width: 240px; border: 1px solid #e2e5ec; border-radius: 10px; padding: 10px 12px; font-size: 16px; outline:none; }
        .location:focus { border-color:#6a0fe0; box-shadow:0 0 0 3px rgba(106,15,224,0.08); }

        #search-rides { background-color: #6a0fe0; color: white; border: none; border-radius: 10px; transition: 0.2s; width: 120px; height: 48px; font-size: 16px; margin-left: 10px; font-weight:600; }

        #search-rides:hover { transform: translateY(-1px); background-color: #5a00d6; color: white; }

        .rides-info { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; width: 100%; margin-top: 30px; }
        @media (max-width: 1200px) { .rides-info { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 900px) { .rides-info { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .rides-info { grid-template-columns: 1fr; } }

        .rides-card { width: 100%; height: auto; display: flex; justify-content: center; flex-direction: column; padding: 12px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06); border-radius: 12px; background: #fff; }

        .car-photos { width: 100%; height: 180px; object-fit: cover; border-radius: 10px; }

        #car-title { margin: 10px 10px 0; font-weight: 600; font-size: 18px; }

        #pickup-loc { margin-left: 10px; }

        #drop-loc { margin-left: 10px; }

        #capacity { margin-left: 10px; }

        #pricetag { margin-left: 10px; }

        #driver-name { margin-left: 10px; color:#555; }

        #date-time { margin-left: 10px; color:#555; }

        #ride-book { margin: 10px auto 4px; width: 90%; height: 40px; font-size: 15px; border: none; border-radius: 10px; color: white; background-color: #22a06b; transition: 0.2s; font-weight:600; }

        #ride-book:hover { background-color: #1b7f55; transform: translateY(-1px); }

        /* Existing styles adjusted */
        h2 {
            text-align: center;
            margin-bottom: 30px;
            margin-top: 100px;
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
    <div class="rides-container wrap">
        
        <form method="GET" id="rides-search">
            <input type="text" name="pickup" class="location" placeholder="Pick Up" value="<?= htmlspecialchars($pickup ?? '') ?>">

            <input type="text" name="drop" class="location" placeholder="Drop" value="<?= htmlspecialchars($drop ?? '') ?>">

            <input type="date" name="date" class="location" value="<?= htmlspecialchars($date ?? '') ?>">

            <button type="submit" id="search-rides">Search</button>
        </form>

        <div class="rides-info">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($car = mysqli_fetch_assoc($result)): ?>
                    <?php $available = $car['seating'] - $car['booked_seats']; ?>
                    <div class="rides-card">
                        <img src="../uploads/<?= htmlspecialchars($car['car_image']) ?>" alt="Car Image" class="car-photos">
                        <h2 id="car-title"><?= htmlspecialchars($car['car_name']) ?></h2>
                        <p id="driver-name">Driver Name: <b><?= htmlspecialchars($car['driver_name'] ?? 'N/A') ?></b></p>
                        <p id="pickup-loc">Pickup: <b><?= htmlspecialchars($car['pickup_location']) ?></b></p>
                        <p id="drop-loc">Drop: <b><?= htmlspecialchars($car['drop_location']) ?></b></p>
                        <p id="date-time">DateTime: <b><?= date('d M Y, h:i A', strtotime($car['date_time'])) ?></b></p>
                        <h3 id="pricetag">₹<?= number_format($car['amount']) ?></h3>
                        <p id="capacity">Seating Capacity: <b><?= $car['seating'] ?? '-' ?></b></p>
                        <?php if ($car['booked_seats'] == 0): ?>
                            <form method="GET" action="checkout.php" style="margin:0;">
                                <input type="hidden" name="car_id" value="<?= (int)$car['car_id'] ?>">
                                <button type="submit" id="ride-book">Book Now</button>
                            </form>
                        <?php else: ?>
                            <button style="background-color:red; color:white; border:none; border-radius:10px; padding:10px; margin:10px auto 4px; width:90%; height:40px; font-size:15px; font-weight:600; cursor:not-allowed;" disabled>Fully Booked</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center;">No cars found for the selected filters.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="custom-footer">
        <?php include('../includes/footer.php'); ?>
    </div>
</body>
</html>
