<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['owner','staff']);
$db = getDB();

$notif_member = $db->query("
    SELECT u.nama_lengkap, u.no_hp, m.paket, m.tgl_selesai, DATEDIFF(m.tgl_selesai, CURDATE()) as sisa
    FROM members m JOIN users u ON m.user_id=u.id
    WHERE DATEDIFF(m.tgl_selesai, CURDATE()) BETWEEN -30 AND 7
    ORDER BY sisa ASC
")->fetchAll();

$notif_alat = $db->query("
    SELECT *, DATEDIFF(tgl_maintenance_berikutnya, CURDATE()) as sisa_hari
    FROM alat WHERE tgl_maintenance_berikutnya <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY sisa_hari ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifikasi – J17 Fitnes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="/j17-fitnes/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/sidebar_owner.php'; ?>
  <div class="main-content">
    <div class="topbar">
      <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
      <div class="topbar-title">Panel Notifikasi</div>
    </div>
    <div class="page-content">
      <div class="row g-4">
        <!-- Notif Member -->
        <div class="col-lg-6">
          <div class="alert-panel">
            <div class="alert-panel-header">
              <i class="fa-solid fa-users"></i> Membership Member
              <span class="badge bg-danger ms-2"><?= count($notif_member) ?></span>
            </div>
            <?php if(empty($notif_member)): ?>
            <div class="notif-item" style="color:var(--gray-400)"><i class="fa-solid fa-circle-check" style="color:var(--success);flex-shrink:0"></i> Semua membership aman.</div>
            <?php endif; ?>
            <?php foreach($notif_member as $n): ?>
            <div class="notif-item">
              <?php if($n['sisa'] < 0): ?>
                <div class="notif-dot-red"></div>
                <div>
                  <strong><?= htmlspecialchars($n['nama_lengkap']) ?></strong><br>
                  <span style="color:var(--danger);font-size:.82rem">❌ EXPIRED <?= abs($n['sisa']) ?> hari yang lalu</span><br>
                  <span style="font-size:.78rem;color:var(--gray-400)"><?= $n['no_hp'] ? 'HP: '.$n['no_hp'] : '' ?></span>
                </div>
              <?php elseif($n['sisa'] <= 3): ?>
                <div class="notif-dot-orange"></div>
                <div>
                  <strong><?= htmlspecialchars($n['nama_lengkap']) ?></strong><br>
                  <span style="color:var(--warning);font-size:.82rem">⚠ Habis dalam <?= $n['sisa'] ?> hari (<?= date('d M', strtotime($n['tgl_selesai'])) ?>)</span><br>
                  <span style="font-size:.78rem;color:var(--gray-400)"><?= $n['no_hp'] ? 'HP: '.$n['no_hp'] : '' ?></span>
                </div>
              <?php else: ?>
                <div class="notif-dot-blue"></div>
                <div>
                  <strong><?= htmlspecialchars($n['nama_lengkap']) ?></strong><br>
                  <span style="font-size:.82rem;color:var(--gray-500)">Sisa <?= $n['sisa'] ?> hari (<?= date('d M', strtotime($n['tgl_selesai'])) ?>)</span>
                </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Notif Alat -->
        <div class="col-lg-6">
          <div class="alert-panel">
            <div class="alert-panel-header">
              <i class="fa-solid fa-dumbbell"></i> Jadwal Maintenance Alat
              <span class="badge bg-danger ms-2"><?= count($notif_alat) ?></span>
            </div>
            <?php if(empty($notif_alat)): ?>
            <div class="notif-item" style="color:var(--gray-400)"><i class="fa-solid fa-circle-check" style="color:var(--success);flex-shrink:0"></i> Semua alat terjadwal dengan baik.</div>
            <?php endif; ?>
            <?php foreach($notif_alat as $a): ?>
            <div class="notif-item">
              <?php if($a['sisa_hari'] <= 0): ?>
                <div class="notif-dot-red"></div>
                <div>
                  <strong><?= htmlspecialchars($a['nama']) ?></strong><br>
                  <span style="color:var(--danger);font-size:.82rem">🔴 HARUS DIMAINTENANCE SEKARANG!</span><br>
                  <span style="font-size:.78rem"><?= $a['catatan'] ? htmlspecialchars($a['catatan']) : '' ?></span>
                  <a href="/j17-fitnes/owner/alat/index.php" style="display:block;font-size:.78rem;margin-top:.25rem;color:var(--blue-600)">→ Pergi ke halaman alat</a>
                </div>
              <?php else: ?>
                <div class="notif-dot-orange"></div>
                <div>
                  <strong><?= htmlspecialchars($a['nama']) ?></strong><br>
                  <span style="color:var(--warning);font-size:.82rem">⚠ Maintenance dalam <?= $a['sisa_hari'] ?> hari (<?= date('d M', strtotime($a['tgl_maintenance_berikutnya'])) ?>)</span>
                </div>
              <?php endif; ?>
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
