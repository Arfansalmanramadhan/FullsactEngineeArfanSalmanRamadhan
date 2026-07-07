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
// proses mengambil data dari database
function query($query)
{
    global $conn;
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        return false;
    }

    if ($result === true) {
        return true;
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}
// 1. Ambil data input JSON dari request body
$input = json_decode(file_get_contents("php://input"), true);
$orderData = query("SELECT * FROM orders");
$products = query("SELECT * FROM products");
$productId = $input['product_id'] ?? null;
$quantity = $input['quantity'] ?? 1;
// // validasi minimal data order
if (!$productId || !$quantity  < 1) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Data order tidak valid"
    ]);
    exit;
} else {
    // Kirim response sukses ke Frontend
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "orders" => $orderData,
        "products" => $products
    ]);
}
http_response_code(200);
echo json_encode([
    "status" => "success",
    "orders" => $orderData,
    "products" => $products
]);
try {
    // Memulai transaksi 
    mysqli_begin_transaction($conn);

    // Ambil input 
    $cleanProductId = (int)$productId;
    $cleanQuantity  = (int)$quantity;

    // 1. Ambil data dengan mengunci baris (FOR UPDATE) menggunakan fungsi query Anda
    $sqlSelect = "SELECT stock FROM products WHERE id = $cleanProductId FOR UPDATE";
    $products = query($sqlSelect);

    // Cek apakah produk ada
    if (!$products || empty($products)) {
        mysqli_rollback($conn);
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Produk tidak ditemukan."]);
        exit;
    }

    $product = $products[0]; // Ambil baris pertama

    // 2. Cegah stok menjadi negatif
    if ($product['stock'] < $cleanQuantity) {
        mysqli_rollback($conn);
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => "Stok habis atau tidak mencukupi."]);
        exit;
    }

    // 3. Kurangi Stok Produk (Menggunakan fungsi query)
    $sqlUpdate = "UPDATE products SET stock = stock - $cleanQuantity WHERE id = $cleanProductId";
    query($sqlUpdate);

    // 4. Buat Pesanan Baru (Menggunakan fungsi query)
    $sqlInsert = "INSERT INTO orders (product_id, quantity) VALUES ($cleanProductId, $cleanQuantity)";
    query($sqlInsert);
    // Ambil ID pesanan yang baru saja dibuat
    $newOrderId = mysqli_insert_id($conn);
    // Commit jika semua sukses
    mysqli_commit($conn);

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Pesanan berhasil dibuat!",
        "order_id" => $newOrderId
    ]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Terjadi kesalahan server: " . $e->getMessage()]);
}
