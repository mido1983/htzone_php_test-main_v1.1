<?php
require_once 'class/HtzoneApi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function outputMessage($status, $message) {
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
}

try {
    $api = new HtzoneApi();

    try {
        $api->initDatabase();
        outputMessage('success', 'Database initialized successfully');
    } catch (Exception $e) {
        throw new Exception('Database initialization failed: ' . $e->getMessage());
    }
    
    try {
        $api->fetchAndStoreCategories();
        outputMessage('success', 'Categories fetched and stored successfully');
    } catch (Exception $e) {
        throw new Exception('Category fetch failed: ' . $e->getMessage());
    }
    
    try {
        $api->fetchAndStoreItems();
        outputMessage('success', 'Items fetched and stored successfully');
    } catch (Exception $e) {
        throw new Exception('Items fetch failed: ' . $e->getMessage());
    }
    
    outputMessage('success', 'Database initialization completed successfully');
    
} catch (Exception $e) {
    outputMessage('error', $e->getMessage());
}
