<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'gudang') {
    header("Location: ../../");
    exit;
}
include '../../database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Stok Gudang | Tennar Shoes</title>
    <link rel="icon" type="image/png" href="../../images/tennar.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #29548a;
            --text-dark: #1f2d3d;
            --bg-light: #f4f7fb;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fb;
        }
        #content {
            margin-left: 250px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 992px) {
            #content { margin-left: 0 !important; padding: 20px; }
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
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .table thead { background-color: var(--primary-color); color: white; }
        .table tbody tr:hover { background-color: rgba(79, 70, 229, 0.08); }
        .btn-primary { background-color: var(--primary-color); border: none; }
        .btn-primary:hover { background-color: #1f3e6d; }
        .badge-gradient {
            color: #fff;
            padding: 6px 12px;
            border-radius: 10px;
            font-weight: 600;
            background: linear-gradient(135deg, #29548a, #3b6cb2);
        }
        .badge-gradient-2 {
            background: linear-gradient(135deg, #3b6cb2, #4b82d4);
        }
        .badge-gradient-3 {
            background: linear-gradient(135deg, #4b82d4, #5b9ae8);
        }
        .badge-gradient-4 {
            background: linear-gradient(135deg, #5b9ae8, #6bb1f9);
        }
    </style>
</head>
<body>

<?php include '../sidebar_gudang.php'; ?>

<div id="content">
    <div class="topbar d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light d-lg-none" onclick="toggleSidebar()" 
                style="background: white; border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                <i class="bi bi-list text-primary fs-4"></i>
            </button>
            <h4 class="mb-0 text-white"><i class="bi bi-box-seam"></i> Data Stok Gudang</h4>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="fw-semibold text-white"><?= $_SESSION['nama_lengkap']; ?></span>
            <i class="bi bi-person-circle fs-4 text-light"></i>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-list-ul"></i> Daftar Stok Barang
            </h5>
            <button class="btn btn-light btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahStok" style="color:#29548a; border-radius:8px;">
                <i class="bi bi-plus-circle"></i> Tambah Barang
            </button>
            <?php include 'tambah_stok.php'; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Brand</th>
                            <th>Artikel</th>
                            <th>Kode Produk</th>
                            <th>Ukuran</th>
                            <th>Warna</th>
                            <th>Satuan</th>
                            <th>HPP</th>
                            <th>Bandrol</th>
                            <th>Gudang</th>
                            <th>Grosir</th>
                            <th>Retail</th>
                            <th>Canvas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "
                            SELECT p.*, b.nama_brand, 
                                   s.jumlah_gudang, s.jumlah_grosir, s.jumlah_retail, s.jumlah_canvas
                            FROM produk p 
                            LEFT JOIN brand b ON p.id_brand = b.id_brand 
                            LEFT JOIN stok s ON p.id_produk = s.id_produk
                            ORDER BY p.id_produk ASC
                        ";
                        $result = $conn->query($query);
                        $no = 1;

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "
                                <tr>
                                    <td>{$no}</td>
                                    <td>{$row['nama_brand']}</td>
                                    <td>{$row['artikel']}</td>
                                    <td>{$row['kode_produk']}</td>
                                    <td>{$row['ukuran']}</td>
                                    <td>{$row['warna']}</td>
                                    <td>{$row['satuan']}</td>
                                    <td>Rp " . number_format($row['hpp'], 0, ',', '.') . "</td>
                                    <td>Rp " . number_format($row['bandrol'], 0, ',', '.') . "</td>
                                    <td><span class='badge badge-gradient'>{$row['jumlah_gudang']}</span></td>
                                    <td><span class='badge badge-gradient badge-gradient-2'>{$row['jumlah_grosir']}</span></td>
                                    <td><span class='badge badge-gradient badge-gradient-3'>{$row['jumlah_retail']}</span></td>
                                    <td><span class='badge badge-gradient badge-gradient-4'>{$row['jumlah_canvas']}</span></td>
                                    <td>
                                        <button class='btn btn-sm btn-warning text-white btn-edit'
                                            data-id='{$row['id_produk']}'
                                            data-brand='{$row['nama_brand']}'
                                            data-artikel='{$row['artikel']}'
                                            data-kode='{$row['kode_produk']}'
                                            data-ukuran='{$row['ukuran']}'
                                            data-warna='{$row['warna']}'
                                            data-hpp='{$row['hpp']}'
                                            data-bandrol='{$row['bandrol']}'
                                            data-gudang='{$row['jumlah_gudang']}'
                                            data-grosir='{$row['jumlah_grosir']}'
                                            data-retail='{$row['jumlah_retail']}'
                                            data-canvas='{$row['jumlah_canvas']}'>
                                            <i class='bi bi-pencil-square'></i>
                                        </button>
                                    </td>
                                </tr>
                                ";
                                $no++;
                            }
                        } else {
                            echo "
                            <tr>
                                <td colspan='14' class='text-muted py-4 text-center'>
                                    Belum ada data produk.
                                </td>
                            </tr>
                            ";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Produk -->
<div class="modal fade" id="modalEditProduk" tabindex="-1" aria-labelledby="modalEditProdukLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header" style="background-color: #29548a; color: white;">
        <h5 class="modal-title fw-semibold">
          <i class="bi bi-pencil-square"></i> Edit Data Produk
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="formEditProduk" autocomplete="off">
        <div class="modal-body px-4 py-3 bg-light rounded-bottom-4">
          <input type="hidden" name="id_produk" id="edit_id_produk">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold text-dark">Brand</label>
              <input type="text" class="form-control" id="edit_nama_brand" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold text-dark">Artikel</label>
              <input type="text" class="form-control" name="artikel" id="edit_artikel" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold text-dark">Kode Produk</label>
              <input type="text" class="form-control" name="kode_produk" id="edit_kode" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Ukuran</label>
              <input type="text" class="form-control" name="ukuran" id="edit_ukuran" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Warna</label>
              <input type="text" class="form-control" name="warna" id="edit_warna" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">HPP</label>
              <input type="number" class="form-control" name="hpp" id="edit_hpp">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Bandrol</label>
              <input type="number" class="form-control" name="bandrol" id="edit_bandrol">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold text-dark">Jumlah Stok Gudang</label>
              <input type="number" class="form-control" name="jumlah_gudang" id="edit_gudang">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold text-dark">Jumlah Stok Grosir</label>
              <input type="number" class="form-control" name="jumlah_grosir" id="edit_grosir">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold text-dark">Jumlah Stok Retail</label>
              <input type="number" class="form-control" name="jumlah_retail" id="edit_retail">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold text-dark">Jumlah Stok Canvas</label>
              <input type="number" class="form-control" name="jumlah_canvas" id="edit_canvas">
            </div>
          </div>
        </div>
        <div class="modal-footer bg-white border-0 rounded-bottom-4">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Tutup
          </button>
          <button type="submit" class="btn btn-primary" style="background-color:#29548a;">
            <i class="bi bi-save"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay')?.classList.toggle('active');
}

// --- isi modal edit ---
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('edit_id_produk').value = btn.dataset.id;
    document.getElementById('edit_nama_brand').value = btn.dataset.brand;
    document.getElementById('edit_artikel').value = btn.dataset.artikel;
    document.getElementById('edit_kode').value = btn.dataset.kode;
    document.getElementById('edit_ukuran').value = btn.dataset.ukuran;
    document.getElementById('edit_warna').value = btn.dataset.warna;
    document.getElementById('edit_hpp').value = btn.dataset.hpp;
    document.getElementById('edit_bandrol').value = btn.dataset.bandrol;
    document.getElementById('edit_gudang').value = btn.dataset.gudang;
    document.getElementById('edit_grosir').value = btn.dataset.grosir;
    document.getElementById('edit_retail').value = btn.dataset.retail;
    document.getElementById('edit_canvas').value = btn.dataset.canvas;
    new bootstrap.Modal(document.getElementById('modalEditProduk')).show();
  });
});

// --- submit edit AJAX ---
document.getElementById('formEditProduk').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('ajax', '1');

  const res = await fetch('edit_produk.php', { method: 'POST', body: fd });
  const result = await res.json();

  if (result.status === 'success') {
    const modalEl = document.getElementById('modalEditProduk');
    bootstrap.Modal.getInstance(modalEl).hide();
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());

    // ✅ Notifikasi sukses tanpa reload
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success position-fixed top-0 end-0 m-3 shadow fade show';
    alertDiv.style.zIndex = '2000';
    alertDiv.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>${result.message}`;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 2000);

    // reload data tabel setelah delay singkat
    setTimeout(() => location.reload(), 1200);
  } else {
    alert(result.message);
  }
});
</script>
</body>
</html>
