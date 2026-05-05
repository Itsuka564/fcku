<?php
$host = "localhost";
$db_name = "fotokopiku_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Kalau berhasil, diam saja. Hehe.
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}
?>