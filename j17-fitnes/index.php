<?php
require_once __DIR__ . '/config/database.php';
$pageTitle = 'J17 Fitnes Ketileng – GYM Terbaik di Tembalang';

// Ambil daftar alat dari DB
$alat_list = [];
try {
    $db = getDB();
    $alat_list = $db->query("SELECT * FROM alat ORDER BY kategori, nama")->fetchAll();
} catch(Exception $e) {}

// Group alat by kategori
$alat_grouped = [];
foreach($alat_list as $a) {
    $alat_grouped[$a['kategori']][] = $a;
}
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

<!-- NAVBAR -->
<nav class="navbar-j17">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between w-100">
      <a href="/j17-fitnes/index.php" class="brand text-decoration-none">
        J<span>17</span> Fitnes
      </a>
      <div class="d-flex align-items-center gap-2">
        <a href="#tentang" class="nav-link d-none d-md-block">Tentang</a>
        <a href="#fasilitas" class="nav-link d-none d-md-block">Fasilitas</a>
        <a href="#jam" class="nav-link d-none d-md-block">Jam Buka</a>
        <a href="/j17-fitnes/auth/login.php" class="btn-nav-login nav-link">Login</a>
        <a href="/j17-fitnes/auth/register.php" class="btn-nav-register nav-link">Daftar Member</a>
      </div>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero" id="home">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <div class="hero-badge">
          <i class="fa-solid fa-bolt"></i> Semarang, Tembalang
        </div>
        <h1>
          Mulai Perjalanan
          <span class="highlight">Fitness-mu Disini</span>
        </h1>
        <p class="lead">
          J17 Fitnes Ketileng — gym lengkap dengan 38 unit alat pilihan,
          suasana nyaman, dan harga terjangkau. Di belakang RSUD Wongsonegoro, Ketileng.
        </p>
        <div class="d-flex gap-3 flex-wrap">
          <a href="/j17-fitnes/auth/register.php" class="btn-hero-primary">
            <i class="fa-solid fa-user-plus"></i> Daftar Sekarang
          </a>
          <a href="#fasilitas" class="btn-hero-outline">
            Lihat Fasilitas
          </a>
        </div>
        <div class="hero-stats">
          <div>
            <span class="hero-stat-num">38+</span>
            <span class="hero-stat-label">Unit Alat</span>
          </div>
          <div>
            <span class="hero-stat-num">3</span>
            <span class="hero-stat-label">Kategori Alat</span>
          </div>
          <div>
            <span class="hero-stat-num">16h</span>
            <span class="hero-stat-label">Jam Operasional</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TENTANG -->
<section id="tentang" style="padding: 5rem 0; background: var(--white);">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="section-tag">Tentang Kami</div>
        <h2 class="section-title">J17 Fitnes Ketileng</h2>
        <p style="color:var(--gray-500); margin-bottom:1.5rem; line-height:1.8;">
          Berlokasi strategis di Jl. Juwono Baru RT04/RW04, Kelurahan Mangunharjo, Kecamatan Tembalang —
          tepat di belakang RSUD KRMT Wongsonegoro, Semarang. Gym kami menyediakan fasilitas lengkap
          untuk mendukung perjalanan fitness Anda, baik pemula maupun yang sudah berpengalaman.
        </p>
        <p style="color:var(--gray-500); line-height:1.8; margin-bottom:1.5rem;">
          Dipimpin oleh <strong>Royce Wijaya</strong>, J17 Fitnes berkomitmen untuk memberikan
          pengalaman latihan terbaik dengan peralatan terawat, lingkungan bersih, dan komunitas
          yang supportif.
        </p>
        <div class="d-flex gap-3 flex-wrap">
          <div style="background:var(--blue-50);border-radius:12px;padding:1rem 1.25rem;flex:1;min-width:140px;">
            <i class="fa-solid fa-location-dot" style="color:var(--blue-600);margin-bottom:.5rem"></i>
            <div style="font-size:.8rem;color:var(--gray-500);">Lokasi</div>
            <div style="font-weight:700;font-size:.9rem;">Ketileng, Tembalang</div>
          </div>
          <div style="background:var(--blue-50);border-radius:12px;padding:1rem 1.25rem;flex:1;min-width:140px;">
            <i class="fa-solid fa-person-running" style="color:var(--blue-600);margin-bottom:.5rem"></i>
            <div style="font-size:.8rem;color:var(--gray-500);">Pemilik</div>
            <div style="font-weight:700;font-size:.9rem;">Royce Wijaya</div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div style="background:linear-gradient(135deg,var(--blue-800),var(--blue-600));border-radius:24px;padding:2.5rem;color:white;position:relative;overflow:hidden;">
          <div style="position:absolute;top:-20px;right:-20px;width:120px;height:120px;background:rgba(255,255,255,.06);border-radius:50%"></div>
          <i class="fa-solid fa-dumbbell" style="font-size:3rem;margin-bottom:1rem;opacity:.8"></i>
          <h3 style="font-weight:800;margin-bottom:1rem;">Kenapa J17 Fitnes?</h3>
          <?php foreach([
            ['fa-check-circle','Peralatan lengkap & terawat rutin'],
            ['fa-check-circle','Harga membership terjangkau'],
            ['fa-check-circle','Lokasi strategis dekat RSUD'],
            ['fa-check-circle','Suasana gym yang nyaman & bersih'],
            ['fa-check-circle','Sistem digital untuk member modern'],
          ] as $item): ?>
          <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;font-size:.9rem;">
            <i class="fa-solid <?= $item[0] ?>" style="color:var(--blue-300);flex-shrink:0"></i>
            <?= $item[1] ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- JAM OPERASIONAL -->
