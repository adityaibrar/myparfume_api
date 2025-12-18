<?php
/**
 * TEST PASSWORD UPDATE - VERIFICATION SCRIPT
 * Script untuk test apakah password update berfungsi
 */

header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

echo "====================================================\n";
echo "    TEST PASSWORD UPDATE VERIFICATION\n";
echo "====================================================\n\n";

// ============================================
// KONFIGURASI TEST
// ============================================
$test_karyawan_id = 2; // Ganti dengan ID karyawan yang mau di-test
$test_username = 'karyawan'; // Username karyawan
$old_password = 'karyawan123'; // Password lama
$new_password = 'password_baru123'; // Password baru untuk test

echo "Test Configuration:\n";
echo "- Karyawan ID: $test_karyawan_id\n";
echo "- Username: $test_username\n";
echo "- Old Password: $old_password\n";
echo "- New Password: $new_password\n\n";

// ============================================
// STEP 1: CEK DATA KARYAWAN
// ============================================
echo "STEP 1: Cek data karyawan...\n";
echo str_repeat("-", 50) . "\n";

$stmt = $koneksi->prepare("
    SELECT id, nama, username, password, no_hp 
    FROM users 
    WHERE id = ? AND role = 'karyawan'
");
$stmt->bind_param("i", $test_karyawan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ Karyawan tidak ditemukan!\n";
    echo "Silakan ganti \$test_karyawan_id dengan ID yang valid.\n";
    exit;
}

$karyawan = $result->fetch_assoc();
echo "✅ Karyawan ditemukan:\n";
echo "   Nama: {$karyawan['nama']}\n";
echo "   Username: {$karyawan['username']}\n";
echo "   No HP: {$karyawan['no_hp']}\n";
echo "   Password Hash (preview): " . substr($karyawan['password'], 0, 30) . "...\n\n";

$old_hash = $karyawan['password'];

// ============================================
// STEP 2: VERIFY OLD PASSWORD
// ============================================
echo "STEP 2: Verify old password...\n";
echo str_repeat("-", 50) . "\n";

if (password_verify($old_password, $old_hash)) {
    echo "✅ Old password '$old_password' VALID\n\n";
} else {
    echo "⚠️ Old password '$old_password' tidak cocok\n";
    echo "Password mungkin sudah berbeda. Lanjut test update...\n\n";
}

// ============================================
// STEP 3: SIMULATE UPDATE PASSWORD
// ============================================
echo "STEP 3: Simulate password update...\n";
echo str_repeat("-", 50) . "\n";

$new_hash = password_hash($new_password, PASSWORD_DEFAULT);
echo "New password: $new_password\n";
echo "New hash: $new_hash\n";
echo "Hash length: " . strlen($new_hash) . "\n\n";

// Update password
$update_stmt = $koneksi->prepare("
    UPDATE users 
    SET password = ?, updated_at = NOW() 
    WHERE id = ? AND role = 'karyawan'
");
$update_stmt->bind_param("si", $new_hash, $test_karyawan_id);

if ($update_stmt->execute()) {
    echo "✅ Password berhasil di-update!\n";
    echo "Affected rows: " . $update_stmt->affected_rows . "\n\n";
} else {
    echo "❌ Gagal update: " . $update_stmt->error . "\n";
    exit;
}

// ============================================
// STEP 4: VERIFY NEW PASSWORD
// ============================================
echo "STEP 4: Verify new password dari database...\n";
echo str_repeat("-", 50) . "\n";

$verify_stmt = $koneksi->prepare("SELECT password FROM users WHERE id = ?");
$verify_stmt->bind_param("i", $test_karyawan_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();
$updated_user = $verify_result->fetch_assoc();

echo "Hash di DB sekarang: " . substr($updated_user['password'], 0, 30) . "...\n";

if (password_verify($new_password, $updated_user['password'])) {
    echo "✅ NEW PASSWORD VERIFIED! Update berhasil!\n\n";
} else {
    echo "❌ NEW PASSWORD VERIFICATION FAILED!\n";
    echo "Ada masalah dengan update.\n\n";
}

// ============================================
// STEP 5: TEST LOGIN DENGAN NEW PASSWORD
// ============================================
echo "STEP 5: Test login dengan new password...\n";
echo str_repeat("-", 50) . "\n";

$login_stmt = $koneksi->prepare("
    SELECT id, username, password, role, nama 
    FROM users 
    WHERE username = ? 
    LIMIT 1
");
$login_stmt->bind_param("s", $test_username);
$login_stmt->execute();
$login_result = $login_stmt->get_result();

if ($login_result->num_rows === 0) {
    echo "❌ Username tidak ditemukan\n";
} else {
    $user = $login_result->fetch_assoc();
    
    if (password_verify($new_password, $user['password'])) {
        unset($user['password']);
        echo "✅ LOGIN BERHASIL!\n";
        echo "Response:\n";
        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => $user
        ], JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "❌ LOGIN GAGAL - Password salah\n\n";
    }
}

// ============================================
// STEP 6: RESTORE OLD PASSWORD (OPTIONAL)
// ============================================
echo "STEP 6: Restore old password? (Manual)\n";
echo str_repeat("-", 50) . "\n";
echo "Jika ingin restore password lama, jalankan SQL:\n";
echo "UPDATE users SET password = '$old_hash' WHERE id = $test_karyawan_id;\n\n";

// ============================================
// SUMMARY
// ============================================
echo "====================================================\n";
echo "                   SUMMARY\n";
echo "====================================================\n\n";

echo "Test password update: ✅ SELESAI\n";
echo "Karyawan ID: $test_karyawan_id\n";
echo "New password: $new_password\n\n";

echo "Untuk menggunakan password baru:\n";
echo "1. Login dengan username: $test_username\n";
echo "2. Password: $new_password\n\n";

echo "Jika mau restore password lama, gunakan SQL di atas.\n";

$koneksi->close();
?>