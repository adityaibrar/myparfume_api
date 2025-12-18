<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

try {
    $laporan_id = $_POST['id'] ?? '';

    if (empty($laporan_id)) {
        echo json_encode([
            "success" => false,
            "message" => "ID laporan tidak ditemukan"
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

    $laporan_id = mysqli_real_escape_string($koneksi, $laporan_id);

    // Query dengan JOIN ke tabel users untuk data karyawan
    $query = "SELECT 
                l.id,
                l.karyawan_id,
                l.tanggal,
                l.daerah,
                l.toko,
                l.alamat,
                l.no_hp,
                l.barang_masuk,
                l.omset,
                l.tanggal_tagihan,
                l.status,
                l.created_at,
                u.nama as nama_karyawan,
                u.username as username_karyawan,
                u.no_hp as no_hp_karyawan
              FROM laporan l
              LEFT JOIN users u ON l.karyawan_id = u.id
              WHERE l.id = '$laporan_id'";

    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        echo json_encode([
            "success" => true,
            "message" => "Data berhasil diambil",
            "data" => [
                'id' => $row['id'],
                'karyawan_id' => $row['karyawan_id'],
                'nama_karyawan' => $row['nama_karyawan'],
                'username_karyawan' => $row['username_karyawan'],
                'no_hp_karyawan' => $row['no_hp_karyawan'],
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
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Laporan tidak ditemukan"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>