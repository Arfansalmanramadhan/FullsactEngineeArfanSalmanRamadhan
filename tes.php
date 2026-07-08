<?php
$conn = mysqli_connect("localhost", "root", "", "toko_online");
$productIdToTest = 1; 

// Ambil data stok produk
$result = mysqli_query($conn, "SELECT stock FROM products WHERE id = $productIdToTest");
$product = mysqli_fetch_assoc($result);
// Jika produk tidak ditemukan di database
if (!$product) {
    die("Error: Produk dengan ID $productIdToTest tidak ditemukan di database.\n");
}

$stokAwalDatabase = (int)$product['stock'];


// ambil url endpoint checkout.php
$url = "http://localhost/php/FULLSTACK-ENGINEER_ARFAN-SALMAN-RAMADHAN/checkout.php";

// Data yang akan dikirim 
$payload = json_encode([
    "product_id" => $productIdToTest,
    "quantity" => 1
]);

//  Tentukan jumlah request simulasi burst 
$totalRequests = 2;
$curlHandles = [];

// Inisialisasi multi-curl untuk menjalankan request secara paralel 
$multiHandle = curl_multi_init();

echo "Memulai Functional Test untuk Race Condition...\n";
echo "Stok awal produk di database saat ini: $stokAwalDatabase\n";
echo "Mengirimkan $totalRequests request secara bersamaan ke $url...\n\n";

for ($i = 0; $i < $totalRequests; $i++) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);

    $curlHandles[$i] = $ch;
    curl_multi_add_handle($multiHandle, $ch);
}

//  Jalankan semua request secara simultan/paralel
$running = null;
do {
    curl_multi_exec($multiHandle, $running);
} while ($running > 0);

// Ambil dan analisis semua respon dari API
$successCount = 0;
$failedCount = 0;

foreach ($curlHandles as $i => $ch) {
    $response = curl_multi_getcontent($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo "Request ke-" . ($i + 1) . " | HTTP Status: $httpCode | Response: $response\n";

    if ($httpCode === 201) {
        $successCount++;
    } else {
        $failedCount++;
    }

    curl_multi_remove_handle($multiHandle, $ch);
    curl_close($ch);
}

curl_multi_close($multiHandle);

// Tampilkan kesimpulan hasil test di CLI
echo "\n=====================================\n";
echo "KESIMPULAN HASIL UJI (SUMMARY):\n";
echo "=====================================\n";
echo "Total Request Dikirim : $totalRequests\n";
echo "Pesanan Sukses (201)  : $successCount\n";
echo "Pesanan Gagal (Error) : $failedCount\n";
echo "=====================================\n";
echo "Catatan: Jika stok awal produk Anda adalah $stokAwalDatabase, maka Pesanan Sukses HARUS tepat bernilai $stokAwalDatabase. Sisanya harus gagal (Stok habis). Jika Pesanan Sukses lebih dari $stokAwalDatabase, berarti sistem Anda bocor (terjadi Race Condition).\n";
