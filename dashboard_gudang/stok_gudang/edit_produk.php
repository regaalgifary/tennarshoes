<?php
include '../../database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $id = intval($_POST['id_produk']);
    $artikel = trim($_POST['artikel']);
    $kode = trim($_POST['kode_produk']);
    $ukuran = trim($_POST['ukuran']);
    $warna = trim($_POST['warna']);
    $hpp = floatval($_POST['hpp']);
    $bandrol = floatval($_POST['bandrol']);
    $jumlah_gudang = intval($_POST['jumlah_gudang']);
    $jumlah_grosir = intval($_POST['jumlah_grosir']);
    $jumlah_retail = intval($_POST['jumlah_retail']);
    $jumlah_canvas = intval($_POST['jumlah_canvas']);

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => '❌ ID produk tidak valid.']);
        exit;
    }

    $update = $conn->prepare("UPDATE produk SET artikel=?, kode_produk=?, ukuran=?, warna=?, hpp=?, bandrol=? WHERE id_produk=?");
    $update->bind_param("ssssddi", $artikel, $kode, $ukuran, $warna, $hpp, $bandrol, $id);
    $update->execute();

    $stok = $conn->prepare("UPDATE stok SET jumlah_gudang=?, jumlah_grosir=?, jumlah_retail=?, jumlah_canvas=? WHERE id_produk=?");
    $stok->bind_param("iiiii", $jumlah_gudang, $jumlah_grosir, $jumlah_retail, $jumlah_canvas, $id);
    $stok->execute();

    echo json_encode(['status' => 'success', 'message' => '✅ Data produk berhasil diperbarui.']);
}
?>
