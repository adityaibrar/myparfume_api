<?php
// FIX: Tambahkan error reporting untuk debugging (matikan di production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set ke 0 di production
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// FIX: Tambahkan pengecekan method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// FIX: Gunakan try-catch untuk error handling
try {
    require_once 'db_connect.php';
    
    // FIX: Tambahkan pengecekan koneksi database
    if (!isset($koneksi) || $koneksi->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // FIX: Trim whitespace
    $username = trim($username);
    $password = trim($password);
    
    if ($username === '' || $password === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Username dan password wajib diisi'
        ]);
        exit;
    }
    
    // FIX: Tambahkan pengecekan panjang input
    if (strlen($username) > 50) {
        echo json_encode([
            'success' => false,
            'message' => 'Username terlalu panjang'
        ]);
        exit;
    }
    
    $stmt = $koneksi->prepare(
        "SELECT id, username, `password`, role, nama 
        FROM users 
        WHERE username = ? 
        LIMIT 1"
    );
    
    // FIX: Tambahkan pengecekan prepare statement
    if (!$stmt) {
        throw new Exception('Prepare statement failed: ' . $koneksi->error);
    }
    
    $stmt->bind_param("s", $username);
    
    // FIX: Tambahkan pengecekan eksekusi
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username tidak terdaftar'
        ]);
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // FIX: Tambahkan pengecekan password_verify
    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Password salah'
        ]);
        exit;
    }
    
    // Remove password from response
    unset($user['password']);
    
    // FIX: Log successful login (optional)
    // error_log("User logged in: " . $user['username']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil',
        'user' => $user
    ]);
    
    $stmt->close();
    $koneksi->close();
    
} catch (Exception $e) {
    // FIX: Log error untuk debugging
    error_log('Login error: ' . $e->getMessage());
    
    // FIX: Kirim response error yang user-friendly
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan server. Silakan coba lagi.'
    ]);
}