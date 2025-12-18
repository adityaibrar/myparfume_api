<?php
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Request Method!"
    ]);
    exit();
}

$daerah = $_POST['daerah'] ?? '';

if ($daerah == '') {
    echo json_encode([
        "success" => false,
        "message" => "Parameter daerah wajib diisi!"
    ]);
    exit();
}

// AMBIL LAPORAN BERDASARKAN DAERAH
$query = mysqli_query($koneksi, "
    SELECT * FROM laporan 
    WHERE daerah='$daerah'
    ORDER BY id DESC
");

$list = [];

while ($row = mysqli_fetch_assoc($query)) {
    $list[] = [
        "id" => $row['id'],
        "karyawan_id" => $row['karyawan_id'],
        "tanggal" => $row['tanggal'],
        "daerah" => $row['daerah'],
        "toko" => $row['toko'],
        "alamat" => $row['alamat'],
        "no_hp" => $row['no_hp'],
        "omset" => $row['omset'],
        "tanggal_tagihan" => $row['tanggal_tagihan'],
        "status" => $row['status']
    ];
}

echo json_encode([
    "success" => true,
    "data" => $list
]);
?>
