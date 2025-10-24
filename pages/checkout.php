<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id'])) {
	header('Location: login.php');
	exit;
}

$userId = (int)$_SESSION['user_id'];
$carId = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;

// Fetch car details
$car = null;
if ($carId > 0) {
	$res = mysqli_query($conn, "SELECT c.car_id, c.car_name, c.amount, c.number_plate, c.pickup_location, c.drop_location, c.date_time, COALESCE(c.driver_name, d.name) AS driver_name FROM cars c LEFT JOIN drivers d ON d.id = c.user_id WHERE c.car_id = $carId");
	if ($res && mysqli_num_rows($res) === 1) {
		$car = mysqli_fetch_assoc($res);
	}
}

if (!$car) {
	echo 'Invalid car selection.';
	exit;
}

// Handle form submission (mock card processing)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$cardName = trim($_POST['card_name'] ?? '');
	$cardNumber = preg_replace('/\D+/', '', $_POST['card_number'] ?? '');
	$expiry = strtoupper(trim($_POST['expiry'] ?? ''));
	$cvv = preg_replace('/\D+/', '', $_POST['cvv'] ?? '');

	// Luhn algorithm
	$luhnValid = function(string $digits): bool {
		$sum = 0; $alt = false; $len = strlen($digits);
		for ($i = $len - 1; $i >= 0; $i--) {
			$n = (int)$digits[$i];
			if ($alt) { $n *= 2; if ($n > 9) { $n -= 9; } }
			$sum += $n; $alt = !$alt;
		}
		return $len >= 12 && $sum % 10 === 0;
	};

	// Expiry validation MM/YY and not past
	$expiryValid = false;
	if (preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry, $m)) {
		$mm = (int)$m[1]; $yy = (int)$m[2];
		$curY = (int)date('y'); $curM = (int)date('m');
		$expiryValid = ($yy > $curY) || ($yy === $curY && $mm >= $curM);
	}

	$cvvValid = (strlen($cvv) === 3 || strlen($cvv) === 4);
	$nameValid = (strlen($cardName) >= 2);
	$lenCard = strlen($cardNumber);
	$cardDigitsOnly = ($lenCard >= 12 && $lenCard <= 19 && ctype_digit($cardNumber));
	// Accept valid Luhn OR, for mock gateway, any 16-digit numeric as fallback
	$cardValid = $cardDigitsOnly && ($luhnValid($cardNumber) || $lenCard === 16);

	if (!$nameValid) {
		$error = 'Enter the name as printed on the card.';
	} else if (!$cardValid) {
		$error = 'Enter a valid card number.';
	} else if (!$expiryValid) {
		$error = 'Enter a valid expiry (MM/YY) that is not in the past.';
	} else if (!$cvvValid) {
		$error = 'Enter a valid CVV (3 or 4 digits).';
	} else {
		$amount = (float)$car['amount'];
		$razorpayId = 'mock_' . time();

		// Ensure we have a valid users.id to satisfy FK in payments/bookings
		// Ensure users table exists (some deployments rely only on `pessanger`)
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
		$userIdForFk = null;
		$chkUsers = mysqli_query($conn, "SELECT id FROM users WHERE id = $userId");
		if ($chkUsers && mysqli_num_rows($chkUsers) === 1) {
			$rowU = mysqli_fetch_assoc($chkUsers);
			$userIdForFk = (int)$rowU['id'];
		} else {
			// Map pessanger -> users by email (create if needed)
			$ps = mysqli_query($conn, "SELECT name, email, contact, password, created_at FROM pessanger WHERE id = $userId");
			if ($ps && mysqli_num_rows($ps) === 1) {
				$p = mysqli_fetch_assoc($ps);
				$emailEsc = mysqli_real_escape_string($conn, (string)$p['email']);
				$u2 = mysqli_query($conn, "SELECT id FROM users WHERE email = '$emailEsc'");
				if ($u2 && mysqli_num_rows($u2) === 1) {
					$userIdForFk = (int)mysqli_fetch_assoc($u2)['id'];
				} else {
					$nameEsc = mysqli_real_escape_string($conn, (string)$p['name']);
					$contactEsc = mysqli_real_escape_string($conn, (string)$p['contact']);
					$passEsc = mysqli_real_escape_string($conn, (string)$p['password']);
					$createdAtEsc = $p['created_at'] ? "'" . mysqli_real_escape_string($conn, (string)$p['created_at']) . "'" : 'CURRENT_TIMESTAMP()';
					$insU = "INSERT INTO users (name, email, contact, password, role, created_at) VALUES ('$nameEsc', '$emailEsc', '$contactEsc', '$passEsc', 'user', $createdAtEsc)";
					if (mysqli_query($conn, $insU)) {
						$userIdForFk = (int)mysqli_insert_id($conn);
					}
				}
			}
		}
		if (!$userIdForFk) { $userIdForFk = 0; }

		// Ensure denormalized columns exist in payments
		$ensureCol = function($col, $type) use ($conn) {
			$exists = mysqli_query($conn, "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = '$col'");
			if ($exists && mysqli_num_rows($exists) === 0) {
				mysqli_query($conn, "ALTER TABLE payments ADD COLUMN `$col` $type");
			}
		};
		$ensureCol('driver_name', 'VARCHAR(100) DEFAULT NULL');
		$ensureCol('passenger_name', 'VARCHAR(100) DEFAULT NULL');
		$ensureCol('car_number_plate', 'VARCHAR(50) DEFAULT NULL');
		$ensureCol('pickup', 'VARCHAR(100) DEFAULT NULL');
		$ensureCol('drop_location', 'VARCHAR(100) DEFAULT NULL');
		$ensureCol('payment_mode', 'VARCHAR(20) DEFAULT NULL');
		$ensureCol('ride_datetime', 'DATETIME DEFAULT NULL');
		$ensureCol('ride_status', "ENUM('pending','active','completed','canceled') DEFAULT 'pending'");

		$driverName = $car['driver_name'] ?? '';
		$passengerName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
		if ($passengerName === '') {
			$resP = mysqli_query($conn, "SELECT name FROM pessanger WHERE id = $userId");
			if ($resP && mysqli_num_rows($resP) === 1) { $rowP = mysqli_fetch_assoc($resP); $passengerName = $rowP['name']; }
		}

		$plate = $car['number_plate'] ?? '';
		$pickupLoc = $car['pickup_location'] ?? '';
		$dropLoc = $car['drop_location'] ?? '';
		$paymentMode = 'ONLINE';
		$rideDateTime = $car['date_time'] ?? null; // may be null if not selected

		// Record payment with denormalized fields (use users.id)
		mysqli_query($conn, "INSERT INTO payments (user_id, car_id, amount, razorpay_payment_id, payment_status, driver_name, passenger_name, car_number_plate, pickup, drop_location, payment_mode, ride_datetime, ride_status) VALUES ($userIdForFk, {$car['car_id']}, $amount, '$razorpayId', 'Success', '" . mysqli_real_escape_string($conn, $driverName) . "', '" . mysqli_real_escape_string($conn, $passengerName) . "', '" . mysqli_real_escape_string($conn, $plate) . "', '" . mysqli_real_escape_string($conn, $pickupLoc) . "', '" . mysqli_real_escape_string($conn, $dropLoc) . "', '$paymentMode', " . ($rideDateTime ? "'" . mysqli_real_escape_string($conn, $rideDateTime) . "'" : 'NULL') . ", 'pending')");

		// Record booking (1 seat) with users.id
		mysqli_query($conn, "INSERT INTO bookings (user_id, car_id, seats_booked) VALUES ($userIdForFk, {$car['car_id']}, 1)");

		header('Location: user_payment.php');
		exit;
	}
}

