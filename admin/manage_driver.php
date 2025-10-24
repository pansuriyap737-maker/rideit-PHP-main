<?php
session_start();
include('admin_header.php');
include('../config.php'); // Assuming this has your database connection

?>
<?php
// Count total drivers
$totalDrivers = 0;
$drvCountRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM drivers");
if ($drvCountRes) {
	$drvCountRow = mysqli_fetch_assoc($drvCountRes);
	$totalDrivers = (int)$drvCountRow['cnt'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        .user-info-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
        }

        .manages-user-title {
            color: black;
            margin-bottom: 20px;
            font-family: "Poppins", sans-serif;
        }

        .top-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .search-user {
            padding: 10px;
            width: 250px;
            height: 50px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .search-user-btn {
            padding: 8px 15px;
            margin-left: 8px;
            background: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s ;
        }

        .search-user-btn:hover {
            transform: scale(105%);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 17px;
        }

        th {
            background-color: #007bff;
            color: white;
            font-size: 19px;
        }

        .no-users {
            text-align: center;
            color: #777;
            padding: 20px;
            font-size: 16px;
        }

        .diactivate {
            padding: 5px;
            font-size: 15px;
            border: 2px solid rgb(174, 10, 10);
            border-radius: 10px;
            background-color: white;
            color: rgb(174, 10, 10);
            transition: 0.3s;
        }

        .diactivate:hover {
            scale: 105%;
        }

        .activate {
            padding: 5px;
            font-size: 15px;
            border: 2px solid rgb(10, 174, 10);
            border-radius: 10px;
            background-color: white;
            color: rgb(10, 174, 10);
            transition: 0.3s;
        }

        .activate:hover {
            scale: 105%;
        }

        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50; /* Green for success, override with JS */
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none; /* Hidden by default */
        }

        .notification.error {
            background-color: #f44336; /* Red for error */
        }
    </style>
</head>
<body>
    <div class="user-info-container">
        <h2>Manage Driver (Total: <?php echo $totalDrivers; ?>)</h2>


        <div class="top-bar">
            <form class="search-bar" onsubmit="return false;"> 
                <input
                    type="text"
                    name="search"
                    placeholder="Search name, email, or contact"
                    id="search-input"
                    class="search-user"
                />
                <button type="button" onclick="filterUsers()" class="search-user-btn">Search</button>
            </form>
        </div>

        <table id="users-table">  <!-- ID for JS filtering -->
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>LicenseNo.</th>
                    <th>Registered Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch drivers from `drivers` table
                $res = mysqli_query($conn, "SELECT id, name, email, contact, license_no, created_at FROM drivers ORDER BY id DESC");
                if ($res && mysqli_num_rows($res) > 0) {
                    while ($row = mysqli_fetch_assoc($res)) {
                        $name = htmlspecialchars($row['name']);
                        $email = htmlspecialchars($row['email']);
                        $contact = htmlspecialchars($row['contact']);
                        $license = htmlspecialchars($row['license_no']);
                        $createdAt = isset($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '';
                        echo "<tr>";
                        echo "<td>{$name}</td>";
                        echo "<td>{$email}</td>";
                        echo "<td>{$contact}</td>";
                        echo "<td><span>Active</span></td>";
                        echo "<td>{$license}</td>";
                        echo "<td>{$createdAt}</td>";
                        echo "<td>";
                        echo "<form method='post' action='driver_status_toggle.php' style='display:inline-block;'>";
                        echo "<input type='hidden' name='id' value='" . (int)$row['id'] . "'>";
                        echo "<input type='hidden' name='new_status' value='inactive'>";
                        echo "<button type='submit' class='diactivate'>Deactivate</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='no-users'>No drivers found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Notification Div -->
    <div id="notification" class="notification"></div>

    <script>
        function filterUsers() {
            var input, filter, table, tr, td, i, j;
            input = document.getElementById("search-input");
            filter = input.value.toLowerCase();
            table = document.getElementById("users-table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) { // Start from 1 to skip header
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) { // Check columns 0, 1, 2 (Full Name, Email, Contact)
                    if (td[j] && (j === 0 || j === 1 || j === 2)) {
                        if (td[j].textContent.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }

        function updateUserStatus(userId, newStatus) {
            // Simulate API call with fetch (replace with actual endpoint)
            fetch(`/api/users/${userId}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`User has been ${newStatus === 'active' ? 'activated' : 'deactivated'} successfully!`, 'success');
                    // Reload page or update UI
                    location.reload(); // Simple reload for now
                } else {
                    showNotification(`Failed to update user status: ${data.message}`, 'error');
                }
            })
            .catch(error => {
                showNotification(`Error: ${error.message}`, 'error');
            });
        }

        function showNotification(message, type) {
            const notificationDiv = document.getElementById('notification');
            notificationDiv.textContent = message;
            notificationDiv.className = 'notification ' + (type === 'success' ? 'success' : 'error');
            notificationDiv.style.display = 'block';
            setTimeout(() => {
                notificationDiv.style.display = 'none';
            }, 4000); // Auto-hide after 4 seconds
        }
    </script>
</body>
</html>
