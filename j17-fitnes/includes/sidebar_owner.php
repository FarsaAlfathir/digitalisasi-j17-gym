<?php
// includes/sidebar_owner.php
$notifCount = 0;
try {
    $db = getDB();
    // Hitung member expiring & expired
    $stmt = $db->query("SELECT COUNT(*) FROM members m
        WHERE DATEDIFF(m.tgl_selesai, CURDATE()) BETWEEN -99 AND 3
        AND m.status = 'aktif'");
    $notifCount += (int)$stmt->fetchColumn();
    // Hitung alat butuh maintenance
    $stmt = $db->query("SELECT COUNT(*) FROM alat WHERE tgl_maintenance_berikutnya <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
    $notifCount += (int)$stmt->fetchColumn();
} catch(Exception $e) {}

$currentPage = basename($_SERVER['PHP_SELF']);
function isActive($pages) {
    global $currentPage;
    return in_array($currentPage, (array)$pages) ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-text">J<span>17</span> Fitnes</div>
    <div class="brand-sub">Ketileng – Management System</div>
  </div>

  <div class="sidebar-user">
    <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['nama_panggilan'] ?? 'U', 0, 1)) ?></div>
    <div>
      <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></div>
      <div class="sidebar-user-role"><?= ucfirst($_SESSION['role'] ?? 'staff') ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Main</div>

    <a href="/j17-fitnes/owner/dashboard.php" class="sidebar-link <?= isActive('dashboard.php') ?>">
      <i class="fa-solid fa-gauge icon"></i> Dashboard
    </a>

    <a href="/j17-fitnes/owner/notifikasi.php" class="sidebar-link <?= isActive('notifikasi.php') ?>">
      <i class="fa-solid fa-bell icon"></i> Notifikasi
      <?php if($notifCount > 0): ?>
        <span class="badge bg-danger ms-auto" style="font-size:0.7rem"><?= $notifCount ?></span>
      <?php endif; ?>
    </a>

    <div class="sidebar-section-label">Manajemen</div>

    <a href="/j17-fitnes/owner/member/index.php" class="sidebar-link <?= isActive(['index.php','add.php','edit.php']) ?>">
      <i class="fa-solid fa-users icon"></i> Data Member
    </a>

    <a href="/j17-fitnes/owner/minuman/index.php" class="sidebar-link <?= isActive('index.php') ?>">
      <i class="fa-solid fa-bottle-water icon"></i> Stok Minuman
    </a>

    <a href="/j17-fitnes/owner/minuman/laporan.php" class="sidebar-link <?= isActive('laporan.php') ?>">
      <i class="fa-solid fa-chart-bar icon"></i> Laporan Keuangan
    </a>

    <a href="/j17-fitnes/owner/alat/index.php" class="sidebar-link <?= isActive('index.php') ?>">
      <i class="fa-solid fa-dumbbell icon"></i> Pemeliharaan Alat
    </a>

    <div class="sidebar-section-label">Akun</div>

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
