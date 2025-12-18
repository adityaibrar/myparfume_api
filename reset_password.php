<?php
/**
 * RESET PASSWORD - FORCE UPDATE
 * Script ini akan reset password user dan test langsung
 */

header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

echo "====================================================\n";
echo "         RESET & TEST PASSWORD\n";
echo "====================================================\n\n";

// ============================================
// KONFIGURASI - EDIT DISINI
// ============================================

$reset_data = [
    [
        'username' => 'admin',
        'password' => 'admin123',
        'role' => 'admin',  // optional, untuk create jika belum ada
        'nama' => 'Administrator'
    ],
    [
        'username' => 'karyawan',
        'password' => 'karyawan123',
        'role' => 'karyawan',
        'nama' => 'Karyawan'
    ],
    // Tambahkan user lain disini
];

// ============================================
// PROSES RESET
// ============================================

foreach ($reset_data as $data) {
    echo "Processing: {$data['username']}\n";
    echo str_repeat("-", 50) . "\n";
    
    // Generate hash password baru
    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    
    echo "Password: {$data['password']}\n";
    echo "Hash: $hashed\n";
    echo "Hash length: " . strlen($hashed) . "\n";
    
    // Cek apakah user sudah ada
    $check = $koneksi->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $data['username']);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        // UPDATE password yang sudah ada
        echo "Action: UPDATE existing user\n";
        
        $update = $koneksi->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update->bind_param("ss", $hashed, $data['username']);
        
        if ($update->execute()) {
            echo "✅ Password berhasil di-update!\n";
        } else {
            echo "❌ Gagal update: " . $update->error . "\n";
            continue;
        }
    } else {
        // INSERT user baru
        echo "Action: CREATE new user\n";
        
        $insert = $koneksi->prepare(
            "INSERT INTO users (username, password, role, nama) VALUES (?, ?, ?, ?)"
        );
        $insert->bind_param(
            "ssss",
            $data['username'],
            $hashed,
            $data['role'],
            $data['nama']
        );
        
        if ($insert->execute()) {
            echo "✅ User baru berhasil dibuat!\n";
        } else {
            echo "❌ Gagal create: " . $insert->error . "\n";
            continue;
        }
    }
    
    // ============================================
    // TEST VERIFY LANGSUNG
    // ============================================
    
    echo "\nTest verify:\n";
    
    // Ambil hash dari database untuk memastikan
    $verify_stmt = $koneksi->prepare("SELECT password FROM users WHERE username = ?");
    $verify_stmt->bind_param("s", $data['username']);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $db_user = $verify_result->fetch_assoc();
    
    echo "Hash di DB: " . substr($db_user['password'], 0, 30) . "...\n";
    
    if (password_verify($data['password'], $db_user['password'])) {
        echo "✅ VERIFY SUCCESS - Password '{$data['password']}' COCOK!\n";
    } else {
        echo "❌ VERIFY FAILED - Ada masalah!\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

// ============================================
// VERIFIKASI AKHIR - CEK SEMUA USER
// ============================================

echo "\n====================================================\n";
echo "         VERIFIKASI SEMUA USER\n";
echo "====================================================\n\n";

$all = $koneksi->query(
    "SELECT id, username, password, role, LENGTH(password) as len FROM users ORDER BY id"
);

echo sprintf("%-4s %-15s %-10s %-10s %s\n", "ID", "Username", "Role", "Hash Len", "Status");
echo str_repeat("-", 70) . "\n";

while ($user = $all->fetch_assoc()) {
    $status = ($user['len'] == 60) ? "✅ OK" : "❌ ERROR";
    echo sprintf(
        "%-4s %-15s %-10s %-10s %s\n",
        $user['id'],
        $user['username'],
        $user['role'],
        $user['len'],
        $status
    );
}

echo "\n====================================================\n";
echo "              TEST LOGIN SIMULASI\n";
echo "====================================================\n\n";

// Simulasi login seperti di login.php
foreach ($reset_data as $data) {
    echo "Login attempt: {$data['username']} / {$data['password']}\n";
    
    $stmt = $koneksi->prepare(
        "SELECT id, username, password, role, nama FROM users WHERE username = ? LIMIT 1"
    );
    $stmt->bind_param("s", $data['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "❌ Username tidak ditemukan\n\n";
        continue;
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($data['password'], $user['password'])) {
        echo "❌ Password salah\n\n";
        continue;
    }
    
    unset($user['password']);
    
    echo "✅ Login berhasil!\n";
    echo "Response:\n";
    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil',
        'user' => $user
    ], JSON_PRETTY_PRINT) . "\n\n";
}

echo "====================================================\n";
echo "                 SELESAI!\n";
echo "====================================================\n\n";

echo "Silakan test login sekarang dengan:\n";
foreach ($reset_data as $data) {
    echo "- Username: {$data['username']}, Password: {$data['password']}\n";
}

$koneksi->close();
?>