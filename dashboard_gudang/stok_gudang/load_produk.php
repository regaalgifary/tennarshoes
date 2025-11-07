<?php
include '../../database.php';
header('Content-Type: application/json');

$mode    = $_GET['mode'] ?? '';
$q       = trim($_GET['q'] ?? '');
$brand   = trim($_GET['brand'] ?? '');
$artikel = trim($_GET['artikel'] ?? '');
$kode    = trim($_GET['kode'] ?? '');
$ukuran  = trim($_GET['ukuran'] ?? '');
$warna   = trim($_GET['warna'] ?? '');

$data = [];

switch ($mode) {
    // --- SUGGEST BRAND ---
    case 'brand':
        $stmt = $conn->prepare("
            SELECT DISTINCT nama_brand 
            FROM brand 
            WHERE nama_brand LIKE CONCAT('%', ?, '%') 
            ORDER BY nama_brand ASC 
            LIMIT 10
        ");
        $stmt->bind_param('s', $q);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $data[] = $row['nama_brand'];
        break;

    // --- SUGGEST ARTIKEL BERDASARKAN BRAND ---
    case 'artikel':
        $stmt = $conn->prepare("
            SELECT DISTINCT p.artikel 
            FROM produk p 
            JOIN brand b ON p.id_brand = b.id_brand
            WHERE b.nama_brand = ?
              AND p.artikel LIKE CONCAT('%', ?, '%')
            ORDER BY p.artikel ASC
            LIMIT 10
        ");
        $stmt->bind_param('ss', $brand, $q);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $data[] = $row['artikel'];
        break;

    // --- SUGGEST KODE PRODUK BERDASARKAN BRAND & ARTIKEL ---
    case 'kode':
        $stmt = $conn->prepare("
            SELECT DISTINCT p.kode_produk 
            FROM produk p 
            JOIN brand b ON p.id_brand = b.id_brand
            WHERE b.nama_brand = ? 
              AND p.artikel = ? 
              AND p.kode_produk LIKE CONCAT('%', ?, '%')
            ORDER BY p.kode_produk ASC
            LIMIT 10
        ");
        $stmt->bind_param('sss', $brand, $artikel, $q);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $data[] = $row['kode_produk'];
        break;

    // --- SUGGEST UKURAN BERDASARKAN BRAND, ARTIKEL, DAN KODE ---
    case 'ukuran':
        $stmt = $conn->prepare("
            SELECT DISTINCT p.ukuran
            FROM produk p
            JOIN brand b ON p.id_brand = b.id_brand
            WHERE b.nama_brand = ?
              AND p.artikel = ?
              AND p.kode_produk = ?
              AND p.ukuran LIKE CONCAT('%', ?, '%')
            ORDER BY p.ukuran ASC
            LIMIT 10
        ");
        $stmt->bind_param('ssss', $brand, $artikel, $kode, $q);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $data[] = $row['ukuran'];
        break;

    // --- SUGGEST WARNA BERDASARKAN BRAND, ARTIKEL, KODE, DAN UKURAN ---
    case 'warna':
        $stmt = $conn->prepare("
            SELECT DISTINCT p.warna
            FROM produk p
            JOIN brand b ON p.id_brand = b.id_brand
            WHERE b.nama_brand = ?
              AND p.artikel = ?
              AND p.kode_produk = ?
              AND p.ukuran = ?
              AND p.warna LIKE CONCAT('%', ?, '%')
            ORDER BY p.warna ASC
            LIMIT 10
        ");
        $stmt->bind_param('sssss', $brand, $artikel, $kode, $ukuran, $q);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $data[] = $row['warna'];
        break;

    // --- DETAIL LENGKAP (UNTUK AUTOFILL) ---
    case 'detail_lengkap':
        $stmt = $conn->prepare("
            SELECT p.satuan, p.hpp, p.bandrol, COALESCE(s.jumlah_gudang, 0) AS jumlah_gudang
            FROM produk p
            JOIN brand b ON p.id_brand = b.id_brand
            LEFT JOIN stok s 
                ON p.kode_produk = s.kode_produk 
               AND p.ukuran = s.ukuran 
               AND p.warna = s.warna
            WHERE b.nama_brand = ? 
              AND p.artikel = ? 
              AND p.kode_produk = ? 
              AND p.ukuran = ? 
              AND p.warna = ?
            LIMIT 1
        ");
        $stmt->bind_param("sssss", $brand, $artikel, $kode, $ukuran, $warna);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_all(MYSQLI_ASSOC);
        break;
}

echo json_encode($data);
