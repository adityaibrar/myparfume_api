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
    
    $query = "SELECT * FROM users WHERE id = '$id' AND role = 'karyawan'";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $karyawan = mysqli_fetch_assoc($result);
        
        echo json_encode([
            "success" => true,
            "data" => $karyawan
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Karyawan tidak ditemukan"
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>