<?php
include_once '../config/db.php';

// Ambil data pendaftaran dari Postman/Frontend
$data = json_decode(file_get_contents("php://input"));

// Pastikan data tidak kosong
if(!empty($data->nama) && !empty($data->no_hp) && !empty($data->password)) {
    
    // 1. Hash Password (biar aman!)
    $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
    
    // 2. Masukkan ke tabel users
    $query = "INSERT INTO users (nama, no_hp, password, role) VALUES (:nama, :no_hp, :pass, :role)";
    $stmt = $conn->prepare($query);
    
    // Set role default jadi 'pelanggan', kecuali ditentukan lain
    $role = !empty($data->role) ? $data->role : 'pelanggan';

    if($stmt->execute(['nama' => $data->nama, 'no_hp' => $data->no_hp, 'pass' => $password_hash, 'role' => $role])) {
        echo json_encode(["status" => "success", "message" => "Akun Pian Cam Pro berhasil terdaftar!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal daftar, coba lagi ya!"]);
    }
}
?>