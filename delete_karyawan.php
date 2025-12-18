<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

try {
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode([
            "success" => false,
            "message" => "ID karyawan tidak ditemukan"
        ]);
        exit;
    }
    
    if (!$koneksi) {
        echo json_encode([
            "success" => false,
            "message" => "Database connection failed"
        ]);
        exit;
    }
    
    $id = mysqli_real_escape_string($koneksi, $id);
    
    // Hapus karyawan
    $query = "DELETE FROM users WHERE id = '$id' AND role = 'karyawan'";
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        if (mysqli_affected_rows($koneksi) > 0) {
            echo json_encode([
                "success" => true,
                "message" => "Karyawan berhasil dihapus"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Karyawan tidak ditemukan atau sudah dihapus"
            ]);
        }
    } else {
        $error = mysqli_error($koneksi);
        echo json_encode([
            "success" => false,
            "message" => "Gagal menghapus: $error"
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>
