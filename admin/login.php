<?php
session_start();
// Include the database connection file
include '../config.php';

$error = "";

// Check if the admin exists; if not, create the default admin
$query = "SELECT * FROM admin WHERE username = 'admin'";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    $username = 'admin';
    $password = 'admin123';
    $first_name = 'uparcase';
    $last_name = 'lovercase';
    $email = 'admin123@gmail.com';
    $contact_number = '7854123690';

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the first admin into the database
    $query = "INSERT INTO admin (username, password, first_name, last_name, email, contact_number, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('ssssss', $username, $hashed_password, $first_name, $last_name, $email, $contact_number);
        $stmt->execute();
        $stmt->close();
    }
}
 
// Admin login process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and fetch input
    $loginIdentifier = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($loginIdentifier) && !empty($password)) {
        // Allow login using either username or email
        $query = "SELECT * FROM admin WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param('ss', $loginIdentifier, $loginIdentifier);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Verify password and proceed if valid
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['admin_id'] = $user['admin_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = 'Admin';

                // Redirect to the dashboard BEFORE any output
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = "Invalid username/email or password.";
            }

            $stmt->close();
        } else {
            $error = "Database query failed.";
        }
    } else {
        $error = "Please fill in both fields.";
    }
}

// Close the database connection
if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Admin Login</title>
    <style>
       
        .login-container {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            margin-top: 30px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        p.error {
            color: red;
            margin-top: 10px;
            font-size: 14px;
        }

        .forgot-password {
           margin-top: 15px;
       }
       .forgot-password a {
           color: #007bff;
           text-decoration: none;
           font-size: 14px;
       }
       .forgot-password a:hover {
           text-decoration: underline;
       }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
    <center>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Email or Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
            <?php if (!empty($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <div class="forgot-password">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
        </form>
    </div>
    </center>
</body>
</html>

<?php include '../includes/footer.php'; ?>
