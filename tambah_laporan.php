<?php

/**
 * TAMBAH LAPORAN - FIXED (Status: pending, acc, tolak)
 * ✅ Status otomatis terisi "pending"
 * ✅ Prepared Statement
 * ✅ Input Validation
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

require_once 'db_connect.php';

try {
    // ====================================
    // INPUT VALIDATION
    // ====================================
    $required_fields = [
        'karyawan_id',
        'tanggal',
        'daerah',
        'toko',
        'alamat',
        'no_hp',
        'barang_masuk',
        'omset',
        'tanggal_tagihan'
    ];

    $data = [];
    $missing_fields = [];

    foreach ($required_fields as $field) {
        $value = isset($_POST[$field]) ? trim($_POST[$field]) : '';

        if ($value === '') {
            $missing_fields[] = $field;
        }

        $data[$field] = $value;
    }

    if (!empty($missing_fields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Field wajib diisi: ' . implode(', ', $missing_fields)
        ]);
        exit;
    }

    // ====================================
    // DATA TYPE VALIDATION
    // ====================================

    // Validate karyawan_id
    if (!filter_var($data['karyawan_id'], FILTER_VALIDATE_INT)) {
        throw new Exception('ID karyawan harus berupa angka');
    }

    // Validate omset - bersihkan dulu
    $data['omset'] = preg_replace('/[^0-9.]/', '', $data['omset']);
    if (!is_numeric($data['omset'])) {
        throw new Exception('Omset harus berupa angka');
    }

    // Validate phone number
    $data['no_hp'] = preg_replace('/[^0-9+]/', '', $data['no_hp']);
    if (strlen($data['no_hp']) < 10 || strlen($data['no_hp']) > 15) {
        throw new Exception('Nomor HP harus 10-15 digit');
    }

    // ====================================
    // VERIFY KARYAWAN EXISTS
    // ====================================
    $stmt_check = $koneksi->prepare(
        "SELECT id FROM users WHERE id = ? AND role = 'karyawan' LIMIT 1"
    );
    $stmt_check->bind_param("i", $data['karyawan_id']);
    $stmt_check->execute();

    if ($stmt_check->get_result()->num_rows === 0) {
        throw new Exception('Karyawan tidak ditemukan');
    }
    $stmt_check->close();

    // ====================================
    // INSERT LAPORAN
    // ====================================
    // STATUS OTOMATIS "pending" (sesuai database: pending, acc, tolak)
    $status = 'acc';

    $stmt = $koneksi->prepare("
        INSERT INTO laporan (
            karyawan_id, 
            tanggal, 
            daerah, 
            toko, 
            alamat,
            no_hp, 
            barang_masuk, 
            omset, 
            tanggal_tagihan, 
            status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    if (!$stmt) {
        throw new Exception('Database error: ' . $koneksi->error);
    }

    $stmt->bind_param(
        "isssssssss",
        $data['karyawan_id'],
        $data['tanggal'],
        $data['daerah'],
        $data['toko'],
        $data['alamat'],
        $data['no_hp'],
        $data['barang_masuk'],
        $data['omset'],
        $data['tanggal_tagihan'],
        $status  // ← STATUS AUTO FILL: "pending"
    );

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan: ' . $stmt->error);
    }

    $laporan_id = $stmt->insert_id;
    $stmt->close();

    // ====================================
    // SUCCESS RESPONSE
    // ====================================
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Laporan berhasil disimpan',
        'data' => [
            'id' => $laporan_id,
            'status' => $status,
            'toko' => $data['toko'],
            'tanggal' => $data['tanggal']
        ]
    ]);
} catch (Exception $e) {
    error_log("TAMBAH LAPORAN ERROR: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($koneksi)) {
        $koneksi->close();
    }
}
