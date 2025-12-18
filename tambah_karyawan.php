<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db_connect.php';

// ==============================
// AMBIL DATA
// ==============================
$nama     = $_POST['nama'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$no_hp    = $_POST['no_hp'] ?? '';
$role     = 'karyawan';

// ==============================
// VALIDASI
// ==============================
if ($nama === '' || $username === '' || $password === '' || $no_hp === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Semua field wajib diisi'
    ]);
    exit;
}

// ==============================
// CEK USERNAME
// ==============================
$cek = $koneksi->prepare("SELECT id FROM users WHERE username = ?");
$cek->bind_param("s", $username);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Username sudah digunakan'
    ]);
    exit;
}
$cek->close();

// ==============================
// UPLOAD FOTO (OPSIONAL)
// ==============================
$fotoPath = null;

// CASE 1: upload file multipart
if (!empty($_FILES['foto']['name'])) {

    $folderUpload = "uploads/karyawan/";
    if (!is_dir($folderUpload)) {
        mkdir($folderUpload, 0777, true);
    }

    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $namaFile = uniqid('karyawan_') . '.' . $ext;
    $fullPath = $folderUpload . $namaFile;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $fullPath)) {
        $fotoPath = $fullPath;
    }
}

// CASE 2: fallback (foto string lama / URL)
if ($fotoPath === null && isset($_POST['foto']) && $_POST['foto'] !== '') {
    $fotoPath = $_POST['foto'];
}

// ==============================
// INSERT USER
// ==============================
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $koneksi->prepare(
    "INSERT INTO users (nama, username, password, no_hp, role, foto)
     VALUES (?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "ssssss",
    $nama,
    $username,
    $hashedPassword,
    $no_hp,
    $role,
    $fotoPath
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Karyawan berhasil ditambahkan'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan karyawan'
    ]);
}

$stmt->close();
$koneksi->close();
