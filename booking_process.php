<?php
require 'db.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Step 1: Validate required fields
$required_fields = ['check_in', 'check_out', 'adult', 'child', 'name', 'phone', 'location', 'room_cat'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo "<script>alert('⚠️ Missing required fields: " . implode(', ', $missing_fields) . "'); window.history.back();</script>";
    exit;
}

// Step 2: Sanitize & format inputs

$check_in_raw   = $_POST['check_in'];
$check_out_raw  = $_POST['check_out'];
$adult          = (int)$_POST['adult'];
$child          = (int)$_POST['child'];
$name           = trim($_POST['name']);
$phone          = trim($_POST['phone']);
$location       = trim($_POST['location']);
$room_category  = $_POST['room_cat'];

$check_in  = convertToMySQLDate($check_in_raw);
$check_out = convertToMySQLDate($check_out_raw);

if (!$check_in || !$check_out) {
    echo "<script>alert('❌ Invalid date format. Use DD-MM-YY or DD-MM-YYYY.'); window.history.back();</script>";
    exit;
}

// Step 3: Define room limits
$room_limits = [
    "Executive" => 3,
    "Deluxe"    => 8,
    "Premium"   => 6,
    "Villa"   => 1

];

if (!array_key_exists($room_category, $room_limits)) {
    echo "<script>alert('❌ Invalid room category selected!'); window.history.back();</script>";
    exit;
}

// Step 4: Check overlapping bookings
$sql = "SELECT room_number FROM bookings 
        WHERE room_category = ? 
        AND check_in < ? AND check_out > ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $room_category, $check_out, $check_in);
$stmt->execute();
$result = $stmt->get_result();

$booked_rooms = [];
while ($row = $result->fetch_assoc()) {
    $booked_rooms[] = (int)$row['room_number'];
}
$stmt->close();

// Step 5: Assign available room number
$total_rooms = $room_limits[$room_category];
$available_room_number = null;

for ($i = 1; $i <= $total_rooms; $i++) {
    if (!in_array($i, $booked_rooms)) {
        $available_room_number = $i;
        break;
    }
}

if (!$available_room_number) {
    echo "<script>alert('❌ All rooms in $room_category category are booked for the selected dates.'); window.history.back();</script>";
    exit;
}

// Step 6: Insert booking
// Step 6: Insert booking
$sql = "INSERT INTO bookings 
    (check_in, check_out, adult, child, name, phone, location, room_category, room_number, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiissssi", $check_in, $check_out, $adult, $child, $name, $phone, $location, $room_category, $available_room_number);

if ($stmt->execute()) {
    $id = $stmt->insert_id; // ✅ Get the auto-incremented ID
    echo "<script>
        alert('✅ Booking confirmed!\\nRoom No. $available_room_number in $room_category category has been booked.\\nYour ID is $id');
        window.location.href = document.referrer;
    </script>";
} else {
    echo "<script>alert('❌ Booking failed: " . $stmt->error . "'); window.history.back();</script>";
}

// Step 7: Send booking confirmation email to admin
$to = 'ss@gmail.com'; // Change to your admin email
$subject = "New Booking - $room_category Room";
$message = "
<html>
<head><title>New Booking Details</title></head>
<body>
  <h2>Booking Confirmed!</h2>
  <p><strong>Name:</strong> {$name}</p>
  <p><strong>Phone:</strong> {$phone}</p>
  <p><strong>Location:</strong> {$location}</p>
  <p><strong>Check-in:</strong> {$check_in_raw}</p>
  <p><strong>Check-out:</strong> {$check_out_raw}</p>
  <p><strong>Adults:</strong> {$adult}</p>
  <p><strong>Children:</strong> {$child}</p>
  <p><strong>Room Category:</strong> {$room_category}</p>
  <p><strong>Room Number:</strong> {$available_room_number}</p>
  <p><strong>Booking ID:</strong> {$id}</p>
</body>
</html>
";

$headers  = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'From: bookings@yourdomain.com' . "\r\n"; // Use a real sender

mail($to, $subject, $message, $headers);

$stmt->close();
$conn->close();


// Utility: Convert DD-MM-YYYY or DD-MM-YY to MySQL date
function convertToMySQLDate($inputDate) {
    $formats = ['d-m-Y', 'd-m-y'];
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $inputDate);
        if ($date && $date->format($format) === $inputDate) {
            return $date->format('Y-m-d');
        }
    }
    return false;
}
?>
