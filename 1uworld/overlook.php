<?php
// overlook.php - manages player overlook platforms
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$overlooks_file = 'data/overlooks.json';

// Load existing overlooks
$overlooks = array();
if (file_exists($overlooks_file)) {
    $json_data = file_get_contents($overlooks_file);
    if ($json_data) {
        $decoded = json_decode($json_data, true);
        if ($decoded && is_array($decoded)) {
            $overlooks = $decoded;
        }
    }
}

$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
} else {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save or update an overlook
    $playerId = isset($_POST['playerId']) ? $_POST['playerId'] : '';
    if (!$playerId) {
        echo json_encode(array('success' => false, 'message' => 'No player ID'));
        exit();
    }
    $entry = array();
    $entry['playerId'] = $playerId;
    $entry['x']    = floatval(isset($_POST['x'])    ? $_POST['x']    : 0);
    $entry['z']    = floatval(isset($_POST['z'])    ? $_POST['z']    : 0);
    $entry['topY'] = floatval(isset($_POST['topY']) ? $_POST['topY'] : 0);
    $entry['savedAt'] = time();

    $overlooks[$playerId] = $entry;

    $result = file_put_contents($overlooks_file, json_encode($overlooks));
    if ($result !== false) {
        echo json_encode(array('success' => true, 'message' => 'Overlook saved'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to save overlook'));
    }

} elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete an overlook
    $playerId = isset($_POST['playerId']) ? $_POST['playerId'] : '';
    if (!$playerId) {
        echo json_encode(array('success' => false, 'message' => 'No player ID'));
        exit();
    }
    if (isset($overlooks[$playerId])) {
        unset($overlooks[$playerId]);
        $result = file_put_contents($overlooks_file, json_encode($overlooks));
        if ($result !== false) {
            echo json_encode(array('success' => true, 'message' => 'Overlook deleted'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to delete overlook'));
        }
    } else {
        echo json_encode(array('success' => true, 'message' => 'Overlook not found (already deleted)'));
    }

} elseif ($action === 'get') {
    // Clean up overlooks for players no longer in players.json
    $players_file = 'data/players.json';
    if (file_exists($players_file)) {
        $players_data = file_get_contents($players_file);
        if ($players_data) {
            $players = json_decode($players_data, true);
            if ($players && is_array($players)) {
                $changed = false;
                foreach ($overlooks as $pid => $overlook) {
                    if (!isset($players[$pid])) {
                        unset($overlooks[$pid]);
                        $changed = true;
                    }
                }
                if ($changed) {
                    file_put_contents($overlooks_file, json_encode($overlooks));
                }
            }
        }
    }
    // Return all overlooks
    echo json_encode($overlooks);

} else {
    echo json_encode(array('success' => false, 'message' => 'Unknown action'));
}
?>
