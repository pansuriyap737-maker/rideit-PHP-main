<?php
session_start();
include('../config.php');
include('uder_index.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$success = "";
$error = "";

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $newName = trim($_POST['name'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    
    // Validation
    if (empty($newName)) {
        $error = "Name is required.";
    } elseif (strlen($newName) < 2) {
        $error = "Name must be at least 2 characters long.";
    } elseif (empty($newEmail)) {
        $error = "Email is required.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $newName = mysqli_real_escape_string($conn, $newName);
        $newEmail = mysqli_real_escape_string($conn, $newEmail);

        $updateSql = "UPDATE pessanger SET name='$newName', email='$newEmail' WHERE id=$userId";
        if (mysqli_query($conn, $updateSql)) {
            $success = "Profile updated successfully!";
            // Update session if name changed
            if (isset($_SESSION['user_name'])) {
                $_SESSION['user_name'] = $newName;
            }
        } else {
            $error = "Failed to update profile: " . mysqli_error($conn);
        }
    }
}

// Handle password update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_password'])) {
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($currentPassword)) {
        $error = "Current password is required.";
    } elseif (empty($newPassword)) {
        $error = "New password is required.";
    } elseif (strlen($newPassword) < 6) {
        $error = "New password must be at least 6 characters long.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New passwords do not match.";
    } else {
        $fetchUser = mysqli_query($conn, "SELECT password FROM pessanger WHERE id=$userId");
        if (!$fetchUser) {
            $error = "Database error occurred.";
        } else {
            $userData = mysqli_fetch_assoc($fetchUser);
            
            if (!password_verify($currentPassword, $userData['password'])) {
                $error = "Current password is incorrect.";
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updatePwdSql = "UPDATE pessanger SET password='$hashedPassword' WHERE id=$userId";
                if (mysqli_query($conn, $updatePwdSql)) {
                    $success = "Password updated successfully!";
                } else {
                    $error = "Failed to update password: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Refresh info
$result = mysqli_query($conn, "SELECT name, email FROM pessanger WHERE id = $userId");
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Profile</title>
    <style>
        /* Importing font from React CSS */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        /* Styles from your React Myprofile.css with adjustments for label layout */
        .profile {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        #profile-heading,
        #change-password {
            text-align: center;
            font-family: "Poppins", sans-serif;
            margin-bottom: 20px;
        }

        .profile-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
            font-family: "Poppins", sans-serif;
        }

        .profile-update {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            width: 450px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-right: 30px;
        }

        label {
            display: block;
            /* Ensure labels start on a new line */
            margin-bottom: 5px;
            /* Add space between label and input */
            font-family: "Poppins", sans-serif;
            font-size: 16px;
            /* Match font size for consistency */
            font-weight: 600;
        }

        #update-btn {
            margin: 30px 0 20px;
            height: 43px;
            text-decoration: none;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            background-color: green;
            color: white;
            width: 100%;
            transition: 0.3s;
            font-family: "Poppins", sans-serif;
        }

        #update-btn:hover {
            transform: scale(105%);
            background-color: #8000ff;
            color: white;
            font-family: "Poppins", sans-serif;
        }

        #update-name,
        #update-email,
        #current-password,
        #new-password,
        #confirm-new-password {
            width: 400px;
            height: 43px;
            font-size: 16px;
            font-family: "Poppins", sans-serif;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin-bottom: 10px;
            /* Ensure space between inputs */
            transition: border-color 0.3s ease;
        }

        #update-name:focus,
        #update-email:focus,
        #current-password:focus,
        #new-password:focus,
        #confirm-new-password:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .password-update {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            width: 450px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-left: 30px;
        }

        #logout-btn {
            margin: 30px 0 20px;
            height: 43px;
            text-decoration: none;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            background-color: green;
            color: white;
            width: 10%;
            transition: 0.3s;
        }

        #logout-btn:hover {
            transform: scale(103%);
            background-color: #8000ff;
            color: white;
        }

        /* Additional styles for success/error messages */
        .success,
        .error {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        .custom-footer {
            background-color: #6a0fe0;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 40px;
        }

        /* Form separation styling */
        .profile-update {
            border: 2px solid #e9ecef;
        }

        .password-update {
            border: 2px solid #e9ecef;
        }

        .profile-update:hover,
        .password-update:hover {
            border-color: #007bff;
            transition: border-color 0.3s ease;
        }
    </style>
</head>

<body>
    <div class="profile">
        <div class="profile-container">
            <div class="profile-update">
                <h1 id="profile-heading">My Profile</h1>
                <?php if ($success): ?>
                    <div class="success"><?php echo $success; ?></div><?php endif; ?>
                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    <label for="update-name">Name:</label>
                    <input type="text" id="update-name" name="name"
                        value="<?php echo htmlspecialchars($user['name']); ?>" maxlength="12" required>
                    <label for="update-email">Email:</label>
                    <input type="email" id="update-email" name="email"
                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    <button type="submit" id="update-btn">Update Profile</button>
                </form>
            </div>

            <div class="password-update">
                <h1 id="change-password">Change Password</h1>
                <?php if ($success): ?>
                    <div class="success"><?php echo $success; ?></div><?php endif; ?>
                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="update_password" value="1">
                    <label for="current-password">Current Password:</label>
                    <input type="password" id="current-password" name="current_password" required>
                    <label for="new-password">New Password:</label>
                    <input type="password" id="new-password" name="new_password" required>
                    <label for="confirm-new-password">Confirm New Password:</label>
                    <input type="password" id="confirm-new-password" name="confirm_password" required>
                    <button type="submit" id="update-btn">Update Password</button>
                </form>
            </div>
        </div>
        <button id="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
    </div>
    <div class="custom-footer">
    <?php include('../includes/footer.php'); ?>
</div>

<script>
// Client-side form validation
document.addEventListener('DOMContentLoaded', function() {
    // Email validation function
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Profile form validation - only validates profile form
    const profileForm = document.querySelector('form input[name="update_profile"]').closest('form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const name = document.getElementById('update-name').value.trim();
            const email = document.getElementById('update-email').value.trim();
            
            if (name.length < 2) {
                alert('Name must be at least 2 characters long.');
                e.preventDefault();
                return false;
            }
            
            if (!isValidEmail(email)) {
                alert('Please enter a valid email address.');
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Password form validation - only validates password form
    const passwordForm = document.querySelector('form input[name="update_password"]').closest('form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-new-password').value;
            
            if (currentPassword.length === 0) {
                alert('Current password is required.');
                e.preventDefault();
                return false;
            }
            
            if (newPassword.length < 6) {
                alert('New password must be at least 6 characters long.');
                e.preventDefault();
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match.');
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Real-time password confirmation validation
    const confirmPasswordField = document.getElementById('confirm-new-password');
    const newPasswordField = document.getElementById('new-password');
    
    if (confirmPasswordField && newPasswordField) {
        confirmPasswordField.addEventListener('input', function() {
            if (this.value !== newPasswordField.value) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#28a745';
            }
        });
    }
});
</script>