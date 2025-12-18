<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "myparfume_db";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

date_default_timezone_set("Asia/Jakarta");
