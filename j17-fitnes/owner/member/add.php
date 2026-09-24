<?php
require_once __DIR__ . '/../../config/database.php';
requireRole(['owner','staff']);
$db = getDB();

$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nama_lengkap   = trim($_POST['nama_lengkap']   ?? '');
    $nama_panggilan = strtolower(trim($_POST['nama_panggilan'] ?? ''));
    $email          = trim($_POST['email']          ?? '');
    $no_hp          = trim($_POST['no_hp']          ?? '');
    $jk             = $_POST['jenis_kelamin']       ?? '';
    $umur           = (int)($_POST['umur']          ?? 0);
    $paket          = $_POST['paket']               ?? '1_bulan';
    $password       = $_POST['password']            ?? 'member123';

    if (!$nama_lengkap)  $errors[] = 'Nama lengkap wajib diisi.';
    if (!$nama_panggilan)$errors[] = 'Nama panggilan wajib diisi.';

    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE nama_panggilan=?");
        $stmt->execute([$nama_panggilan]);
        if ($stmt->fetch()) $errors[] = 'Nama panggilan sudah dipakai.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (nama_lengkap,nama_panggilan,email,password,role,no_hp,jenis_kelamin,umur) VALUES (?,?,?,?,'member',?,?,?)");
        $stmt->execute([$nama_lengkap,$nama_panggilan,$email,$hash,$no_hp,$jk,$umur]);
        $uid = $db->lastInsertId();

        $durasi = ['1_bulan'=>30,'3_bulan'=>90,'6_bulan'=>180,'1_tahun'=>365];
        $hari   = $durasi[$paket] ?? 30;
        $stmt = $db->prepare("INSERT INTO members (user_id,paket,tgl_mulai,tgl_selesai,status) VALUES (?,?,CURDATE(),DATE_ADD(CURDATE(), INTERVAL ? DAY),'aktif')");
        $stmt->execute([$uid,$paket,$hari]);

        setFlash('success','Member baru berhasil ditambahkan!');
        header("Location: index.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Member – J17 Fitnes</title>
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
      <div class="topbar-title">Tambah Member Baru</div>
      <a href="index.php" class="btn-j17 btn-j17-outline btn-j17-sm"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="page-content">
      <div class="data-card" style="max-width:600px">
        <div class="data-card-header">
          <span class="data-card-title"><i class="fa-solid fa-user-plus" style="color:var(--blue-600)"></i> Form Tambah Member</span>
        </div>
        <div style="padding:1.5rem">
          <?php if($errors): ?>
          <div class="flash-error mb-3"><ul style="margin:0;padding-left:1.25rem"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
          <?php endif; ?>

          <form method="POST">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($_POST['nama_lengkap']??'') ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Nama Panggilan (untuk login) <span class="text-danger">*</span></label>
                <input type="text" name="nama_panggilan" class="form-control" value="<?= htmlspecialchars($_POST['nama_panggilan']??'') ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email']??'') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($_POST['no_hp']??'') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Umur</label>
                <input type="number" name="umur" class="form-control" min="10" max="80" value="<?= htmlspecialchars($_POST['umur']??'') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select">
                  <option value="">—</option>
                  <option value="L" <?= ($_POST['jenis_kelamin']??'')==='L'?'selected':'' ?>>Laki-laki</option>
                  <option value="P" <?= ($_POST['jenis_kelamin']??'')==='P'?'selected':'' ?>>Perempuan</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Paket Membership</label>
                <select name="paket" class="form-select">
                  <option value="1_bulan">1 Bulan (30 hari)</option>
                  <option value="3_bulan">3 Bulan (90 hari)</option>
                  <option value="6_bulan">6 Bulan (180 hari)</option>
                  <option value="1_tahun">1 Tahun (365 hari)</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Password Default</label>
                <input type="text" name="password" class="form-control" value="member123">
                <div style="font-size:.75rem;color:var(--gray-400);margin-top:.25rem">Member bisa ganti nanti</div>
              </div>
              <div class="col-12 mt-2">
                <button type="submit" class="btn-j17 btn-j17-primary"><i class="fa-solid fa-save"></i> Simpan Member</button>
                <a href="index.php" class="btn-j17 btn-j17-outline ms-2">Batal</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
