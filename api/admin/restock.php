<?php
include_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id_bahan) && !empty($data->tambah_jumlah)) {
    // 1. Update jumlah stok di database
    $query = "UPDATE stock_items SET jumlah_stok = jumlah_stok + :tambah WHERE id = :id";
    $stmt = $conn->prepare($query);
    
    if($stmt->execute(['tambah' => $data->tambah_jumlah, 'id' => $data->id_bahan])) {
        echo json_encode([
            "status" => "success",
            "message" => "Berhasil restock! Sekarang stok sudah penuh lagi, Pian! 👻✨"
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal restock, hiii~"]);
    }
}
?>