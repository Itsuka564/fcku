<?php
include_once '../config/db.php';

// Ambil data input JSON
$data = json_decode(file_get_contents("php://input"));

// Validasi data minimal yang harus ada
if(!empty($data->jenis_cetak) && !empty($data->ukuran_kertas) && !empty($data->jumlah_halaman)) {
    
    // 1. Antisipasi Data Kosong (Hantu Warning) 👻
    // Jika tidak ada data di JSON, gunakan nilai default
    $jumlah_kopian = $data->jumlah_kopian ?? 1;
    $jenis_jilid = $data->jenis_jilid ?? 'tanpa';

    try {
        // 2. Ambil Harga Cetak dari Database
        $key_cetak = ($data->jenis_cetak == 'color') ? 'harga_warna_standar' : "harga_" . $data->jenis_cetak . "_" . $data->ukuran_kertas;
        
        $stmt_harga = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :key");
        $stmt_harga->execute(['key' => $key_cetak]);
        $harga_per_hal = $stmt_harga->fetchColumn() ?: 0;

        // 3. Ambil Harga Jilid
        $harga_jilid = 0;
        if ($jenis_jilid != 'tanpa') {
            $key_jilid = "harga_jilid_lakban_" . $jenis_jilid;
            $stmt_harga->execute(['key' => $key_jilid]);
            $harga_jilid = $stmt_harga->fetchColumn() ?: 0;
        }

        // 4. Hitung Total Tagihan
        $total_tagihan = ($data->jumlah_halaman * $harga_per_hal * $jumlah_kopian) + ($harga_jilid * $jumlah_kopian);

        // 5. Kirim Hasil ke Postman/Frontend
        echo json_encode([
            "status" => "success",
            "total_tagihan" => $total_tagihan,
            "rincian" => [
                "harga_satuan" => (float)$harga_per_hal,
                "biaya_jilid" => (float)$harga_jilid,
                "halaman" => (int)$data->jumlah_halaman,
                "kopian" => (int)$jumlah_kopian
            ]
        ]);

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Ada masalah teknis: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Data kirimanmu belum lengkap, Pian!"]);
}
?>