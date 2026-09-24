<?php
require_once __DIR__ . '/../../config/database.php';
requireRole(['owner','staff']);
$db = getDB();

$bulan = (int)($_GET['bulan'] ?? date('n'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));

$pendapatan = $db->prepare("SELECT COALESCE(SUM(total),0) FROM transaksi_minuman WHERE tipe='keluar' AND MONTH(tgl_transaksi)=? AND YEAR(tgl_transaksi)=?");
$pendapatan->execute([$bulan,$tahun]); $pendapatan = $pendapatan->fetchColumn();

$modal = $db->prepare("SELECT COALESCE(SUM(total),0) FROM transaksi_minuman WHERE tipe='masuk' AND MONTH(tgl_transaksi)=? AND YEAR(tgl_transaksi)=?");
$modal->execute([$bulan,$tahun]); $modal = $modal->fetchColumn();

$laba = $pendapatan - $modal;

// Per-produk summary
$per_produk = $db->prepare("
    SELECT m.nama,
           SUM(CASE WHEN t.tipe='keluar' THEN t.jumlah ELSE 0 END) as unit_terjual,
           SUM(CASE WHEN t.tipe='keluar' THEN t.total ELSE 0 END) as pendapatan,
           SUM(CASE WHEN t.tipe='masuk'  THEN t.total ELSE 0 END) as modal,
           SUM(CASE WHEN t.tipe='keluar' THEN t.total ELSE 0 END) -
           SUM(CASE WHEN t.tipe='masuk'  THEN t.total ELSE 0 END) as laba
    FROM transaksi_minuman t JOIN minuman m ON t.minuman_id=m.id
    WHERE MONTH(t.tgl_transaksi)=? AND YEAR(t.tgl_transaksi)=?
    GROUP BY m.id, m.nama ORDER BY pendapatan DESC
");
$per_produk->execute([$bulan,$tahun]);
$per_produk = $per_produk->fetchAll();

$bulan_nama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Keuangan – J17 Fitnes</title>
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
      <div class="topbar-title">Laporan Keuangan Minuman</div>
    </div>
    <div class="page-content">
      <!-- Filter -->
      <form method="GET" class="d-flex gap-2 mb-4 align-items-center">
        <select name="bulan" class="form-select" style="width:150px">
          <?php for($i=1;$i<=12;$i++): ?>
          <option value="<?= $i ?>" <?= $i==$bulan?'selected':'' ?>><?= $bulan_nama[$i] ?></option>
          <?php endfor; ?>
        </select>
        <select name="tahun" class="form-select" style="width:100px">
          <?php for($y=date('Y');$y>=2023;$y--): ?>
          <option value="<?= $y ?>" <?= $y==$tahun?'selected':'' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
        <button type="submit" class="btn-j17 btn-j17-primary btn-j17-sm"><i class="fa-solid fa-filter"></i> Filter</button>
      </form>

      <h5 style="font-weight:800;color:var(--dark);margin-bottom:1.25rem">
        Laporan: <?= $bulan_nama[$bulan] ?> <?= $tahun ?>
      </h5>

      <!-- Summary Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-arrow-up"></i></div>
            <div>
              <div class="stat-num" style="font-size:1.4rem"><?= rupiah($pendapatan) ?></div>
              <div class="stat-label">Total Pendapatan</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-icon orange"><i class="fa-solid fa-arrow-down"></i></div>
            <div>
              <div class="stat-num" style="font-size:1.4rem"><?= rupiah($modal) ?></div>
              <div class="stat-label">Total Modal</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-icon <?= $laba>=0?'green':'red' ?>">
              <i class="fa-solid fa-<?= $laba>=0?'chart-line':'chart-line-down' ?>"></i>
            </div>
            <div>
              <div class="stat-num" style="font-size:1.4rem;color:<?= $laba>=0?'var(--success)':'var(--danger)' ?>"><?= rupiah($laba) ?></div>
              <div class="stat-label">Laba Bersih</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Per Produk -->
      <div class="data-card">
        <div class="data-card-header">
          <span class="data-card-title">Rincian Per Produk</span>
        </div>
        <div style="overflow-x:auto">
          <table class="table-j17">
            <thead><tr><th>Produk</th><th>Unit Terjual</th><th>Pendapatan</th><th>Modal</th><th>Laba</th></tr></thead>
            <tbody>
              <?php foreach($per_produk as $p): ?>
              <tr>
                <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                <td><?= $p['unit_terjual'] ?> unit</td>
                <td style="color:var(--blue-700);font-weight:600"><?= rupiah($p['pendapatan']) ?></td>
                <td style="color:var(--warning)"><?= rupiah($p['modal']) ?></td>
                <td style="font-weight:700;color:<?= $p['laba']>=0?'var(--success)':'var(--danger)' ?>"><?= rupiah($p['laba']) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($per_produk)): ?>
              <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--gray-400)">Tidak ada transaksi pada periode ini.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
