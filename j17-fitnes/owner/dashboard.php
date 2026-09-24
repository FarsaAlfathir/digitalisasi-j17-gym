<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['owner','staff']);
$db = getDB();

// === STATISTIK ===
$total_member   = $db->query("SELECT COUNT(*) FROM members WHERE status='aktif'")->fetchColumn();
$total_expired  = $db->query("SELECT COUNT(*) FROM members WHERE status='expired' OR tgl_selesai < CURDATE()")->fetchColumn();
$total_minuman  = $db->query("SELECT SUM(stok) FROM minuman")->fetchColumn() ?? 0;
$total_alat     = $db->query("SELECT COUNT(*) FROM alat")->fetchColumn();

// Pendapatan bulan ini
$pendapatan = $db->query("SELECT COALESCE(SUM(total),0) FROM transaksi_minuman WHERE tipe='keluar' AND MONTH(tgl_transaksi)=MONTH(CURDATE()) AND YEAR(tgl_transaksi)=YEAR(CURDATE())")->fetchColumn();
$modal      = $db->query("SELECT COALESCE(SUM(total),0) FROM transaksi_minuman WHERE tipe='masuk' AND MONTH(tgl_transaksi)=MONTH(CURDATE()) AND YEAR(tgl_transaksi)=YEAR(CURDATE())")->fetchColumn();
$laba       = $pendapatan - $modal;

