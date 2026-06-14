<?php
// check_version.php - Check what version of update_player.php is actually being used
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>update_player.php Version Check</h1>";

$update_file = 'update_player.php';

if (!file_exists($update_file)) {
    echo "<p style='color: red'>❌ update_player.php does not exist!</p>";
    exit;
}

$content = file_get_contents($update_file);

echo "<h2>File Analysis</h2>";
echo "<p><strong>File size:</strong> " . strlen($content) . " bytes</p>";
echo "<p><strong>Last modified:</strong> " . date('Y-m-d H:i:s', filemtime($update_file)) . "</p>";

// Check for key indicators of the fixed version
$checks = array(
    'Has resetWorldData function' => 'function resetWorldData()',
    'Has billboard directory creation' => 'if (!is_dir($billboard_dir))',
    'Has 30-second cleanup' => '($current_time - $player[\'lastSeen\']) <= 30',
    'Calls reset on empty world' => 'resetWorldData();',
    'Has correct default URL path' => '../1worldthings/images/default_billboard.png',
    'Has billboard file deletion' => 'unlink($full_path)'
);

echo "<h2>Feature Check</h2>";
$all_good = true;

foreach ($checks as $description => $search_string) {
    echo "<p><strong>$description:</strong> ";
    if (strpos($content, $search_string) !== false) {
        echo "<span style='color: green'>✅ FOUND</span>";
    } else {
        echo "<span style='color: red'>❌ MISSING</span>";
        $all_good = false;
    }
    echo "</p>";
}

if ($all_good) {
    echo "<h2 style='color: green'>✅ Your update_player.php appears to be the FIXED version</h2>";
    
    // If it's the right version but not working, let's check the logic more closely
    echo "<h3>Detailed Logic Check</h3>";
    
    // Extract the cleanup section
    if (preg_match('/\/\/ Clean up inactive players.*?foreach.*?\}/s', $content, $matches)) {
        echo "<p><strong>Cleanup code found:</strong></p>";
        echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars($matches[0]) . "</pre>";
    }
    
    // Extract the reset trigger section
    if (preg_match('/if \(\$player_count == 0\).*?resetWorldData\(\);/s', $content, $matches)) {
        echo "<p><strong>Reset trigger code found:</strong></p>";
        echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars($matches[0]) . "</pre>";
    }
    
} else {
    echo "<h2 style='color: red'>❌ Your update_player.php is NOT the fixed version</h2>";
    echo "<p>You need to replace it with the fixed version I provided.</p>";
}

// Check if there are any syntax errors
echo "<h2>Syntax Check</h2>";
$syntax_check = shell_exec("php -l $update_file 2>&1");
if (strpos($syntax_check, 'No syntax errors') !== false) {
    echo "<p style='color: green'>✅ No PHP syntax errors</p>";
} else {
    echo "<p style='color: red'>❌ PHP syntax errors found:</p>";
    echo "<pre style='background: #ffe6e6; padding: 10px;'>" . htmlspecialchars($syntax_check) . "</pre>";
}

// Show recent error log entries related to this file
echo "<h2>Recent Error Log</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $log_lines = file($error_log);
    $recent_lines = array_slice($log_lines, -50); // Last 50 lines
    
    echo "<p><strong>Checking last 50 lines of error log for update_player.php related entries...</strong></p>";
    $found_errors = false;
    foreach ($recent_lines as $line) {
        if (stripos($line, 'update_player') !== false || stripos($line, 'reset') !== false) {
            echo "<p style='background: #ffe6e6; padding: 5px;'>" . htmlspecialchars($line) . "</p>";
            $found_errors = true;
        }
    }
    
    if (!$found_errors) {
        echo "<p style='color: green'>✅ No recent errors related to update_player.php found</p>";
    }
} else {
    echo "<p>Error log not accessible or configured</p>";
}

// Test if the file can be executed
echo "<h2>Execution Test</h2>";
echo "<p>Testing if update_player.php can handle a GET request...</p>";

$test_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/update_player.php';
echo "<p><strong>Test URL:</strong> <a href='$test_url' target='_blank'>$test_url</a></p>";

$context = stream_context_create(array('http' => array('timeout' => 5)));
$response = @file_get_contents($test_url, false, $context);

if ($response !== false) {
    echo "<p style='color: green'>✅ File is accessible and returns response</p>";
    echo "<p><strong>Response:</strong> <code>" . htmlspecialchars($response) . "</code></p>";
} else {
    echo "<p style='color: red'>❌ File is not accessible or returned error</p>";
}
?>
