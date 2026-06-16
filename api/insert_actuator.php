<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("shinkansen.proxy.rlwy.net", "root", "XIvzNZWrfjjaxhAjKDrIJYZJTlxnfsJu", "railway", 49967);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(["error" => "No data received"]);
    exit;
}

$pump    = $data['pump']    ?? 0;
$drain   = $data['drain']   ?? 0;
$heater  = $data['heater']  ?? 0;
$ph_down = $data['ph_down'] ?? 0;
$ph_up   = $data['ph_up']   ?? 0;
$aerator = $data['aerator'] ?? 0;

$stmt = $conn->prepare("INSERT INTO actuator_status (pump, drain, heater, ph_down, ph_up, aerator) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiiiii", $pump, $drain, $heater, $ph_down, $ph_up, $aerator);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok", "message" => "Actuator status saved"]);
} else {
    echo json_encode(["error" => $stmt->error]);
}

$stmt->close();
$conn->close();
