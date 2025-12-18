<?php
/**
 * GET HISTORY - FIXED (No updated_at column)
 * ✅ Prepared Statement
 * ✅ Input Validation
 * ✅ Proper Error Handling
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db_connect.php';

try {
    // ====================================
    // GET KARYAWAN_ID
    // ====================================
    $karyawan_id = $_POST['karyawan_id'] ?? $_GET['karyawan_id'] ?? null;

    if (empty($karyawan_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'karyawan_id tidak boleh kosong',
            'data' => []
        ]);
        exit;
    }

    // ====================================
    // VALIDATE INPUT
    // ====================================
    if (!is_numeric($karyawan_id)) {
        throw new Exception('karyawan_id harus berupa angka');
    }
    $karyawan_id = (int)$karyawan_id;

    // ====================================
    // PREPARED STATEMENT QUERY (FIXED)
    // ====================================
    $stmt = $koneksi->prepare("
        SELECT 
            id, karyawan_id, tanggal, daerah, toko, alamat,
            no_hp, barang_masuk, omset, tanggal_tagihan, status,
            created_at
        FROM laporan
        WHERE karyawan_id = ?
        ORDER BY id DESC
    ");

    if (!$stmt) {
        throw new Exception('Prepare statement failed: ' . $koneksi->error);
    }

    $stmt->bind_param("i", $karyawan_id);

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $data = [];

    // ====================================
    // FORMAT DATA
    // ====================================
    while ($row = $result->fetch_assoc()) {
        $laporan = [
            'id' => (int)$row['id'],
            'user_id' => (int)$row['karyawan_id'],
            'karyawan_id' => (int)$row['karyawan_id'],
            
            // Data utama
            'judul_laporan' => $row['toko'],
            'lokasi_laporan' => $row['daerah'],
            'tanggal_keberangkatan' => $row['tanggal'],
            'tanggal_laporan' => $row['tanggal'],
            'status_laporan' => $row['status'] ?? 'terkirim',
            
            // Data detail toko
            'nama_toko' => $row['toko'],
            'alamat_toko' => $row['alamat'],
            'no_hp_toko' => $row['no_hp'],
            'omset_toko' => $row['omset'],
            'barang_masuk' => $row['barang_masuk'],
            'isi_laporan' => $row['barang_masuk'],
            
            // Tanggal tagihan
            'tanggal_tagihan' => $row['tanggal_tagihan'],
            
            // Alias untuk kompatibilitas
            'daerah' => $row['daerah'],
            'tanggal' => $row['tanggal'],
            'toko' => $row['toko'],
            'alamat' => $row['alamat'],
            'no_hp' => $row['no_hp'],
            'omset' => $row['omset'],
            'status' => $row['status'] ?? 'terkirim',
            
            // Timestamps
            'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at' => $row['created_at'] ?? date('Y-m-d H:i:s'), // Fallback ke created_at
        ];

        $data[] = $laporan;
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Data berhasil diambil',
        'data' => $data,
        'count' => count($data)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ]);
} finally {
    if (isset($koneksi)) {
        $koneksi->close();
    }
}
?>