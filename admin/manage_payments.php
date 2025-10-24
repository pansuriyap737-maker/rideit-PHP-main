<?php
session_start();
include('../config.php'); // Assuming this has your database connection
include('admin_header.php');

// Check if completedtrip table exists
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'completedtrip'");
$useCompletedTripTable = mysqli_num_rows($tableCheck) > 0;

if ($useCompletedTripTable) {
    // Fetch from completedtrip table
    $query = "SELECT 
        ct.id as payment_id,
        ct.passenger_name,
        (SELECT name FROM drivers WHERE id = ct.driver_id) AS driver_name,
        (SELECT car_name FROM cars WHERE car_id = ct.car_id) AS car_name,
        ct.car_number_plate,
        ct.payment_mode,
        ct.amount,
        'Success' AS payment_status,
        ct.completed_at AS payment_date,
        ct.ride_datetime,
        ct.pickup,
        ct.drop_location,
        'completed' AS ride_status
    FROM completedtrip ct 
    ORDER BY ct.completed_at DESC";
    
    // Calculate total payments from completedtrip
    $total_query = "SELECT SUM(amount) as total_amount FROM completedtrip";
} else {
    // Fallback to payments table
    $query = "SELECT 
        p.payment_id,
        p.passenger_name,
        p.driver_name,
        c.car_name,
        p.car_number_plate,
        p.payment_mode,
        p.amount,
        p.payment_status,
        p.payment_date,
        p.ride_datetime,
        p.pickup,
        p.drop_location,
        p.ride_status
    FROM payments p 
    LEFT JOIN cars c ON p.car_id = c.car_id 
    WHERE p.ride_status = 'completed' 
    ORDER BY p.payment_date DESC";
    
    // Calculate total payments from payments table
    $total_query = "SELECT SUM(amount) as total_amount FROM payments WHERE ride_status = 'completed'";
}

$result = mysqli_query($conn, $query);
$payments = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
}

// Calculate total payments
$total_result = mysqli_query($conn, $total_query);
$total_amount = 0;
if ($total_result) {
    $total_row = mysqli_fetch_assoc($total_result);
    $total_amount = $total_row['total_amount'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Payments</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        .user-payments-container {
            height: 100vh;
            margin-top: 0;
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .title {
            color: #007bff;
        }

        .search-form {
            margin-bottom: 20px;
            text-align: center;
        }

        .search-input {
            padding: 8px;
            width: 300px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .search-button {
            padding: 8px 15px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 8px;
        }

        .search-button:hover {
            background-color: #0056b3;
        }

        .payments-table {
            width: 95%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            margin: 0 auto;
            font-size: 12px;
        }

        .payments-table th,
        .payments-table td {
            padding: 8px 6px;
            text-align: center;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .payments-table th {
            background-color: #007bff;
            color: white;
        }

        .no-results {
            text-align: center;
            color: #999;
            padding: 20px;
        }

       .payments-table th {
    font-size: 17px !important;
}

.payments-table td {
    font-size: 15px !important;
}

    th,
    tr{
        border: 1px solid #999;
    }


        tr{
            font-size: 16px;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="user-payments-container">
        <center>
            <h2 class="title">User Payment Details</h2>

            <form class="search-form" onsubmit="return false;">
                <input
                    type="text"
                    placeholder="Search by payment ID, passenger, driver, car, plate, amount, status, location"
                    id="search-input"
                    class="search-input"
                />
                <button type="button" onclick="filterPayments()" class="search-button">Search</button>
            </form>

            <div style="margin-bottom: 20px; text-align: center;">
                <h3 style="color: #28a745; font-size: 24px;">
                    Total Completed Trip Revenue: ₹<?php echo number_format($total_amount, 2); ?>
                </h3>
            </div>

            <table class="payments-table" id="payments-table">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Passenger Name</th>
                        <th>Driver Name</th>
                        <th>Car Name</th>
                        <th>Number Plate</th>
                        <th>Payment Mode</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Payment Date</th>
                        <th>Ride Date</th>
                        <th>Pickup Location</th>
                        <th>Drop Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                <td><?php echo htmlspecialchars($payment['passenger_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($payment['driver_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($payment['car_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($payment['car_number_plate'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($payment['payment_mode'] ?? 'N/A'); ?></td>
                                <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                <td>
                                    <span style="color: <?php echo $payment['payment_status'] === 'Success' ? '#28a745' : '#dc3545'; ?>; font-weight: bold;">
                                        <?php echo htmlspecialchars($payment['payment_status'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo $payment['ride_datetime'] ? date('Y-m-d H:i', strtotime($payment['ride_datetime'])) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($payment['pickup'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($payment['drop_location'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="no-results">No completed trips found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </center>
    </div>

    <script>
        function filterPayments() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("search-input");
            filter = input.value.toLowerCase();
            table = document.getElementById("payments-table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) { // Start from 1 to skip header
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }

        // Add real-time search functionality
        document.getElementById("search-input").addEventListener("keyup", filterPayments);
    </script>
</body>
</html>
