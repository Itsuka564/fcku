<?php
//
include_once '../config/db.php';

// 1. Ambil Token dari Header (Simulasi Keamanan JWT)
$headers = apache_request_headers();
$token = isset($headers['Authorization']) ? $headers['Authorization'] : null;

if(!$token) {
    echo json_encode(["status" => "error", "message" => "Aiya! Kamu nggak punya izin masuk, harus login dulu!"]);
    exit;
}

// 2. Ambil Data Pesanan dari Postman/Frontend
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->jenis_cetak) && !empty($data->ukuran_kertas) && !empty($data->jumlah_halaman)) {
    
    try {
        // 3. PRICING ENGINE: Ambil Harga Cetak
        // Contoh key: harga_bw_a4, harga_bw_f4, atau harga_warna_standar
        $key_cetak = ($data->jenis_cetak == 'color') ? 'harga_warna_standar' : "harga_" . $data->jenis_cetak . "_" . $data->ukuran_kertas;
        
        $stmt_harga = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :key");
        $stmt_harga->execute(['key' => $key_cetak]);
        $harga_per_hal = $stmt_harga->fetchColumn() ?: 0;

        // 4. PRICING ENGINE: Ambil Harga Jilid
        $harga_jilid = 0;
        if (!empty($data->jenis_jilid) && $data->jenis_jilid != 'tanpa') {
            $key_jilid = "harga_jilid_lakban_" . $data->jenis_jilid; // kecil/sedang/besar
            $stmt_harga->execute(['key' => $key_jilid]);
            $harga_jilid = $stmt_harga->fetchColumn() ?: 0;
        }

        // 5. Rumus Hitung Total
        // Total = (Halaman * Harga * Copy) + (Harga Jilid * Copy)
        $jumlah_kopian = !empty($data->jumlah_kopian) ? $data->jumlah_kopian : 1;
        $total_tagihan = ($data->jumlah_halaman * $harga_per_hal * $jumlah_kopian) + ($harga_jilid * $jumlah_kopian);

        // 6. Buat Nomor Antrean Otomatis (SRS Spesifikasi)
        $queue_num = "Q-" . date("Ymd") . "-" . rand(100, 999);

        // 7. Simpan ke Database
        $query = "INSERT INTO orders (user_id, queue_num, jenis_cetak, ukuran_kertas, jumlah_halaman, jumlah_kopian, jenis_jilid, total_tagihan, status) 
                  VALUES (1, :q_num, :jenis, :ukuran, :hal, :copy, :jilid, :total, 'menunggu')";

        $stmt = $conn->prepare($query);
        $success = $stmt->execute([
            'q_num' => $queue_num,
            'jenis' => $data->jenis_cetak,
            'ukuran' => $data->ukuran_kertas,
            'hal' => $data->jumlah_halaman,
            'copy' => $jumlah_kopian,
            'jilid' => $data->jenis_jilid,
            'total' => $total_tagihan
        ]);

        if($success) {
            echo json_encode([
                "status" => "success",
                "message" => "Pesanan berhasil dibuat!",
                "data" => [
                    "nomor_antrean" => $queue_num,
                    "rincian_harga" => [
                        "per_halaman" => $harga_per_hal,
                        "biaya_jilid" => $harga_jilid
                    ],
                    "total_bayar" => $total_tagihan
                ]
            ]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Ada hantu di server: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data pesanan belum lengkap, Pian!"]);
}
?>