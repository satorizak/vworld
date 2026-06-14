<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$max_file_size = 8.2 * 1024 * 1024; // 8.2MB
$objects_dir = '../1worldthings/objects/';
$data_dir = 'data/';

if (!is_dir($data_dir)) {
    mkdir($data_dir);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_platform = isset($_POST['platform']) ? $_POST['platform'] : 'NOT SET';
    $platform = intval($raw_platform);

    if ($platform !== 1 && $platform !== 2) {
        echo json_encode(array('success' => false, 'message' => 'Invalid platform ID. Raw value: ' . $raw_platform . ' Intval: ' . $platform . ' POST keys: ' . implode(',', array_keys($_POST))));
        exit();
    }

    if (!isset($_FILES['glbFile'])) {
        echo json_encode(array('success' => false, 'message' => 'No file uploaded.'));
        exit();
    }

    $file = $_FILES['glbFile'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(array('success' => false, 'message' => 'Upload error code: ' . $file['error']));
        exit();
    }

    if ($file['size'] > $max_file_size) {
        echo json_encode(array('success' => false, 'message' => 'File exceeds 8.2MB limit.'));
        exit();
    }

    $filename = 'platform' . $platform . '.glb';
    $dest = $objects_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        $slot = array('occupied' => true, 'scale' => 1.0, 'rotation' => 0);
        file_put_contents($data_dir . 'platform' . $platform . '_object.json', json_encode($slot));
        echo json_encode(array('success' => true, 'message' => 'GLB uploaded successfully.'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to move uploaded file. Dir: ' . $objects_dir . ' Exists: ' . (is_dir($objects_dir) ? 'yes' : 'no') . ' Writable: ' . (is_writable($objects_dir) ? 'yes' : 'no')));
    }

} else {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
}
?>
