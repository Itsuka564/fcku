<?php
include_once '../config/db.php';

// 1. Simulasi Cek Role (RBAC)
// Harusnya cek token JWT, tapi kita buat simpel dulu biar Pian nggak pusing
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->queue_num) && !empty($data->status_baru)) {
    
    // 2. Update status di database sesuai 5 tahapan SRS
    $query = "UPDATE orders SET status = :status WHERE queue_num = :q";
    $stmt = $conn->prepare($query);
    
    if($stmt->execute(['status' => $data->status_baru, 'q' => $data->queue_num])) {
        echo json_encode([
            "status" => "success", 
            "message" => "Status pesanan " . $data->queue_num . " sekarang: " . $data->status_baru
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal update status, hiii~"]);
    }
}
?>