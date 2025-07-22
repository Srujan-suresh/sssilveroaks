<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "ss_silver_oaks";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  echo json_encode(["status" => "error", "message" => "DB connection failed"]);
  exit;
}

// Use correct field name
$room_cat = $_GET['room_cat'] ?? '';
$limit = [
  'Executive' => 3,
  'Deluxe' => 8,
  'Premium' => 6,
  'Villa' => 1

];

if (!$room_cat || !isset($limit[$room_cat])) {
  echo json_encode(["status" => "error", "message" => "Invalid room category"]);
  exit;
}

$sql = "SELECT COUNT(*) as count FROM bookings WHERE room_category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $room_cat);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['count'] < $limit[$room_cat]) {
  echo json_encode(["status" => "available"]);
} else {
  echo json_encode(["status" => "full"]);
}

$stmt->close();
$conn->close();
?>
