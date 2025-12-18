<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "db_connect.php";

error_log("get_karyawan.php: Request received");

try {
    if (!$koneksi) {
        echo json_encode([
            "success" => false,
            "message" => "Database connection failed"
        ]);
        exit;
    }

    // ✅ FIXED: Only select columns that exist in database
    $query = "SELECT 
                id,
                nama,
                username,
                foto,
                no_hp,
                role,
                created_at
              FROM users 
              WHERE role = 'karyawan'
              ORDER BY nama ASC";

    error_log("Query: $query");

    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $karyawan = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $karyawan[] = [
                'id' => $row['id'],
                'nama' => $row['nama'],
                'username' => $row['username'] ?? null,
                'foto' => $row['foto'],
                'no_hp' => $row['no_hp'],
                'role' => $row['role'],
                'created_at' => $row['created_at']
            ];
        }

        error_log("Found " . count($karyawan) . " karyawan");

        echo json_encode([
            "success" => true,
            "message" => "Data berhasil diambil",
            "data" => $karyawan,
            "count" => count($karyawan)
        ]);
    } else {
        $error = mysqli_error($koneksi);
        error_log("Query failed: $error");
        
        echo json_encode([
            "success" => false,
            "message" => "Query failed: $error"
        ]);
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>