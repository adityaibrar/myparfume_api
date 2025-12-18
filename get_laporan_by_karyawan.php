<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

try {
    $karyawan_id = $_POST['karyawan_id'] ?? '';

    if (empty($karyawan_id)) {
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

    $karyawan_id = mysqli_real_escape_string($koneksi, $karyawan_id);

    // Query untuk mengambil semua laporan berdasarkan karyawan_id
    $query = "SELECT 
                id,
                karyawan_id,
                tanggal,
                daerah,
                toko,
                alamat,
                no_hp,
                barang_masuk,
                omset,
                tanggal_tagihan,
                status,
                created_at
              FROM laporan 
              WHERE karyawan_id = '$karyawan_id'
              ORDER BY daerah ASC, tanggal DESC";

    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $laporan = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $laporan[] = [
                'id' => $row['id'],
                'karyawan_id' => $row['karyawan_id'],
                'tanggal' => $row['tanggal'],
                'daerah' => $row['daerah'],
                'toko' => $row['toko'],
                'alamat' => $row['alamat'],
                'no_hp' => $row['no_hp'],
                'barang_masuk' => $row['barang_masuk'],
                'omset' => $row['omset'],
                'tanggal_tagihan' => $row['tanggal_tagihan'],
                'status' => $row['status'],
                'created_at' => $row['created_at']
            ];
        }

        echo json_encode([
            "success" => true,
            "message" => "Data berhasil diambil",
            "data" => $laporan,
            "count" => count($laporan)
        ]);
    } else {
        $error = mysqli_error($koneksi);
        echo json_encode([
            "success" => false,
            "message" => "Query failed: $error"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>