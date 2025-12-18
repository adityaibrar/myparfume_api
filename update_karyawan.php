<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

try {
    $id = $_POST['id'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    
    if (empty($id) || empty($nama) || empty($no_hp)) {
        echo json_encode([
            "success" => false,
            "message" => "ID, Nama, dan No HP wajib diisi"
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
    $nama = mysqli_real_escape_string($koneksi, $nama);
    $no_hp = mysqli_real_escape_string($koneksi, $no_hp);
    $alamat = mysqli_real_escape_string($koneksi, $alamat);
    
    $query = "UPDATE users SET 
                nama = '$nama',
                no_telp = '$no_hp',
                alamat = '$alamat',
                updated_at = NOW()
              WHERE id = '$id' AND role = 'karyawan'";
    
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        if (mysqli_affected_rows($koneksi) > 0) {
            echo json_encode([
                "success" => true,
                "message" => "Data karyawan berhasil diupdate"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Tidak ada perubahan data atau karyawan tidak ditemukan"
            ]);
        }
    } else {
        $error = mysqli_error($koneksi);
        echo json_encode([
            "success" => false,
            "message" => "Gagal update: $error"
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>