<?php
require_once __DIR__ . '/../../config/database.php';
requireRole(['owner','staff']);
$db = getDB();

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    // Tambah alat
    if ($_POST['action']==='tambah') {
        $nama      = trim($_POST['nama'] ?? '');
        $kategori  = $_POST['kategori'];
        $jumlah    = (int)$_POST['jumlah'];
        $interval  = (int)$_POST['interval_hari'];
        $tgl_maint = $_POST['tgl_maintenance_terakhir'];
        $catatan   = trim($_POST['catatan'] ?? '');
        if ($nama) {
            $tgl_berikut = date('Y-m-d', strtotime($tgl_maint . " +{$interval} days"));
            $db->prepare("INSERT INTO alat (nama,kategori,jumlah,status,interval_hari,tgl_maintenance_terakhir,tgl_maintenance_berikutnya,catatan) VALUES (?,?,?,'tersedia',?,?,?,?)")
               ->execute([$nama,$kategori,$jumlah,$interval,$tgl_maint,$tgl_berikut,$catatan]);
            setFlash('success','Alat berhasil ditambahkan!');
        }
        header("Location: index.php"); exit;
    }

    if ($_POST['action']==='selesai') {
        $id       = (int)$_POST['alat_id'];
        $interval = (int)$_POST['interval_hari'];
        $tgl_baru = date('Y-m-d', strtotime("+{$interval} days"));
        $db->prepare("UPDATE alat SET status='tersedia', tgl_maintenance_terakhir=CURDATE(), tgl_maintenance_berikutnya=? WHERE id=?")
           ->execute([$tgl_baru, $id]);
        setFlash('success','Maintenance selesai! Timer direset ke ' . date('d M Y', strtotime($tgl_baru)));
        header("Location: index.php"); exit;
    }

    // Update status ke maintenance
    if ($_POST['action']==='set_maintenance') {
        $id = (int)$_POST['alat_id'];
        $db->prepare("UPDATE alat SET status='maintenance' WHERE id=?")->execute([$id]);
        setFlash('success','Status alat diubah ke Maintenance.');
        header("Location: index.php"); exit;
    }

    // Hapus alat
    if ($_POST['action']==='hapus') {
        $id = (int)$_POST['alat_id'];
        $db->prepare("DELETE FROM alat WHERE id=?")->execute([$id]);
        setFlash('success','Alat dihapus.');
        header("Location: index.php"); exit;
    }
}

$alat_list = $db->query("SELECT *, DATEDIFF(tgl_maintenance_berikutnya, CURDATE()) as sisa_hari FROM alat ORDER BY kategori, sisa_hari ASC")->fetchAll();

