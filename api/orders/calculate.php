<?php
// Izinkan akses dari frontend
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Sambungkan ke database (pastikan path-nya benar ya, Pian!)
include_once '../config/db.php';

// Tangkap data JSON yang dikirim dari User Page.html
$data = json_decode(file_get_contents("php://input"));

// Pastikan data yang dikirim tidak kosong
if (!empty($data->jenis_layanan) && isset($data->jumlah_halaman)) {
    $layanan = $data->jenis_layanan; // 'cetak', 'soft_cover', 'skripsi', 'scan_cd'
    $halaman = (int)$data->jumlah_halaman;
    $kopian  = isset($data->jumlah_kopian) ? (int)$data->jumlah_kopian : 1;

    $total_harga = 0;
    $rincian = [];

    // ==========================================
    // 1. LOGIKA SCAN & BURNING CD (Paket Flat Rp20.000)
    // ==========================================
    if ($layanan == 'scan_cd') {
        $harga_paket = 20000;
        $total_harga = $harga_paket * $kopian;
        $rincian['layanan'] = "Scan & Burning CD";
    }
    
    // ==========================================
    // 2. LOGIKA JILID SKRIPSI (Rp40.000 - Rp45.000)
    // ==========================================
    elseif ($layanan == 'skripsi') {
        $jenis_cover = isset($data->jenis_cover) ? $data->jenis_cover : 'soft';
        $harga_skripsi = ($jenis_cover == 'hard') ? 45000 : 40000;
        
        $total_harga = $harga_skripsi * $kopian;
        $rincian['layanan'] = "Jilid Skripsi (" . strtoupper($jenis_cover) . " Cover)";
    }
    
    // ==========================================
    // 3. LOGIKA CETAK & FOTOKOPI (+ Jilid Lakban)
    // ==========================================
    elseif ($layanan == 'cetak') {
        $kertas = isset($data->ukuran_kertas) ? strtolower($data->ukuran_kertas) : 'a4';
        $warna  = isset($data->mode_warna) ? strtolower($data->mode_warna) : 'bw';

        // Hitung harga per lembar
        $harga_per_lembar = 0;
        if ($warna == 'bw') {
            $harga_per_lembar = ($kertas == 'f4') ? 300 : 250;
        } else {
            // Deteksi Kualitas Warna (500, 1000, 2000)
            if ($warna == 'warna_hi') $harga_per_lembar = 2000;
            elseif ($warna == 'warna_mid') $harga_per_lembar = 1000;
            else $harga_per_lembar = 500; // warna_low
        }

        $biaya_cetak = $halaman * $harga_per_lembar;

        // Hitung biaya Jilid Lakban berdasarkan tebal halaman
        $biaya_jilid = 0;
        if (isset($data->jenis_jilid) && $data->jenis_jilid == 'lakban') {
            if ($halaman <= 30) {
                $biaya_jilid = 3000; // Lakban tipis
            } elseif ($halaman <= 60) {
                $biaya_jilid = 5000; // Lakban sedang
            } else {
                $biaya_jilid = 8000; // Lakban tebal
            }
        }

        $total_harga = ($biaya_cetak + $biaya_jilid) * $kopian;
        $rincian['biaya_cetak_per_buku'] = $biaya_cetak;
        $rincian['biaya_jilid_per_buku'] = $biaya_jilid;
    }
    
    // ==========================================
    // 4. LOGIKA JILID SOFT COVER MURNI
    // ==========================================
    elseif ($layanan == 'soft_cover') {
        $biaya_softcover = 0;
        if ($halaman <= 30) {
            $biaya_softcover = 3000;
        } elseif ($halaman <= 60) {
            $biaya_softcover = 6000;
        } else {
            $biaya_softcover = 8000;
        }
        
        $total_harga = $biaya_softcover * $kopian;
        $rincian['layanan'] = "Jilid Soft Cover";
    }

    // Kembalikan Respon Sukses ke Frontend
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "total_tagihan" => $total_harga,
        "rincian" => $rincian,
        "pesan" => "Harga berhasil dihitung oleh Asisten Hu Tao! 👻"
    ]);

} else {
    // Jika data dari frontend ada yang kurang
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "message" => "Aiya! Data kurang lengkap, Pian!"
    ]);
}
?>