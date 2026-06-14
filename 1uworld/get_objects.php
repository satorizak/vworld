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

$result = array();

foreach (array(1, 2) as $platform) {
    $jsonPath = $data_dir . 'platform' . $platform . '_object.json';
    if (file_exists($jsonPath)) {
        $slot = json_decode(file_get_contents($jsonPath), true);
    } else {
        $slot = array('occupied' => false, 'scale' => 1.0, 'rotation' => 0);
    }

    $glbPath = $objects_dir . 'platform' . $platform . '.glb';
    if ($slot['occupied'] && !file_exists($glbPath)) {
        $slot['occupied'] = false;
        $slot['scale'] = 1.0;
        $slot['rotation'] = 0;
        file_put_contents($jsonPath, json_encode($slot));
    }

    $result[$platform] = $slot;
}

echo json_encode($result);
?>
