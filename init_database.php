<?php
require_once 'class/HtzoneApi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Helper function to output messages in JSON format
 */
function outputMessage($status, $message) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    echo "\n";
}

/**
 * Ensure database directory exists
 */
function ensureDatabaseDirectory() {
    $dbDir = __DIR__ . '/database';
    if (!is_dir($dbDir)) {
        if (!mkdir($dbDir, 0777, true)) {
            throw new Exception("Failed to create database directory");
        }
    }
    
    // Create .htaccess to prevent direct access
    $htaccess = $dbDir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Deny from all");
    }
}

/**
 * Main initialization process
 */
try {
    outputMessage('info', 'Starting database initialization...');
    
    // Ensure database directory exists
    ensureDatabaseDirectory();
    
    // Initialize API handler
    $api = new HtzoneApi();
    outputMessage('success', 'Database structure created successfully');
    
    try {
        // Fetch and store categories
        outputMessage('info', 'Fetching categories from API...');
        $api->fetchAndStoreCategories();
        outputMessage('success', 'Categories fetched and stored successfully');
        
        // Fetch and store items for each category
        outputMessage('info', 'Fetching items from API...');
        $api->fetchAndStoreItems();
        outputMessage('success', 'Items fetched and stored successfully');
        
    } catch (Exception $e) {
        throw new Exception('Data synchronization failed: ' . $e->getMessage());
    }
    
    outputMessage('success', 'Database initialization completed successfully');
    
} catch (Exception $e) {
    outputMessage('error', $e->getMessage());
    exit(1);
}

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0777, true);
    file_put_contents($logsDir . '/.htaccess', "Deny from all");
}

// Create a cron job file for periodic updates
$cronFile = __DIR__ . '/update_database.php';
if (!file_exists($cronFile)) {
    $cronContent = <<<'PHP'
<?php
require_once __DIR__ . '/class/HtzoneApi.php';

try {
    $api = new HtzoneApi();
    
    // Update categories
    $api->fetchAndStoreCategories();
    
    // Update items
    $api->fetchAndStoreItems();
    
    file_put_contents(
        __DIR__ . '/logs/cron.log',
        date('Y-m-d H:i:s') . " - Update successful\n",
        FILE_APPEND
    );
    
} catch (Exception $e) {
    file_put_contents(
        __DIR__ . '/logs/cron.log',
        date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n",
        FILE_APPEND
    );
}
PHP;
    
    file_put_contents($cronFile, $cronContent);
    outputMessage('info', 'Created update script for cron job');
}

// Output cron job instructions
outputMessage('info', 'To keep the database updated, add the following cron job:');
outputMessage('info', "0 */6 * * * php " . realpath($cronFile) . " > /dev/null 2>&1");
