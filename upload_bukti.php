<?php
require_once "db_connect.php";

header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Request Method"
    ]);
    exit();
}

$laporan_id = $_POST['laporan_id'] ?? '';

if ($laporan_id == '') {
    echo json_encode([
        "success" => false,
        "message" => "laporan_id wajib diisi!"
    ]);
    exit();
}

if (!isset($_FILES['file'])) {
    echo json_encode([
        "success" => false,
        "message" => "File tidak ditemukan!"
    ]);
    exit();
}

$folder = "uploads/";
$namaFile = time() . "_" . basename($_FILES['file']['name']);
$pathFile = $folder . $namaFile;

// Pindahkan file
if (move_uploaded_file($_FILES['file']['tmp_name'], $pathFile)) {
    
    // Simpan URL ke database
    mysqli_query($koneksi, "
        INSERT INTO bukti_laporan (laporan_id, file_url)
        VALUES ('$laporan_id', '$pathFile')
    ");

    echo json_encode([
        "success" => true,
        "message" => "Upload berhasil!",
        "file_url" => $pathFile
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Upload gagal!"
    ]);
}
?>
