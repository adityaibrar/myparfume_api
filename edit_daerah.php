<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

try {
    $id = $_POST['id'] ?? '';
    $nama_daerah = $_POST['nama_daerah'] ?? '';

    if (empty($id) || empty($nama_daerah)) {
        echo json_encode([
            "success" => false,
            "message" => "ID dan nama daerah harus diisi"
        ]);
        exit;
    }

    $id = mysqli_real_escape_string($koneksi, $id);
    $nama_daerah = mysqli_real_escape_string($koneksi, trim($nama_daerah));

    // Cek duplikat (exclude id ini)
    $cek = mysqli_query($koneksi, "SELECT id FROM daerah WHERE nama_daerah = '$nama_daerah' AND id != '$id'");
    if (mysqli_num_rows($cek) > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Nama daerah sudah digunakan"
        ]);
        exit;
    }

    $query = "UPDATE daerah SET nama_daerah = '$nama_daerah' WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo json_encode([
            "success" => true,
            "message" => "Daerah berhasil diupdate"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Gagal mengupdate daerah"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
