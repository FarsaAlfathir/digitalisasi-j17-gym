<?php
require_once __DIR__ . '/../../config/database.php';
requireRole(['owner','staff']);
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$user = $db->prepare("SELECT u.*, m.id as member_id, m.paket, m.tgl_mulai, m.tgl_selesai, m.status as m_status FROM users u LEFT JOIN members m ON m.user_id=u.id WHERE u.id=?");
$user->execute([$id]);
$user = $user->fetch();

if (!$user) { header("Location: index.php"); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $no_hp        = trim($_POST['no_hp'] ?? '');
    $jk           = $_POST['jenis_kelamin'] ?? '';
    $umur         = (int)($_POST['umur'] ?? 0);
    $tgl_selesai  = $_POST['tgl_selesai'] ?? null;
    $status       = $_POST['m_status'] ?? 'aktif';

    if (!$nama_lengkap) $errors[] = 'Nama lengkap wajib diisi.';

    if (empty($errors)) {
        $db->prepare("UPDATE users SET nama_lengkap=?, email=?, no_hp=?, jenis_kelamin=?, umur=? WHERE id=?")
           ->execute([$nama_lengkap,$email,$no_hp,$jk,$umur,$id]);

        if ($user['member_id'] && $tgl_selesai) {
            $db->prepare("UPDATE members SET tgl_selesai=?, status=? WHERE id=?")
               ->execute([$tgl_selesai, $status, $user['member_id']]);
        }

        setFlash('success','Data member berhasil diperbarui!');
        header("Location: index.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Member – J17 Fitnes</title>
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
      <div class="topbar-title">Edit Data Member</div>
      <a href="index.php" class="btn-j17 btn-j17-outline btn-j17-sm"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="page-content">
      <div class="data-card" style="max-width:600px">
        <div class="data-card-header">
          <span class="data-card-title"><i class="fa-solid fa-pen" style="color:var(--blue-600)"></i> Edit: <?= htmlspecialchars($user['nama_lengkap']) ?> (@<?= htmlspecialchars($user['nama_panggilan']) ?>)</span>
        </div>
        <div style="padding:1.5rem">
          <?php if($errors): ?>
          <div class="flash-error mb-3"><ul style="margin:0;padding-left:1.25rem"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
          <?php endif; ?>

          <form method="POST">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']??'') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($user['no_hp']??'') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Umur</label>
                <input type="number" name="umur" class="form-control" value="<?= htmlspecialchars($user['umur']??'') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select">
                  <option value="">—</option>
                  <option value="L" <?= $user['jenis_kelamin']==='L'?'selected':'' ?>>Laki-laki</option>
                  <option value="P" <?= $user['jenis_kelamin']==='P'?'selected':'' ?>>Perempuan</option>
                </select>
              </div>

              <?php if($user['member_id']): ?>
              <div class="col-md-6">
                <label class="form-label">Tanggal Berakhir Membership</label>
                <input type="date" name="tgl_selesai" class="form-control" value="<?= $user['tgl_selesai'] ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Status Membership</label>
                <select name="m_status" class="form-select">
                  <option value="aktif" <?= $user['m_status']==='aktif'?'selected':'' ?>>Aktif</option>
                  <option value="expired" <?= $user['m_status']==='expired'?'selected':'' ?>>Expired</option>
                  <option value="pending" <?= $user['m_status']==='pending'?'selected':'' ?>>Pending</option>
                </select>
              </div>
              <?php endif; ?>

              <div class="col-12 mt-2">
                <button type="submit" class="btn-j17 btn-j17-primary"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
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
