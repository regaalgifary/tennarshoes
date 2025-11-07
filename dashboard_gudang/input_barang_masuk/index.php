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
    <title>Input Barang Masuk | Tennar Shoes</title>
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

        .card-option {
            border: none;
            border-radius: 15px;
            text-align: center;
            padding: 30px 20px;
            background-color: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card-option:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(41,84,138,0.25);
        }

        .card-option i {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .card-option h5 {
            font-weight: 600;
            color: var(--primary-color);
        }
    </style>
</head>
<body>

<?php include '../../dashboard_gudang/sidebar_gudang.php'; ?>

<div id="content">
    <div class="topbar d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-toggle d-lg-none" onclick="toggleSidebar()">
                <i class="bi bi-list fs-4"></i>
            </button>
            <h4><i class="bi bi-arrow-down-circle"></i> Input Barang Masuk</h4>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="fw-semibold"><?= $_SESSION['nama_lengkap']; ?></span>
            <i class="bi bi-person-circle fs-4"></i>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row g-4 justify-content-center">
            <div class="col-md-3">
                <a href="supplier.php" class="text-decoration-none">
                    <div class="card-option">
                        <i class="bi bi-truck"></i>
                        <h5>Supplier</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="grosir.php" class="text-decoration-none">
                    <div class="card-option">
                        <i class="bi bi-shop"></i>
                        <h5>Grosir</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="retail.php" class="text-decoration-none">
                    <div class="card-option">
                        <i class="bi bi-basket2"></i>
                        <h5>Retail</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="canvas.php" class="text-decoration-none">
                    <div class="card-option">
                        <i class="bi bi-truck-flatbed"></i>
                        <h5>Canvas</h5>
                    </div>
                </a>
            </div>
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
