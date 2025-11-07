<?php
$current_file = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/dashboard_gudang/') !== false) {
    $depth = substr_count(str_replace('/dashboard_gudang/', '', $_SERVER['PHP_SELF']), '/');
    $base_path = str_repeat('../', $depth);
}

function isActive($targetDir, $targetFile = '')
{
    global $current_dir, $current_file;
    if ($targetFile) {
        return ($current_file === $targetFile) ? 'active' : '';
    }
    return ($current_dir === $targetDir) ? 'active' : '';
}

$isBarangMasukActive = in_array($current_dir, ['input_barang_masuk', 'riwayat_barang_masuk']);
?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Inter', sans-serif;
    }

    #sidebar {
        width: 250px;
        background: #29548a;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        padding: 25px 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 5px 0 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        z-index: 200;
        border-right: 1px solid rgba(255, 255, 255, 0.2);
    }

    #overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 150;
    }

    #overlay.active {
        display: block;
    }

    #sidebar .brand {
        text-align: center;
    }

    #sidebar .brand img {
        width: 140px;
        transition: transform 0.3s ease;
        filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.15));
    }

    #sidebar .brand img:hover {
        transform: scale(1.05);
    }

    #sidebar .brand h5 {
        color: #fff;
        font-weight: 700;
        margin-top: 12px;
        letter-spacing: 0.5px;
        font-size: 18px;
    }

    #sidebar .brand p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 13px;
        margin-bottom: 0;
        font-weight: 400;
    }

    #sidebar ul.nav {
        list-style: none;
        padding-left: 0;
        margin-top: 25px;
    }

    #sidebar ul.nav li {
        margin-bottom: 8px;
    }

    #sidebar ul.nav a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        padding: 12px 45px 12px 15px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        position: relative;
        justify-content: flex-start;
        font-weight: 500;
        letter-spacing: 0.3px;
        transition: all 0.25s ease;
        font-size: 15px;
        gap: 10px;
        width: 100%;
        box-sizing: border-box;
    }

    #sidebar ul.nav a:hover {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        transform: translateX(5px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    #sidebar ul.nav a.active {
        background: rgba(255, 255, 255, 0.3);
        color: #fff;
        font-weight: 600;
        border-left: 4px solid #fff;
        box-shadow: inset 0 0 12px rgba(255, 255, 255, 0.2);
    }

    #sidebar ul.nav a .arrow {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%) rotate(90deg);
        transition: transform 0.3s ease;
        font-size: 14px;
    }

    #sidebar ul.nav a .arrow.open {
        transform: translateY(-50%) rotate(0deg);
    }

    .submenu {
        list-style: none;
        padding-left: 35px;
        margin: 5px 0 8px;
        display: none;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .submenu.show {
        display: block;
        animation: slideDown 0.3s ease forwards;
    }

    .submenu li a {
        padding: 10px 15px;
        display: block;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.85);
        border-radius: 10px;
        transition: all 0.25s ease;
    }

    .submenu li a:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-toggle::after {
        display: none !important;
    }

    #sidebar .bottom {
        text-align: center;
        margin-top: auto;
    }

    #sidebar .bottom hr {
        border-color: rgba(255, 255, 255, 0.25);
    }

    #sidebar .bottom .btn {
        border-radius: 8px;
        border-color: rgba(255, 255, 255, 0.8);
        color: #fff;
        font-weight: 500;
        font-size: 14px;
        letter-spacing: 0.3px;
    }

    #sidebar .bottom .btn:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    #sidebar .bottom p {
        color: rgba(255, 255, 255, 0.65);
        font-size: 12px;
        margin-top: 12px;
        margin-bottom: 0;
    }

    @media (max-width: 992px) {
        #sidebar {
            transform: translateX(-100%);
        }

        #sidebar.active {
            transform: translateX(0);
        }
    }
</style>

<div id="overlay"></div>

<nav id="sidebar">
    <div>
        <div class="brand mb-4">
            <img src="<?= $base_path ?>images/tennar.png" alt="Logo Tennar">
            <h5>Tennar Shoes</h5>
            <p>Dashboard Gudang</p>
        </div>

        <ul class="nav">
            <li>
                <a href="<?= $base_path ?>dashboard_gudang/" class="<?= isActive('dashboard_gudang') ?>">
                    <span><i class="bi bi-speedometer2"></i> Dashboard</span>
                </a>
            </li>

            <li>
                <a href="<?= $base_path ?>dashboard_gudang/stok_gudang/" class="<?= isActive('stok_gudang') ?>">
                    <span><i class="bi bi-box-seam"></i> Data Stok</span>
                </a>
            </li>

            <li>
                <a href="javascript:void(0)" class="dropdown-toggle <?= $isBarangMasukActive ? 'active' : '' ?>">
                    <span><i class="bi bi-arrow-down-circle"></i> Barang Masuk</span>
                    <i class="bi bi-chevron-right arrow <?= $isBarangMasukActive ? 'open' : '' ?>"></i>
                </a>
                <ul class="submenu <?= $isBarangMasukActive ? 'show' : '' ?>">
                    <li>
                        <a href="<?= $base_path ?>dashboard_gudang/input_barang_masuk/" class="<?= isActive('input_barang_masuk') ?>">
                            <i class="bi bi-plus-circle"></i> Input Barang Masuk
                        </a>
                    </li>
                    <li>
                        <a href="<?= $base_path ?>dashboard_gudang/riwayat_barang_masuk/" class="<?= isActive('riwayat_barang_masuk') ?>">
                            <i class="bi bi-clock-history"></i> Riwayat Barang Masuk
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="<?= $base_path ?>dashboard_gudang/barang_keluar.php" class="<?= isActive('dashboard_gudang', 'barang_keluar.php') ?>">
                    <span><i class="bi bi-arrow-up-circle"></i> Barang Keluar</span>
                </a>
            </li>

            <li>
                <a href="<?= $base_path ?>dashboard_gudang/laporan.php" class="<?= isActive('dashboard_gudang', 'laporan.php') ?>">
                    <span><i class="bi bi-file-earmark-text"></i> Laporan</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="bottom mt-auto">
        <hr>
        <a href="<?= $base_path ?>logout.php" class="btn btn-outline-light w-100">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
        <p>© 2025 Tennar Shoes</p>
    </div>
</nav>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', () => {
            const submenu = toggle.nextElementSibling;
            const arrow = toggle.querySelector('.arrow');
            submenu.classList.toggle('show');
            arrow.classList.toggle('open');
        });
    });
</script>
