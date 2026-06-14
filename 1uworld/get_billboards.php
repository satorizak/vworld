<?php
// get_billboards.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$billboards_file = 'data/billboards.json';

// Ensure the data directory exists
if (!is_dir('data/')) {
    mkdir('data/');
}

// Check if the billboards data file exists and has content
if (file_exists($billboards_file) && filesize($billboards_file) > 0) {
    $billboards_data = file_get_contents($billboards_file);
    $decoded_data = json_decode($billboards_data);

    // Check if the data is valid JSON. json_decode returns null for invalid JSON in PHP 5.2.14
    if ($decoded_data === null && strtolower($billboards_data) !== 'null') {
        echo json_encode(array());
    } else {
        echo $billboards_data;
    }
} else {
    // If the file doesn't exist or is empty, return an empty JSON object
    echo json_encode(array());
}
?>
