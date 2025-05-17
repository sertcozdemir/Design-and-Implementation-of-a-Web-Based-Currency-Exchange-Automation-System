<?php
include 'db.php';

if (!isset($_GET['currency_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'currency_id parametresi eksik']);
    exit;
}

$currency_id = intval($_GET['currency_id']);

$stmt = $conn->prepare("
    SELECT DATE(recorded_at) AS date, rate_buy, rate_sell 
    FROM currency_rates_history 
    WHERE currency_id = ? 
    ORDER BY recorded_at ASC
");
$stmt->bind_param("i", $currency_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);