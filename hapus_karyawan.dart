<?php
// ====================================================================
// FILE: hapus_karyawan.php
// Path: C:\xampp\htdocs\myparfume_api\hapus_karyawan.php
// ====================================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

try {
    $id = $_POST['id'] ?? '';
    
    // Validasi
    if (empty($id)) {
        echo json_encode([
            "success" => false,
            "message" => "ID karyawan wajib diisi"
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
    
    // Escape string
    $id = mysqli_real_escape_string($koneksi, $id);
    
    // Cek apakah karyawan ada
    $checkQuery = "SELECT * FROM users WHERE id = '$id'";
    $checkResult = mysqli_query($koneksi, $checkQuery);
    
    if (mysqli_num_rows($checkResult) == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Karyawan tidak ditemukan"
        ]);
        exit;
    }
    
    // Hapus karyawan
    $deleteQuery = "DELETE FROM users WHERE id = '$id'";
    $result = mysqli_query($koneksi, $deleteQuery);
    
    if ($result) {
        echo json_encode([
            "success" => true,
            "message" => "Karyawan berhasil dihapus"
        ]);
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