<section id="jam" style="padding: 5rem 0; background: var(--gray-100);">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-tag">Jam Operasional</div>
      <h2 class="section-title">Kapan Kami Buka?</h2>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4">
        <div class="jam-card">
          <div class="jam-icon"><i class="fa-solid fa-sun"></i></div>
          <h5 style="font-weight:800;color:var(--dark)">Senin – Sabtu</h5>
          <p style="font-size:1.5rem;font-weight:900;color:var(--blue-700);margin:.5rem 0">06.00 – 22.00</p>
          <p style="color:var(--gray-500);font-size:.85rem;">16 jam penuh setiap hari kerja</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="jam-card">
          <div class="jam-icon"><i class="fa-solid fa-moon"></i></div>
          <h5 style="font-weight:800;color:var(--dark)">Minggu</h5>
          <p style="font-size:1.5rem;font-weight:900;color:var(--blue-700);margin:.5rem 0">16.00 – 22.00</p>
          <p style="color:var(--gray-500);font-size:.85rem;">Sore hingga malam di akhir pekan</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="jam-card">
          <div class="jam-icon"><i class="fa-solid fa-bottle-water"></i></div>
          <h5 style="font-weight:800;color:var(--dark)">Minuman Tersedia</h5>
          <p style="font-size:1rem;font-weight:700;color:var(--blue-700);margin:.5rem 0">Air & Minuman Energi</p>
          <p style="color:var(--gray-500);font-size:.85rem;">Tersedia selama jam operasional</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FASILITAS -->
<section id="fasilitas" style="padding: 5rem 0; background: var(--white);">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-tag">Fasilitas</div>
      <h2 class="section-title">Peralatan Gym Kami</h2>
      <p style="color:var(--gray-500);max-width:500px;margin:0 auto">
        Total 38+ unit alat fitness terlengkap dalam 3 kategori untuk mendukung semua jenis latihan.
      </p>
    </div>
    <div class="row g-4">
      <?php
      $icons = [
        'Free Weight' => 'fa-weight-hanging',
        'Machine'     => 'fa-gear',
        'All-in-One'  => 'fa-star',
      ];
      foreach($alat_grouped as $kat => $items): ?>
      <div class="col-lg-4">
        <div class="facility-category">
          <div class="facility-header">
            <i class="fa-solid <?= $icons[$kat] ?? 'fa-dumbbell' ?>"></i>
            <?= htmlspecialchars($kat) ?>
            <span class="badge-count"><?= count($items) ?> item</span>
          </div>
          <div class="facility-list">
            <?php foreach($items as $a): ?>
            <div class="facility-item">
              <span><?= htmlspecialchars($a['nama']) ?></span>
              <span style="margin-left:auto;font-size:.78rem;color:var(--gray-400)">x<?= $a['jumlah'] ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section style="padding:5rem 0;background:linear-gradient(135deg,var(--blue-900),var(--blue-700));">
  <div class="container text-center text-white">
    <h2 style="font-size:2.2rem;font-weight:900;margin-bottom:1rem;">Siap Mulai Latihan?</h2>
    <p style="opacity:.8;margin-bottom:2rem;font-size:1.05rem;">Daftar sekarang dan akses semua fitur member digital J17 Fitnes Ketileng.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="/j17-fitnes/auth/register.php" class="btn-hero-primary">
        <i class="fa-solid fa-user-plus"></i> Daftar Member
      </a>
      <a href="/j17-fitnes/auth/login.php" class="btn-hero-outline">
        <i class="fa-solid fa-right-to-bracket"></i> Login
      </a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer style="background:var(--dark);color:rgba(255,255,255,.6);padding:2rem 0;text-align:center;font-size:.85rem;">
  <div class="container">
    <p style="font-weight:700;color:white;font-size:1.1rem;margin-bottom:.25rem">J17 Fitnes Ketileng</p>
    <p>Jl. Juwono Baru RT04/RW04, Mangunharjo, Tembalang, Semarang</p>
    <p style="margin-top:.5rem">© <?= date('Y') ?> J17 Fitnes Ketileng. Hak cipta dilindungi.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
