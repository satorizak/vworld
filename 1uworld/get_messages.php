<?php
// get_messages.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$messages_file = 'data/messages.json';

// Ensure the data directory exists
if (!is_dir('data/')) {
    mkdir('data/');
}

// Check if the messages data file exists and has content
if (file_exists($messages_file) && filesize($messages_file) > 0) {
    $messages_data = file_get_contents($messages_file);
    $decoded_data = json_decode($messages_data);

    // Check if the data is valid JSON. json_decode returns null for invalid JSON in PHP 5.2.14
    if ($decoded_data === null && strtolower($messages_data) !== 'null') {
        echo json_encode(array());
    } else {
        echo $messages_data;
    }
} else {
    // If the file doesn't exist or is empty, return an empty JSON array
    echo json_encode(array());
}
?>
