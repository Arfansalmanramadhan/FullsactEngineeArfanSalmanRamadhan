# Flash Sale API - Technical Assessment Project

Proyek ini adalah implementasi REST API sederhana berbasis **PHP Native** dan **MySQLi** yang dirancang khusus untuk menangani skenario *Flash Sale* dengan volume permintaan tinggi (*burst of orders*). Sistem ini mengimplementasikan mekanisme *Database Locking* di dalam sebuah transaksi database untuk mencegah terjadinya **Race Condition** dan memastikan stok barang tidak pernah bernilai negatif (dibawah 0).

---

## 🚀 Fitur Utama
* **Pencegahan Race Condition:** Menggunakan tingkat isolasi transaksi database yang aman (`FOR UPDATE`) untuk mengunci baris produk saat proses pengecekan hingga pengurangan stok selesai.
* **REST API Endpoint:** Menyediakan endpoint murni berbasis JSON untuk mengecek data serta melakukan *checkout*.
* **Automated Functional Test:** Dilengkapi dengan skrip simulasi *multi-threading/paralel* via cURL untuk menguji ketahanan sistem terhadap *race condition* langsung dari Command Line Interface (CLI).

---

## 🛠️ Prasyarat (Prerequisites)
Sebelum menjalankan proyek ini, pastikan Anda telah menginstal lingkungan berikut:
* **PHP** 
* **MySQL**.

---

## 📦 Langkah Instalasi & Konfigurasi

### 1. Duplikasi Proyek
Pindahkan atau *clone* folder proyek ini ke dalam direktori server lokal Anda (misalnya di `D:/laragon/www/php/FULLSTACK-ENGINEER_ARFAN-SALMAN-RAMADHAN/`).

### 2. Konfigurasi Database
1. Buka **phpMyAdmin** atau SQL editor pilihan Anda, lalu buat database baru bernama `toko_online`.
2. Impor struktur tabel dan contoh data berikut:

```sql
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `stock` INT NOT NULL
);

CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Masukkan data awal produk untuk pengujian (Stok awal: 5)
INSERT INTO `products` (`id`, `name`, `stock`) VALUES (1, 'Produk Flash Sale', 5);

```

## 🔌 API Endpoints Documentation
### 1. Ambil Data Produk & Pesanan
URL: http://localhost/php/FULLSTACK-ENGINEER_ARFAN-SALMAN-RAMADHAN/checkout.php

Method: GET

Headers: Content-Type: application/json

Response (200 OK):

```JSON
{
    "status": "success",
    "message": "Berhasil mengambil data",
    
}
```
### 2. Proses Pembuatan Pesanan (Checkout)
```test
URL: http://localhost/php/FULLSTACK-ENGINEER_ARFAN-SALMAN-RAMADHAN/checkout.php
```
Method: POST

Headers: Content-Type: application/json

Request Body (JSON):

```JSON
{
    "product_id": 1,
    "quantity": 1
}
```
Response Sukses (201 Created):

```JSON
{
    "status": "success",
    "message": "Pesanan berhasil dibuat!",
}
```
Response Gagal - Stok Habis (422 Unprocessable Entity):

```JSON
{
    "status": "error",
    "message": "Stok habis atau tidak mencukupi."
}
```
## 🧪 Cara Menjalankan Uji Race Condition (Functional Test)
Aplikasi ini menyertakan skrip test_race_condition.php yang mensimulasikan banyak permintaan (burst requests) yang dikirim secara bersamaan secara paralel menggunakan mekanisme curl_multi.

Buka Terminal, Command Prompt, atau Git Bash.

Masuk ke direktori proyek Anda:

```Bash
cd D:/laragon/www/php/FULLSTACK-ENGINEER_ARFAN-SALMAN-RAMADHAN/
```
Jalankan perintah berikut:

```Bash
php test.php
```
Hasil yang Diharapkan (Expected Summary):
Jika Anda mengatur stok awal produk di database bernilai 5, dan mengirimkan simulasi 10 atau lebih permintaan secara serentak, maka sistem backend harus mengembalikan:

Pesanan Sukses (HTTP 201): Tepat 5

Pesanan Gagal (Error): Sisanya

Ini membuktikan bahwa mekanisme penguncian baris (FOR UPDATE) berhasil mengantrekan proses pembacaan stok dengan benar dan mencegah kebocoran data (overselling).


### 📝 Tips Pengumpulan:
* Anda bisa mengganti nama path URL di dalam dokumen tersebut (`toko_online`) agar sesuai