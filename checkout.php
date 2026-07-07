<?php
// Membuat header untuk mengatur tipe konten menjadi JSON
header('Content-Type: application/json');
try {
    // Menghubungkan ke database MySQLi
    $conn = mysqli_connect("localhost", "root", "", "toko_online");
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Koneksi database berhasil"
    ]);
} catch (Exception $e) {
    // Menangani kesalahan dan mengirimkan respons JSON dengan pesan kesalahan
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Koneksi database gagal: " . $e->getMessage()
    ]);
    exit;
}
