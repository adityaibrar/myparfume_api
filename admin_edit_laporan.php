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
$daerah             = $_POST['daerah'] ?? '';
$toko               = $_POST['toko'] ?? '';
$alamat             = $_POST['alamat'] ?? '';
$no_hp              = $_POST['no_hp'] ?? '';
$omset              = $_POST['omset'] ?? '';
$tanggal_tagihan    = $_POST['tanggal_tagihan'] ?? '';
$status             = $_POST['status'] ?? '';
$barang_masuk_json  = $_POST['barang_masuk'] ?? '[]';

if ($id == '') {
    echo json_encode([
        "success" => false,
        "message" => "ID laporan wajib diisi!"
    ]);
    exit();
}

if (
    $tanggal == '' || $daerah == '' || $toko == '' ||
    $alamat == '' || $no_hp == '' || $omset == '' ||
    $tanggal_tagihan == '' || $status == ''
) {
    echo json_encode([
        "success" => false,
        "message" => "Semua field wajib diisi!"
    ]);
    exit();
}

// ================================
// UPDATE LAPORAN UTAMA
// ================================
$update = mysqli_query($koneksi, "
    UPDATE laporan SET
        tanggal = '$tanggal',
        daerah = '$daerah',
        toko = '$toko',
        alamat = '$alamat',
        no_hp = '$no_hp',
        omset = '$omset',
        tanggal_tagihan = '$tanggal_tagihan',
        status = '$status'
    WHERE id='$id'
");

if (!$update) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal update laporan!"
    ]);
    exit();
}

// ================================
// HAPUS BARANG MASUK LAMA
// ================================
mysqli_query($koneksi, "DELETE FROM barang_masuk WHERE laporan_id='$id'");

// ================================
// TAMBAH BARANG MASUK BARU
// ================================
$barang_list = json_decode($barang_masuk_json, true);

foreach ($barang_list as $b) {
    $nama   = $b['nama_barang'];
    $jumlah = $b['jumlah'];

    mysqli_query($koneksi, "
        INSERT INTO barang_masuk (laporan_id, nama_barang, jumlah)
        VALUES ('$id', '$nama', '$jumlah')
    ");
}

// ================================
// CATAT TIMELINE UPDATE
// ================================
mysqli_query($koneksi, "
    INSERT INTO timeline_laporan (laporan_id, status, catatan)
    VALUES ('$id', 'updated_admin', 'Laporan diperbarui oleh admin')
");

echo json_encode([
    "success" => true,
    "message" => "Laporan berhasil diubah oleh admin!"
]);
?>
