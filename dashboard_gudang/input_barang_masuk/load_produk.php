<?php
include '../../database.php';
header('Content-Type: application/json');

// 🚀 Cache sederhana (1 menit per query di memori PHP)
static $cache = [];

$mode    = $_GET['mode'] ?? '';
$q       = trim($_GET['q'] ?? '');
$brand   = trim($_GET['brand'] ?? '');
$artikel = trim($_GET['artikel'] ?? '');
$kode    = trim($_GET['kode'] ?? '');
$ukuran  = trim($_GET['ukuran'] ?? '');
$warna   = trim($_GET['warna'] ?? '');

// 🧠 Cegah query kosong agar tidak load semua data
if ($q === '' && $mode !== 'detail_lengkap') {
    echo json_encode([]);
    exit;
}

// 🔑 Buat key unik untuk cache
$cacheKey = md5(json_encode(compact('mode','q','brand','artikel','kode','ukuran','warna')));
if (isset($cache[$cacheKey]) && (time() - $cache[$cacheKey]['time'] < 60)) {
    echo json_encode($cache[$cacheKey]['data']);
    exit;
}

$data = [];

switch ($mode) {
    // 🔹 SUGGEST BRAND
    case 'brand':
        $stmt = $conn->prepare("
            SELECT DISTINCT nama_brand 
            FROM brand 
            WHERE LOWER(nama_brand) LIKE LOWER(CONCAT('%', ?, '%')) 
            ORDER BY nama_brand ASC 
            LIMIT 10
        ");
        $stmt->bind_param('s', $q);
        break;

    // 🔹 SUGGEST ARTIKEL BERDASARKAN BRAND
    case 'artikel':
        $stmt = $conn->prepare("
            SELECT DISTINCT p.artikel 
            FROM produk p 
            JOIN brand b ON p.id_brand = b.id_brand
            WHERE LOWER(b.nama_brand) = LOWER(?)
              AND LOWER(p.artikel) LIKE LOWER(CONCAT('%', ?, '%'))
            ORDER BY p.artikel ASC
            LIMIT 10
        ");
        $stmt->bind_param('ss', $brand, $q);
        break;

    // 🔹 SUGGEST KODE PRODUK
    case 'kode':
        $stmt = $conn->prepare("
            SELECT DISTINCT p.kode_produk 
            FROM produk p 
            JOIN brand b ON p.id_brand = b.id_brand
            WHERE LOWER(b.nama_brand) = LOWER(?)
              AND LOWER(p.artikel) = LOWER(?)
              AND LOWER(p.kode_produk) LIKE LOWER(CONCAT('%', ?, '%'))
            ORDER BY p.kode_produk ASC
            LIMIT 10
        ");
        $stmt->bind_param('sss', $brand, $artikel, $q);
        break;

    // 🔹 SUGGEST UKURAN
    case 'ukuran':
        $stmt = $conn->prepare("
            SELECT DISTINCT p.ukuran
            FROM produk p
            JOIN brand b ON p.id_brand = b.id_brand
            WHERE LOWER(b.nama_brand) = LOWER(?)
              AND LOWER(p.artikel) = LOWER(?)
              AND LOWER(p.kode_produk) = LOWER(?)
              AND LOWER(p.ukuran) LIKE LOWER(CONCAT('%', ?, '%'))
            ORDER BY p.ukuran ASC
            LIMIT 10
        ");
        $stmt->bind_param('ssss', $brand, $artikel, $kode, $q);
        break;

    // 🔹 SUGGEST WARNA
    case 'warna':
        $stmt = $conn->prepare("
            SELECT DISTINCT p.warna
            FROM produk p
            JOIN brand b ON p.id_brand = b.id_brand
            WHERE LOWER(b.nama_brand) = LOWER(?)
              AND LOWER(p.artikel) = LOWER(?)
              AND LOWER(p.kode_produk) = LOWER(?)
              AND LOWER(p.ukuran) = LOWER(?)
              AND LOWER(p.warna) LIKE LOWER(CONCAT('%', ?, '%'))
            ORDER BY p.warna ASC
            LIMIT 10
        ");
        $stmt->bind_param('sssss', $brand, $artikel, $kode, $ukuran, $q);
        break;

    // 🔹 DETAIL LENGKAP UNTUK AUTO-FILL
    case 'detail_lengkap':
        $stmt = $conn->prepare("
            SELECT p.satuan, p.hpp, p.bandrol, COALESCE(s.jumlah_gudang, 0) AS jumlah_gudang
            FROM produk p
            JOIN brand b ON p.id_brand = b.id_brand
            LEFT JOIN stok s ON s.id_produk = p.id_produk
            WHERE LOWER(b.nama_brand) = LOWER(?) 
              AND LOWER(p.artikel) = LOWER(?) 
              AND LOWER(p.kode_produk) = LOWER(?) 
              AND LOWER(p.ukuran) = LOWER(?) 
              AND LOWER(p.warna) = LOWER(?)
            LIMIT 1
        ");
        $stmt->bind_param('sssss', $brand, $artikel, $kode, $ukuran, $warna);
        break;

    default:
        echo json_encode([]); 
        exit;
}

$stmt->execute();
$res = $stmt->get_result();

if ($mode === 'detail_lengkap') {
    $data = $res->fetch_all(MYSQLI_ASSOC);
} else {
    while ($row = $res->fetch_assoc()) {
        $val = reset($row);
        if ($val !== null && $val !== '') $data[] = $val;
    }
}


// 💾 Simpan ke cache sementara (RAM PHP)
$cache[$cacheKey] = ['data' => $data, 'time' => time()];

echo json_encode($data);
