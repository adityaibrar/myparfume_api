<?php
/**
 * GENERATE PASSWORD HASH
 * 
 * Script simpel untuk generate password hash yang bisa langsung
 * di-copy paste ke database
 */

header('Content-Type: text/plain; charset=utf-8');

echo "================================================\n";
echo "     PASSWORD HASH GENERATOR\n";
echo "================================================\n\n";

// ============================================
// EDIT PASSWORD DISINI
// ============================================

$passwords_to_generate = [
    'admin' => 'admin123',          // Format: username => password
    'karyawan' => 'karyawan123',
    'user' => 'user123',
    'manager' => 'manager123',
    // Tambahkan lebih banyak user disini...
];

// ============================================
// GENERATE HASH
// ============================================

echo "=== PASSWORD HASH ===\n\n";

foreach ($passwords_to_generate as $username => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "Username: $username\n";
    echo "Password: $password\n";
    echo "Hash: $hash\n";
    echo "\nSQL Query:\n";
    echo "UPDATE users SET password = '$hash' WHERE username = '$username';\n";
    echo "\n-------------------------------------------\n\n";
}

// ============================================
// CARA PENGGUNAAN
// ============================================

echo "\n=== CARA MENGGUNAKAN ===\n\n";
echo "1. Edit bagian 'EDIT PASSWORD DISINI' di atas\n";
echo "2. Tambahkan username dan password yang mau di-hash\n";
echo "3. Jalankan file ini:\n";
echo "   - Via browser: http://yourserver.com/generate_hash.php\n";
echo "   - Via command line: php generate_hash.php\n";
echo "4. Copy SQL Query dan jalankan di database\n";
echo "5. Test login dengan username dan password yang sudah di-generate\n\n";

// ============================================
// VERIFIKASI
// ============================================

echo "=== VERIFIKASI (Testing) ===\n\n";

// Test bahwa hash bisa di-verify
foreach ($passwords_to_generate as $username => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $is_valid = password_verify($password, $hash);
    
    echo "Username: $username\n";
    echo "Status: " . ($is_valid ? "✅ Hash VALID" : "❌ Hash INVALID") . "\n\n";
}

echo "\n================================================\n";
echo "Jika semua status ✅ VALID, hash siap digunakan!\n";
echo "================================================\n";

?>