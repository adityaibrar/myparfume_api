<?php
// ========================================
// FILE 4: update_status_laporan.php
// ========================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

try {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if (empty($id) || empty($status)) {
        echo json_encode([
            "success" => false,
            "message" => "ID dan status harus diisi"
        ]);
        exit;
    }
    
    $id = mysqli_real_escape_string($koneksi, $id);
    $status = mysqli_real_escape_string($koneksi, $status);
    
    // Validasi status
    $valid_status = ['pending', 'terkirim', 'approved', 'selesai', 'rejected', 'ditolak'];
    if (!in_array(strtolower($status), $valid_status)) {
        echo json_encode([
            "success" => false,
            "message" => "Status tidak valid"
        ]);
        exit;
    }
    
    $query = "UPDATE laporan 
              SET status = '$status', 
                  updated_at = NOW() 
              WHERE id = '$id'";
    
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            "success" => true,
            "message" => "Status berhasil diupdate"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Gagal update status: " . mysqli_error($koneksi)
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>