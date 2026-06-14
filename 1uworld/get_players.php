<?php
// get_players.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data_file = 'data/players.json';
$current_time = time();

// Include the same reset function from update_player.php
function resetWorldData() {
    error_log("=== RESET WORLD DATA CALLED FROM GET_PLAYERS ===");
    
    // Reset chat messages
    $messages_file = 'data/messages.json';
    if (file_exists($messages_file)) {
        $result = file_put_contents($messages_file, json_encode(array()));
        if ($result !== false) {
            error_log("SUCCESS: Chat messages reset - wrote " . $result . " bytes");
        } else {
            error_log("ERROR: Failed to reset chat messages");
        }
    } else {
        error_log("WARNING: Messages file does not exist: " . $messages_file);
        // Try to create it
        $result = file_put_contents($messages_file, json_encode(array()));
        if ($result !== false) {
            error_log("SUCCESS: Created messages file");
        } else {
            error_log("ERROR: Could not create messages file");
        }
    }
    
    // Create billboards directory if it doesn't exist
    $billboard_dir = 'billboards/';
    if (!is_dir($billboard_dir)) {
        if (mkdir($billboard_dir)) {
            error_log("SUCCESS: Created billboards directory");
        } else {
            error_log("ERROR: Could not create billboards directory");
        }
    }
    
    // Remove billboard images - PHP 5.2 compatible way
    error_log("Checking billboard directory: " . $billboard_dir);
    if (is_dir($billboard_dir)) {
        if ($handle = opendir($billboard_dir)) {
            $file_count = 0;
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $file_count++;
                    $full_path = $billboard_dir . $entry;
                    if (is_file($full_path)) {
                        if (unlink($full_path)) {
                            error_log("SUCCESS: Deleted billboard file: " . $entry);
                        } else {
                            error_log("ERROR: Failed to delete billboard file: " . $entry);
                        }
                    } else {
                        error_log("SKIPPED: " . $entry . " (not a file)");
                    }
                }
            }
            closedir($handle);
            error_log("Found " . $file_count . " files in billboard directory");
        } else {
            error_log("ERROR: Could not open billboard directory");
        }
    } else {
        error_log("ERROR: Billboard directory does not exist: " . $billboard_dir);
    }
    
    // Reset billboards to default
    $billboards_file = 'data/billboards.json';
    error_log("Resetting billboards file: " . $billboards_file);
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url_base = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    
    error_log("URL base for billboards: " . $url_base);
    
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
    
    $json_result = json_encode($default_data);
    error_log("Billboard JSON to write: " . $json_result);
    
    $write_result = file_put_contents($billboards_file, $json_result);
    if ($write_result !== false) {
        error_log("SUCCESS: Billboards reset to default - wrote " . $write_result . " bytes");
    } else {
        error_log("ERROR: Failed to reset billboards file");
    }
    
    error_log("=== RESET WORLD DATA COMPLETE ===");
}

// Load player data
$players = array();
if (file_exists($data_file)) {
    $json_data = file_get_contents($data_file);
    if ($json_data) {
        $players = json_decode($json_data, true);
        if (!$players) {
            $players = array();
        }
    }
}

// Filter out offline players (older than 30 seconds)
$active_players = array();
$removed_count = 0;
foreach ($players as $id => $player) {
    if (isset($player['lastSeen']) && ($current_time - $player['lastSeen']) <= 12) {
        $active_players[$id] = $player;
    } else {
        $removed_count++;
        error_log("get_players.php - Removed inactive player: " . $id);
    }
}

// Save cleaned up player data if we removed anyone
if ($removed_count > 0) {
    $save_result = file_put_contents($data_file, json_encode($active_players));
    if ($save_result !== false) {
        error_log("get_players.php - Saved cleaned player data - removed " . $removed_count . " players");
    } else {
        error_log("ERROR: get_players.php - Failed to save cleaned player data");
    }
}

// If no players left after cleanup, trigger reset
if (empty($active_players) && !empty($players)) {
    error_log("get_players.php - No active players remaining, triggering reset");
    resetWorldData();
}

echo json_encode($active_players);
?>
