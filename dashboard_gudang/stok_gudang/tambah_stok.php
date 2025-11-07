<?php
include '../../database.php';

// ==== PROSES TAMBAH DATA STOK (AJAX) ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    $nama_brand = trim($_POST['nama_brand']);
    $artikel = trim($_POST['artikel']);
    $kode_produk = trim($_POST['kode_produk']);
    $ukuran = trim($_POST['ukuran']);
    $warna = trim($_POST['warna']);
    $satuan = "PASANG"; // ✅ otomatis diisi PASANG
    $hpp = $_POST['hpp'] ?: 0;
    $bandrol = $_POST['bandrol'] ?: 0;
    $jumlah_gudang = $_POST['jumlah_gudang'] ?: 0;

    // Validasi wajib isi
    if ($nama_brand === '' || $artikel === '' || $ukuran === '' || $warna === '' || $jumlah_gudang <= 0) {
        echo json_encode(['status' => 'error', 'message' => '❌ Kolom wajib belum lengkap!']);
        exit;
    }

    // === BRAND ===
    $cekBrand = $conn->prepare("SELECT id_brand FROM brand WHERE nama_brand = ?");
    $cekBrand->bind_param("s", $nama_brand);
    $cekBrand->execute();
    $cekBrand->store_result();

    if ($cekBrand->num_rows > 0) {
        $cekBrand->bind_result($id_brand);
        $cekBrand->fetch();
    } else {
        $insertBrand = $conn->prepare("INSERT INTO brand (nama_brand) VALUES (?)");
        $insertBrand->bind_param("s", $nama_brand);
        $insertBrand->execute();
        $id_brand = $insertBrand->insert_id;
    }

    // === PRODUK ===
    $cekProduk = $conn->prepare("
        SELECT id_produk FROM produk 
        WHERE kode_produk = ? AND ukuran = ? AND warna = ? AND satuan = ? AND hpp = ? AND bandrol = ?
    ");
    $cekProduk->bind_param("ssssdd", $kode_produk, $ukuran, $warna, $satuan, $hpp, $bandrol);
    $cekProduk->execute();
    $cekProduk->store_result();

    if ($cekProduk->num_rows == 0) {
        $insertProduk = $conn->prepare("
            INSERT INTO produk (id_brand, artikel, kode_produk, ukuran, warna, satuan, hpp, bandrol)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insertProduk->bind_param("isssssdd", $id_brand, $artikel, $kode_produk, $ukuran, $warna, $satuan, $hpp, $bandrol);
        $insertProduk->execute();
    }

    // === STOK ===
    $cekProdukId = $conn->prepare("
        SELECT id_produk FROM produk
        WHERE kode_produk = ? AND ukuran = ? AND warna = ? AND satuan = ?
    ");
    $cekProdukId->bind_param("ssss", $kode_produk, $ukuran, $warna, $satuan);
    $cekProdukId->execute();
    $cekProdukId->bind_result($id_produk);
    $cekProdukId->fetch();
    $cekProdukId->close();

    if (!$id_produk) {
        echo json_encode(['status' => 'error', 'message' => '❌ Produk tidak ditemukan!']);
        exit;
    }

    $cekStok = $conn->prepare("SELECT id_stok, jumlah_gudang FROM stok WHERE id_produk = ?");
    $cekStok->bind_param("i", $id_produk);
    $cekStok->execute();
    $resultStok = $cekStok->get_result();

    if ($resultStok->num_rows > 0) {
        $data = $resultStok->fetch_assoc();
        $jumlah_baru = $data['jumlah_gudang'] + $jumlah_gudang;
        $updateStok = $conn->prepare("UPDATE stok SET jumlah_gudang = ?, tanggal_update = NOW() WHERE id_stok = ?");
        $updateStok->bind_param("ii", $jumlah_baru, $data['id_stok']);
        $updateStok->execute();
    } else {
        $insertStok = $conn->prepare("INSERT INTO stok (id_produk, jumlah_gudang) VALUES (?, ?)");
        $insertStok->bind_param("ii", $id_produk, $jumlah_gudang);
        $insertStok->execute();
    }

    echo json_encode(['status' => 'success', 'message' => '✅ Data stok berhasil disimpan!']);
    exit;
}
?>

<!-- Modal Tambah Stok -->
<div class="modal fade" id="modalTambahStok" tabindex="-1" aria-labelledby="modalTambahStokLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header" style="background: linear-gradient(135deg, #29548a, #3b6cb2); color: white;">
        <h5 class="modal-title fw-semibold">
          <i class="bi bi-plus-circle"></i> Tambah Stok Barang
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="formTambahStok" autocomplete="off">
        <div class="modal-body px-4 py-3 bg-light rounded-bottom-4">
          <div class="row g-3">

            <label class="form-label fw-semibold text-dark">IDENTITAS PRODUK</label>

            <!-- Brand -->
            <div class="col-md-4 position-relative">
              <label class="form-label fw-semibold text-dark">Brand <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nama_brand" id="nama_brand" placeholder="Ketik atau pilih brand..." required>
              <div id="suggest_brand" class="suggest-box"></div>
            </div>

            <!-- Artikel -->
            <div class="col-md-4 position-relative">
              <label class="form-label fw-semibold text-dark">Artikel <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="artikel" id="artikel" placeholder="Ketik atau pilih artikel..." required>
              <div id="suggest_artikel" class="suggest-box"></div>
            </div>

            <!-- Kode Produk -->
            <div class="col-md-4 position-relative">
              <label class="form-label fw-semibold text-dark">Kode Produk</label>
              <input type="text" class="form-control" name="kode_produk" id="kode_produk" placeholder="Ketik atau pilih kode produk...">
              <div id="suggest_kode" class="suggest-box"></div>
            </div>

            <label class="form-label fw-semibold text-dark">DETAIL PRODUK</label>

            <!-- Ukuran -->
            <div class="col-md-6 position-relative">
                <label class="form-label fw-semibold text-dark">Ukuran <span class="text-danger">*</span></label>
                <input type="text" name="ukuran" id="ukuran" class="form-control" placeholder="Ukuran" required>
                <div id="suggest_ukuran" class="suggest-box"></div>
            </div>

            <!-- Warna -->
            <div class="col-md-6 position-relative">
                <label class="form-label fw-semibold text-dark">Warna <span class="text-danger">*</span></label>
                <input type="text" name="warna" id="warna" class="form-control" placeholder="Warna" required>
                <div id="suggest_warna" class="suggest-box"></div>
            </div>

            <!-- HPP -->
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">HPP</label>
              <input type="number" name="hpp" id="hpp" class="form-control" placeholder="Harga pokok produksi">
            </div>

            <!-- Bandrol -->
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Bandrol</label>
              <input type="number" name="bandrol" id="bandrol" class="form-control" placeholder="Harga bandrol">
            </div>

            <label class="form-label fw-semibold text-dark">JUMLAH STOK</label>

            <!-- Jumlah -->
            <div class="col-md-12">
              <label class="form-label fw-semibold text-dark">Jumlah Stok <span class="text-danger">*</span></label>
              <input type="number" name="jumlah_gudang" id="jumlah_gudang" class="form-control" placeholder="Masukkan jumlah stok gudang" required>
            </div>

          </div>
        </div>

        <div class="modal-footer bg-white border-0 rounded-bottom-4">
          <button type="button" class="btn btn-warning text-white" onclick="resetForm()">
            <i class="bi bi-arrow-counterclockwise"></i> Reset
          </button>
          <button type="submit" class="btn btn-primary" style="background-color:#29548a;">
            <i class="bi bi-save"></i> Simpan Data
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.suggest-box {
  position: absolute;
  background: #fff;
  border: 1px solid #29548a40;
  border-radius: 8px;
  width: 100%;
  max-height: 200px;
  overflow-y: auto;
  box-shadow: 0 4px 10px rgba(41, 84, 138, 0.15);
  z-index: 1050;
  display: none;
}
.suggest-box div {
  padding: 8px 12px;
  cursor: pointer;
  color: #1f2d3d;
}
.suggest-box div:hover {
  background: #29548a;
  color: #fff;
}
</style>

<script>
// --- Reset form ---
function resetForm() {
  document.getElementById('formTambahStok').reset();
  document.querySelectorAll('.suggest-box').forEach(b => b.style.display = 'none');
}

// --- Submit Form AJAX ---
document.getElementById('formTambahStok').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('ajax', '1');

  const res = await fetch('tambah_stok.php', { method: 'POST', body: fd });
  const result = await res.json();

  if (result.status === 'success') {
    bootstrap.Modal.getInstance(document.getElementById('modalTambahStok')).hide();
    resetForm();
    fetch('table_stok.php').then(r => r.text()).then(html => {
      document.querySelector('#table-stok-container').innerHTML = html;
    });

    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success position-fixed top-0 end-0 m-3 shadow fade show';
    alertDiv.style.zIndex = '2000';
    alertDiv.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>${result.message}`;
    document.body.appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 2000);
  } else {
    alert(result.message);
  }
});

// === AUTO SUGGEST ===
function suggestInput(inputId, suggestId, url, callback) {
  const input = document.getElementById(inputId);
  const suggest = document.getElementById(suggestId);
  const q = input.value.trim();
  if (q.length < 1) { suggest.style.display = "none"; return; }

  fetch(url + encodeURIComponent(q))
    .then(r => r.json())
    .then(data => {
      suggest.innerHTML = data.map(d => `<div>${d}</div>`).join('');
      suggest.style.display = data.length ? "block" : "none";
      suggest.querySelectorAll('div').forEach(div => {
        div.onclick = () => { input.value = div.textContent; suggest.style.display = "none"; callback && callback(); };
      });
    });
}

document.getElementById('nama_brand').addEventListener('keyup', () => {
  suggestInput('nama_brand', 'suggest_brand', 'load_produk.php?mode=brand&q=');
});

document.getElementById('artikel').addEventListener('keyup', () => {
  const brandVal = document.getElementById('nama_brand').value.trim();
  if (!brandVal) return;
  suggestInput('artikel', 'suggest_artikel', 
    `load_produk.php?mode=artikel&brand=${encodeURIComponent(brandVal)}&q=`);
});

document.getElementById('kode_produk').addEventListener('keyup', () => {
  const brandVal = document.getElementById('nama_brand').value.trim();
  const artikelVal = document.getElementById('artikel').value.trim();
  if (!brandVal || !artikelVal) return;
  suggestInput('kode_produk', 'suggest_kode', 
    `load_produk.php?mode=kode&brand=${encodeURIComponent(brandVal)}&artikel=${encodeURIComponent(artikelVal)}&q=`);
});

document.getElementById('ukuran').addEventListener('keyup', () => {
  const brandVal = document.getElementById('nama_brand').value.trim();
  const artikelVal = document.getElementById('artikel').value.trim();
  const kodeVal = document.getElementById('kode_produk').value.trim();
  if (!brandVal || !artikelVal || !kodeVal) return;
  suggestInput('ukuran', 'suggest_ukuran', 
    `load_produk.php?mode=ukuran&brand=${encodeURIComponent(brandVal)}&artikel=${encodeURIComponent(artikelVal)}&kode=${encodeURIComponent(kodeVal)}&q=`);
});

document.getElementById('warna').addEventListener('keyup', () => {
  const brandVal = document.getElementById('nama_brand').value.trim();
  const artikelVal = document.getElementById('artikel').value.trim();
  const kodeVal = document.getElementById('kode_produk').value.trim();
  const ukuranVal = document.getElementById('ukuran').value.trim();
  if (!brandVal || !artikelVal || !kodeVal || !ukuranVal) return;
  suggestInput('warna', 'suggest_warna', 
    `load_produk.php?mode=warna&brand=${encodeURIComponent(brandVal)}&artikel=${encodeURIComponent(artikelVal)}&kode=${encodeURIComponent(kodeVal)}&ukuran=${encodeURIComponent(ukuranVal)}&q=`);
});

// === AUTO FILL ===
async function autoFill() {
  const brand = nama_brand.value.trim(),
        artikelVal = artikel.value.trim(),
        kode = kode_produk.value.trim(),
        ukuranVal = ukuran.value.trim(),
        warnaVal = warna.value.trim();

  if (brand && artikelVal && kode && ukuranVal && warnaVal) {
    const res = await fetch(`load_produk.php?mode=detail_lengkap&brand=${encodeURIComponent(brand)}&artikel=${encodeURIComponent(artikelVal)}&kode=${encodeURIComponent(kode)}&ukuran=${encodeURIComponent(ukuranVal)}&warna=${encodeURIComponent(warnaVal)}`);
    const data = await res.json();
    if (data && data.length) {
      const d = data[0];
      document.getElementById('hpp').value = d.hpp || '';
      document.getElementById('bandrol').value = d.bandrol || '';
      document.getElementById('jumlah_gudang').value = d.jumlah_gudang || '';
    }
  }
}

['nama_brand','artikel','kode_produk','ukuran','warna'].forEach(id => {
  document.getElementById(id).addEventListener('change', autoFill);
  document.getElementById(id).addEventListener('keyup', autoFill);
});
</script>
