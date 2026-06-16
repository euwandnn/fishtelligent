<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = new mysqli("shinkansen.proxy.rlwy.net", "root", "XIvzNZWrfjjaxhAjKDrIJYZJTlxnfsJu", "railway", 49967);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$sql = "SELECT temperature, ph, dissolved_oxygen, water_level, turbidity, salinity, created_at
        FROM water_quality_logs
        ORDER BY created_at DESC
        LIMIT 1";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $row['temperature']      = floatval($row['temperature']);
    $row['ph']               = floatval($row['ph']);
    $row['dissolved_oxygen'] = floatval($row['dissolved_oxygen']);
    $row['water_level']      = floatval($row['water_level']);
    $row['turbidity']        = floatval($row['turbidity']);
    $row['salinity']         = $row['salinity'] !== null ? floatval($row['salinity']) : null;
    echo json_encode($row);
} else {
    echo json_encode(["error" => "No data found"]);
}
$conn->close();
