<?php
require_once __DIR__ . '/../config/database.php';

if (isLoggedIn()) {
    header("Location: /j17-fitnes/index.php"); exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap   = trim($_POST['nama_lengkap'] ?? '');
    $nama_panggilan = strtolower(trim($_POST['nama_panggilan'] ?? ''));
    $email          = trim($_POST['email'] ?? '');
    $no_hp          = trim($_POST['no_hp'] ?? '');
    $jenis_kelamin  = $_POST['jenis_kelamin'] ?? '';
    $umur           = (int)($_POST['umur'] ?? 0);
    $paket          = $_POST['paket'] ?? '1_bulan';
    $password       = $_POST['password'] ?? '';
    $confirm        = $_POST['confirm_password'] ?? '';

    if (empty($nama_lengkap))    $errors[] = 'Nama lengkap wajib diisi.';
    if (empty($nama_panggilan))  $errors[] = 'Nama panggilan wajib diisi.';
    if (strlen($password) < 6)   $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $confirm)  $errors[] = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        try {
            $db = getDB();

            // Cek duplikat
            $stmt = $db->prepare("SELECT id FROM users WHERE nama_panggilan=? OR (email!='' AND email=?)");
            $stmt->execute([$nama_panggilan, $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Nama panggilan atau email sudah terdaftar.';
            } else {
                // Insert user
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (nama_lengkap, nama_panggilan, email, password, role, no_hp, jenis_kelamin, umur) VALUES (?,?,?,?,'member',?,?,?)");
                $stmt->execute([$nama_lengkap, $nama_panggilan, $email, $hash, $no_hp, $jenis_kelamin, $umur]);
                $userId = $db->lastInsertId();

                // Hitung tanggal selesai berdasarkan paket
                $durasi = ['1_bulan'=>30,'3_bulan'=>90,'6_bulan'=>180,'1_tahun'=>365];
                $hari   = $durasi[$paket] ?? 30;
                $tgl_mulai   = date('Y-m-d');
                $tgl_selesai = date('Y-m-d', strtotime("+{$hari} days"));

                $stmt = $db->prepare("INSERT INTO members (user_id, paket, tgl_mulai, tgl_selesai, status) VALUES (?,?,?,?,'aktif')");
                $stmt->execute([$userId, $paket, $tgl_mulai, $tgl_selesai]);

                setFlash('success', 'Registrasi berhasil! Silakan login.');
                header("Location: /j17-fitnes/auth/login.php"); exit;
            }
        } catch(Exception $e) {
            $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Member – J17 Fitnes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="/j17-fitnes/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-page" style="padding:2rem 1rem">
  <div class="auth-card" style="max-width:560px">
    <div class="auth-logo">
      <a href="/j17-fitnes/index.php" class="text-decoration-none">
        <div class="logo-text">J<span>17</span> Fitnes</div>
      </a>
      <p>Daftar sebagai member baru</p>
    </div>

    <h2 class="auth-title">Formulir Pendaftaran</h2>
    <p class="auth-sub">Data kamu akan langsung tersimpan di database.</p>

    <?php if($errors): ?>
    <div class="flash-error">
      <div>
        <i class="fa-solid fa-circle-exclamation"></i>
        <ul style="margin:0;padding-left:1.25rem">
          <?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
          <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama sesuai KTP"
                 value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Nama Panggilan <span class="text-danger">*</span></label>
          <input type="text" name="nama_panggilan" class="form-control" placeholder="Untuk login (unik)"
                 value="<?= htmlspecialchars($_POST['nama_panggilan'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="opsional@email.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label">No. HP</label>
          <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx"
                 value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Usia</label>
          <input type="number" name="umur" class="form-control" placeholder="Thn" min="10" max="80"
                 value="<?= htmlspecialchars($_POST['umur'] ?? '') ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="form-select">
            <option value="">Pilih</option>
            <option value="L" <?= ($_POST['jenis_kelamin']??'')==='L'?'selected':'' ?>>Laki-laki</option>
            <option value="P" <?= ($_POST['jenis_kelamin']??'')==='P'?'selected':'' ?>>Perempuan</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Paket Membership <span class="text-danger">*</span></label>
          <div class="row g-2">
            <?php
            $pakets = [
              '1_bulan'  => ['label'=>'1 Bulan',  'desc'=>'30 hari'],
              '3_bulan'  => ['label'=>'3 Bulan',  'desc'=>'90 hari'],
              '6_bulan'  => ['label'=>'6 Bulan',  'desc'=>'180 hari'],
              '1_tahun'  => ['label'=>'1 Tahun',  'desc'=>'365 hari'],
            ];
            foreach($pakets as $val => $p): ?>
            <div class="col-6 col-md-3">
              <label style="display:block;cursor:pointer">
                <input type="radio" name="paket" value="<?= $val ?>"
                       <?= (($_POST['paket']??'1_bulan')===$val)?'checked':'' ?>
                       style="display:none" class="paket-radio">
                <div class="paket-option" style="border:2px solid var(--gray-300);border-radius:var(--radius);padding:.75rem;text-align:center;transition:all .2s">
                  <div style="font-weight:700;color:var(--dark)"><?= $p['label'] ?></div>
                  <div style="font-size:.75rem;color:var(--gray-500)"><?= $p['desc'] ?></div>
                </div>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Password <span class="text-danger">*</span></label>
          <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
        </div>

        <div class="col-12 mt-2">
          <button type="submit" class="btn-primary-j17">
            <i class="fa-solid fa-user-plus"></i> Daftar Sekarang
          </button>
        </div>
      </div>
    </form>

    <div style="text-align:center;margin-top:1.25rem;font-size:.875rem;color:var(--gray-500)">
      Sudah punya akun? <a href="/j17-fitnes/auth/login.php" style="color:var(--blue-700);font-weight:600">Login di sini</a>
    </div>
  </div>
</div>

<script>
// Highlight paket yang dipilih
document.querySelectorAll('.paket-radio').forEach(r => {
  function update() {
    document.querySelectorAll('.paket-option').forEach(o => {
      o.style.borderColor = 'var(--gray-300)';
      o.style.background = 'white';
    });
    if (r.checked) {
      r.nextElementSibling.style.borderColor = 'var(--blue-600)';
      r.nextElementSibling.style.background = 'var(--blue-50)';
    }
  }
  r.addEventListener('change', () => {
    document.querySelectorAll('.paket-radio').forEach(x => {
      x.nextElementSibling.style.borderColor = 'var(--gray-300)';
      x.nextElementSibling.style.background = 'white';
    });
    r.nextElementSibling.style.borderColor = 'var(--blue-600)';
    r.nextElementSibling.style.background = 'var(--blue-50)';
  });
  if (r.checked) update();
});
</script>
</body>
</html>
