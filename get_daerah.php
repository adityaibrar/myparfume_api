<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

try {
    // Cek koneksi database
    if (!$koneksi) {
        echo json_encode([
            "success" => false,
            "message" => "Database connection failed"
        ]);
        exit;
    }

    // Query ambil semua daerah
    $query = mysqli_query($koneksi, "SELECT * FROM daerah ORDER BY nama_daerah ASC");
    
    if (!$query) {
        echo json_encode([
            "success" => false,
            "message" => "Query error: " . mysqli_error($koneksi)
        ]);
        exit;
    }

    $hasil = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $hasil[] = [
            "id" => $row['id'],
            "nama_daerah" => $row['nama_daerah']
        ];
    }

    // Debug log
    error_log("get_daerah.php: Found " . count($hasil) . " items");

    echo json_encode([
        "success" => true,
        "data" => $hasil,
        "count" => count($hasil)
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Exception: " . $e->getMessage()
    ]);
}
?>