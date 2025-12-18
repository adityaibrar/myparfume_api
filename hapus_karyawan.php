<?php
/**
 * HAPUS KARYAWAN - PRODUCTION READY
 * ✅ Prepared Statement
 * ✅ Transaction Support
 * ✅ Cascade Delete
 * ✅ Input Validation
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
    
    if (empty($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID karyawan wajib diisi'
        ]);
        exit;
    }
    
    if (!is_numeric($id)) {
        throw new Exception('ID harus berupa angka');
    }
    
    $id = (int)$id;
    
    // ====================================
    // START TRANSACTION
    // ====================================
    $koneksi->begin_transaction();
    
    try {
        // ====================================
        // 1. CHECK IF KARYAWAN EXISTS
        // ====================================
        $stmt_check = $koneksi->prepare("
            SELECT id, nama, username 
            FROM users 
            WHERE id = ? AND role = 'karyawan'
            LIMIT 1
        ");
        
        if (!$stmt_check) {
            throw new Exception('Prepare check failed: ' . $koneksi->error);
        }
        
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows === 0) {
            $koneksi->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan atau bukan karyawan'
            ]);
            exit;
        }
        
        $karyawan = $result->fetch_assoc();
        $stmt_check->close();
        
        // ====================================
        // 2. COUNT LAPORAN
        // ====================================
        $stmt_count = $koneksi->prepare("
            SELECT COUNT(*) as total 
            FROM laporan 
            WHERE karyawan_id = ?
        ");
        $stmt_count->bind_param("i", $id);
        $stmt_count->execute();
        $count_result = $stmt_count->get_result();
        $laporan_count = $count_result->fetch_assoc()['total'];
        $stmt_count->close();
        
        // ====================================
        // 3. DELETE LAPORAN (CASCADE)
        // ====================================
        if ($laporan_count > 0) {
            $stmt_delete_laporan = $koneksi->prepare("
                DELETE FROM laporan WHERE karyawan_id = ?
            ");
            $stmt_delete_laporan->bind_param("i", $id);
            
            if (!$stmt_delete_laporan->execute()) {
                throw new Exception('Gagal menghapus laporan: ' . $stmt_delete_laporan->error);
            }
            $stmt_delete_laporan->close();
        }
        
        // ====================================
        // 4. DELETE KARYAWAN
        // ====================================
        $stmt_delete = $koneksi->prepare("
            DELETE FROM users 
            WHERE id = ? AND role = 'karyawan'
        ");
        
        if (!$stmt_delete) {
            throw new Exception('Prepare delete failed: ' . $koneksi->error);
        }
        
        $stmt_delete->bind_param("i", $id);
        
        if (!$stmt_delete->execute()) {
            throw new Exception('Gagal menghapus karyawan: ' . $stmt_delete->error);
        }
        
        $stmt_delete->close();
        
        // ====================================
        // COMMIT TRANSACTION
        // ====================================
        $koneksi->commit();
        
        $message = "Karyawan {$karyawan['nama']} berhasil dihapus";
        if ($laporan_count > 0) {
            $message .= " beserta {$laporan_count} laporan";
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => [
                'deleted_karyawan' => $karyawan['nama'],
                'deleted_laporan_count' => $laporan_count
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