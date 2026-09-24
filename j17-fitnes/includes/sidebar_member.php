<?php
// includes/sidebar_member.php
$currentPage = basename($_SERVER['PHP_SELF']);
function isActiveM($pages) {
    global $currentPage;
    return in_array($currentPage, (array)$pages) ? 'active' : '';
}
// Cek sudah check-in hari ini?
$sudahCheckin = false;
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM kehadiran WHERE user_id=? AND tgl_hadir=CURDATE()");
    $stmt->execute([$_SESSION['user_id']]);
    $sudahCheckin = (bool)$stmt->fetch();
} catch(Exception $e) {}
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-text">J<span>17</span> Fitnes</div>
    <div class="brand-sub">Member Dashboard</div>
  </div>

  <div class="sidebar-user">
    <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['nama_panggilan'] ?? 'U', 0, 1)) ?></div>
    <div>
      <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Member') ?></div>
      <div class="sidebar-user-role">Member Aktif</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Member Area</div>

    <a href="/j17-fitnes/member/dashboard.php" class="sidebar-link <?= isActiveM('dashboard.php') ?>">
      <i class="fa-solid fa-gauge icon"></i> Dashboard
    </a>

    <a href="/j17-fitnes/member/checkin.php" class="sidebar-link <?= isActiveM('checkin.php') ?>">
      <i class="fa-solid fa-calendar-check icon"></i> Daily Check-In
      <?php if(!$sudahCheckin): ?>
        <span class="notif-dot" style="width:8px;height:8px;background:#f59e0b;border-radius:50%;margin-left:auto;flex-shrink:0"></span>
      <?php endif; ?>
    </a>

    <a href="/j17-fitnes/member/status_alat.php" class="sidebar-link <?= isActiveM('status_alat.php') ?>">
      <i class="fa-solid fa-dumbbell icon"></i> Status Alat
    </a>

    <a href="/j17-fitnes/member/rekomendasi.php" class="sidebar-link <?= isActiveM('rekomendasi.php') ?>">
      <i class="fa-solid fa-fire icon"></i> Rekomendasi Latihan
    </a>

    <a href="/j17-fitnes/member/jadwal.php" class="sidebar-link <?= isActiveM('jadwal.php') ?>">
      <i class="fa-solid fa-calendar-week icon"></i> Jadwal Personal
    </a>

    <a href="/j17-fitnes/member/riwayat.php" class="sidebar-link <?= isActiveM('riwayat.php') ?>">
      <i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Kehadiran
    </a>

    <div class="sidebar-section-label">Lainnya</div>
    <a href="/j17-fitnes/index.php" class="sidebar-link">
      <i class="fa-solid fa-house icon"></i> Homepage
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="/j17-fitnes/auth/logout.php" class="btn-logout">
      <i class="fa-solid fa-right-from-bracket"></i> Keluar
    </a>
  </div>
</aside>
