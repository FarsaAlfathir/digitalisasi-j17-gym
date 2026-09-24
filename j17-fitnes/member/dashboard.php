<?php
require_once __DIR__ . '/../config/database.php';
requireRole('member');
$db = getDB();

$uid = $_SESSION['user_id'];

// Data member
$member = $db->prepare("SELECT u.*, m.paket, m.tgl_mulai, m.tgl_selesai, m.status as m_status,
    DATEDIFF(m.tgl_selesai, CURDATE()) as sisa_hari
    FROM users u LEFT JOIN members m ON m.user_id=u.id WHERE u.id=?");
$member->execute([$uid]);
$member = $member->fetch();

$sisa = (int)($member['sisa_hari'] ?? 0);

// Total kehadiran
$total_hadir = $db->prepare("SELECT COUNT(*) FROM kehadiran WHERE user_id=?");
$total_hadir->execute([$uid]);
$total_hadir = $total_hadir->fetchColumn();

// Sudah check-in hari ini?
$sudah_checkin = $db->prepare("SELECT id FROM kehadiran WHERE user_id=? AND tgl_hadir=CURDATE()");
$sudah_checkin->execute([$uid]);
$sudah_checkin = (bool)$sudah_checkin->fetch();

// Kehadiran 7 hari terakhir
$hadir_7hari = $db->prepare("SELECT tgl_hadir FROM kehadiran WHERE user_id=? AND tgl_hadir >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) ORDER BY tgl_hadir");
$hadir_7hari->execute([$uid]);
$hadir_7hari = array_column($hadir_7hari->fetchAll(), 'tgl_hadir');

// Alat sedang maintenance
$alat_maintenance = $db->query("SELECT COUNT(*) FROM alat WHERE status='maintenance'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Member – J17 Fitnes</title>
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
      <div class="topbar-title">Dashboard Member</div>
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
        Hei, <?= htmlspecialchars($member['nama_lengkap']) ?>! 💪
      </h4>

      <div class="row g-3 mb-4">
        <!-- Membership Card -->
        <div class="col-lg-4">
          <div class="membership-card">
            <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:1px;opacity:.7;margin-bottom:.5rem">Membership Kamu</div>
            <div class="days-big">
              <?= $sisa >= 0 ? $sisa : 0 ?>
            </div>
            <div style="opacity:.8;margin-bottom:1rem">hari tersisa</div>
            <div style="font-size:.8rem;opacity:.7">Paket: <?= str_replace('_',' ',ucfirst($member['paket']??'-')) ?></div>
            <div style="font-size:.8rem;opacity:.7">Berakhir: <?= $member['tgl_selesai'] ? date('d M Y',strtotime($member['tgl_selesai'])) : '—' ?></div>

            <?php if($sisa < 0): ?>
            <div style="margin-top:.75rem;background:rgba(220,38,38,.3);border-radius:6px;padding:.5rem .75rem;font-size:.82rem">
              ⚠ Membership kamu sudah HABIS. Hubungi owner untuk perpanjang.
            </div>
            <?php elseif($sisa <= 3): ?>
            <div style="margin-top:.75rem;background:rgba(217,119,6,.3);border-radius:6px;padding:.5rem .75rem;font-size:.82rem">
              ⚠ Sisa <?= $sisa ?> hari lagi, segera perpanjang!
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Stat Cards -->
        <div class="col-lg-8">
          <div class="row g-3 h-100">
            <div class="col-6">
              <div class="stat-card h-100">
                <div class="stat-icon blue"><i class="fa-solid fa-calendar-check"></i></div>
                <div>
                  <div class="stat-num"><?= $total_hadir ?></div>
                  <div class="stat-label">Total Kehadiran</div>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="stat-card h-100">
                <div class="stat-icon <?= $alat_maintenance > 0 ? 'orange' : 'green' ?>">
                  <i class="fa-solid fa-dumbbell"></i>
                </div>
                <div>
                  <div class="stat-num"><?= $alat_maintenance ?></div>
                  <div class="stat-label">Alat Maintenance</div>
                </div>
              </div>
            </div>

            <!-- Mini Check-In -->
            <div class="col-12">
              <div style="background:white;border-radius:var(--radius-lg);padding:1.25rem;box-shadow:var(--shadow);display:flex;align-items:center;justify-content:space-between;gap:1rem">
                <div>
                  <div style="font-weight:700;color:var(--dark)">Check-In Hari Ini</div>
                  <div style="font-size:.85rem;color:var(--gray-500)"><?= $sudah_checkin ? 'Sudah check-in ✓' : 'Belum check-in hari ini' ?></div>
                </div>
                <?php if(!$sudah_checkin): ?>
                <a href="checkin.php" class="btn-j17 btn-j17-primary">
                  <i class="fa-solid fa-hand-pointer"></i> Check-In Sekarang
                </a>
                <?php else: ?>
                <span class="badge-j17 badge-aktif" style="padding:.5rem 1rem;font-size:.875rem">✓ Sudah Check-In</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Aktivitas 7 Hari -->
      <div class="data-card mb-4">
        <div class="data-card-header">
          <span class="data-card-title"><i class="fa-solid fa-fire" style="color:var(--blue-600)"></i> Aktivitas 7 Hari Terakhir</span>
        </div>
        <div style="padding:1.25rem">
          <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <?php
            for($d=6;$d>=0;$d--) {
              $tgl = date('Y-m-d', strtotime("-{$d} days"));
              $label = date('D', strtotime($tgl));
              $hadir = in_array($tgl, $hadir_7hari);
              $isToday = $tgl === date('Y-m-d');
              echo '<div style="text-align:center;flex:1;min-width:40px">
                <div style="width:40px;height:40px;border-radius:10px;margin:0 auto .25rem;display:flex;align-items:center;justify-content:center;font-size:1.1rem;
                  background:'.($hadir?'var(--blue-700)':($isToday?'var(--blue-100)':'var(--gray-100)')).';">
                  '.($hadir?'<span style="color:white">✓</span>':'<span style="color:var(--gray-300)">·</span>').'
                </div>
                <div style="font-size:.7rem;color:var(--gray-500)">'.$label.'</div>
              </div>';
            }
            ?>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="row g-3">
        <?php
        $links = [
          ['checkin.php','fa-calendar-check','Daily Check-In','Cap kehadiran harian kamu','blue'],
          ['status_alat.php','fa-dumbbell','Status Alat','Cek alat tersedia/maintenance','green'],
          ['rekomendasi.php','fa-fire','Rekomendasi Latihan','Gerakan untuk otot tertentu','orange'],
          ['jadwal.php','fa-calendar-week','Jadwal Personal','Saran latihan mingguan kamu','blue'],
        ];
        foreach($links as $l): ?>
        <div class="col-6 col-md-3">
          <a href="<?= $l[0] ?>" style="text-decoration:none">
            <div class="member-card" style="text-align:center;transition:all .2s;cursor:pointer" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
              <div class="member-card-icon" style="margin:0 auto 1rem">
                <i class="fa-solid <?= $l[1] ?>"></i>
              </div>
              <div style="font-weight:700;font-size:.9rem;color:var(--dark);margin-bottom:.25rem"><?= $l[2] ?></div>
              <div style="font-size:.78rem;color:var(--gray-400)"><?= $l[3] ?></div>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
