<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data_dir = 'data/';
$objects_dir = '../1worldthings/objects/';

$input = json_decode(file_get_contents('php://input'), true);

$platform = isset($input['platform']) ? intval($input['platform']) : 0;
if ($platform !== 1 && $platform !== 2) {
    echo json_encode(array('success' => false, 'message' => 'Invalid platform ID.'));
    exit();
}

$glbPath = $objects_dir . 'platform' . $platform . '.glb';
if (file_exists($glbPath)) {
    unlink($glbPath);
}

$slot = array('occupied' => false, 'scale' => 1.0, 'rotation' => 0);
file_put_contents($data_dir . 'platform' . $platform . '_object.json', json_encode($slot));

echo json_encode(array('success' => true, 'message' => 'Object deleted.'));
?>
