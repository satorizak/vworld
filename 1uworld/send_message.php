<?php
// send_message.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$messages_file = 'data/messages.json';
$max_messages = 50;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderUsername = isset($_POST['senderUsername']) ? $_POST['senderUsername'] : 'Anonymous';
    $text = isset($_POST['text']) ? $_POST['text'] : '';
    
    if (empty($text)) {
        echo json_encode(array('success' => false, 'message' => 'Message cannot be empty.'));
        exit();
    }
    
    // Load existing messages or initialize an empty array
    $messages = array();
    if (file_exists($messages_file)) {
        $json_data = file_get_contents($messages_file);
        if ($json_data) {
            $messages = json_decode($json_data);
            if (!$messages) {
                $messages = array();
            }
        }
    }
    
    // PHP 5.2.14 does not support json_decode($data, true) for associative arrays
    // We can't directly use $messages[] = array(...)
    // So we'll add the new message as an object and then re-encode.
    $newMessage = new stdClass();
    $newMessage->senderUsername = $senderUsername;
    $newMessage->text = $text;
    $newMessage->timestamp = time();
    $messages[] = $newMessage;
    
    // Keep only the last N messages
    if (count($messages) > $max_messages) {
        $messages = array_slice($messages, count($messages) - $max_messages);
    }
    
    if (file_put_contents($messages_file, json_encode($messages))) {
        echo json_encode(array('success' => true, 'message' => 'Message sent.'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to save message.'));
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
}
?>
