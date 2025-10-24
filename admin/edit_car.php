<?php
session_start();
include('../config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: manage_cars.php");
    exit;
}

$car_id = (int)$_GET['id'];

// Fetch car data
$query = "SELECT * FROM cars WHERE car_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

if (!$car) {
    echo "Car not found.";
    exit;
}

// Gujarat cities
$gujarat_cities = ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Gandhinagar', 'Junagadh', 'Anand', 'Nadiad', 'Bharuch', 'Mehsana', 'Morbi', 'Gondal', 'Navsari', 'Vapi', 'Veraval', 'Botad', 'Porbandar'];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_name = $_POST['car_name'];
    $seating = (int)$_POST['seating'];
    $city = $_POST['city'];
    $pickup_location = $_POST['pickup_location'];
    $drop_location = $_POST['drop_location'];
    $user_id = (int)$_POST['user_id'];
    $amount = (float)$_POST['amount'];
    $number_plate = $_POST['number_plate'];
    $date_time = $_POST['date_time'];

    // Check for duplicate number plate
    $check_query = "SELECT * FROM cars WHERE number_plate = ? AND car_id != ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("si", $number_plate, $car_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "This car number plate already exists.";
        header("Location: edit_car.php?id=" . $car_id);
        exit;
    }

    // Image update
    if ($_FILES['car_image']['name']) {
        $car_image = time() . '_' . basename($_FILES['car_image']['name']);
        $target_dir = "../uploads/";
        move_uploaded_file($_FILES['car_image']['tmp_name'], $target_dir . $car_image);
    } else {
        $car_image = $car['car_image'];
    }

    // Update query with corrected bind_param types
    $update = "UPDATE cars SET car_image=?, car_name=?, seating=?, city=?, pickup_location=?, drop_location=?, user_id=?, amount=?, number_plate=?, date_time=? WHERE car_id=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssisssssdsi", $car_image, $car_name, $seating, $city, $pickup_location, $drop_location, $user_id, $amount, $number_plate, $date_time, $car_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Car updated successfully.";
    } else {
        $_SESSION['error'] = "Update failed: " . $stmt->error;
    }

    header("Location: manage_cars.php");
    exit;
}

// Date-time restriction
$minDate = date('Y-m-d\TH:i');
$maxDate = date('Y-m-d\T23:59', strtotime('+90 day'));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Car</title>
    <style>
        body { font-family: Arial; margin-left: 220px; padding: 20px; }
        form { display: flex; flex-wrap: wrap; gap: 20px; width: 800px; }
        .form-group { flex: 0 0 48%; display: flex; flex-direction: column; }
        label { margin-bottom: 5px; font-weight: bold; }
        input, select { padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
        button { padding: 10px 20px; background: #007bff; border: none; color: white; border-radius: 5px; cursor: pointer; }
        .full-width { flex: 1 0 100%; }
        .back-btn {
            text-decoration: none;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<?php include('admin_header.php'); ?>
<center>
<h2>Edit Car</h2>

<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label>Current Image</label>
        <img src="../uploads/<?= htmlspecialchars($car['car_image']) ?>" width="100">
    </div>
    <div class="form-group">
        <label for="car_image">Change Image</label>
        <input type="file" name="car_image">
    </div>
    <div class="form-group">
        <label>Car Name</label>
        <input type="text" name="car_name" value="<?= htmlspecialchars($car['car_name']) ?>" required>
    </div>
    <div class="form-group">
        <label>Seating</label>
        <select name="seating" required>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?= $i ?>" <?= $car['seating'] == $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="form-group">
        <label>City</label>
        <select name="city" required>
            <?php foreach ($gujarat_cities as $city): ?>
                <option value="<?= $city ?>" <?= $car['city'] == $city ? 'selected' : '' ?>><?= $city ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Pickup Location</label>
        <select name="pickup_location" required>
            <?php foreach ($gujarat_cities as $city): ?>
                <option value="<?= $city ?>" <?= $car['pickup_location'] == $city ? 'selected' : '' ?>><?= $city ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Drop Location</label>
        <select name="drop_location" required>
            <?php foreach ($gujarat_cities as $city): ?>
                <option value="<?= $city ?>" <?= $car['drop_location'] == $city ? 'selected' : '' ?>><?= $city ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>User</label>
        <select name="user_id" required>
            <option value="">Select User</option>
            <?php
            $users = mysqli_query($conn, "SELECT id, name FROM users ORDER BY name ASC");
            while ($user = mysqli_fetch_assoc($users)) {
                $selected = ($car['user_id'] == $user['id']) ? "selected" : "";
                echo "<option value='{$user['id']}' $selected>{$user['name']}</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label>Amount (₹)</label>
        <input type="number" name="amount" step="0.01" value="<?= $car['amount'] ?>" required>
    </div>
    <div class="form-group">
        <label>Number Plate</label>
        <input type="text" name="number_plate" value="<?= htmlspecialchars($car['number_plate']) ?>" readonly required>

    </div>
    <div class="form-group">
        <label>Undra Date Time</label>
        <input type="datetime-local" name="date_time" value="<?= date('Y-m-d\TH:i', strtotime($car['date_time'])) ?>" min="<?= $minDate ?>" max="<?= $maxDate ?>" required>
    </div>
    <div class="form-group full-width">
        <button type="submit">Update Car</button>
    </div>
</form>

<a href="manage_cars.php" class="back-btn">Back</a>
</center>
</body>
</html>
