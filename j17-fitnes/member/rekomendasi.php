<?php
require_once __DIR__ . '/../config/database.php';
requireRole('member');
$db = getDB();

// Data rekomendasi gerakan per kelompok otot, dipetakan ke alat yang ada di gym
$rekomendasi = [
    'dada' => [
        'label' => 'Dada (Chest)', 'emoji' => '🏋️',
        'exercises' => [
            ['nama'=>'Flat Bench Press', 'alat'=>'Flat Barbell Bench Press Rack', 'desc'=>'3-4 set x 8-12 reps. Fokus dada bagian tengah.'],
            ['nama'=>'Incline Bench Press', 'alat'=>'Incline Barbell Bench Press Rack', 'desc'=>'3-4 set x 8-12 reps. Fokus dada bagian atas.'],
            ['nama'=>'Decline Bench Press', 'alat'=>'Decline Barbell Bench Press Rack', 'desc'=>'3 set x 10-12 reps. Fokus dada bagian bawah.'],
            ['nama'=>'Chest Fly', 'alat'=>'Pec Deck Fly', 'desc'=>'3 set x 12-15 reps. Isolasi otot dada, gerakan terkontrol.'],
            ['nama'=>'Dumbbell Press', 'alat'=>'Dumbbell Set', 'desc'=>'3 set x 10-12 reps. Variasi gerakan press dengan dumbbell.'],
        ]
    ],
    'punggung' => [
        'label' => 'Punggung (Back)', 'emoji' => '🦾',
        'exercises' => [
            ['nama'=>'Lat Pulldown', 'alat'=>'Lat Pulldown', 'desc'=>'3-4 set x 10-12 reps. Fokus latissimus dorsi (sayap).'],
            ['nama'=>'Seated Row', 'alat'=>'Seated Row Machine', 'desc'=>'3-4 set x 10-12 reps. Fokus punggung tengah & bisep.'],
            ['nama'=>'Bent-Over Barbell Row', 'alat'=>'Barbell', 'desc'=>'3 set x 8-10 reps. Compound movement untuk seluruh punggung.'],
            ['nama'=>'Dumbbell Row', 'alat'=>'Dumbbell Set', 'desc'=>'3 set x 10-12 reps per sisi. Fokus lat satu sisi.'],
        ]
    ],
    'kaki' => [
        'label' => 'Kaki (Legs)', 'emoji' => '🦵',
        'exercises' => [
            ['nama'=>'Leg Press', 'alat'=>'Multi-Station Leg Press & Dip Combo', 'desc'=>'3-4 set x 12-15 reps. Compound untuk quadriceps & glutes.'],
            ['nama'=>'Leg Extension', 'alat'=>'Seated Leg Extension & Leg Curl Combo', 'desc'=>'3 set x 12-15 reps. Isolasi quadriceps (paha depan).'],
            ['nama'=>'Leg Curl', 'alat'=>'Seated Leg Extension & Leg Curl Combo', 'desc'=>'3 set x 12-15 reps. Isolasi hamstring (paha belakang).'],
            ['nama'=>'Barbell Squat', 'alat'=>'Barbell', 'desc'=>'4 set x 8-10 reps. Latihan kaki paling fundamental.'],
            ['nama'=>'Dip Tricep/Kaki', 'alat'=>'Multi-Station Leg Press & Dip Combo', 'desc'=>'3 set x 10-12 reps. Gerakan tambahan dip.'],
        ]
    ],
    'bahu' => [
        'label' => 'Bahu (Shoulder)', 'emoji' => '💪',
        'exercises' => [
            ['nama'=>'Shoulder Press (Smith Machine)', 'alat'=>'All-in-One Functional Trainer & Smith Machine Combo', 'desc'=>'3-4 set x 8-12 reps. Press untuk deltoid keseluruhan.'],
            ['nama'=>'Lateral Raise', 'alat'=>'Dumbbell Set', 'desc'=>'3 set x 12-15 reps. Fokus deltoid samping (lebar bahu).'],
            ['nama'=>'Front Raise', 'alat'=>'Dumbbell Set', 'desc'=>'3 set x 12-15 reps. Fokus deltoid depan.'],
            ['nama'=>'Rotary Torso untuk Stabilitas', 'alat'=>'Rotary Torso Machine', 'desc'=>'3 set x 15 reps. Membantu stabilitas core & rotasi bahu.'],
        ]
    ],
    'lengan' => [
        'label' => 'Lengan (Arms)', 'emoji' => '💪',
        'exercises' => [
            ['nama'=>'Barbell Curl', 'alat'=>'Barbell', 'desc'=>'3-4 set x 10-12 reps. Fokus bisep.'],
            ['nama'=>'Dumbbell Curl', 'alat'=>'Dumbbell Set', 'desc'=>'3 set x 10-12 reps per lengan. Variasi isolasi bisep.'],
            ['nama'=>'Tricep Pushdown', 'alat'=>'Seated Leg Extension & Leg Curl Combo with Tricep Pull', 'desc'=>'3 set x 12-15 reps. Fokus trisep.'],
            ['nama'=>'Tricep Dip', 'alat'=>'Multi-Station Leg Press & Dip Combo', 'desc'=>'3 set x 10-12 reps. Compound trisep.'],
        ]
    ],
    'perut' => [
        'label' => 'Perut (Core)', 'emoji' => '🔥',
        'exercises' => [
            ['nama'=>'Sit-Up', 'alat'=>'Decline Sit-Up Bench', 'desc'=>'3-4 set x 15-20 reps. Latihan dasar untuk perut.'],
            ['nama'=>'Decline Crunch', 'alat'=>'Decline Sit-Up Bench', 'desc'=>'3 set x 15-20 reps. Variasi sit-up dengan sudut decline.'],
            ['nama'=>'Russian Twist', 'alat'=>'Rotary Torso Machine', 'desc'=>'3 set x 20 reps (kanan-kiri). Fokus oblique (samping perut).'],
            ['nama'=>'Cable Crunch', 'alat'=>'All-in-One Functional Trainer & Smith Machine Combo', 'desc'=>'3 set x 15-20 reps. Isolasi perut dengan beban kabel.'],
        ]
    ],
    'kardio' => [
        'label' => 'Kardio', 'emoji' => '🚴',
        'exercises' => [
            ['nama'=>'Cycling Steady-State', 'alat'=>'Bike Machine', 'desc'=>'20-30 menit intensitas sedang. Bagus untuk pemanasan / pembakaran lemak.'],
            ['nama'=>'Cycling Interval (HIIT)', 'alat'=>'Bike Machine', 'desc'=>'1 menit cepat - 1 menit pelan, ulangi 10-15x. Bakar kalori maksimal.'],
        ]
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekomendasi Latihan – J17 Fitnes</title>
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
      <div class="topbar-title">Rekomendasi Gerakan Otot</div>
    </div>
    <div class="page-content">
      <p style="color:var(--gray-500);margin-bottom:1.25rem">
        Pilih bagian otot yang ingin kamu latih, dan kami tampilkan gerakan beserta alat yang tersedia di J17 Fitnes Ketileng.
      </p>

      <div class="muscle-grid mb-3">
        <?php foreach($rekomendasi as $key => $r): ?>
        <div class="muscle-card" data-target="<?= $key ?>" onclick="showExercise('<?= $key ?>')">
          <span class="emoji"><?= $r['emoji'] ?></span>
          <div class="muscle-card-name"><?= $r['label'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <?php foreach($rekomendasi as $key => $r): ?>
      <div class="exercise-result" id="result-<?= $key ?>">
        <h5 style="font-weight:800;color:var(--dark);margin-bottom:1rem"><?= $r['emoji'] ?> Latihan untuk <?= $r['label'] ?></h5>
        <?php foreach($r['exercises'] as $ex): ?>
        <div class="exercise-item">
          <div class="exercise-icon"><i class="fa-solid fa-dumbbell"></i></div>
          <div>
            <div style="font-weight:700;color:var(--dark)"><?= htmlspecialchars($ex['nama']) ?></div>
            <div style="font-size:.82rem;color:var(--blue-600);font-weight:600;margin:.15rem 0">
              <i class="fa-solid fa-location-dot"></i> Alat: <?= htmlspecialchars($ex['alat']) ?>
            </div>
            <div style="font-size:.85rem;color:var(--gray-500)"><?= htmlspecialchars($ex['desc']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showExercise(key) {
  document.querySelectorAll('.muscle-card').forEach(c => c.classList.remove('selected'));
  document.querySelectorAll('.exercise-result').forEach(r => r.classList.remove('show'));

  document.querySelector(`.muscle-card[data-target="${key}"]`).classList.add('selected');
  const result = document.getElementById('result-' + key);
  result.classList.add('show');
  result.scrollIntoView({behavior:'smooth', block:'nearest'});
}
</script>
</body>
</html>
