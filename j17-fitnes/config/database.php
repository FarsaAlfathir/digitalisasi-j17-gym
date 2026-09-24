<?php
// ============================================================
// config/database.php - Konfigurasi Koneksi Database
// J17 Fitnes Ketileng
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'j17_fitnes');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:40px;background:#fee;color:#c00;border:1px solid #f99;border-radius:8px;margin:40px auto;max-width:600px;">
                <h2>&#9888; Koneksi Database Gagal</h2>
                <p>Pastikan XAMPP/MySQL sudah berjalan dan database <strong>j17_fitnes</strong> sudah dibuat.</p>
                <p style="font-size:12px;color:#999;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
            </div>');
        }
    }
    return $pdo;
}

// Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: Cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: Require login, redirect jika belum
function requireLogin($redirect = '/auth/login.php') {
    if (!isLoggedIn()) {
        $base = dirname(dirname($_SERVER['PHP_SELF']));
        header("Location: " . $base . "/auth/login.php");
        exit;
    }
}

// Helper: Require role tertentu
function requireRole($roles) {
    requireLogin();
    $roles = is_array($roles) ? $roles : [$roles];
    if (!in_array($_SESSION['role'], $roles)) {
        header("Location: /j17-fitnes/index.php?error=akses_ditolak");
        exit;
    }
}

// Helper: Flash message
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Helper: Format Rupiah
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Helper: Hitung sisa hari membership
function sisaHari($tgl_selesai) {
    $sekarang = new DateTime();
    $selesai  = new DateTime($tgl_selesai);
    $diff = $sekarang->diff($selesai);
    if ($selesai < $sekarang) return -$diff->days;
    return $diff->days;
}
