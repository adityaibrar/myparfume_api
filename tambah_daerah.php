<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

try {
    $nama_daerah = $_POST['nama_daerah'] ?? '';
    
    if (empty($nama_daerah)) {
        echo json_encode([
            "success" => false,
            "message" => "Nama daerah tidak boleh kosong"
        ]);
        exit;
    }
    
    $nama_daerah = mysqli_real_escape_string($koneksi, trim($nama_daerah));
    
    // Cek duplikat
    $check = mysqli_query($koneksi, "SELECT id FROM daerah WHERE nama_daerah = '$nama_daerah'");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Daerah sudah ada"
        ]);
        exit;
    }
    
    $query = "INSERT INTO daerah (nama_daerah) VALUES ('$nama_daerah')";
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        echo json_encode([
            "success" => true,
            "message" => "Daerah berhasil ditambahkan"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Gagal menambahkan daerah"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>