$grouped = [];
foreach($alat_list as $a) $grouped[$a['kategori']][] = $a;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pemeliharaan Alat – J17 Fitnes</title>
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
      <div class="topbar-title">Pemeliharaan Alat Gym</div>
      <div class="topbar-actions">
        <button class="btn-j17 btn-j17-primary btn-j17-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
          <i class="fa-solid fa-plus"></i> Tambah Alat
        </button>
      </div>
    </div>
    <div class="page-content">
      <?php $flash = getFlash(); if($flash): ?>
      <div class="flash-<?= $flash['type']==='success'?'success':'error' ?>">
        <i class="fa-solid fa-<?= $flash['type']==='success'?'circle-check':'circle-exclamation' ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <?php foreach($grouped as $kat => $alats): ?>
      <div class="data-card mb-4">
        <div class="data-card-header">
          <span class="data-card-title">
            <i class="fa-solid fa-dumbbell" style="color:var(--blue-600)"></i>
            <?= htmlspecialchars($kat) ?>
            <span style="font-weight:400;color:var(--gray-400);font-size:.85rem;margin-left:.5rem">(<?= count($alats) ?> jenis)</span>
          </span>
        </div>
        <div style="overflow-x:auto">
          <table class="table-j17">
            <thead>
              <tr><th>Nama Alat</th><th>Jml</th><th>Status</th><th>Maintenance Terakhir</th><th>Jadwal Berikutnya</th><th>Sisa Hari</th><th>Catatan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php foreach($alats as $a): ?>
              <?php $sisa = (int)$a['sisa_hari']; ?>
              <tr>
                <td><strong><?= htmlspecialchars($a['nama']) ?></strong></td>
                <td><?= $a['jumlah'] ?></td>
                <td>
                  <?php if($a['status']==='maintenance'): ?>
                    <span class="badge-j17 badge-maintenance">🔧 Maintenance</span>
                  <?php else: ?>
                    <span class="badge-j17 badge-tersedia">✓ Tersedia</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:.82rem"><?= $a['tgl_maintenance_terakhir'] ? date('d M Y',strtotime($a['tgl_maintenance_terakhir'])) : '—' ?></td>
                <td style="font-size:.82rem"><?= $a['tgl_maintenance_berikutnya'] ? date('d M Y',strtotime($a['tgl_maintenance_berikutnya'])) : '—' ?></td>
                <td>
                  <?php if($sisa < 0): ?>
                    <span style="color:var(--danger);font-weight:700">Terlambat <?= abs($sisa) ?>h</span>
                  <?php elseif($sisa <= 3): ?>
                    <span style="color:var(--warning);font-weight:700">⚠ <?= $sisa ?> hari</span>
                  <?php else: ?>
                    <span style="font-weight:600"><?= $sisa ?> hari</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:.78rem;color:var(--gray-500)">
                  <?= $a['catatan'] ? htmlspecialchars($a['catatan']) : '—' ?>
                </td>
                <td>
                  <div class="d-flex gap-1 flex-nowrap">
                    <?php if($a['status']==='maintenance' || $sisa <= 3): ?>
                    <form method="POST">
                      <input type="hidden" name="action" value="selesai">
                      <input type="hidden" name="alat_id" value="<?= $a['id'] ?>">
                      <input type="hidden" name="interval_hari" value="<?= $a['interval_hari'] ?>">
                      <button type="submit" class="btn-j17 btn-j17-success btn-j17-sm" title="Tandai selesai maintenance">
                        <i class="fa-solid fa-check"></i> Selesai
                      </button>
                    </form>
                    <?php endif; ?>
                    <?php if($a['status']==='tersedia'): ?>
                    <form method="POST">
                      <input type="hidden" name="action" value="set_maintenance">
                      <input type="hidden" name="alat_id" value="<?= $a['id'] ?>">
                      <button type="submit" class="btn-j17 btn-j17-warning btn-j17-sm" title="Set ke mode maintenance">
                        <i class="fa-solid fa-wrench"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" onsubmit="return confirm('Hapus alat ini?')">
                      <input type="hidden" name="action" value="hapus">
                      <input type="hidden" name="alat_id" value="<?= $a['id'] ?>">
                      <button type="submit" class="btn-j17 btn-j17-danger btn-j17-sm"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>


<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title"><i class="fa-solid fa-dumbbell"></i> Tambah Alat Baru</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="tambah">
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nama Alat</label><input type="text" name="nama" class="form-control" required></div>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <select name="kategori" class="form-select">
                <option value="Free Weight">Free Weight</option>
                <option value="Machine">Machine</option>
                <option value="All-in-One">All-in-One</option>
              </select>
            </div>
            <div class="col-md-3"><label class="form-label">Jumlah Unit</label><input type="number" name="jumlah" class="form-control" value="1" min="1"></div>
            <div class="col-md-3"><label class="form-label">Interval (hari)</label><input type="number" name="interval_hari" class="form-control" value="30" min="1"></div>
          </div>
          <div class="mt-2"><label class="form-label">Tanggal Maintenance Terakhir</label><input type="date" name="tgl_maintenance_terakhir" class="form-control" value="<?= date('Y-m-d') ?>"></div>
          <div class="mt-2"><label class="form-label">Catatan</label><input type="text" name="catatan" class="form-control" placeholder="opsional"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn-j17 btn-j17-primary btn-j17-sm"><i class="fa-solid fa-save"></i> Simpan</button></div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
