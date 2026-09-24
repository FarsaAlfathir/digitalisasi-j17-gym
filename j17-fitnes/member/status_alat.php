<?php
require_once __DIR__ . '/../config/database.php';
requireRole('member');
$db = getDB();

$alat_list = $db->query("SELECT * FROM alat ORDER BY kategori, nama")->fetchAll();
$grouped = [];
foreach($alat_list as $a) $grouped[$a['kategori']][] = $a;

$icons = ['Free Weight'=>'fa-weight-hanging','Machine'=>'fa-gear','All-in-One'=>'fa-star'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Alat – J17 Fitnes</title>
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
      <div class="topbar-title">Status Alat Gym</div>
    </div>
    <div class="page-content">
      <?php foreach($grouped as $kat => $alats): ?>
      <div class="data-card mb-4">
        <div class="data-card-header">
          <span class="data-card-title"><i class="fa-solid <?= $icons[$kat] ?? 'fa-dumbbell' ?>" style="color:var(--blue-600)"></i> <?= htmlspecialchars($kat) ?></span>
        </div>
        <div style="overflow-x:auto">
          <table class="table-j17">
            <thead><tr><th>Nama Alat</th><th>Jumlah Unit</th><th>Status</th><th>Terakhir Dirawat</th></tr></thead>
            <tbody>
              <?php foreach($alats as $a): ?>
              <tr>
                <td><strong><?= htmlspecialchars($a['nama']) ?></strong></td>
                <td><?= $a['jumlah'] ?> unit</td>
                <td>
                  <?php if($a['status']==='maintenance'): ?>
                    <span class="badge-j17 badge-maintenance">🔧 Sedang Dimaintenance</span>
                  <?php else: ?>
                    <span class="badge-j17 badge-tersedia">✓ Tersedia</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:.85rem"><?= $a['tgl_maintenance_terakhir'] ? date('d M Y', strtotime($a['tgl_maintenance_terakhir'])) : '—' ?></td>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
