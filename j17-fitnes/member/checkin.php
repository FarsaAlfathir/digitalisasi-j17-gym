<?php
require_once __DIR__ . '/../config/database.php';
requireRole('member');
$db = getDB();
$uid = $_SESSION['user_id'];

// Handle check-in
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='checkin') {
    $cek = $db->prepare("SELECT id FROM kehadiran WHERE user_id=? AND tgl_hadir=CURDATE()");
    $cek->execute([$uid]);
    if (!$cek->fetch()) {
        $db->prepare("INSERT INTO kehadiran (user_id, tgl_hadir, jam_hadir) VALUES (?, CURDATE(), CURTIME())")
           ->execute([$uid]);
        setFlash('success','Check-in berhasil! Tetap semangat hari ini 💪');
    } else {
        setFlash('error','Kamu sudah check-in hari ini.');
    }
    header("Location: checkin.php"); exit;
}

$sudah_checkin = $db->prepare("SELECT * FROM kehadiran WHERE user_id=? AND tgl_hadir=CURDATE()");
$sudah_checkin->execute([$uid]);
$sudah_checkin = $sudah_checkin->fetch();

// Riwayat 5 terakhir
$riwayat = $db->prepare("SELECT * FROM kehadiran WHERE user_id=? ORDER BY tgl_hadir DESC LIMIT 5");
$riwayat->execute([$uid]);
$riwayat = $riwayat->fetchAll();

// Total bulan ini
$total_bulan = $db->prepare("SELECT COUNT(*) FROM kehadiran WHERE user_id=? AND MONTH(tgl_hadir)=MONTH(CURDATE()) AND YEAR(tgl_hadir)=YEAR(CURDATE())");
$total_bulan->execute([$uid]);
$total_bulan = $total_bulan->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daily Check-In – J17 Fitnes</title>
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
      <div class="topbar-title">Daily Check-In</div>
    </div>
    <div class="page-content">
      <?php $flash = getFlash(); if($flash): ?>
      <div class="flash-<?= $flash['type']==='success'?'success':'error' ?>">
        <i class="fa-solid fa-<?= $flash['type']==='success'?'circle-check':'circle-exclamation' ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <!-- Check-in Button -->
      <div style="display:flex;flex-direction:column;align-items:center;padding:3rem 1rem;text-align:center">
        <h4 style="font-weight:800;color:var(--dark);margin-bottom:.5rem"><?= date('l, d F Y') ?></h4>
        <p style="color:var(--gray-500);margin-bottom:2rem"><?= date('H:i') ?> WIB</p>

        <?php if($sudah_checkin): ?>
        <button class="checkin-btn" disabled>
          <span class="icon-big">✅</span>
          <span>Sudah Check-In</span>
          <span style="font-size:.85rem;font-weight:400;opacity:.8">pukul <?= substr($sudah_checkin['jam_hadir'],0,5) ?> WIB</span>
        </button>
        <?php else: ?>
        <form method="POST">
          <input type="hidden" name="action" value="checkin">
          <button type="submit" class="checkin-btn">
            <span class="icon-big">💪</span>
            <span>Check-In Sekarang</span>
            <span style="font-size:.85rem;font-weight:400;opacity:.8">Klik untuk mencatat kehadiran</span>
          </button>
        </form>
        <?php endif; ?>
      </div>

      <!-- Stats -->
      <div class="row g-3 mb-4 justify-content-center">
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-calendar"></i></div>
            <div>
              <div class="stat-num"><?= $total_bulan ?></div>
              <div class="stat-label">Check-In Bulan Ini</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat -->
      <div class="data-card" style="max-width:600px;margin:0 auto">
        <div class="data-card-header">
          <span class="data-card-title"><i class="fa-solid fa-clock-rotate-left" style="color:var(--blue-600)"></i> Check-In Terakhir</span>
        </div>
        <table class="table-j17">
          <thead><tr><th>Tanggal</th><th>Jam</th></tr></thead>
          <tbody>
            <?php foreach($riwayat as $r): ?>
            <tr>
              <td><?= date('d M Y (D)', strtotime($r['tgl_hadir'])) ?></td>
              <td><?= substr($r['jam_hadir'],0,5) ?> WIB</td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($riwayat)): ?>
            <tr><td colspan="2" style="text-align:center;padding:1.5rem;color:var(--gray-400)">Belum ada riwayat check-in.</td></tr>
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
