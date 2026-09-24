<?php
require_once __DIR__ . '/../../config/database.php';
requireRole(['owner','staff']);
$db = getDB();

// Handle DELETE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    if ($_POST['action']==='delete' && isset($_POST['user_id'])) {
        $stmt = $db->prepare("DELETE FROM users WHERE id=? AND role='member'");
        $stmt->execute([(int)$_POST['user_id']]);
        setFlash('success','Member berhasil dihapus.');
        header("Location: index.php"); exit;
    }
    // Perpanjang membership
    if ($_POST['action']==='perpanjang') {
        $member_id = (int)$_POST['member_id'];
        $tambah    = (int)$_POST['tambah_hari'];
        $stmt = $db->prepare("UPDATE members SET tgl_selesai = DATE_ADD(GREATEST(tgl_selesai, CURDATE()), INTERVAL ? DAY), status='aktif' WHERE id=?");
        $stmt->execute([$tambah, $member_id]);
        setFlash('success','Masa aktif member berhasil diperpanjang.');
        header("Location: index.php"); exit;
    }
}

// Search/Filter
$search = trim($_GET['q'] ?? '');
$where  = "WHERE u.role='member'";
$params = [];
if($search) {
    $where .= " AND (u.nama_lengkap LIKE ? OR u.nama_panggilan LIKE ? OR u.email LIKE ?)";
    $s = "%$search%";
    $params = [$s,$s,$s];
}

$members = $db->prepare("
    SELECT u.*, m.id as member_id, m.paket, m.tgl_mulai, m.tgl_selesai, m.status as m_status,
           DATEDIFF(m.tgl_selesai, CURDATE()) as sisa_hari,
           (SELECT COUNT(*) FROM kehadiran k WHERE k.user_id=u.id) as total_hadir
    FROM users u
    LEFT JOIN members m ON m.user_id=u.id
    $where
    ORDER BY m.tgl_selesai ASC
");
$members->execute($params);
$members = $members->fetchAll();

$pageTitle = 'Manajemen Member – J17 Fitnes';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?></title>
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
      <div class="topbar-title">Manajemen Member</div>
    </div>
    <div class="page-content">
      <?php $flash = getFlash(); if($flash): ?>
      <div class="flash-<?= $flash['type']==='success'?'success':'error' ?>">
        <i class="fa-solid fa-<?= $flash['type']==='success'?'circle-check':'circle-exclamation' ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <div class="data-card">
        <div class="data-card-header">
          <span class="data-card-title">Data Member (<?= count($members) ?>)</span>
          <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
              <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" style="width:200px;font-size:.85rem" placeholder="Cari nama / email...">
              <button class="btn-j17 btn-j17-outline btn-j17-sm" type="submit"><i class="fa-solid fa-search"></i></button>
            </form>
            <a href="add.php" class="btn-j17 btn-j17-primary btn-j17-sm"><i class="fa-solid fa-plus"></i> Tambah</a>
          </div>
        </div>
        <div style="overflow-x:auto">
          <table class="table-j17">
            <thead>
              <tr>
                <th>#</th><th>Nama Member</th><th>Kontak</th><th>Paket</th>
                <th>Mulai</th><th>Selesai</th><th>Sisa Hari</th><th>Kehadiran</th><th>Status</th><th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($members as $i=>$m): ?>
              <?php $sisa = (int)$m['sisa_hari']; ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td>
                  <strong><?= htmlspecialchars($m['nama_lengkap']) ?></strong><br>
                  <span style="font-size:.78rem;color:var(--gray-400)">@<?= htmlspecialchars($m['nama_panggilan']) ?></span>
                </td>
                <td style="font-size:.82rem">
                  <?= $m['no_hp'] ? htmlspecialchars($m['no_hp']) : '—' ?><br>
                  <span style="color:var(--gray-400)"><?= $m['email'] ? htmlspecialchars($m['email']) : '—' ?></span>
                </td>
                <td><?= $m['paket'] ? str_replace('_',' ',ucfirst($m['paket'])) : '—' ?></td>
                <td style="font-size:.82rem"><?= $m['tgl_mulai'] ? date('d/m/Y',strtotime($m['tgl_mulai'])) : '—' ?></td>
                <td style="font-size:.82rem"><?= $m['tgl_selesai'] ? date('d/m/Y',strtotime($m['tgl_selesai'])) : '—' ?></td>
                <td>
                  <?php if($m['tgl_selesai']): ?>
                    <?php if($sisa < 0): ?>
                      <span style="color:var(--danger);font-weight:700"><?= $sisa ?> hari</span>
                    <?php elseif($sisa <= 3): ?>
                      <span style="color:var(--warning);font-weight:700">H-<?= $sisa ?></span>
                    <?php else: ?>
                      <span style="font-weight:600"><?= $sisa ?> hari</span>
                    <?php endif; ?>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td style="text-align:center"><strong><?= $m['total_hadir'] ?>x</strong></td>
                <td>
                  <?php if(!$m['tgl_selesai']): ?>
                    <span class="badge-j17 badge-pending">Belum Ada</span>
                  <?php elseif($sisa < 0): ?>
                    <span class="badge-j17 badge-expired">Expired</span>
                  <?php elseif($sisa <= 3): ?>
                    <span class="badge-j17 badge-warning">⚠ H-<?= $sisa ?></span>
                  <?php else: ?>
                    <span class="badge-j17 badge-aktif">Aktif</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="d-flex gap-1 flex-nowrap">
                    <?php if($m['member_id']): ?>
                    <button class="btn-j17 btn-j17-success btn-j17-sm"
                            onclick="perpanjang(<?= $m['member_id'] ?>, '<?= htmlspecialchars($m['nama_lengkap'],ENT_QUOTES) ?>')">
                      <i class="fa-solid fa-rotate"></i> Perpanjang
                    </button>
                    <?php endif; ?>
                    <a href="edit.php?id=<?= $m['id'] ?>" class="btn-j17 btn-j17-warning btn-j17-sm"><i class="fa-solid fa-pen"></i></a>
                    <button class="btn-j17 btn-j17-danger btn-j17-sm"
                            onclick="hapus(<?= $m['id'] ?>, '<?= htmlspecialchars($m['nama_lengkap'],ENT_QUOTES) ?>')">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($members)): ?>
              <tr><td colspan="10" style="text-align:center;padding:2rem;color:var(--gray-400)">Tidak ada data member.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Perpanjang -->
