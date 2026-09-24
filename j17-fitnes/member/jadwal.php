<?php
require_once __DIR__ . '/../config/database.php';
requireRole('member');

// Template split latihan berdasarkan frekuensi per minggu
$splits = [
    1 => [
        ['hari'=>'Hari 1', 'fokus'=>'Full Body', 'desc'=>'Latihan seluruh tubuh: dada, punggung, kaki, dan core. Cocok untuk pemula dengan waktu terbatas.'],
    ],
    2 => [
        ['hari'=>'Hari 1', 'fokus'=>'Upper Body (Dada, Bahu, Lengan)', 'desc'=>'Fokus tubuh bagian atas: bench press, shoulder press, bicep & tricep curl.'],
        ['hari'=>'Hari 2', 'fokus'=>'Lower Body + Core', 'desc'=>'Fokus kaki dan perut: leg press, leg extension/curl, sit-up.'],
    ],
    3 => [
        ['hari'=>'Hari 1', 'fokus'=>'Dada & Trisep', 'desc'=>'Bench press (flat/incline/decline), chest fly, tricep pushdown & dip.'],
        ['hari'=>'Hari 2', 'fokus'=>'Punggung & Bisep', 'desc'=>'Lat pulldown, seated row, barbell/dumbbell row, bicep curl.'],
        ['hari'=>'Hari 3', 'fokus'=>'Kaki, Bahu & Core', 'desc'=>'Leg press, leg extension/curl, shoulder press, sit-up & russian twist.'],
    ],
    4 => [
        ['hari'=>'Hari 1', 'fokus'=>'Dada & Trisep', 'desc'=>'Bench press (flat/incline/decline), chest fly, tricep pushdown & dip.'],
        ['hari'=>'Hari 2', 'fokus'=>'Punggung & Bisep', 'desc'=>'Lat pulldown, seated row, barbell/dumbbell row, bicep curl.'],
        ['hari'=>'Hari 3', 'fokus'=>'Kaki (Quad, Hamstring, Glutes)', 'desc'=>'Squat, leg press, leg extension, leg curl.'],
        ['hari'=>'Hari 4', 'fokus'=>'Bahu & Core', 'desc'=>'Shoulder press, lateral raise, sit-up, russian twist, cable crunch.'],
    ],
    5 => [
        ['hari'=>'Hari 1', 'fokus'=>'Dada', 'desc'=>'Bench press (flat/incline/decline) + chest fly. Fokus volume tinggi.'],
        ['hari'=>'Hari 2', 'fokus'=>'Punggung', 'desc'=>'Lat pulldown, seated row, bent-over row, dumbbell row.'],
        ['hari'=>'Hari 3', 'fokus'=>'Kaki', 'desc'=>'Squat, leg press, leg extension, leg curl. Hari paling intens.'],
        ['hari'=>'Hari 4', 'fokus'=>'Bahu & Trisep', 'desc'=>'Shoulder press, lateral raise, front raise, tricep dip & pushdown.'],
        ['hari'=>'Hari 5', 'fokus'=>'Bisep, Core & Kardio', 'desc'=>'Bicep curl, sit-up, russian twist, ditutup dengan cycling 20 menit.'],
    ],
    6 => [
        ['hari'=>'Hari 1', 'fokus'=>'Dada', 'desc'=>'Bench press (flat/incline/decline) + chest fly.'],
        ['hari'=>'Hari 2', 'fokus'=>'Punggung', 'desc'=>'Lat pulldown, seated row, bent-over row.'],
        ['hari'=>'Hari 3', 'fokus'=>'Kaki', 'desc'=>'Squat, leg press, leg extension, leg curl.'],
        ['hari'=>'Hari 4', 'fokus'=>'Bahu', 'desc'=>'Shoulder press, lateral raise, front raise, rotary torso.'],
        ['hari'=>'Hari 5', 'fokus'=>'Lengan (Bisep & Trisep)', 'desc'=>'Barbell/dumbbell curl, tricep pushdown & dip.'],
        ['hari'=>'Hari 6', 'fokus'=>'Core & Kardio', 'desc'=>'Sit-up, russian twist, cable crunch + cycling 20-30 menit.'],
    ],
];

