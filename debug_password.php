<?php
// DEBUG_PASSWORD.PHP - File untuk cek dan fix password
// Jalankan file ini untuk debugging password issue

header('Content-Type: application/json; charset=utf-8');

require_once 'db_connect.php';

// ============================================
// BAGIAN 1: CEK PASSWORD DI DATABASE
// ============================================

echo "=== CEK PASSWORD DI DATABASE ===\n\n";

// Ambil semua user untuk dicek
$query = "SELECT id, username, password, role FROM users";
$result = $koneksi->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Username: " . $row['username'] . "\n";
        echo "Password Hash: " . $row['password'] . "\n";
        echo "Role: " . $row['role'] . "\n";
        
        // Cek apakah password sudah di-hash atau masih plain text
        if (strlen($row['password']) == 60 && strpos($row['password'], '$2y$') === 0) {
            echo "Status: ✅ Password sudah di-hash dengan benar\n";
        } else {
            echo "Status: ❌ Password PLAIN TEXT - HARUS DI-HASH!\n";
        }
        echo "-------------------\n\n";
    }
} else {
    echo "Tidak ada user di database\n";
}

// ============================================
// BAGIAN 2: TEST PASSWORD VERIFY
// ============================================

echo "\n=== TEST PASSWORD VERIFY ===\n\n";

// Ganti dengan username dan password yang mau di-test
$test_username = 'admin'; // Ganti dengan username Anda
$test_password = 'admin123'; // Ganti dengan password yang Anda coba

$stmt = $koneksi->prepare("SELECT id, username, password FROM users WHERE username = ?");
$stmt->bind_param("s", $test_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "Testing untuk username: " . $user['username'] . "\n";
    echo "Password yang dicoba: " . $test_password . "\n";
    echo "Hash di database: " . $user['password'] . "\n\n";
    
    // Test password_verify
    if (password_verify($test_password, $user['password'])) {
        echo "✅ PASSWORD COCOK!\n";
    } else {
        echo "❌ PASSWORD TIDAK COCOK!\n\n";
        echo "Kemungkinan masalah:\n";
        echo "1. Password di database masih plain text (belum di-hash)\n";
        echo "2. Password yang Anda masukkan salah\n";
        echo "3. Ada whitespace di password\n";
    }
} else {
    echo "❌ Username '$test_username' tidak ditemukan\n";
}

// ============================================
// BAGIAN 3: GENERATE PASSWORD HASH BARU
// ============================================

echo "\n\n=== GENERATE PASSWORD HASH BARU ===\n\n";

// Password yang ingin di-hash
$passwords_to_hash = [
    'admin' => 'admin123',      // username => password
    'user' => 'user123',
    'karyawan' => 'karyawan123',
];

foreach ($passwords_to_hash as $username => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Username: $username\n";
    echo "Password: $password\n";
    echo "Hash: $hash\n\n";
    
    echo "Query untuk update:\n";
    echo "UPDATE users SET password = '$hash' WHERE username = '$username';\n";
    echo "-------------------\n\n";
}

$koneksi->close();

// ============================================
// INSTRUKSI PENGGUNAAN
// ============================================

echo "\n=== CARA MENGGUNAKAN FILE INI ===\n\n";
echo "1. Edit BAGIAN 2: Ganti \$test_username dan \$test_password dengan data Anda\n";
echo "2. Edit BAGIAN 3: Tambahkan username dan password yang mau di-hash\n";
echo "3. Jalankan file ini melalui browser atau command line\n";
echo "4. Copy query UPDATE dari output dan jalankan di database\n";
echo "5. Coba login lagi\n";
?>