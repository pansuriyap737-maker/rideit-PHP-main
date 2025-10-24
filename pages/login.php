<?php
session_start();
include('../config.php');


$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $loginType = isset($_POST['login_type']) ? trim($_POST['login_type']) : '';

    // Helper: check if a table exists in current database
    function table_exists($conn, $tableName) {
        $t = mysqli_real_escape_string($conn, $tableName);
        $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$t'";
        $res = mysqli_query($conn, $sql);
        return $res && mysqli_num_rows($res) > 0;
    }

    if ($loginType === 'pessenger') {
        // Prefer 'pessanger' table if present; otherwise fallback to users with role 'user'
        if (table_exists($conn, 'pessanger')) {
            $sql = "SELECT * FROM pessanger WHERE email = '$email'";
            $res = mysqli_query($conn, $sql);
            if ($res && mysqli_num_rows($res) == 1) {
                $row = mysqli_fetch_assoc($res);
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = isset($row['id']) ? $row['id'] : $row['user_id'];
                    $_SESSION['user_role'] = 'user';
                    $_SESSION['user_name'] = $row['name'];
                    header("Location: user_dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "User not found.";
            }
        } else {
            // Fallback to users table (role user)
            $sql_user = "SELECT * FROM users WHERE email = '$email' AND role = 'user'";
            $result_user = mysqli_query($conn, $sql_user);
            if ($result_user && mysqli_num_rows($result_user) == 1) {
                $user = mysqli_fetch_assoc($result_user);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['name'];
                    header("Location: user_dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "User not found.";
            }
        }
    } else if ($loginType === 'driver') {
        // Driver login against `drivers` table
        if (table_exists($conn, 'drivers')) {
            $sql = "SELECT * FROM drivers WHERE email = '$email'";
            $res = mysqli_query($conn, $sql);
            if ($res && mysqli_num_rows($res) == 1) {
                $row = mysqli_fetch_assoc($res);
                if (password_verify($password, $row['password'])) {
                    $_SESSION['driver_id'] = $row['id'];
                    $_SESSION['driver_name'] = $row['name'];
                    header("Location: /rideit/driver/add_trip.php");
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "Driver not found.";
            }
        } else {
            $error = "Drivers module not initialized.";
        }
    } else {
        // Original admin + general users flow
        $sql_admin = "SELECT * FROM admin WHERE email = '$email'";
        $result_admin = mysqli_query($conn, $sql_admin);

        if ($result_admin && mysqli_num_rows($result_admin) == 1) {
            $admin = mysqli_fetch_assoc($result_admin);
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];

                header("Location: ../admin/admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $sql_user = "SELECT * FROM users WHERE email = '$email'";
            $result_user = mysqli_query($conn, $sql_user);

            if ($result_user && mysqli_num_rows($result_user) == 1) {
                $user = mysqli_fetch_assoc($result_user);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['name'];

                    if ($user['role'] === 'admin') {
                        header("Location: ../admin/admin_dashboard.php");
                    } else {
                        header("Location: user_dashboard.php");
                    }
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "User not found.";
            }
        }
    }
}
?>
<?php include('../includes/header.php'); ?>

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
        margin-bottom: 5px;
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

    .register-link {
        margin-top: 15px;
        text-align: center;
    }
</style>

<main>
    <div class="login-container">
        <h2 style="text-align: center; margin-bottom : 20px;">Login</h2>

        <?php if ($error): ?>
            <div style="color: red; text-align: center; margin-bottom: 10px;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="margin-bottom: 15px;">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required class="form-control" placeholder="Enter your Email">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="password">Password:</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required class="form-control" placeholder="Enter your Password">
                </div>
            </div>

            <button type="submit" name="login_type" value="pessenger" class="submit-btn pessenger">Login</button>
            <p style=" margin: 10px; text-align: center; ">Or</p>
            <button type="submit" name="login_type" value="driver" class="submit-btn" class="driver">Login as a Driver</button>
        </form>

        <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</main>

<script>
    function togglePassword() {
        const pwd = document.getElementById('password');
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
    }
</script>

<?php include('../includes/footer.php'); ?>