$result = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $frekuensi = (int)($_POST['frekuensi'] ?? 3);
    $hari_libur = $_POST['hari_libur'] ?? [];
    $tujuan = $_POST['tujuan'] ?? 'umum';

    $frekuensi = max(1, min(6, $frekuensi));
    $result = $splits[$frekuensi];

    // Assign hari aktual berdasarkan hari libur yang dipilih
    $semua_hari = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
    $hari_tersedia = array_values(array_diff($semua_hari, $hari_libur));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jadwal Personal – J17 Fitnes</title>
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
      <div class="topbar-title">Rekomendasi Jadwal Personal</div>
    </div>
    <div class="page-content">

      <div class="ai-form-card mb-4">
        <h5 style="font-weight:800;margin-bottom:.25rem"><i class="fa-solid fa-calendar-week"></i> Buat Jadwal Latihanmu</h5>
        <p style="opacity:.8;font-size:.9rem;margin-bottom:1.5rem">Isi preferensi kamu, dan sistem akan menyusun jadwal mingguan yang dipersonalisasi.</p>

        <form method="POST">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" style="color:rgba(255,255,255,.85)">Berapa kali seminggu?</label>
              <select name="frekuensi" class="form-select">
                <?php for($i=1;$i<=6;$i++): ?>
                <option value="<?= $i ?>" <?= (isset($_POST['frekuensi']) && $_POST['frekuensi']==$i)?'selected':'' ?>><?= $i ?>x per minggu</option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="color:rgba(255,255,255,.85)">Tujuan Latihan</label>
              <select name="tujuan" class="form-select">
                <option value="umum">Kebugaran Umum</option>
                <option value="otot">Membangun Otot (Bulking)</option>
                <option value="lemak">Menurunkan Lemak (Cutting)</option>
                <option value="kekuatan">Kekuatan (Strength)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="color:rgba(255,255,255,.85)">Hari Libur (tidak bisa gym)</label>
              <select name="hari_libur[]" class="form-select" multiple style="height:80px">
                <?php foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h): ?>
                <option value="<?= $h ?>"><?= $h ?></option>
                <?php endforeach; ?>
              </select>
              <div style="font-size:.72rem;color:rgba(255,255,255,.6);margin-top:.25rem">Tahan Ctrl untuk pilih lebih dari satu</div>
            </div>
            <div class="col-12">
              <button type="submit" class="btn-j17 btn-j17-primary"><i class="fa-solid fa-wand-magic-sparkles"></i> Buatkan Jadwal</button>
            </div>
          </div>
        </form>
      </div>

      <?php if($result): ?>
      <div class="ai-result">
        <h5 style="font-weight:800;color:var(--dark);margin-bottom:.25rem">
          📅 Jadwal Latihan: <?= (int)$_POST['frekuensi'] ?>x / Minggu
        </h5>
        <p style="color:var(--gray-500);font-size:.9rem;margin-bottom:1.25rem">
          Tujuan: <strong><?= [
            'umum'=>'Kebugaran Umum','otot'=>'Membangun Otot','lemak'=>'Menurunkan Lemak','kekuatan'=>'Kekuatan'
          ][$_POST['tujuan']] ?? 'Kebugaran Umum' ?></strong>
          <?php if(!empty($hari_libur)): ?>
          | Hari libur: <strong><?= implode(', ', $hari_libur) ?></strong>
          <?php endif; ?>
        </p>

        <?php
        // Distribusi hari latihan secara merata di hari yang tersedia
        $jumlah_tersedia = count($hari_tersedia);
        $jadwal_hari = [];
        if ($jumlah_tersedia > 0) {
            $step = $jumlah_tersedia / max(1,(int)$_POST['frekuensi']);
            for($i=0; $i<(int)$_POST['frekuensi']; $i++) {
                $idx = (int)round($i * $step) % $jumlah_tersedia;
                $jadwal_hari[] = $hari_tersedia[$idx];
            }
            $jadwal_hari = array_values(array_unique($jadwal_hari));
            // Jika ada duplikat karena pembulatan, isi dengan hari tersedia lain
            $i = 0;
            while(count($jadwal_hari) < (int)$_POST['frekuensi'] && $i < $jumlah_tersedia) {
                if(!in_array($hari_tersedia[$i], $jadwal_hari)) $jadwal_hari[] = $hari_tersedia[$i];
                $i++;
            }
        }
        ?>

        <?php foreach($result as $idx => $r): ?>
        <div class="schedule-day">
          <div class="schedule-day-name">
            <?= $r['hari'] ?>
            <?php if(isset($jadwal_hari[$idx])): ?>
              <span style="font-weight:400;color:var(--blue-600)">— <?= $jadwal_hari[$idx] ?></span>
            <?php endif; ?>
          </div>
          <div style="font-weight:700;color:var(--dark);font-size:.95rem;margin-bottom:.25rem"><?= $r['fokus'] ?></div>
          <div style="font-size:.85rem;color:var(--gray-500)"><?= $r['desc'] ?></div>
        </div>
        <?php endforeach; ?>

        <?php if($jumlah_tersedia < (int)$_POST['frekuensi']): ?>
        <div class="flash-error mt-3" style="font-size:.85rem">
          <i class="fa-solid fa-circle-exclamation"></i>
          Frekuensi yang kamu pilih lebih besar dari hari yang tersedia (setelah dikurangi hari libur). Jadwal di atas tetap ditampilkan, namun beberapa hari mungkin perlu disesuaikan manual.
        </div>
        <?php endif; ?>

        <div style="background:var(--blue-50);border-radius:var(--radius);padding:1rem 1.25rem;margin-top:1rem;font-size:.85rem;color:var(--gray-600)">
          <i class="fa-solid fa-lightbulb" style="color:var(--blue-600)"></i>
          <strong>Tips:</strong> Jangan lupa untuk pemanasan 5-10 menit sebelum latihan dan pendinginan setelahnya.
          Lihat halaman <a href="rekomendasi.php" style="color:var(--blue-700);font-weight:600">Rekomendasi Latihan</a> untuk detail gerakan dan alat yang digunakan.
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
