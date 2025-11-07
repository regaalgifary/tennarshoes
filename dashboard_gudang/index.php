<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'gudang') {
    header("Location: ../");
    exit;
}
include '../database.php';

// Hitung total barang dari semua lokasi
$total_barang = 0;
$result = $conn->query("
    SELECT 
        SUM(jumlah_gudang + jumlah_grosir + jumlah_retail + jumlah_canvas) AS total_barang 
    FROM stok
");
if ($result && $row = $result->fetch_assoc()) {
    $total_barang = $row['total_barang'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gudang | Tennar Shoes</title>
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
            font-family: 'Inter', system-ui, sans-serif;
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
                margin-left: 0 !important;
                padding: 20px;
            }
        }

        /* ===== Topbar ===== */
        .topbar {
            background-color: var(--primary-color);
            color: #fff;
            padding: 16px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(41, 84, 138, 0.2);
            margin-bottom: 25px;
        }

        .topbar h4 {
            font-weight: 600;
            margin: 0;
        }

        .btn-toggle {
            background: #fff;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-toggle i {
            color: var(--primary-color);
        }

        /* ===== Card Statistik ===== */
        .card {
            border: none;
            border-radius: 15px;
            background-color: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-align: center;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(41,84,138,0.25);
        }

        .card i {
            color: var(--primary-color);
        }

        .card h6 {
            font-weight: 500;
            color: #555;
            margin-top: 10px;
        }

        .card h3 {
            font-weight: 700;
            color: var(--primary-color);
            margin-top: 5px;
        }

        /* ===== Tabel & Aktivitas ===== */
        .card-header {
            background-color: #fff;
            border-bottom: 2px solid #eaeaea;
        }

        .card-header h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            border: none;
            color: #fff;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .btn-primary-custom:hover {
            background-color: #1f3e6d;
        }

        .table thead {
            background-color: var(--primary-color);
            color: #fff;
        }

        .badge.bg-success {
            background-color: #28a745 !important;
        }

        .badge.bg-danger {
            background-color: #dc3545 !important;
        }
    </style>
</head>
<body>

<?php include 'sidebar_gudang.php'; ?>

<div id="content">
    <!-- TOPBAR -->
    <div class="topbar d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-toggle d-lg-none" onclick="toggleSidebar()">
                <i class="bi bi-list fs-4"></i>
            </button>
            <h4><i class="bi bi-speedometer2"></i> Dashboard Gudang</h4>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="fw-semibold"><?= $_SESSION['nama_lengkap']; ?></span>
            <i class="bi bi-person-circle fs-4"></i>
        </div>
    </div>

    <!-- STATISTIK CARD -->
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card p-3">
                <i class="bi bi-box-seam fs-1"></i>
                <h6>Total Barang</h6>
                <h3><?= number_format($total_barang); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <i class="bi bi-arrow-down-circle fs-1"></i>
                <h6>Barang Masuk</h6>
                <h3>-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <i class="bi bi-arrow-up-circle fs-1"></i>
                <h6>Barang Keluar</h6>
                <h3>-</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <i class="bi bi-exclamation-circle fs-1"></i>
                <h6>Stok Minimum</h6>
                <h3>-</h3>
            </div>
        </div>
    </div>

    <!-- AKTIVITAS TERBARU -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="bi bi-clock-history"></i> Aktivitas Terbaru</h5>
            <a href="laporan.php" class="btn btn-primary-custom btn-sm">Lihat Semua</a>
        </div>
        <div class="card-body text-center text-muted">
            <p class="mb-0">Belum ada aktivitas terbaru.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay')?.classList.toggle('active');
}
</script>
</body>
</html>
