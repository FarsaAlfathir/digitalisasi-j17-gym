<?php
require_once __DIR__ . '/../config/database.php';
requireRole('member');
$db = getDB();
$uid = $_SESSION['user_id'];

$bulan = (int)($_GET['bulan'] ?? date('n'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));

$riwayat = $db->prepare("SELECT * FROM kehadiran WHERE user_id=? AND MONTH(tgl_hadir)=? AND YEAR(tgl_hadir)=? ORDER BY tgl_hadir DESC");
$riwayat->execute([$uid, $bulan, $tahun]);
$riwayat = $riwayat->fetchAll();

$total_all = $db->prepare("SELECT COUNT(*) FROM kehadiran WHERE user_id=?");
$total_all->execute([$uid]);
$total_all = $total_all->fetchColumn();

$bulan_nama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Kehadiran – J17 Fitnes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="/j17-fitnes/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar_member.php'; ?>
  <div class="main-content">
    <div class="topbar">
      <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
      <div class="topbar-title">Riwayat Kehadiran</div>
    </div>
    <div class="page-content">
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-trophy"></i></div>
            <div>
              <div class="stat-num"><?= $total_all ?></div>
              <div class="stat-label">Total Kehadiran Sepanjang Waktu</div>
            </div>
          </div>
        </div>
      </div>

      <form method="GET" class="d-flex gap-2 mb-3">
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

      <div class="data-card">
        <div class="data-card-header">
          <span class="data-card-title">Kehadiran <?= $bulan_nama[$bulan] ?> <?= $tahun ?></span>
          <span class="badge-j17 badge-aktif"><?= count($riwayat) ?>x hadir</span>
        </div>
        <table class="table-j17">
          <thead><tr><th>#</th><th>Tanggal</th><th>Hari</th><th>Jam Masuk</th></tr></thead>
          <tbody>
            <?php foreach($riwayat as $i=>$r): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= date('d F Y', strtotime($r['tgl_hadir'])) ?></td>
              <td><?= date('l', strtotime($r['tgl_hadir'])) ?></td>
              <td><?= substr($r['jam_hadir'],0,5) ?> WIB</td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($riwayat)): ?>
            <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--gray-400)">Tidak ada kehadiran pada periode ini.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
