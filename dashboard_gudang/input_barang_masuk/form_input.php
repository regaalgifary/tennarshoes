<?php
include '../../database.php';

// ==== PROSES TAMBAH BARANG MASUK (AJAX) ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    $nama_brand = trim($_POST['nama_brand']);
    $artikel = trim($_POST['artikel']);
    $kode_produk = trim($_POST['kode_produk']);
    $ukuran = trim($_POST['ukuran']);
    $warna = trim($_POST['warna']);
    $satuan = "PASANG";
    $hpp = $_POST['hpp'] ?: 0;
    $bandrol = $_POST['bandrol'] ?: 0;
    $jumlah_gudang = $_POST['jumlah_gudang'] ?: 0;
    $keterangan = $_POST['keterangan'] ?? '';

    if ($nama_brand === '' || $artikel === '' || $ukuran === '' || $warna === '' || $jumlah_gudang <= 0) {
        echo json_encode(['status' => 'error', 'message' => '❌ Kolom wajib belum lengkap!']);
        exit;
    }

    // 🔹 BRAND
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

    // 🔹 PRODUK (cek apakah sudah ada)
    $cekProduk = $conn->prepare("
        SELECT id_produk FROM produk 
        WHERE id_brand = ? AND artikel = ? AND kode_produk = ? AND ukuran = ? AND warna = ? AND satuan = ?
    ");
    $cekProduk->bind_param("isssss", $id_brand, $artikel, $kode_produk, $ukuran, $warna, $satuan);
    $cekProduk->execute();
    $cekProduk->store_result();

    if ($cekProduk->num_rows > 0) {
        // Jika sudah ada → ambil ID
        $cekProduk->bind_result($id_produk);
        $cekProduk->fetch();

        // Update harga bila berubah
        $updateHarga = $conn->prepare("UPDATE produk SET hpp = ?, bandrol = ? WHERE id_produk = ?");
        $updateHarga->bind_param("ddi", $hpp, $bandrol, $id_produk);
        $updateHarga->execute();
    } else {
        // Jika belum ada → tambah produk baru
        $insertProduk = $conn->prepare("
            INSERT INTO produk (id_brand, artikel, kode_produk, ukuran, warna, satuan, hpp, bandrol)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insertProduk->bind_param("isssssdd", $id_brand, $artikel, $kode_produk, $ukuran, $warna, $satuan, $hpp, $bandrol);
        $insertProduk->execute();
        $id_produk = $insertProduk->insert_id;
    }

    // 🔹 STOK
    $cekStok = $conn->prepare("SELECT id_stok, jumlah_gudang FROM stok WHERE id_produk = ?");
    $cekStok->bind_param("i", $id_produk);
    $cekStok->execute();
    $resultStok = $cekStok->get_result();

    if ($resultStok->num_rows > 0) {
        $data = $resultStok->fetch_assoc();
        $jumlah_baru = $data['jumlah_gudang'] + $jumlah_gudang;
        $updateStok = $conn->prepare("UPDATE stok SET jumlah_gudang = ? WHERE id_stok = ?");
        $updateStok->bind_param("ii", $jumlah_baru, $data['id_stok']);
        $updateStok->execute();
    } else {
        $insertStok = $conn->prepare("INSERT INTO stok (id_produk, jumlah_gudang) VALUES (?, ?)");
        $insertStok->bind_param("ii", $id_produk, $jumlah_gudang);
        $insertStok->execute();
    }

    // 🔹 CATAT TRANSAKSI KE TABEL BARANG_MASUK
    $insertMasuk = $conn->prepare("
        INSERT INTO barang_masuk (id_produk, jumlah, tanggal, keterangan)
        VALUES (?, ?, NOW(), ?)
    ");
    $insertMasuk->bind_param("iis", $id_produk, $jumlah_gudang, $keterangan);
    $insertMasuk->execute();

    echo json_encode(['status' => 'success', 'message' => '✅ Barang masuk berhasil disimpan!']);
    exit;
}
?>

<!-- 🔹 Modal Input Barang Masuk -->
<div class="modal fade" id="modalSupplier" tabindex="-1" aria-labelledby="modalSupplierLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header" style="background: linear-gradient(135deg, #29548a, #3b6cb2); color: white;">
        <h5 class="modal-title fw-semibold"><i class="bi bi-truck"></i> Barang Masuk dari Supplier</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="formBarangMasuk" autocomplete="off">
        <div class="modal-body px-4 py-3 bg-light rounded-bottom-4">
          <div class="row g-3">

            <label class="form-label fw-semibold text-dark">IDENTITAS PRODUK</label>

            <div class="col-md-4 position-relative">
              <label class="form-label fw-semibold text-dark">Brand <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nama_brand" id="nama_brand" placeholder="Ketik atau pilih brand..." required>
              <div id="suggest_brand" class="suggest-box"></div>
            </div>

            <div class="col-md-4 position-relative">
              <label class="form-label fw-semibold text-dark">Artikel <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="artikel" id="artikel" placeholder="Ketik atau pilih artikel..." required>
              <div id="suggest_artikel" class="suggest-box"></div>
            </div>

            <div class="col-md-4 position-relative">
              <label class="form-label fw-semibold text-dark">Kode Produk</label>
              <input type="text" class="form-control" name="kode_produk" id="kode_produk" placeholder="Ketik atau pilih kode produk...">
              <div id="suggest_kode" class="suggest-box"></div>
            </div>

            <label class="form-label fw-semibold text-dark">DETAIL PRODUK</label>

            <div class="col-md-6 position-relative">
              <label class="form-label fw-semibold text-dark">Ukuran <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="ukuran" id="ukuran" placeholder="Ukuran" required>
              <div id="suggest_ukuran" class="suggest-box"></div>
            </div>

            <div class="col-md-6 position-relative">
              <label class="form-label fw-semibold text-dark">Warna <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="warna" id="warna" placeholder="Warna" required>
              <div id="suggest_warna" class="suggest-box"></div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">HPP</label>
              <input type="number" class="form-control" name="hpp" id="hpp" placeholder="Harga pokok produksi">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">Bandrol</label>
              <input type="number" class="form-control" name="bandrol" id="bandrol" placeholder="Harga bandrol">
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold text-dark">Jumlah Barang Masuk <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="jumlah_gudang" id="jumlah_gudang" required>
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold text-dark">Keterangan</label>
              <textarea name="keterangan" id="keterangan" class="form-control" rows="2"></textarea>
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
function resetForm() {
  document.getElementById('formBarangMasuk').reset();
  document.querySelectorAll('.suggest-box').forEach(b => b.style.display = 'none');
}

document.getElementById('formBarangMasuk').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('ajax', '1');

  const res = await fetch('form_input.php', { method: 'POST', body: fd });
  const result = await res.json();

  if (result.status === 'success') {
    bootstrap.Modal.getInstance(document.getElementById('modalSupplier')).hide();
    resetForm();
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
  suggestInput('artikel', 'suggest_artikel', `load_produk.php?mode=artikel&brand=${encodeURIComponent(brandVal)}&q=`);
});
document.getElementById('kode_produk').addEventListener('keyup', () => {
  const brandVal = document.getElementById('nama_brand').value.trim();
  const artikelVal = document.getElementById('artikel').value.trim();
  if (!brandVal || !artikelVal) return;
  suggestInput('kode_produk', 'suggest_kode', `load_produk.php?mode=kode&brand=${encodeURIComponent(brandVal)}&artikel=${encodeURIComponent(artikelVal)}&q=`);
});
document.getElementById('ukuran').addEventListener('keyup', () => {
  const brandVal = document.getElementById('nama_brand').value.trim();
  const artikelVal = document.getElementById('artikel').value.trim();
  const kodeVal = document.getElementById('kode_produk').value.trim();
  if (!brandVal || !artikelVal || !kodeVal) return;
  suggestInput('ukuran', 'suggest_ukuran', `load_produk.php?mode=ukuran&brand=${encodeURIComponent(brandVal)}&artikel=${encodeURIComponent(artikelVal)}&kode=${encodeURIComponent(kodeVal)}&q=`);
});
document.getElementById('warna').addEventListener('keyup', () => {
  const brandVal = document.getElementById('nama_brand').value.trim();
  const artikelVal = document.getElementById('artikel').value.trim();
  const kodeVal = document.getElementById('kode_produk').value.trim();
  const ukuranVal = document.getElementById('ukuran').value.trim();
  if (!brandVal || !artikelVal || !kodeVal || !ukuranVal) return;
  suggestInput('warna', 'suggest_warna', `load_produk.php?mode=warna&brand=${encodeURIComponent(brandVal)}&artikel=${encodeURIComponent(artikelVal)}&kode=${encodeURIComponent(kodeVal)}&ukuran=${encodeURIComponent(ukuranVal)}&q=`);
});

// === AUTO-FILL DETAIL PRODUK ===
let autoFillTimeout;
function autoFillProduk() {
  clearTimeout(autoFillTimeout);
  autoFillTimeout = setTimeout(async () => {
    const brand   = document.getElementById('nama_brand').value.trim();
    const artikel = document.getElementById('artikel').value.trim();
    const kode    = document.getElementById('kode_produk').value.trim();
    const ukuran  = document.getElementById('ukuran').value.trim();
    const warna   = document.getElementById('warna').value.trim();
    if (!brand || !artikel || !kode || !ukuran || !warna) return;

    const hppField = document.getElementById('hpp');
    const bandrolField = document.getElementById('bandrol');
    hppField.placeholder = "Memuat...";
    bandrolField.placeholder = "Memuat...";

    try {
      const res = await fetch(`load_produk.php?mode=detail_lengkap&brand=${encodeURIComponent(brand)}&artikel=${encodeURIComponent(artikel)}&kode=${encodeURIComponent(kode)}&ukuran=${encodeURIComponent(ukuran)}&warna=${encodeURIComponent(warna)}`);
      const data = await res.json();

      if (data.length > 0) {
        const p = data[0];
        document.getElementById('hpp').value = p.hpp || '';
        document.getElementById('bandrol').value = p.bandrol || '';
        let stokDiv = document.getElementById('stokSekarang');
        if (!stokDiv) {
          stokDiv = document.createElement('div');
          stokDiv.id = 'stokSekarang';
          stokDiv.className = 'alert alert-info mt-2 py-2 px-3 shadow-sm';
          document.getElementById('jumlah_gudang').closest('.col-md-12').appendChild(stokDiv);
        }
        stokDiv.innerHTML = `<i class="bi bi-box-seam"></i> Stok Gudang Saat Ini: <strong>${p.jumlah_gudang}</strong>`;
      } else {
        document.getElementById('hpp').value = '';
        document.getElementById('bandrol').value = '';
        const stokDiv = document.getElementById('stokSekarang');
        if (stokDiv) stokDiv.remove();
      }
    } catch (err) {
      console.error('Gagal auto-fill:', err);
    } finally {
      hppField.placeholder = "Harga pokok produksi";
      bandrolField.placeholder = "Harga bandrol";
    }
  }, 400);
}
['nama_brand','artikel','kode_produk','ukuran','warna'].forEach(id => {
  document.getElementById(id).addEventListener('change', autoFillProduk);
  document.getElementById(id).addEventListener('blur', autoFillProduk);
});
</script>
