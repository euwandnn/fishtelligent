<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = new mysqli("shinkansen.proxy.rlwy.net", "root", "XIvzNZWrfjjaxhAjKDrIJYZJTlxnfsJu", "railway", 49967);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$rawData = file_get_contents("php://input");
if (empty($rawData)) {
    http_response_code(400);
    echo json_encode(["error" => "No data received"]);
    exit;
}

$data = json_decode($rawData, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON data"]);
    exit;
}

$temperature = $data['temperature']      ?? null;
$ph          = $data['ph']               ?? null;
$oxygen      = $data['dissolved_oxygen'] ?? null;
$water_level = $data['water_level']      ?? 0;
$turbidity   = $data['turbidity']        ?? 0;
$salinity    = $data['salinity']         ?? null;

if ($temperature === null || $ph === null || $oxygen === null) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required sensor values"]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO water_quality_logs 
     (temperature, ph, dissolved_oxygen, water_level, turbidity, salinity)
     VALUES (?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param("dddddd", $temperature, $ph, $oxygen, $water_level, $turbidity, $salinity);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to insert data"]);
}

$stmt->close();
$conn->close();
