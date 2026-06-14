<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data_dir = 'data/';

$input = json_decode(file_get_contents('php://input'), true);

$platform = isset($input['platform']) ? intval($input['platform']) : 0;
if ($platform !== 1 && $platform !== 2) {
    echo json_encode(array('success' => false, 'message' => 'Invalid platform ID.'));
    exit();
}

$jsonPath = $data_dir . 'platform' . $platform . '_object.json';
if (!file_exists($jsonPath)) {
    echo json_encode(array('success' => false, 'message' => 'Slot not found.'));
    exit();
}

$slot = json_decode(file_get_contents($jsonPath), true);

if (isset($input['scale'])) {
    $scale = floatval($input['scale']);
    $slot['scale'] = max(0.2, min(3.0, $scale));
}
if (isset($input['rotation'])) {
    $rot = intval($input['rotation']) % 360;
    if ($rot < 0) $rot += 360;
    $slot['rotation'] = $rot;
}

file_put_contents($jsonPath, json_encode($slot));
echo json_encode(array('success' => true, 'slot' => $slot));
?>
