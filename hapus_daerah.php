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
            "message" => "ID tidak boleh kosong"
        ]);
        exit;
    }
    
    $id = mysqli_real_escape_string($koneksi, $id);
    
    $query = "DELETE FROM daerah WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            "success" => true,
            "message" => "Daerah berhasil dihapus"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Gagal menghapus daerah"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>