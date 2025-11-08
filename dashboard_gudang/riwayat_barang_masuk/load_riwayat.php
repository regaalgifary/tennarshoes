<?php
include '../../database.php';

$q = trim($_GET['q'] ?? '');
$where = '';
if ($q !== '') {
  $where = "WHERE b.nama_brand LIKE '%$q%' OR p.artikel LIKE '%$q%' OR p.warna LIKE '%$q%'";
}

$sql = "
  SELECT bm.*, b.nama_brand, p.artikel, p.kode_produk, p.ukuran, p.warna, p.satuan
  FROM barang_masuk bm
  JOIN produk p ON bm.id_produk = p.id_produk
  JOIN brand b ON p.id_brand = b.id_brand
  $where
  ORDER BY bm.tanggal DESC
";

$res = $conn->query($sql);
$no = 1;

if ($res->num_rows > 0) {
  while ($row = $res->fetch_assoc()) {
    echo "<tr>
      <td>{$no}</td>
      <td>{$row['nama_brand']}</td>
      <td>{$row['artikel']}</td>
      <td>{$row['kode_produk']}</td>
      <td>{$row['ukuran']}</td>
      <td>{$row['warna']}</td>
      <td>{$row['satuan']}</td>
      <td>{$row['jumlah']}</span></td>
      <td>" . date('d/m/Y H:i', strtotime($row['tanggal'])) . "</td>
      <td>{$row['keterangan']}</td>
    </tr>";
    $no++;
  }
} else {
  echo "<tr><td colspan='10' class='text-muted py-3'>Tidak ada data barang masuk.</td></tr>";
}
