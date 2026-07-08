<?php
//  Definisikan
// Y bertambah ke bawah (0 di paling atas, 5 di paling bawah)
// X bertambah ke kanan (0 di paling kiri, 7 di paling kanan)
$grid = [
    ['#', '#', '#', '#', '#', '#', '#', '#'], // baris 0
    ['#', '.', '.', '.', '.', '.', '.', '#'], // baris 1
    ['#', '.', '#', '#', '#', '.', '.', '#'], // baris 2
    ['#', '.', '.', '.', '#', '.', '#', '#'], // baris 3
    ['#', 'X', '#', '.', '.', '.', '.', '#'], // baris 4
    ['#', '#', '#', '#', '#', '#', '#', '#'], // baris 5
];

// Cari posisi awal 'X' secara dinamis 
$startX = 1; 
$startY = 4; 

// Menerima argument langkah A (Utara), B (Timur), C (Selatan) 

if ($argc < 4) {
    echo "Cara maiinnya  [Langkah_Utara] [Langkah_Timur] [Langkah_Selatan]\n";
    echo "Contoh: php game.php 3 4 1\n";
    exit(1);
}

$stepsNorth = (int)$argv[1]; 
$stepsEast  = (int)$argv[2];
$stepsSouth = (int)$argv[3];  

// Hitung koordinat tujuan akhir teoritis jika tidak menabrak rintangan
$targetY = $startY - $stepsNorth + $stepsSouth;
$targetX = $startX + $stepsEast;

$probableLocations = [];

//  Validasi apakah koordinat tersebut valid 
if (isset($grid[$targetY][$targetX]) && $grid[$targetY][$targetX] === '.') {
    $probableLocations[] = ['x' => $targetX, 'y' => $targetY];
}

//  Output daftar koordinat kemungkinan 
echo "\nDaftar Koordinat Kemungkinan Lokasi Barang:\n";
if (empty($probableLocations)) {
    echo "Tidak ada koordinat valid yang ditemukan.\n";
} else {
    foreach ($probableLocations as $loc) {
        echo "- Koordinat X: {$loc['x']}, Y: {$loc['y']}\n";
        
        // Tandai peta dengan simbol '$' untuk lokasi kemungkinan
        $grid[$loc['y']][$loc['x']] = '$';
    }
}

//  Tampilkan Grid ke Layar 
echo "\nTampilan Grid Saat Ini:\n";
foreach ($grid as $row) {
    echo implode('', $row) . "\n";
}