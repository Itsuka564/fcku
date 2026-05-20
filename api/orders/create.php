<?php
// Izinkan akses dari frontend (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Sihir OPTIONS Request dari browser
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/db.php';

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : null);

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Aiya! Kamu harus login dulu, Pian!"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->jenis_layanan) && !empty($data->total_tagihan)) {
    try {
        $jenis_cetak    = $data->jenis_layanan;
        $ukuran_kertas  = isset($data->ukuran_kertas) ? $data->ukuran_kertas : '-';
        $jumlah_halaman = isset($data->jumlah_halaman) ? (int)$data->jumlah_halaman : 1;
        $jumlah_kopian  = isset($data->jumlah_kopian) ? (int)$data->jumlah_kopian : 1;
        $jenis_jilid    = isset($data->jenis_jilid) ? $data->jenis_jilid : 'Tidak Ada';
        $total_tagihan  = (float)$data->total_tagihan;
        $nama_file      = isset($data->nama_file) ? $data->nama_file : 'Dokumen_Tanpa_Nama.pdf';
        
        // 🔥 TANGKAP DATA NOMOR HP DARI FRONTEND
        $nama_pelanggan = isset($data->nama_pelanggan) ? $data->nama_pelanggan : 'Hamba Allah';
        $no_hp          = isset($data->no_hp) ? $data->no_hp : '-';

        $tanggal_hari_ini = date("Ymd");
        $query_q = "SELECT queue_num FROM orders WHERE queue_num LIKE :hari_ini ORDER BY id DESC LIMIT 1";
        $stmt_q = $conn->prepare($query_q);
        $param_hari = "Q-" . $tanggal_hari_ini . "-%";
        $stmt_q->bindParam(':hari_ini', $param_hari);
        $stmt_q->execute();
        
        $urutan = 1;
        if ($stmt_q->rowCount() > 0) {
            $row = $stmt_q->fetch(PDO::FETCH_ASSOC);
            $last_q = $row['queue_num'];
            $last_urutan = (int)substr($last_q, -3);
            $urutan = $last_urutan + 1;
        }
        $queue_num = "Q-" . $tanggal_hari_ini . "-" . str_pad($urutan, 3, "0", STR_PAD_LEFT);

        // 🔥 MEMASUKKAN no_hp KE DALAM QUERY INSERT DATABASE
        $query = "INSERT INTO orders 
                  (user_id, queue_num, nama_file, jenis_cetak, ukuran_kertas, jumlah_halaman, jumlah_kopian, jenis_jilid, total_tagihan, status, nama_pelanggan, no_hp) 
                  VALUES 
                  (1, :queue_num, :nama_file, :jenis_cetak, :ukuran_kertas, :jumlah_halaman, :jumlah_kopian, :jenis_jilid, :total_tagihan, 'Menunggu', :nama_pelanggan, :no_hp)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':queue_num', $queue_num);
        $stmt->bindParam(':nama_file', $nama_file);
        $stmt->bindParam(':jenis_cetak', $jenis_cetak);
        $stmt->bindParam(':ukuran_kertas', $ukuran_kertas);
        $stmt->bindParam(':jumlah_halaman', $jumlah_halaman);
        $stmt->bindParam(':jumlah_kopian', $jumlah_kopian);
        $stmt->bindParam(':jenis_jilid', $jenis_jilid);
        $stmt->bindParam(':total_tagihan', $total_tagihan);
        $stmt->bindParam(':nama_pelanggan', $nama_pelanggan); 
        $stmt->bindParam(':no_hp', $no_hp); // 🔥 BINDING VARIABEL NOMOR HP Baru

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "queue_num" => $queue_num,
                "total_tagihan" => $total_tagihan
            ]);
        } else {
            http_response_code(503);
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan ke database."]);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
}
?>