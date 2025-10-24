<?php
session_start();
include('driver_index.php');
include('../config.php');

if (!isset($_GET['id'])) {
    header("Location: add_trip.php");
    exit;
}

$car_id = (int)$_GET['id'];
$driver_id = isset($_SESSION['driver_id']) ? (int)$_SESSION['driver_id'] : 0;

// Fetch the existing trip data (owned by this driver)
$query = "SELECT * FROM cars WHERE car_id = $car_id AND user_id = $driver_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Trip not found.'); window.location.href = 'add_trip.php';</script>";
    exit;
}

$row = mysqli_fetch_assoc($result);

// Gujarat cities for dropdown
$gujarat_cities = [
    'Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar',
    'Gandhinagar', 'Junagadh', 'Anand', 'Nadiad', 'Bharuch', 'Mehsana',
    'Morbi', 'Gondal', 'Navsari', 'Vapi', 'Veraval', 'Botad', 'Porbandar'
];

// Seating capacity dropdown values
$seating_options = range(1, 8);

// Limit booking date-time range
$minDate = date('Y-m-d\TH:i'); // Current date and time
$maxDate = date('Y-m-d\T23:59', strtotime('+90 day')); // 90 days from now


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trip</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background: #f6f7fb; margin: 0; }
        form {
            width: 900px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }
        h2 {
            text-align: center;
            margin-bottom: 18px;
            margin-top: 110px;
            font-weight: 600;
        }
        .form-row {
            display: flex;
            gap: 18px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        input, select {
            height: 44px;
            padding: 10px 12px;
            font-size: 15px;
            border: 1px solid #e2e5ec;
            border-radius: 10px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
            background: #fff;
        }
        input:focus, select:focus { border-color: #6a0fe0; box-shadow: 0 0 0 3px rgba(106,15,224,0.08); }
        button {
            background-color: #6a0fe0;
            color: #fff;
            padding: 12px 20px;
            border: none;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
            transition: transform .1s ease, background .15s ease;
        }
        button:hover { background: #5a00d6; transform: translateY(-1px); }
        .submit-row {
            text-align: center;
            margin-top: 4px;
        }
        .image-inline { display: flex; flex-direction: column; align-items: stretch; gap: 10px; }
        .image-inline img { width: 100%; height: auto; max-height: 280px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e5ec; }
        @media (max-width: 760px) { form { width: 96%; } }
    </style>
</head>
<body>

<h2>Edit Trip</h2>

<form method="POST" action="update_trip.php" enctype="multipart/form-data">

    <input type="hidden" name="car_id" value="<?= $row['car_id'] ?>">

    <div class="form-row">
        <div class="form-group" style="flex:1 1 100%;">
            <label>Car Image</label>
            <div class="image-inline">
                <img id="carPreview" src="../uploads/<?= htmlspecialchars($row['car_image']) ?>" alt="Car Image">
                <input type="file" name="car_image" id="car_image">
            </div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Car Name</label>
            <input type="text" name="car_name" value="<?= htmlspecialchars($row['car_name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Seating Capacity</label>
            <select name="seating" required>
                <option value="">Select</option>
                <?php foreach ($seating_options as $seat): ?>
                    <option value="<?= $seat ?>" <?= ($seat == $row['seating']) ? 'selected' : '' ?>><?= $seat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Amount (₹)</label>
            <input type="number" name="amount" value="<?= htmlspecialchars($row['amount']) ?>" required>
        </div>
        <div class="form-group">
            <label>Number Plate</label>
            <input type="text" name="number_plate" value="<?= htmlspecialchars($row['number_plate']) ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Pickup Location</label>
            <input type="text" name="pickup_location" value="<?= htmlspecialchars($row['pickup_location']) ?>" placeholder="Enter pickup location" required>
        </div>
        <div class="form-group">
            <label>Drop Location</label>
            <input type="text" name="drop_location" value="<?= htmlspecialchars($row['drop_location']) ?>" placeholder="Enter drop location" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Booking DateTime</label>
            <input type="datetime-local" name="date_time" value="<?= date('Y-m-d\TH:i', strtotime($row['date_time'])) ?>" min="<?= $minDate ?>" max="<?= $maxDate ?>" required>
        </div>
    </div>

    <div class="submit-row">
        <button type="submit">Update Trip</button>
    </div>

</form>

</body>
</html>
