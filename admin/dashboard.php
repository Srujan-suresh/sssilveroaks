<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require '../db.php';

$result = $conn->query("SELECT * FROM bookings ORDER BY created_at ASC");
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fdfbf9;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #ab8a62;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .logo {
            font-size: 1.5em;
            font-weight: bold;
        }

        .logout a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            border: 1px solid white;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .logout a:hover {
            background-color: white;
            color: #ab8a62;
        }

        h2 {
            text-align: center;
            margin-top: 30px;
            color: #ab8a62;
        }

        table {
            width: 95%;
            margin: 40px auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #f0e6dc;
            color: #3e3e3e;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
    <script>
        function deleteBooking(id, btn) {
            if (!confirm('Are you sure you want to delete this booking?')) return;

            fetch('delete_booking.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + encodeURIComponent(id)
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    const row = btn.closest('tr');
                    row.remove();
                } else {
                    alert('Failed to delete booking.');
                }
            })
            .catch(err => {
                alert('Error deleting booking.');
                console.error(err);
            });
        }
    </script>
</head>
<body>
<header>
    <div class="logo">
    <img src="../assets/newimages/final logo-01.jpg" alt="Admin Logo" height="90" width="129">
    </div>
    <div class="logout">
        <a href="logout.php">Logout</a>
    </div>
</header>


    <h2>Booking Dashboard</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Adults</th>
            <th>Children</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Location</th>
            <th>Room Category</th>
            <th>Booked At</th>
            <th>Action</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars(formatDate($row['check_in'])) ?></td>
            <td><?= htmlspecialchars(formatDate($row['check_out'])) ?></td>
            <td><?= htmlspecialchars($row['adult']) ?></td>
            <td><?= htmlspecialchars($row['child']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['location']) ?></td>
            <td><?= htmlspecialchars($row['room_category']) ?></td>
            <td><?= htmlspecialchars(formatDateTime($row['created_at'])) ?></td>
            <td>
                <button onclick="deleteBooking(<?= $row['id'] ?>, this)" class="btn btn-danger">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
$conn->close();

function formatDate($date) {
    $ts = strtotime($date);
    return $ts !== false ? date('d-m-Y', $ts) : $date;
}

function formatDateTime($datetime) {
    $ts = strtotime($datetime);
    return $ts !== false ? date('d-m-Y H:i', $ts) : $datetime;
}
?>
