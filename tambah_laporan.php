<?php
/**
 * TAMBAH LAPORAN - PRODUCTION READY
 * ✅ Prepared Statement (Anti SQL Injection)
 * ✅ Input Validation
 * ✅ Transaction Support
 * ✅ Error Handling
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
    $required_fields = [
        'karyawan_id', 'tanggal', 'daerah', 'toko', 
        'alamat', 'no_hp', 'barang_masuk', 'omset', 'tanggal_tagihan'
    ];
    
    $data = [];
    foreach ($required_fields as $field) {
        $value = $_POST[$field] ?? '';
        
        if (trim($value) === '') {
            echo json_encode([
                'success' => false,
                'message' => "Field '$field' wajib diisi"
            ]);
            exit;
        }
        
        $data[$field] = trim($value);
    }
    
    // ====================================
    // DATA TYPE VALIDATION
    // ====================================
    if (!is_numeric($data['karyawan_id'])) {
        throw new Exception('ID karyawan harus berupa angka');
    }
    
    if (!is_numeric($data['omset'])) {
        throw new Exception('Omset harus berupa angka');
    }
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['tanggal'])) {
        throw new Exception('Format tanggal tidak valid');
    }
    
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['tanggal_tagihan'])) {
        throw new Exception('Format tanggal tagihan tidak valid');
    }
    
    // Validate phone number
    if (!preg_match('/^[0-9+]{10,15}$/', $data['no_hp'])) {
        throw new Exception('Format nomor HP tidak valid');
    }
    
    // ====================================
    // VERIFY KARYAWAN EXISTS
    // ====================================
    $stmt_check = $koneksi->prepare(
        "SELECT id FROM users WHERE id = ? AND role = 'karyawan' LIMIT 1"
    );
    $stmt_check->bind_param("i", $data['karyawan_id']);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows === 0) {
        throw new Exception('Karyawan tidak ditemukan');
    }
    $stmt_check->close();
    
    // ====================================
    // START TRANSACTION
    // ====================================
    $koneksi->begin_transaction();
    
    try {
        // ====================================
        // INSERT LAPORAN (PREPARED STATEMENT)
        // ====================================
        $stmt = $koneksi->prepare("
            INSERT INTO laporan (
                karyawan_id, tanggal, daerah, toko, alamat,
                no_hp, barang_masuk, omset, tanggal_tagihan, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'terkirim', NOW())
        ");
        
        $stmt->bind_param(
            "issssssss",
            $data['karyawan_id'],
            $data['tanggal'],
            $data['daerah'],
            $data['toko'],
            $data['alamat'],
            $data['no_hp'],
            $data['barang_masuk'],
            $data['omset'],
            $data['tanggal_tagihan']
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan laporan: ' . $stmt->error);
        }
        
        $laporan_id = $stmt->insert_id;
        $stmt->close();
        
        // ====================================
        // COMMIT TRANSACTION
        // ====================================
        $koneksi->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Laporan berhasil disimpan',
            'data' => [
                'id' => $laporan_id,
                'status' => 'terkirim'
            ]
        ]);
        
    } catch (Exception $e) {
        $koneksi->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
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