<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'gudang') {
    header("Location: ../");
    exit;
}
include '../../database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Barang Masuk | Tennar Shoes</title>
  <link rel="icon" type="image/png" href="../images/tennar.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-color: #29548a;
      --text-dark: #1f2d3d;
      --bg-light: #f4f7fb;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg-light);
      color: var(--text-dark);
    }

    #content {
      margin-left: 250px;
      padding: 30px;
      transition: margin-left 0.3s ease;
    }

    @media (max-width: 992px) {
      #content {
        margin-left: 0;
        padding: 20px;
      }
    }

    /* 🔹 Header bergaya dashboard Tennar */
    .topbar {
        background-color: var(--primary-color);
        color: #fff;
        padding: 16px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(41, 84, 138, 0.2);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .topbar h4 {
      font-weight: 700;
      font-size: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 3px;
    }

    .topbar .desc {
      font-size: 14px;
      color: #dbe6f6;
      margin: 0;
    }

    .btn-toggle {
      background: #fff;
      border: none;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 42px;
      width: 42px;
    }

    .btn-toggle i {
      color: var(--primary-color);
    }

    .card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .card-header {
      background-color: #fff;
      border-bottom: 1px solid #dee2e6;
      padding: 16px 24px;
    }

    .table thead {
      background-color: var(--primary-color);
      color: #fff;
    }

    .text-theme {
      color: var(--primary-color) !important;
    }
  </style>
</head>
<body>

<?php include '../../dashboard_gudang/sidebar_gudang.php'; ?>

<div id="content">
  <!-- 🔹 Header -->
  <div class="topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-toggle d-lg-none" onclick="toggleSidebar()">
        <i class="bi bi-list fs-5"></i>
      </button>
      <div>
        <h4><i class="bi bi-clock-history"></i> Riwayat Barang Masuk</h4>
      </div>
    </div>

    <div class="d-flex align-items-center gap-3">
      <span class="fw-semibold"><?= $_SESSION['nama_lengkap']; ?></span>
      <i class="bi bi-person-circle fs-4"></i>
    </div>
  </div>

  <!-- 🔹 Card Riwayat -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-semibold text-theme">
        <i class="bi bi-box-arrow-in-down"></i> Data Riwayat Barang Masuk
      </h5>
      <input type="text" id="searchInput" class="form-control w-25" placeholder="Cari Brand / Artikel / Warna...">
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center mb-0" id="riwayatTable">
          <thead>
            <tr>
              <th>No</th>
              <th>Brand</th>
              <th>Artikel</th>
              <th>Kode Produk</th>
              <th>Ukuran</th>
              <th>Warna</th>
              <th>Satuan</th>
              <th>Jumlah Masuk</th>
              <th>Tanggal</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody id="dataRiwayat">
            <tr><td colspan="10" class="text-muted py-3">Memuat data...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- 🔹 Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('active');
}

async function loadRiwayat(q = '') {
  const res = await fetch('load_riwayat.php?q=' + encodeURIComponent(q));
  const html = await res.text();
  document.getElementById('dataRiwayat').innerHTML = html;
}

document.getElementById('searchInput').addEventListener('keyup', e => loadRiwayat(e.target.value));
window.addEventListener('DOMContentLoaded', () => loadRiwayat());
</script>

</body>
</html>
