<?php
// ========================================
// FILE 1: get_all_karyawan.php
// ========================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

try {
    // Query untuk mengambil semua karyawan (role = 'karyawan')
    $query = "SELECT id, nama,no_hp, foto, role, created_at 
              FROM users 
              WHERE role = 'karyawan' 
              ORDER BY nama ASC";
    
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        
        echo json_encode([
            "success" => true,
            "data" => $data,
            "count" => count($data)
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Gagal mengambil data: " . mysqli_error($koneksi)
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>