<?php
// Izinkan akses dari frontend (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Sambungkan ke database
include_once '../config/db.php';

try {
    // 🔥 KINI MEMANGGIL KOLOM no_hp JUGA
    $query = "SELECT id, queue_num, nama_pelanggan, no_hp, nama_file, jenis_cetak, ukuran_kertas, jumlah_halaman, jumlah_kopian, jenis_jilid, total_tagihan, status, created_at FROM orders ORDER BY id DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $orders_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $order_item = array(
                "id" => $id,
                "queue_num" => $queue_num,
                "nama_pelanggan" => $nama_pelanggan,
                "no_hp" => $no_hp, // 🔥 DATA SEKARANG IKUT DIKIRIM KE FRONTEND ADMIN
                "nama_file" => $nama_file,
                "jenis_cetak" => $jenis_cetak,
                "ukuran_kertas" => $ukuran_kertas,
                "jumlah_halaman" => $jumlah_halaman,
                "jumlah_kopian" => $jumlah_kopian,
                "jenis_jilid" => $jenis_jilid,
                "total_tagihan" => $total_tagihan,
                "status" => $status,
                "created_at" => $created_at
            );
            array_push($orders_arr, $order_item);
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "data" => $orders_arr
        ]);
    } else {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Belum ada pesanan di database.",
            "data" => []
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Gagal mengambil data: " . $e->getMessage()]);
}
?>