<?php
/**
 * DEBUG PASSWORD HASH - DEEP ANALYSIS
 * Script ini akan mengecek detail password di database
 */

header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

echo "====================================================\n";
echo "      DEBUG PASSWORD - DEEP ANALYSIS\n";
echo "====================================================\n\n";

// Username yang mau dicek
$test_username = 'admin';
$test_password = 'admin123'; // Password yang Anda coba

echo "Testing untuk username: $test_username\n";
echo "Password yang dicoba: $test_password\n\n";

// Ambil data user dari database
$stmt = $koneksi->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $test_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ ERROR: Username '$test_username' tidak ditemukan di database!\n";
    exit;
}

$user = $result->fetch_assoc();

echo "=== DATA USER DI DATABASE ===\n";
echo "ID: " . $user['id'] . "\n";
echo "Username: " . $user['username'] . "\n";
echo "Role: " . $user['role'] . "\n";
echo "Password Hash: " . $user['password'] . "\n\n";

// Analisis detail hash
echo "=== ANALISIS HASH ===\n";
echo "Panjang hash: " . strlen($user['password']) . " karakter\n";
echo "Preview: " . substr($user['password'], 0, 20) . "...\n";
echo "Starts with: " . substr($user['password'], 0, 4) . "\n";

// Cek apakah hash valid
if (strlen($user['password']) == 60 && preg_match('/^\$2[ayb]\$/', $user['password'])) {
    echo "✅ Format hash VALID (bcrypt)\n\n";
} else {
    echo "❌ Format hash TIDAK VALID!\n";
    if (strlen($user['password']) < 60) {
        echo "   Problem: Hash terlalu pendek (mungkin terpotong)\n";
    }
    if (!preg_match('/^\$2[ayb]\$/', $user['password'])) {
        echo "   Problem: Bukan bcrypt hash atau plain text\n";
    }
    echo "\n";
}

// Test password_verify
echo "=== TEST PASSWORD_VERIFY ===\n";
echo "Testing: password_verify('$test_password', hash_dari_database)\n";

$verify_result = password_verify($test_password, $user['password']);

if ($verify_result) {
    echo "✅ PASSWORD COCOK!\n\n";
    echo "Kesimpulan: Password di database benar, ada masalah di endpoint login.php\n";
} else {
    echo "❌ PASSWORD TIDAK COCOK!\n\n";
    
    echo "=== DIAGNOSIS ===\n";
    
    // Test dengan berbagai variasi
    $variations = [
        $test_password,
        trim($test_password),
        strtolower($test_password),
        strtoupper($test_password),
        $test_password . ' ', // dengan spasi
        ' ' . $test_password,
    ];
    
    echo "Mencoba berbagai variasi password:\n\n";
    $found_match = false;
    
    foreach ($variations as $i => $var_pass) {
        $test = password_verify($var_pass, $user['password']);
        $display = str_replace(' ', '␣', $var_pass); // Tampilkan spasi
        
        echo ($i + 1) . ". Testing: '$display'\n";
        if ($test) {
            echo "   ✅ COCOK! Password sebenarnya: '$display'\n";
            $found_match = true;
        } else {
            echo "   ❌ Tidak cocok\n";
        }
    }
    
    if (!$found_match) {
        echo "\n=== KEMUNGKINAN PENYEBAB ===\n";
        echo "1. Password yang Anda coba ('$test_password') memang salah\n";
        echo "2. Hash di database rusak atau tidak valid\n";
        echo "3. Password asli yang di-hash bukan '$test_password'\n\n";
        
        echo "=== SOLUSI ===\n";
        echo "Generate hash baru untuk password yang benar:\n\n";
        
        // Generate hash baru
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "Password: $test_password\n";
        echo "Hash baru: $new_hash\n\n";
        
        echo "SQL untuk update:\n";
        echo "UPDATE users SET password = '$new_hash' WHERE username = '$test_username';\n\n";
        
        // Verify hash baru
        if (password_verify($test_password, $new_hash)) {
            echo "✅ Hash baru sudah di-verify dan VALID!\n";
        }
    }
}

// Test hash yang lain juga kalau ada
echo "\n=== CEK SEMUA USER ===\n";
$all_users = $koneksi->query("SELECT username, password, LENGTH(password) as len FROM users");
while ($u = $all_users->fetch_assoc()) {
    echo "\nUsername: " . $u['username'] . "\n";
    echo "Hash length: " . $u['len'] . "\n";
    echo "Hash preview: " . substr($u['password'], 0, 20) . "...\n";
    
    if ($u['len'] == 60) {
        echo "Status: ✅ OK\n";
    } else {
        echo "Status: ❌ PROBLEM - Hash terpotong atau salah!\n";
    }
}

echo "\n====================================================\n";
echo "                   SELESAI\n";
echo "====================================================\n";

$koneksi->close();
?>