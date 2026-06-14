<?php
// update_player.php - PHP 5.2.14 compatible - IMPROVED TIMEOUT LOGIC
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data_file = 'data/players.json';
$current_time = time();

// INCREASED timeout to 60 seconds (was 30)
$PLAYER_TIMEOUT = 60;

function resetWorldData() {
    error_log("=== RESET WORLD DATA CALLED ===");
    
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
    $billboard_dir = 'billboards';
    if (!is_dir($billboard_dir)) {
        if (mkdir($billboard_dir)) {
            error_log("SUCCESS: Created billboards directory");
        } else {
            error_log("ERROR: Could not create billboards directory");
        }
    }
    
    // Remove billboard images - FIXED VERSION
    error_log("Checking billboard directory: " . $billboard_dir);
    if (is_dir($billboard_dir)) {
        if ($handle = opendir($billboard_dir)) {
            $file_count = 0;
            $deleted_count = 0;
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $file_count++;
                    $full_path = $billboard_dir . '/' . $entry;  // FIXED: proper path construction
                    if (is_file($full_path)) {
                        if (unlink($full_path)) {  // FIXED: delete ALL files, no exceptions
                            $deleted_count++;
                            error_log("SUCCESS: Deleted billboard file: " . $entry);
                        } else {
                            error_log("ERROR: Failed to delete billboard file: " . $entry);
                        }
                    } else {
                        error_log("SKIPPED: " . $entry . " (not a regular file)");
                    }
                }
            }
            closedir($handle);
            error_log("Found " . $file_count . " files in billboard directory, deleted " . $deleted_count);
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

// Create data directory if it doesn't exist
if (!is_dir('data')) {
    if (mkdir('data')) {
        error_log("Created data directory");
    } else {
        error_log("ERROR: Could not create data directory");
    }
}

// Load existing players
$players = array();
if (file_exists($data_file)) {
    $json_data = file_get_contents($data_file);
    if ($json_data) {
        $decoded = json_decode($json_data, true);
        if ($decoded && is_array($decoded)) {
            $players = $decoded;
            error_log("Loaded " . count($players) . " players from file");
        } else {
            error_log("WARNING: Could not decode players JSON or result was not array");
        }
    } else {
        error_log("WARNING: Players file exists but is empty");
    }
} else {
    error_log("Players file does not exist, starting with empty array");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    error_log("POST request - Player ID: " . $id);
    
    if (isset($_POST['disconnect']) && $_POST['disconnect']) {
        // Handle disconnect
        if (isset($players[$id])) {
            unset($players[$id]);
            error_log("Player disconnected: " . $id);
        } else {
            error_log("Disconnect request for unknown player: " . $id);
        }
    } else {
        // Handle regular update
        if ($id) {
            $player_data = array();
            $player_data['id'] = $id;
            $player_data['username'] = isset($_POST['username']) ? $_POST['username'] : 'Anonymous';
            $player_data['avatarUrl'] = isset($_POST['avatarUrl']) ? $_POST['avatarUrl'] : 'default';
            
            $position = array();
            $position['x'] = floatval(isset($_POST['positionX']) ? $_POST['positionX'] : 0);
            $position['y'] = floatval(isset($_POST['positionY']) ? $_POST['positionY'] : 0);
            $position['z'] = floatval(isset($_POST['positionZ']) ? $_POST['positionZ'] : 0);
            $player_data['position'] = $position;
            
            $player_data['rotationY'] = floatval(isset($_POST['rotationY']) ? $_POST['rotationY'] : 0);
            $player_data['avatarScale'] = floatval(isset($_POST['avatarScale']) ? $_POST['avatarScale'] : 1);
            $player_data['lastSeen'] = $current_time;
            
            $players[$id] = $player_data;
            error_log("Updated player: " . $id . " (" . $player_data['username'] . ")");
        }
    }
    
    // Clean up inactive players - IMPROVED LOGGING
    $active_players = array();
    $removed_count = 0;
    foreach ($players as $player_id => $player) {
        if (isset($player['lastSeen'])) {
            $time_since_last_seen = $current_time - $player['lastSeen'];
            if ($time_since_last_seen <= $PLAYER_TIMEOUT) {
                $active_players[$player_id] = $player;
                error_log("Player " . $player_id . " is active (last seen " . $time_since_last_seen . " seconds ago)");
            } else {
                $removed_count++;
                error_log("Removed inactive player: " . $player_id . " (last seen " . $time_since_last_seen . " seconds ago - TIMEOUT!)");
            }
        } else {
            // Player has no lastSeen timestamp, remove them
            $removed_count++;
            error_log("Removed player with no lastSeen timestamp: " . $player_id);
        }
    }
    $players = $active_players;

    // Check if world is now empty BEFORE saving
    $player_count = count($players);
    error_log("Current player count after cleanup: " . $player_count . " (removed " . $removed_count . " players)");
    
    // Save the player data
    $save_result = file_put_contents($data_file, json_encode($players));
    if ($save_result !== false) {
        error_log("Saved player data - " . $save_result . " bytes written");
        
        // If no players remain, reset the world
        if ($player_count == 0) {
            error_log("*** NO PLAYERS REMAINING - RESETTING WORLD DATA ***");
            resetWorldData();
        }
        
        $response = array(
            'success' => true, 
            'message' => 'Player updated', 
            'playerCount' => $player_count
        );
        echo json_encode($response);
    } else {
        error_log("ERROR: Failed to save player data");
        $response = array('success' => false, 'message' => 'Failed to save player data');
        echo json_encode($response);
    }
} else {
    // For GET requests, just return current status but also clean up old players
    $player_count = count($players);
    error_log("GET request - initial player count: " . $player_count);
    
    // Clean up inactive players during GET requests too - WITH BETTER LOGGING
    $active_players = array();
    $removed_count = 0;
    foreach ($players as $player_id => $player) {
        if (isset($player['lastSeen'])) {
            $time_since_last_seen = $current_time - $player['lastSeen'];
            if ($time_since_last_seen <= $PLAYER_TIMEOUT) {
                $active_players[$player_id] = $player;
                error_log("GET - Player " . $player_id . " is active (last seen " . $time_since_last_seen . " seconds ago)");
            } else {
                $removed_count++;
                error_log("GET - Removed inactive player: " . $player_id . " (last seen " . $time_since_last_seen . " seconds ago - TIMEOUT!)");
            }
        } else {
            $removed_count++;
            error_log("GET - Removed player with no lastSeen: " . $player_id);
        }
    }
    $players = $active_players;
    $player_count = count($players);
    
    // Save the cleaned player list if we removed anyone
    if ($removed_count > 0) {
        $save_result = file_put_contents($data_file, json_encode($players));
        if ($save_result !== false) {
            error_log("GET request - Saved cleaned player data - " . $save_result . " bytes written, removed " . $removed_count . " players");
        } else {
            error_log("ERROR: GET request - Failed to save cleaned player data");
        }
    }
    
    // Check if world is now empty and reset if needed
    if ($player_count == 0) {
        error_log("GET request with 0 players - resetting world");
        resetWorldData();
    }
    
    $response = array(
        'success' => false, 
        'message' => 'Invalid request method', 
        'playerCount' => $player_count
    );
    echo json_encode($response);
}
?>
