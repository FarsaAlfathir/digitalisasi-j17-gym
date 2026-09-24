<?php
require_once __DIR__ . '/../../config/database.php';
requireRole(['owner','staff']);
$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {

    // Tambah minuman baru
    if ($_POST['action']==='tambah') {
        $nama        = trim($_POST['nama']       ?? '');
        $harga_jual  = (int)$_POST['harga_jual'];
        $harga_modal = (int)$_POST['harga_modal'];
        $stok_awal   = (int)$_POST['stok_awal'];

        if ($nama) {
            $db->prepare("INSERT INTO minuman (nama,harga_jual,harga_modal,stok) VALUES (?,?,?,?)")
               ->execute([$nama,$harga_jual,$harga_modal,$stok_awal]);
            if ($stok_awal > 0) {
                $db->prepare("INSERT INTO transaksi_minuman (minuman_id,tipe,jumlah,harga_satuan,total,keterangan) VALUES (LAST_INSERT_ID(),'masuk',?,?,?,'Stok awal')")
                   ->execute([$stok_awal,$harga_modal,$stok_awal*$harga_modal]);
            }
            setFlash('success','Minuman berhasil ditambahkan!');
        }
        header("Location: index.php"); exit;
    }

    // Restok
    if ($_POST['action']==='restok') {
        $mid    = (int)$_POST['minuman_id'];
        $jumlah = (int)$_POST['jumlah'];
        $modal  = (int)$_POST['harga_modal'];
        $db->prepare("UPDATE minuman SET stok=stok+?, harga_modal=? WHERE id=?")->execute([$jumlah,$modal,$mid]);
        $total = $jumlah * $modal;
        $db->prepare("INSERT INTO transaksi_minuman (minuman_id,tipe,jumlah,harga_satuan,total,keterangan) VALUES (?,'masuk',?,?,?,'Restok')")
           ->execute([$mid,$jumlah,$modal,$total]);
        setFlash('success','Restok berhasil! Stok bertambah '.$jumlah.' unit.');
        header("Location: index.php"); exit;
    }

    // Jual
    if ($_POST['action']==='jual') {
        $mid    = (int)$_POST['minuman_id'];
        $jumlah = (int)$_POST['jumlah'];
        $minuman = $db->prepare("SELECT * FROM minuman WHERE id=?");
        $minuman->execute([$mid]);
        $m = $minuman->fetch();
        if ($m && $m['stok'] >= $jumlah) {
            $db->prepare("UPDATE minuman SET stok=stok-? WHERE id=?")->execute([$jumlah,$mid]);
            $total = $jumlah * $m['harga_jual'];
            $db->prepare("INSERT INTO transaksi_minuman (minuman_id,tipe,jumlah,harga_satuan,total,keterangan) VALUES (?,'keluar',?,?,?,'Penjualan')")
               ->execute([$mid,$jumlah,$m['harga_jual'],$total]);
            $laba = ($m['harga_jual'] - $m['harga_modal']) * $jumlah;
            setFlash('success',"Terjual {$jumlah} {$m['nama']}. Total: ".rupiah($total)." | Laba: ".rupiah($laba));
        } else {
            setFlash('error','Stok tidak mencukupi!');
        }
        header("Location: index.php"); exit;
    }

    // Hapus
    if ($_POST['action']==='hapus') {
        $mid = (int)$_POST['minuman_id'];
        $db->prepare("DELETE FROM minuman WHERE id=?")->execute([$mid]);
        setFlash('success','Minuman dihapus.');
        header("Location: index.php"); exit;
    }
}

