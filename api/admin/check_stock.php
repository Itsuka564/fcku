<?php
include_once '../config/db.php';

// Ambil semua stok yang jumlahnya sudah di bawah batas (threshold)
$query = "SELECT nama_bahan, jumlah_stok, satuan, threshold_min 
          FROM stock_items 
          WHERE jumlah_stok <= threshold_min";

$stmt = $conn->prepare($query);
$stmt->execute();
$stok_kritis = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($stok_kritis) {
    echo json_encode([
        "status" => "warning",
        "message" => "Aiya! Ada stok yang mau habis, Pian!",
        "data" => $stok_kritis
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => "Semua stok aman terkendali! 👻✨"
    ]);
}
?>