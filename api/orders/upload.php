<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

if (isset($_FILES['file'])) {
    $file_name = $_FILES['file']['name'];
    $file_tmp  = $_FILES['file']['tmp_name'];
    
    // Sihir pengaman: Potong nama ekstensi (pdf/docx)
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $raw_name  = pathinfo($file_name, PATHINFO_FILENAME);
    
    // Gabungkan dengan timestamp waktu biar nama filenya unik dan anti-bentrok! 🛡️
    $clean_name = time() . "_" . preg_replace("/[^a-zA-Z0-9]/", "_", $raw_name) . "." . $file_ext;
    $upload_dir = "uploads/";
    
    // Buat folder uploads otomatis jika belum ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($file_tmp, $upload_dir . $clean_name)) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "File berhasil bersarang di server XAMPP!",
            "file_name" => $clean_name
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Aiya! Gagal memindahkan file fisik ke folder."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Tidak ada file fisik yang dikirim, Pian!"]);
}
?>