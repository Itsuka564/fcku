<?php
include_once '../config/db.php';

// Ambil data login dari Postman
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->no_hp) && !empty($data->password)) {
    
    // 1. Cari user berdasarkan nomor HP
    $query = "SELECT * FROM users WHERE no_hp = :no_hp";
    $stmt = $conn->prepare($query);
    $stmt->execute(['no_hp' => $data->no_hp]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Cek apakah user ada dan password-nya cocok
    if($user && password_verify($data->password, $user['password'])) {
        
        // Simulasikan pembuatan Token JWT (KTP Digital)
        // Di sistem beneran, ini adalah kode acak yang terenkripsi
        $token = base64_encode(json_encode(['id' => $user['id'], 'role' => $user['role']]));

        echo json_encode([
            "status" => "success",
            "message" => "Selamat datang kembali, " . $user['nama'] . "!",
            "token" => $token,
            "role" => $user['role']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Nomor HP atau Password salah, hiii~"]);
    }
}
?>