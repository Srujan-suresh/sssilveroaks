<?php
$mysqli = new mysqli("localhost", "root", "", "ss_silver_oaks");

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Set max rooms per category
$roomLimits = [
    'Executive' => 3,
    'Deluxe' => 8,
    'Premium' => 6,
    'Villa' => 1

];

$availability = [];

foreach ($roomLimits as $category => $maxRooms) {
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM bookings WHERE room_category = ?");
    if (!$stmt) {
        echo json_encode(["error" => "Query preparation failed: " . $mysqli->error]);
        exit;
    }

    $stmt->bind_param("s", $category);
    if (!$stmt->execute()) {
        echo json_encode(["error" => "Execution failed: " . $stmt->error]);
        exit;
    }

    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    $left = max(0, $maxRooms - $count);
    $availability[$category] = $left;
}

// Respond with proper JSON
header('Content-Type: application/json');
echo json_encode($availability);
?>
