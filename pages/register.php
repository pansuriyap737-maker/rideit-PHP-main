<?php
include('../includes/header.php');
include('../config.php');

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registerType = isset($_POST['register_type']) ? trim($_POST['register_type']) : '';
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $licenseNo = isset($_POST['license_no']) ? trim($_POST['license_no']) : '';

    // Basic validations
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        if ($registerType === 'pessenger') {
            // Register into `pessenger` table
            $check_name = mysqli_query($conn, "SELECT * FROM pessanger WHERE name = '$name'");
            if ($check_name && mysqli_num_rows($check_name) > 0) {
                $error = "Username already taken. Please choose another.";
            } else {
                $check_email = mysqli_query($conn, "SELECT * FROM pessanger WHERE email = '$email'");
                if ($check_email && mysqli_num_rows($check_email) > 0) {
                    $error = "Email already registered.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert = mysqli_query($conn, "INSERT INTO pessanger (name, email, contact, password) VALUES ('$name', '$email', '$contact', '$hashed_password')");
                    if ($insert) {
                        $success = "Registration successful! Redirecting to <a href='login.php'>Login</a>...";
                        echo "<script>setTimeout(() => { window.location.href = 'login.php'; }, 3000);</script>";
                    } else {
                        $error = "Something went wrong. Try again.";
                    }
                }
            }
        } else {
            // Driver registration into `drivers` table with license number
            if ($registerType === 'driver') {
                if ($licenseNo === '') {
                    $error = "Please enter your License Number.";
                } else {
                    // Ensure drivers table exists
                    $createDrivers = "CREATE TABLE IF NOT EXISTS drivers (
                        id INT(11) NOT NULL AUTO_INCREMENT,
                        name VARCHAR(100) DEFAULT NULL,
                        email VARCHAR(100) DEFAULT NULL,
                        contact VARCHAR(15) DEFAULT NULL,
                        password VARCHAR(255) DEFAULT NULL,
                        license_no VARCHAR(100) DEFAULT NULL,
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                        PRIMARY KEY (id),
                        UNIQUE KEY email (email)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                    mysqli_query($conn, $createDrivers);

                    $check_name = mysqli_query($conn, "SELECT * FROM drivers WHERE name = '$name'");
                    if ($check_name && mysqli_num_rows($check_name) > 0) {
                        $error = "Username already taken. Please choose another.";
                    } else {
                        $check_email = mysqli_query($conn, "SELECT * FROM drivers WHERE email = '$email'");
                        if ($check_email && mysqli_num_rows($check_email) > 0) {
                            $error = "Email already registered.";
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $insert = mysqli_query($conn, "INSERT INTO drivers (name, email, contact, password, license_no) VALUES ('$name', '$email', '$contact', '$hashed_password', '$licenseNo')");
                            if ($insert) {
                                $success = "Driver registration successful! Redirecting to <a href='login.php'>Login</a>...";
                                echo "<script>setTimeout(() => { window.location.href = 'login.php'; }, 3000);</script>";
                            } else {
                                $error = "Something went wrong. Try again.";
                            }
                        }
                    }
                }
            } else {
                // Fallback safety (shouldn't hit)
                $error = "Invalid registration type.";
            }
        }
    }
}
?>

<style>
        *{
            font-family: poppins;
        }
       body, html {
        height: 100%;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    main {
        flex: 1;
        width: 600px;
    }

    footer {
        margin-top: auto;
    }

    .login-container {
        width: 500px;
        margin: 70px auto;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    label {
        display: block;
        margin-bottom: -20px;
        font-weight: bold;
        text-align: left;
    }

    .password-wrapper {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 9px;
        cursor: pointer;
        color: #555;
    }

    .form-control {
        width: 100%;
        height: 45px;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .submit-btn {
        padding: 10px 20px;
        background-color: green;
        color: white;
        border: none;
        border-radius: 5px;
        width: 100%;
        cursor: pointer;
    }

    .submit-btn:hover {
         transform: scale(105%);
    background-color: #8000ff;
    color: white;
    }
</style>

<main style="max-width: 450px; margin: 50px auto;" class="login-container">
    <h2 style="text-align: center; margin-bottom:20px;">Register</h2>

    <?php if ($error): ?>
        <div style="color: red; text-align: center; margin-bottom: 10px;"><?php echo $error; ?></div>
    <?php elseif ($success): ?>
        <div style="color: green; text-align: center; margin-bottom: 10px;"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div style="margin-bottom: 15px;">
            <label>Username:</label><br>
            <input type="text" name="name" required class="form-control" maxlength="12" placeholder="FirstName LastName">

        </div>

        <div style="margin-bottom: 15px;">
            <label>Email:</label><br>
            <input type="email" name="email" required class="form-control" placeholder="Enter your Email">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Contact:</label><br>
            <input type="text" name="contact" required class="form-control" pattern="[0-9]{10}" maxlength="10" placeholder="Enter 10 digit Mobile No.">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Password:</label><br>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" required class="form-control" placeholder="Enter your Password">
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label>Confirm Password:</label><br>
            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="confirm_password" required class="form-control" placeholder="Confirm Password" >
            </div>
        </div>

        <button type="submit" name="register_type" value="pessenger" class="submit-btn pessenger">Register</button>
        <p id="or-separator" style="display: block; text-align: center; margin: 10px;">Or</p>
        <div id="driver-extra" style="display: none; margin-bottom: 10px;">
            <label>License Number:</label><br>
            <input type="text" name="license_no" id="license_no" class="form-control" placeholder="Enter your License Number">
        </div>
        <button type="submit" name="register_type" value="driver" class="submit-btn driver">Register as a Driver</button>
    </form>

    <p style="margin-top: 15px;">Already have an account? <a href="login.php">Login here</a></p>
</main>

<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        input.type = input.type === "password" ? "text" : "password";
    }

    // Show license field on first click, submit on second click
    (function() {
        const driverBtn = document.querySelector('button[name="register_type"][value="driver"]');
        const licenseDiv = document.getElementById('driver-extra');
        const licenseInput = document.getElementById('license_no');
        let revealed = false;
        if (driverBtn) {
            driverBtn.addEventListener('click', function(e) {
                if (!revealed) {
                    e.preventDefault();
                    licenseDiv.style.display = 'block';
                    licenseInput.setAttribute('required', 'required');
                    revealed = true;
                    driverBtn.textContent = 'Submit Driver Registration';
                    // Scroll to the license field for visibility
                    licenseInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }
    })();
</script>

<?php include('../includes/footer.php'); ?>
