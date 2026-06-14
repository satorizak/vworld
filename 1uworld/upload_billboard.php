<?php
// upload_billboard.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$billboards_file = 'data/billboards.json';
$billboards_dir = 'billboards/';
$max_file_size = 2 * 1024 * 1024; // 2MB

if (!is_dir('data/')) {
    mkdir('data/');
}
if (!is_dir($billboards_dir)) {
    mkdir($billboards_dir);
}

$current_time = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $billboardId = isset($_POST['billboardId']) ? $_POST['billboardId'] : '';
    
    if (!$billboardId) {
        echo json_encode(array('success' => false, 'message' => 'No billboard ID provided.'));
        exit();
    }
    
    if (!isset($_FILES['billboardImage'])) {
        echo json_encode(array('success' => false, 'message' => 'No image file uploaded.'));
        exit();
    }

    $image = $_FILES['billboardImage'];
    
    if ($image['size'] > $max_file_size) {
        echo json_encode(array('success' => false, 'message' => 'File size exceeds 2MB limit.'));
        exit();
    }
    
    if ($image['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(array('success' => false, 'message' => 'File upload failed with error code: ' . $image['error']));
        exit();
    }
    
    $unique_filename = 'billboard_' . $billboardId . '_' . $current_time . '_' . uniqid() . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
    $upload_path = $billboards_dir . $unique_filename;
    
    if (move_uploaded_file($image['tmp_name'], $upload_path)) {
        $billboards = array();
        if (file_exists($billboards_file)) {
            $billboards_data = file_get_contents($billboards_file);
            $billboards = json_decode($billboards_data);
            if (!$billboards) {
                $billboards = new stdClass();
            }
        } else {
            $billboards = new stdClass();
        }
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url_base = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

        $billboard_entry = new stdClass();
        $billboard_entry->id = $billboardId;
        $billboard_entry->url = $url_base . '/' . $upload_path;
        $billboard_entry->updatedAt = $current_time;
        
        $billboards->$billboardId = $billboard_entry;
        
        file_put_contents($billboards_file, json_encode($billboards));
        
        echo json_encode(array('success' => true, 'message' => 'Image uploaded successfully.'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to move uploaded file.'));
    }
    
} else {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
}
?>
