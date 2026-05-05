<?php
include_once '../config/db.php';

// 1. Ambil Nomor Antrean dari URL (misal: track.php?q=Q-20260505-878)
$q_num = isset($_GET['q']) ? $_GET['q'] : null;

if($q_num) {
    // 2. Cari data pesanan berdasarkan nomor antrean
    $query = "SELECT queue_num, status, total_tagihan, created_at FROM orders WHERE queue_num = :q";
    $stmt = $conn->prepare($query);
    $stmt->execute(['q' => $q_num]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if($order) {
        echo json_encode([
            "status" => "success",
            "data" => [
                "nomor" => $order['queue_num'],
                "status_sekarang" => $order['status'], // Ini yang bakal jadi tracker 5 tahap
                "total" => $order['total_tagihan'],
                "waktu_pesan" => $order['created_at']
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Nomor antrean tidak ditemukan, hiii~"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Masukkan nomor antrean dulu ya!"]);
}
?>