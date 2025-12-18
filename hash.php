<?php
// hash.php
// Gunakan untuk generate HASH password BCRYPT

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (empty($_POST['password'])) {
        echo json_encode([
            "success" => false,
            "message" => "Password tidak boleh kosong"
        ]);
        exit;
    }

    $password = $_POST['password'];
    $hashed   = password_hash($password, PASSWORD_BCRYPT);

    echo json_encode([
        "success" => true,
        "password" => $password,
        "hash" => $hashed
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buat Password Hash</title>
</head>
<body>
    <h2>Generate Password Hash (BCRYPT)</h2>
    <form method="POST">
        <label>Masukkan Password :</label><br>
        <input type="text" name="password" required />
        <br><br>
        <button type="submit">Generate</button>
    </form>
</body>
</html>
