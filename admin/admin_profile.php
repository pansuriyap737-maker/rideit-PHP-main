<?php
session_start();
include('../config.php');

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$admin_result = mysqli_query($conn, "SELECT * FROM admin WHERE admin_id = $admin_id");
$admin = mysqli_fetch_assoc($admin_result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_number']);

    $update = "UPDATE admin SET 
        first_name='$first_name',
        last_name='$last_name',
        email='$email',
        contact_number='$contact',
        updated_at=NOW() 
        WHERE admin_id=$admin_id";

    $message = mysqli_query($conn, $update) ? "Profile updated successfully!" : "Error updating profile!";
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    if (password_verify($current_password, $admin['password'])) {
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE admin SET password='$new_hashed', updated_at=NOW() WHERE admin_id=$admin_id");
        $password_message = "Password changed successfully!";
    } else {
        $password_message = "Incorrect current password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin-left: 220px;
            padding: 40px 20px;
            background-color: #f2f2f2;
        }

        .title {
            text-align: center;
            font-size: 26px;
            color: #007bff;
            margin-bottom: 10px;
        }

        .toggle-buttons {
            text-align: center;
            margin-bottom: 30px;
        }

        .toggle-buttons button {
            margin: 0 10px;
            padding: 10px 25px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .toggle-buttons button:hover {
            background-color: #0056b3;
        }

        .form-container {
            display: none;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            max-width: 800px;
            margin: 0 auto 50px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form-container.active {
            display: block;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 90%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button[type="submit"] {
            padding: 10px 25px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            color: green;
            margin-bottom: 10px;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>

<?php include('admin_header.php'); ?>

<div class="title">Admin Profile & Change Password</div>

<div class="toggle-buttons">
    <button onclick="showForm('profile')">Show Profile</button>
    <button onclick="showForm('password')">Change Password</button>
</div>

<!-- Profile Form -->
<div id="profileForm" class="form-container active">
    <h3 style="text-align:center;">Admin Profile</h3>
    <?php if (!empty($message)): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($admin['first_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($admin['last_name']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact_number" value="<?= htmlspecialchars($admin['contact_number']) ?>" required>
            </div>
        </div>
        <div class="form-group" style="text-align: center;">
            <button type="submit" name="update_profile">Update Profile</button>
        </div>
    </form>
</div>

<!-- Password Change Form -->
<div id="passwordForm" class="form-container">
    <h3 style="text-align:center;">Change Password</h3>
    <?php if (!empty($password_message)): ?>
        <p class="message <?= strpos($password_message, 'Incorrect') !== false ? 'error' : '' ?>">
            <?= $password_message ?>
        </p>
    <?php endif; ?>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required>
            </div>
        </div>
        <div class="form-group" style="text-align: center;">
            <button type="submit" name="change_password">Change Password</button>
        </div>
    </form>
</div>

<script>
function showForm(form) {
    document.getElementById('profileForm').classList.remove('active');
    document.getElementById('passwordForm').classList.remove('active');

    if (form === 'profile') {
        document.getElementById('profileForm').classList.add('active');
    } else {
        document.getElementById('passwordForm').classList.add('active');
    }
}
</script>

</body>
</html>
