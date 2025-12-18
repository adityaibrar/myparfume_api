<?php
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Request"
    ]);
    exit();
}

$id = $_POST['id'] ?? '';

if ($id == '') {
    echo json_encode([
        "success" => false,
        "message" => "ID laporan wajib diisi!"
    ]);
    exit();
}

// Update status laporan
$update = mysqli_query($koneksi, "
    UPDATE laporan SET status='acc' WHERE id='$id'
");

if (!$update) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengupdate status laporan!"
    ]);
    exit();
}

// Tambahkan timeline
mysqli_query($koneksi, "
    INSERT INTO timeline_laporan (laporan_id, status, catatan)
    VALUES ('$id', 'acc', 'Laporan telah di ACC oleh admin')
");

echo json_encode([
    "success" => true,
    "message" => "Laporan berhasil di ACC!"
]);
?>
