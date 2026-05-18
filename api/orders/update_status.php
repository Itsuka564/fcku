<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id) && !empty($data->status)) {
    try {
        // Update data berdasarkan ID utama angka
        $query = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $conn->prepare($query);

        $stmt->bindParam(':status', $data->status);
        $stmt->bindParam(':id', $data->id);
        $stmt->execute();

        // 🔥 SIHIR DETEKTOR NYATA: Cek apakah ada baris di MySQL yang bener-bener berubah!
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Status pesanan ID " . $data->id . " berhasil diperbarui menjadi " . $data->status
            ]);
        } else {
            // Jika 0 baris berubah, berarti ID angka dari admin.html tidak match dengan field di tabelmu!
            http_response_code(200);
            echo json_encode([
                "status" => "error",
                "message" => "MySQL menolak berubah! Cek apakah nama kolom Primary Key di tabelmu beneran 'id' (huruf kecil semua) atau bukan!"
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error Database: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Data ID atau status kosong!"]);
}
?>