<div class="modal fade" id="modalPerpanjang" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="fa-solid fa-rotate"></i> Perpanjang Membership</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="perpanjang">
        <input type="hidden" name="member_id" id="perpMemberId">
        <div class="modal-body">
          <p style="font-size:.9rem">Member: <strong id="perpNama"></strong></p>
          <label class="form-label">Tambah Durasi</label>
          <select name="tambah_hari" class="form-select">
            <option value="30">1 Bulan (30 hari)</option>
            <option value="90">3 Bulan (90 hari)</option>
            <option value="180">6 Bulan (180 hari)</option>
            <option value="365">1 Tahun (365 hari)</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-j17 btn-j17-success btn-j17-sm"><i class="fa-solid fa-check"></i> Perpanjang</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title">Konfirmasi Hapus</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="user_id" id="hapusId">
        <div class="modal-body" style="font-size:.9rem">Hapus member <strong id="hapusNama"></strong>? Data tidak bisa dikembalikan.</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-j17 btn-j17-danger btn-j17-sm"><i class="fa-solid fa-trash"></i> Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function perpanjang(id, nama) {
  document.getElementById('perpMemberId').value = id;
  document.getElementById('perpNama').textContent = nama;
  new bootstrap.Modal(document.getElementById('modalPerpanjang')).show();
}
function hapus(id, nama) {
  document.getElementById('hapusId').value = id;
  document.getElementById('hapusNama').textContent = nama;
  new bootstrap.Modal(document.getElementById('modalHapus')).show();
}
</script>
</body>
</html>
