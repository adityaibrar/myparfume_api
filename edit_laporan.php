<?php
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Request"
    ]);
    exit();
}

$id                 = $_POST['id'] ?? '';
$tanggal            = $_POST['tanggal'] ?? '';
$toko               = $_POST['toko'] ?? '';
$alamat             = $_POST['alamat'] ?? '';
$no_hp              = $_POST['no_hp'] ?? '';
$omset              = $_POST['omset'] ?? '';
$tanggal_tagihan    = $_POST['tanggal_tagihan'] ?? '';
$barang_masuk_json  = $_POST['barang_masuk'] ?? '[]';

if (
    $id == '' || $tanggal == '' || $toko == '' ||
    $alamat == '' || $no_hp == '' || $omset == '' ||
    $tanggal_tagihan == ''
) {
    echo json_encode([
        "success" => false,
        "message" => "Semua field wajib diisi!"
    ]);
    exit();
}

// UPDATE laporan utama
$update = mysqli_query($koneksi, "
    UPDATE laporan SET
        tanggal = '$tanggal',
        toko = '$toko',
        alamat = '$alamat',
        no_hp = '$no_hp',
        omset = '$omset',
        tanggal_tagihan = '$tanggal_tagihan'
    WHERE id='$id'
");

if (!$update) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal update laporan"
    ]);
    exit();
}

// HAPUS barang masuk lama
mysqli_query($koneksi, "DELETE FROM barang_masuk WHERE laporan_id='$id'");

// TAMBAH barang baru
$barang_list = json_decode($barang_masuk_json, true);

foreach ($barang_list as $b) {
    $nama   = $b['nama_barang'];
    $jumlah = $b['jumlah'];

    mysqli_query($koneksi, "
        INSERT INTO barang_masuk (laporan_id, nama_barang, jumlah)
        VALUES ('$id', '$nama', '$jumlah')
    ");
}

// Tambahkan timeline
mysqli_query($koneksi, "
    INSERT INTO timeline_laporan (laporan_id, status, catatan)
    VALUES ('$id', 'updated', 'Laporan diperbarui oleh karyawan')
");

echo json_encode([
    "success" => true,
    "message" => "Laporan berhasil diperbarui!"
]);
?>
    