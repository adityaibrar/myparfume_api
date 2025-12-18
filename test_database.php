<?php
header('Content-Type: application/json');

require_once "db_connect.php";

echo "<h2>Database Connection Test</h2>";

// 1. Test koneksi
if ($koneksi) {
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit;
}

// 2. Cek struktur tabel users
echo "<h3>Struktur Tabel Users:</h3>";
$query = "DESCRIBE users";
$result = mysqli_query($koneksi, $query);

if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error: " . mysqli_error($koneksi) . "</p>";
}

// 3. Cek jumlah data users
echo "<h3>Data Users:</h3>";
$query = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($koneksi, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>Total users: <strong>" . $row['total'] . "</strong></p>";
}

// 4. Cek jumlah karyawan
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'karyawan'";
$result = mysqli_query($koneksi, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>Total karyawan: <strong>" . $row['total'] . "</strong></p>";
}

// 5. Tampilkan semua karyawan
echo "<h3>List Karyawan:</h3>";
$query = "SELECT id, nama, username, no_hp, alamat, role FROM users WHERE role = 'karyawan'";
$result = mysqli_query($koneksi, $query);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nama</th><th>Username</th><th>No HP</th><th>Alamat</th><th>Role</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['nama'] . "</td>";
            echo "<td>" . ($row['username'] ?? '-') . "</td>";
            echo "<td>" . ($row['no_hp'] ?? '-') . "</td>";
            echo "<td>" . ($row['alamat'] ?? '-') . "</td>";
            echo "<td>" . $row['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ Tidak ada data karyawan</p>";
        echo "<p>Silakan jalankan query INSERT untuk menambahkan data dummy</p>";
    }
} else {
    echo "<p style='color: red;'>Error: " . mysqli_error($koneksi) . "</p>";
}

// 6. Test query yang sama dengan get_karyawan.php
echo "<h3>Test Query get_karyawan.php:</h3>";
$query = "SELECT 
            id,
            nama,
            username,
            foto,
            no_hp,
            alamat,
            role,
            created_at,
            updated_at
          FROM users 
          WHERE role = 'karyawan'
          ORDER BY nama ASC";

$result = mysqli_query($koneksi, $query);

if ($result) {
    echo "<p style='color: green;'>✅ Query berhasil!</p>";
    echo "<p>Jumlah data: " . mysqli_num_rows($result) . "</p>";
    
    echo "<h4>JSON Response:</h4>";
    $karyawan = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $karyawan[] = [
            'id' => $row['id'],
            'nama' => $row['nama'],
            'username' => $row['username'] ?? null,
            'foto' => $row['foto'],
            'no_hp' => $row['no_hp'],  // Pakai no_hp sesuai database
            'alamat' => $row['alamat'],
            'role' => $row['role'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    $response = [
        "success" => true,
        "message" => "Data berhasil diambil",
        "data" => $karyawan,
        "count" => count($karyawan)
    ];
    
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ Query gagal: " . mysqli_error($koneksi) . "</p>";
}

mysqli_close($koneksi);
?>