<?php
require_once 'db_connect.php';

$id = $_POST['id'] ?? $_GET['id'] ?? 0;

$stmt = $koneksi->prepare("
    SELECT id, nama, no_hp, foto, role 
    FROM users 
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'User tidak ditemukan'
    ]);
}