// === NOTIFIKASI MEMBER ===
$notif_member = $db->query("
    SELECT u.nama_lengkap, m.tgl_selesai, DATEDIFF(m.tgl_selesai, CURDATE()) as sisa
    FROM members m JOIN users u ON m.user_id=u.id
    WHERE DATEDIFF(m.tgl_selesai, CURDATE()) BETWEEN -7 AND 3
    ORDER BY sisa ASC
    LIMIT 10
")->fetchAll();

// === NOTIFIKASI ALAT ===
$notif_alat = $db->query("
    SELECT nama, tgl_maintenance_berikutnya, DATEDIFF(tgl_maintenance_berikutnya, CURDATE()) as sisa_hari, status
    FROM alat
    WHERE tgl_maintenance_berikutnya <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    ORDER BY sisa_hari ASC
    LIMIT 10
")->fetchAll();

// === KEHADIRAN HARI INI ===
$checkin_hari_ini = $db->query("
    SELECT u.nama_lengkap, k.jam_hadir
    FROM kehadiran k JOIN users u ON k.user_id=u.id
    WHERE k.tgl_hadir = CURDATE()
    ORDER BY k.jam_hadir DESC
    LIMIT 5
")->fetchAll();

// === DATA MEMBER TERBARU ===
$member_terbaru = $db->query("
    SELECT u.nama_lengkap, u.nama_panggilan, m.paket, m.tgl_selesai, m.status
    FROM members m JOIN users u ON m.user_id=u.id
    ORDER BY m.created_at DESC LIMIT 5
")->fetchAll();

$pageTitle = 'Dashboard Owner – J17 Fitnes';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="/j17-fitnes/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar_owner.php'; ?>

  <div class="main-content">
    <!-- TOPBAR -->
    <div class="topbar">
      <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="topbar-title">Dashboard</div>
      <div class="topbar-actions">
        <span style="font-size:.85rem;color:var(--gray-500)"><?= date('l, d F Y') ?></span>
      </div>
    </div>

    <div class="page-content">
      <?php $flash = getFlash(); if($flash): ?>
      <div class="flash-<?= $flash['type']==='success'?'success':'error' ?>">
        <i class="fa-solid fa-<?= $flash['type']==='success'?'circle-check':'circle-exclamation' ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <h4 style="font-weight:800;color:var(--dark);margin-bottom:1.5rem">
        Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>! 👋
      </h4>

      <!-- STAT CARDS -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
            <div>
              <div class="stat-num"><?= $total_member ?></div>
              <div class="stat-label">Member Aktif</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon green"><i class="fa-solid fa-chart-line"></i></div>
            <div>
              <div class="stat-num"><?= rupiah($pendapatan) ?></div>
              <div class="stat-label">Pendapatan Bulan Ini</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon orange"><i class="fa-solid fa-bottle-water"></i></div>
            <div>
              <div class="stat-num"><?= (int)$total_minuman ?></div>
              <div class="stat-label">Total Stok Minuman</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-icon <?= $total_expired > 0 ? 'red' : 'blue' ?>">
              <i class="fa-solid fa-user-xmark"></i>
            </div>
            <div>
              <div class="stat-num"><?= $total_expired ?></div>
              <div class="stat-label">Member Expired</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ROW: NOTIFIKASI + KEHADIRAN -->
      <div class="row g-3 mb-4">
        <div class="col-lg-7">
          <!-- NOTIFIKASI PANEL -->
          <div class="alert-panel">
            <div class="alert-panel-header">
              <i class="fa-solid fa-bell"></i> Panel Notifikasi
              <?php $totalNotif = count($notif_member) + count($notif_alat); ?>
              <?php if($totalNotif): ?>
              <span class="badge bg-danger ms-2"><?= $totalNotif ?></span>
              <?php endif; ?>
            </div>

            <?php if(empty($notif_member) && empty($notif_alat)): ?>
            <div class="notif-item" style="color:var(--gray-400);font-style:italic">
              <i class="fa-solid fa-circle-check" style="color:var(--success);margin-top:2px;flex-shrink:0"></i>
              Tidak ada notifikasi mendesak saat ini.
            </div>
            <?php endif; ?>

            <?php foreach($notif_member as $n): ?>
            <div class="notif-item">
              <?php if($n['sisa'] < 0): ?>
                <div class="notif-dot-red"></div>
                <div>
                  <strong><?= htmlspecialchars($n['nama_lengkap']) ?></strong> — Membership sudah
                  <strong style="color:var(--danger)">HABIS</strong> <?= abs($n['sisa']) ?> hari yang lalu
                </div>
              <?php else: ?>
                <div class="notif-dot-orange"></div>
                <div>
                  <strong><?= htmlspecialchars($n['nama_lengkap']) ?></strong> — Sisa
                  <strong style="color:var(--warning)"><?= $n['sisa'] ?> hari</strong> (berakhir <?= date('d M', strtotime($n['tgl_selesai'])) ?>)
                </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <?php foreach($notif_alat as $a): ?>
            <div class="notif-item">
              <?php if($a['sisa_hari'] <= 0): ?>
                <div class="notif-dot-red"></div>
                <div>
                  <strong><?= htmlspecialchars($a['nama']) ?></strong> —
                  <strong style="color:var(--danger)">PERLU MAINTENANCE SEKARANG!</strong>
                  <a href="/j17-fitnes/owner/alat/index.php" style="font-size:.8rem;margin-left:.5rem">→ Kelola</a>
                </div>
              <?php else: ?>
                <div class="notif-dot-orange"></div>
                <div>
                  <strong><?= htmlspecialchars($a['nama']) ?></strong> — Maintenance dalam
                  <strong style="color:var(--warning)"><?= $a['sisa_hari'] ?> hari</strong>
                  (tgl <?= date('d M', strtotime($a['tgl_maintenance_berikutnya'])) ?>)
                </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="col-lg-5">
          <!-- LAPORAN KEUANGAN SINGKAT -->
          <div class="finance-summary">
            <h6 style="font-weight:700;margin-bottom:1rem;color:var(--dark)">
              <i class="fa-solid fa-chart-pie" style="color:var(--blue-600)"></i> Keuangan Bulan Ini
            </h6>
            <div class="finance-row">
              <span class="finance-label">Pendapatan</span>
              <span class="finance-value" style="color:var(--blue-700)"><?= rupiah($pendapatan) ?></span>
            </div>
            <div class="finance-row">
              <span class="finance-label">Modal</span>
              <span class="finance-value finance-modal"><?= rupiah($modal) ?></span>
            </div>
            <div class="finance-row" style="border-top:2px solid var(--gray-200);margin-top:.5rem;padding-top:.75rem">
              <span class="finance-label" style="font-weight:700">Laba Bersih</span>
              <span class="finance-value finance-laba" style="font-size:1.2rem"><?= rupiah($laba) ?></span>
            </div>
            <a href="/j17-fitnes/owner/minuman/laporan.php" class="btn-j17 btn-j17-primary w-100 justify-content-center mt-3" style="font-size:.85rem">
              <i class="fa-solid fa-chart-bar"></i> Lihat Laporan Lengkap
            </a>
          </div>
        </div>
      </div>

      <!-- ROW: MEMBER TERBARU + KEHADIRAN HARI INI -->
      <div class="row g-3">
        <div class="col-lg-7">
          <div class="data-card">
            <div class="data-card-header">
              <span class="data-card-title"><i class="fa-solid fa-users" style="color:var(--blue-600)"></i> Member Terbaru</span>
              <a href="/j17-fitnes/owner/member/index.php" class="btn-j17 btn-j17-outline btn-j17-sm">Lihat Semua</a>
            </div>
            <table class="table-j17">
              <thead>
                <tr><th>Nama</th><th>Paket</th><th>Berakhir</th><th>Status</th></tr>
              </thead>
              <tbody>
                <?php foreach($member_terbaru as $m): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($m['nama_lengkap']) ?></strong><br>
                    <span style="font-size:.78rem;color:var(--gray-400)">@<?= htmlspecialchars($m['nama_panggilan']) ?></span>
                  </td>
                  <td><?= str_replace('_',' ',ucfirst($m['paket'])) ?></td>
                  <td style="font-size:.85rem"><?= date('d M Y', strtotime($m['tgl_selesai'])) ?></td>
                  <td>
                    <?php $sisa = sisaHari($m['tgl_selesai']); ?>
                    <?php if($sisa < 0): ?>
                      <span class="badge-j17 badge-expired">Expired</span>
                    <?php elseif($sisa <= 3): ?>
                      <span class="badge-j17 badge-warning">H-<?= $sisa ?></span>
                    <?php else: ?>
                      <span class="badge-j17 badge-aktif">Aktif</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="data-card">
            <div class="data-card-header">
              <span class="data-card-title"><i class="fa-solid fa-calendar-check" style="color:var(--blue-600)"></i> Check-In Hari Ini</span>
              <span class="badge-j17 badge-aktif"><?= count($checkin_hari_ini) ?> member</span>
            </div>
            <?php if(empty($checkin_hari_ini)): ?>
            <div style="padding:1.5rem;text-align:center;color:var(--gray-400);font-style:italic;font-size:.875rem">
              Belum ada check-in hari ini.
            </div>
            <?php endif; ?>
            <?php foreach($checkin_hari_ini as $c): ?>
            <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--gray-100);display:flex;align-items:center;gap:.75rem;font-size:.875rem">
              <div style="width:36px;height:36px;background:var(--blue-100);color:var(--blue-700);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0">
                <?= strtoupper(substr($c['nama_lengkap'],0,1)) ?>
              </div>
              <div>
                <div style="font-weight:600"><?= htmlspecialchars($c['nama_lengkap']) ?></div>
                <div style="color:var(--gray-400);font-size:.78rem"><?= substr($c['jam_hadir'],0,5) ?> WIB</div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
