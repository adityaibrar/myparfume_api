<?php
/**
 * TEST.PHP - File untuk test koneksi backend
 * Letakkan file ini di: C:/xampp/htdocs/myparfume_api/test.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Test 1: PHP Version
$phpVersion = phpversion();

// Test 2: Database Connection
$dbConnected = false;
$dbMessage = '';

try {
    require_once 'db_connect.php';
    
    if (isset($koneksi) && !$koneksi->connect_error) {
        $dbConnected = true;
        $dbMessage = 'Database connected successfully';
    } else {
        $dbMessage = 'Database connection failed';
    }
} catch (Exception $e) {
    $dbMessage = 'Error: ' . $e->getMessage();
}

// Test 3: Check users table
$usersCount = 0;
$usersMessage = '';

if ($dbConnected) {
    try {
        $result = $koneksi->query("SELECT COUNT(*) as total FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            $usersCount = $row['total'];
            $usersMessage = "Found $usersCount users in database";
        }
    } catch (Exception $e) {
        $usersMessage = 'Error: ' . $e->getMessage();
    }
}

// Response
echo json_encode([
    'success' => true,
    'message' => 'MyParfume API is running',
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => [
        'php' => [
            'status' => 'OK',
            'version' => $phpVersion
        ],
        'database' => [
            'status' => $dbConnected ? 'OK' : 'FAILED',
            'message' => $dbMessage
        ],
        'users_table' => [
            'status' => $usersCount > 0 ? 'OK' : 'EMPTY',
            'count' => $usersCount,
            'message' => $usersMessage
        ]
    ]
], JSON_PRETTY_PRINT);
?>