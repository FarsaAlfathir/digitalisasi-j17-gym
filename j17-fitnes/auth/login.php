<?php
require_once __DIR__ . '/../config/database.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    if ($role === 'owner' || $role === 'staff') {
        header("Location: /j17-fitnes/owner/dashboard.php"); exit;
    } else {
        header("Location: /j17-fitnes/member/dashboard.php"); exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Nama panggilan/email dan password wajib diisi.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE nama_panggilan = ? OR email = ? LIMIT 1");
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['nama_lengkap']  = $user['nama_lengkap'];
                $_SESSION['nama_panggilan']= $user['nama_panggilan'];
                $_SESSION['role']          = $user['role'];
                $_SESSION['email']         = $user['email'];

                setFlash('success', 'Selamat datang, ' . $user['nama_lengkap'] . '!');

                if ($user['role'] === 'owner' || $user['role'] === 'staff') {
                    header("Location: /j17-fitnes/owner/dashboard.php");
                } else {
                    header("Location: /j17-fitnes/member/dashboard.php");
                }
                exit;
            } else {
                $error = 'Nama panggilan/email atau password salah.';
            }
        } catch(Exception $e) {
            $error = 'Terjadi kesalahan sistem. Coba lagi.';
        }
    }
}
$pageTitle = 'Login – J17 Fitnes';
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
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="/j17-fitnes/index.php" class="text-decoration-none">
        <div class="logo-text">J<span>17</span> Fitnes</div>
      </a>
      <p>Ketileng – Sistem Informasi GYM</p>
    </div>

    <h2 class="auth-title">Selamat Datang!</h2>
    <p class="auth-sub">Masuk untuk mengakses dashboard kamu.</p>

    <?php if($error): ?>
    <div class="flash-error mb-3">
      <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <?php $flash = getFlash(); if($flash && $flash['type']==='success'): ?>
    <div class="flash-success mb-3">
      <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Nama Panggilan / Email</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0" style="border:1.5px solid var(--gray-300);border-right:none;border-radius:var(--radius) 0 0 var(--radius)">
            <i class="fa-solid fa-user text-muted"></i>
          </span>
          <input type="text" name="login" class="form-control border-start-0"
                 style="border-left:none" placeholder="contoh: budi atau budi@gmail.com"
                 value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0" style="border:1.5px solid var(--gray-300);border-right:none;border-radius:var(--radius) 0 0 var(--radius)">
            <i class="fa-solid fa-lock text-muted"></i>
          </span>
          <input type="password" name="password" id="passwordInput" class="form-control border-start-0 border-end-0"
                 style="border-left:none;border-right:none" placeholder="Masukkan password" required>
          <span class="input-group-text bg-white border-start-0" style="border:1.5px solid var(--gray-300);border-left:none;border-radius:0 var(--radius) var(--radius) 0;cursor:pointer"
                onclick="togglePass()">
            <i class="fa-solid fa-eye text-muted" id="eyeIcon"></i>
          </span>
        </div>
      </div>

      <button type="submit" class="btn-primary-j17">
        <i class="fa-solid fa-right-to-bracket"></i> Masuk
      </button>
    </form>

    <div style="text-align:center;margin-top:1.5rem;font-size:.875rem;color:var(--gray-500)">
      Belum punya akun?
      <a href="/j17-fitnes/auth/register.php" style="color:var(--blue-700);font-weight:600">Daftar di sini</a>
    </div>

  </div>
</div>

<script>
function togglePass() {
  const inp = document.getElementById('passwordInput');
  const ico = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    ico.classList.replace('fa-eye','fa-eye-slash');
  } else {
    inp.type = 'password';
    ico.classList.replace('fa-eye-slash','fa-eye');
  }
}
</script>
</body>
</html>
