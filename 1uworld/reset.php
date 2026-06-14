<?php
// manual_reset.php - Force reset the world
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function resetWorldData() {
    $results = array();
    
    // Clear players
    $players_file = 'data/players.json';
    $result = file_put_contents($players_file, json_encode(array()));
    if ($result !== false) {
        $results[] = "SUCCESS: Players cleared";
    } else {
        $results[] = "ERROR: Failed to clear players";
    }
    
    // Reset chat messages
    $messages_file = 'data/messages.json';
    $result = file_put_contents($messages_file, json_encode(array()));
    if ($result !== false) {
        $results[] = "SUCCESS: Messages cleared";
    } else {
        $results[] = "ERROR: Failed to clear messages";
    }
    
    // Remove billboard images
    $billboard_dir = 'billboards';
    $deleted_count = 0;
    if (is_dir($billboard_dir)) {
        if ($handle = opendir($billboard_dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $full_path = $billboard_dir . '/' . $entry;
                    if (is_file($full_path)) {
                        if (unlink($full_path)) {
                            $deleted_count++;
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
    $results[] = "SUCCESS: Deleted " . $deleted_count . " billboard files";
    
    // Reset billboards to default
    $billboards_file = 'data/billboards.json';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url_base = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    
    $default_data = array();
    
    $billboard1 = array();
    $billboard1['id'] = "1";
    $billboard1['url'] = $url_base . '/../1worldthings/images/default_billboard.png';
    $billboard1['updatedAt'] = time();
    
    $billboard2 = array();
    $billboard2['id'] = "2";
    $billboard2['url'] = $url_base . '/../1worldthings/images/default_billboard.png';
    $billboard2['updatedAt'] = time();
    
    $default_data["1"] = $billboard1;
    $default_data["2"] = $billboard2;
    
    $result = file_put_contents($billboards_file, json_encode($default_data));
    if ($result !== false) {
        $results[] = "SUCCESS: Billboards reset to default";
    } else {
        $results[] = "ERROR: Failed to reset billboards";
    }
    
    return $results;
}

$results = resetWorldData();

$response = array(
    'success' => true,
    'message' => 'World reset completed',
    'results' => $results
);

echo json_encode($response);
?>
