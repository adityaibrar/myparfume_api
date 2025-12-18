<?php
/**
 * EDIT KARYAWAN - FIXED PASSWORD UPDATE
 * ✅ Prepared Statement
 * ✅ Password Optional Update (FIXED)
 * ✅ Input Validation
 * ✅ Update password di tabel users
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

require_once 'db_connect.php';

try {
    // ====================================
    // INPUT VALIDATION
    // ====================================
    $id = $_POST['id'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Debug log
    error_log("Edit Karyawan Request - ID: $id, Nama: $nama, No HP: $no_hp, Password: " . (!empty($password) ? "provided" : "empty"));

    // Required fields
    if (empty($id) || empty($nama) || empty($no_hp)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID, Nama, dan No HP wajib diisi'
        ]);
        exit;
    }

    // Validate ID
    if (!is_numeric($id)) {
        throw new Exception('ID harus berupa angka');
    }
    $id = (int)$id;

    // Validate phone number
    if (!preg_match('/^[0-9+]{10,15}$/', $no_hp)) {
        throw new Exception('Format nomor HP tidak valid (10-15 digit)');
    }

    // Validate password if provided
    if (!empty($password) && strlen($password) < 6) {
        throw new Exception('Password minimal 6 karakter');
    }

    // Validate nama length
    if (strlen($nama) < 3) {
        throw new Exception('Nama minimal 3 karakter');
    }

    // ====================================
    // CHECK IF KARYAWAN EXISTS
    // ====================================
    $stmt_check = $koneksi->prepare("
        SELECT id, nama 
        FROM users 
        WHERE id = ? AND role = 'karyawan' 
        LIMIT 1
    ");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Karyawan tidak ditemukan'
        ]);
        exit;
    }
    $stmt_check->close();

    // ====================================
    // UPDATE QUERY - FIXED
    // ====================================
    if (!empty($password)) {
        // ✅ UPDATE WITH PASSWORD
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        
        error_log("Updating with password - Hash length: " . strlen($password_hashed));
        
        $stmt = $koneksi->prepare("
            UPDATE users 
            SET nama = ?, 
                password = ?, 
                no_hp = ?
            WHERE id = ? AND role = 'karyawan'
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $koneksi->error);
        }
        
        $stmt->bind_param("sssi", $nama, $password_hashed, $no_hp, $id);
        
    } else {
        // ✅ UPDATE WITHOUT PASSWORD
        error_log("Updating without password");
        
        $stmt = $koneksi->prepare("
            UPDATE users 
            SET nama = ?, 
                no_hp = ?
            WHERE id = ? AND role = 'karyawan'
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $koneksi->error);
        }
        
        $stmt->bind_param("ssi", $nama, $no_hp, $id);
    }

    // ====================================
    // EXECUTE UPDATE
    // ====================================
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    error_log("Affected rows: $affected_rows");

    if ($affected_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak ada perubahan data'
        ]);
        exit;
    }

    // ====================================
    // VERIFY PASSWORD IF UPDATED
    // ====================================
    if (!empty($password)) {
        $verify_stmt = $koneksi->prepare("SELECT password FROM users WHERE id = ?");
        $verify_stmt->bind_param("i", $id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        $user_data = $verify_result->fetch_assoc();
        
        if (password_verify($password, $user_data['password'])) {
            error_log("✅ Password verified successfully");
        } else {
            error_log("❌ Password verification failed");
        }
        $verify_stmt->close();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Data karyawan berhasil diperbarui',
        'data' => [
            'id' => $id,
            'nama' => $nama,
            'no_hp' => $no_hp,
            'password_updated' => !empty($password)
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in edit_karyawan: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($koneksi)) {
        $koneksi->close();
    }
}
?>