// Include header after all PHP processing is done
include('uder_index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Checkout</title>
	<style>
		@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
		body { font-family: 'Poppins', sans-serif; background: #f6f7fb; }
		.container { max-width: 700px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
		.summary { margin-bottom: 20px; }
		.field { margin-bottom: 12px; }
		.label { display:block; font-weight:600; margin-bottom:6px; }
		.input { width:100%; height:44px; border:1px solid #ddd; border-radius:8px; padding:8px 10px; font-size:15px; box-sizing:border-box; }
		.row { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-start; }
		.row .field { flex:1; min-width:180px; }
		@media (max-width: 520px) { .row { flex-direction:column; } }
		.btn { width:100%; height:44px; background:green; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; }
		.btn:hover { background:#8000ff; }
		.error { color:#c00; margin-bottom: 10px; text-align:center; }
	</style>
</head>
<body>
	<div class="container">
		<h2>Checkout</h2>
		<div class="summary">
			<p><strong>Car:</strong> <?= htmlspecialchars($car['car_name']) ?> (<?= htmlspecialchars($car['number_plate']) ?>)</p>
			<p><strong>Driver:</strong> <?= htmlspecialchars($car['driver_name'] ?? 'N/A') ?></p>
			<p><strong>Route:</strong> <?= htmlspecialchars($car['pickup_location']) ?> → <?= htmlspecialchars($car['drop_location']) ?></p>
			<p><strong>Amount:</strong> ₹<?= number_format((float)$car['amount'], 2) ?></p>
		</div>
		<?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
		<form method="POST">
			<div class="field">
				<label class="label">Name on Card</label>
				<input class="input" type="text" name="card_name" required minlength="2">
			</div>
			<div class="field">
				<label class="label">Card Number</label>
				<input class="input" type="text" id="card_number" name="card_number" inputmode="numeric" maxlength="19" placeholder="1234 5678 9012 3456" required pattern="[0-9 ]{12,19}">
			</div>
			<div class="row">
				<div class="field">
					<label class="label">Expiry (MM/YY)</label>
					<input class="input" type="text" id="expiry" name="expiry" placeholder="MM/YY" maxlength="5" required pattern="^(0[1-9]|1[0-2])\/(\d{2})$">
				</div>
				<div class="field">
					<label class="label">CVV</label>
					<input class="input" type="password" name="cvv" inputmode="numeric" maxlength="4" minlength="3" required pattern="\d{3,4}">
				</div>
			</div>
			<button class="btn" type="submit">Pay and Confirm</button>
		</form>
	</div>

	<script>
	(function() {
		const card = document.getElementById('card_number');
		const expiry = document.getElementById('expiry');

		if (card) {
			card.addEventListener('input', function(e) {
				let digits = this.value.replace(/\D+/g, '').slice(0, 19);
				// Group in blocks of 4 for readability
				const parts = [];
				for (let i = 0; i < digits.length; i += 4) {
					parts.push(digits.substring(i, i + 4));
				}
				this.value = parts.join(' ').slice(0, 19);
			});
		}

		if (expiry) {
			expiry.addEventListener('input', function(e) {
				let digits = this.value.replace(/\D+/g, '').slice(0, 4);
				if (digits.length >= 3) {
					this.value = digits.slice(0,2) + '/' + digits.slice(2);
				} else {
					this.value = digits;
				}
			});

			expiry.addEventListener('keydown', function(e) {
				// Handle backspace just after '/'
				if (e.key === 'Backspace' && this.selectionStart === 3 && this.value.charAt(2) === '/') {
					this.value = this.value.slice(0,2);
					e.preventDefault();
				}
			});
		}
	})();
	</script>
	</div>
</body>
</html>