$minumans = $db->query("SELECT * FROM minuman ORDER BY nama")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stok Minuman – J17 Fitnes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="/j17-fitnes/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_owner.php'; ?>
  <div class="main-content">
    <div class="topbar">
      <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
      <div class="topbar-title">Manajemen Stok Minuman</div>
      <div class="topbar-actions">
        <button class="btn-j17 btn-j17-primary btn-j17-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
          <i class="fa-solid fa-plus"></i> Tambah Minuman
        </button>
      </div>
    </div>
    <div class="page-content">
      <?php $flash = getFlash(); if($flash): ?>
      <div class="flash-<?= $flash['type']==='success'?'success':'error' ?>">
        <i class="fa-solid fa-<?= $flash['type']==='success'?'circle-check':'circle-exclamation' ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <!-- GRID MINUMAN -->
      <div class="row g-3 mb-4">
        <?php foreach($minumans as $m): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div style="background:white;border-radius:var(--radius-lg);padding:1.25rem;box-shadow:var(--shadow);border:1px solid rgba(0,0,0,.04);height:100%">
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:.75rem">
              <div style="background:var(--blue-100);color:var(--blue-700);width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                🥤
              </div>
              <span style="font-size:.75rem;font-weight:700;padding:3px 8px;border-radius:999px;
                <?= $m['stok'] <= 3 ? 'background:#fee2e2;color:#dc2626' : ($m['stok'] <= 10 ? 'background:#fef3c7;color:#b45309' : 'background:#dcfce7;color:#15803d') ?>">
                Stok: <?= $m['stok'] ?>
              </span>
            </div>
            <div style="font-weight:700;font-size:.9rem;margin-bottom:.25rem"><?= htmlspecialchars($m['nama']) ?></div>
            <div style="font-size:.8rem;color:var(--gray-500)">Jual: <strong><?= rupiah($m['harga_jual']) ?></strong></div>
            <div style="font-size:.8rem;color:var(--gray-500)">Modal: <?= rupiah($m['harga_modal']) ?></div>
            <div style="font-size:.78rem;color:var(--blue-600);font-weight:600;margin-top:.25rem">
              Laba/unit: <?= rupiah($m['harga_jual']-$m['harga_modal']) ?>
            </div>
            <div class="d-flex gap-1 mt-3">
              <button class="btn-j17 btn-j17-success btn-j17-sm flex-fill justify-content-center"
                      onclick="openRestok(<?= $m['id'] ?>, '<?= addslashes($m['nama']) ?>', <?= $m['harga_modal'] ?>)">
                <i class="fa-solid fa-plus"></i> Restok
              </button>
              <button class="btn-j17 btn-j17-primary btn-j17-sm flex-fill justify-content-center"
                      onclick="openJual(<?= $m['id'] ?>, '<?= addslashes($m['nama']) ?>', <?= $m['stok'] ?>)">
                <i class="fa-solid fa-shopping-cart"></i> Jual
              </button>
              <form method="POST" onsubmit="return confirm('Hapus minuman ini?')">
                <input type="hidden" name="action" value="hapus">
                <input type="hidden" name="minuman_id" value="<?= $m['id'] ?>">
                <button type="submit" class="btn-j17 btn-j17-danger btn-j17-sm"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($minumans)): ?>
        <div class="col-12"><div style="text-align:center;padding:3rem;color:var(--gray-400)">Belum ada data minuman. Klik "Tambah Minuman" untuk memulai.</div></div>
        <?php endif; ?>
      </div>

      <!-- Riwayat Transaksi Terbaru -->
      <div class="data-card">
        <div class="data-card-header">
          <span class="data-card-title">Transaksi Terbaru</span>
          <a href="laporan.php" class="btn-j17 btn-j17-outline btn-j17-sm">Laporan Lengkap</a>
        </div>
        <div style="overflow-x:auto">
          <table class="table-j17">
            <thead><tr><th>Waktu</th><th>Produk</th><th>Tipe</th><th>Jumlah</th><th>Harga Satuan</th><th>Total</th><th>Keterangan</th></tr></thead>
            <tbody>
              <?php
              $transaksi = $db->query("
                SELECT t.*, m.nama as nama_minuman
                FROM transaksi_minuman t JOIN minuman m ON t.minuman_id=m.id
                ORDER BY t.tgl_transaksi DESC LIMIT 15
              ")->fetchAll();
              foreach($transaksi as $t): ?>
              <tr>
                <td style="font-size:.8rem;white-space:nowrap"><?= date('d/m H:i', strtotime($t['tgl_transaksi'])) ?></td>
                <td><?= htmlspecialchars($t['nama_minuman']) ?></td>
                <td>
                  <?php if($t['tipe']==='masuk'): ?>
                    <span class="badge-j17 badge-aktif">↑ Masuk</span>
                  <?php else: ?>
                    <span class="badge-j17 badge-danger">↓ Keluar</span>
                  <?php endif; ?>
                </td>
                <td><?= $t['jumlah'] ?> unit</td>
                <td><?= rupiah($t['harga_satuan']) ?></td>
                <td style="font-weight:700"><?= rupiah($t['total']) ?></td>
                <td style="font-size:.8rem;color:var(--gray-400)"><?= htmlspecialchars($t['keterangan']??'') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Minuman -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title"><i class="fa-solid fa-plus"></i> Tambah Minuman Baru</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="tambah">
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nama Minuman</label><input type="text" name="nama" class="form-control" placeholder="cth: Aqua 600ml" required></div>
          <div class="row g-2">
            <div class="col-md-6"><label class="form-label">Harga Jual (Rp)</label><input type="number" name="harga_jual" class="form-control" placeholder="5000" required></div>
            <div class="col-md-6"><label class="form-label">Harga Modal (Rp)</label><input type="number" name="harga_modal" class="form-control" placeholder="2500" required></div>
          </div>
          <div class="mt-2"><label class="form-label">Stok Awal</label><input type="number" name="stok_awal" class="form-control" value="0" min="0"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn-j17 btn-j17-primary btn-j17-sm"><i class="fa-solid fa-save"></i> Simpan</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Restok -->
<div class="modal fade" id="modalRestok" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title"><i class="fa-solid fa-arrow-up"></i> Restok Minuman</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="restok">
        <input type="hidden" name="minuman_id" id="restokId">
        <div class="modal-body">
          <p style="font-size:.9rem">Produk: <strong id="restokNama"></strong></p>
          <div class="mb-2"><label class="form-label">Jumlah Restok</label><input type="number" name="jumlah" class="form-control" min="1" value="1" required></div>
          <div><label class="form-label">Harga Modal/unit (Rp)</label><input type="number" name="harga_modal" id="restokModal" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn-j17 btn-j17-success btn-j17-sm"><i class="fa-solid fa-check"></i> Restok</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Jual -->
<div class="modal fade" id="modalJual" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title"><i class="fa-solid fa-shopping-cart"></i> Catat Penjualan</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="jual">
        <input type="hidden" name="minuman_id" id="jualId">
        <div class="modal-body">
          <p style="font-size:.9rem">Produk: <strong id="jualNama"></strong></p>
          <p style="font-size:.8rem;color:var(--gray-500)">Stok tersedia: <strong id="jualStok"></strong></p>
          <label class="form-label">Jumlah Terjual</label>
          <input type="number" name="jumlah" class="form-control" min="1" value="1" required>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn-j17 btn-j17-primary btn-j17-sm"><i class="fa-solid fa-check"></i> Catat Jual</button></div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openRestok(id, nama, modal) {
  document.getElementById('restokId').value    = id;
  document.getElementById('restokNama').textContent = nama;
  document.getElementById('restokModal').value = modal;
  new bootstrap.Modal(document.getElementById('modalRestok')).show();
}
function openJual(id, nama, stok) {
  document.getElementById('jualId').value     = id;
  document.getElementById('jualNama').textContent = nama;
  document.getElementById('jualStok').textContent = stok;
  new bootstrap.Modal(document.getElementById('modalJual')).show();
}
</script>
</body>
</html>
