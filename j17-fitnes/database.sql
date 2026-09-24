-- ============================================================
-- DATABASE: j17_fitnes
-- J17 Fitnes Ketileng - Sistem Informasi GYM
-- ============================================================

CREATE DATABASE IF NOT EXISTS j17_fitnes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE j17_fitnes;

-- ============================================================
-- TABEL 1: users (semua pengguna sistem)
-- Relasi: 1 user bisa punya 1 member profile, banyak kehadiran
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_lengkap VARCHAR(100) NOT NULL,
    nama_panggilan VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('owner', 'staff', 'member') NOT NULL DEFAULT 'member',
    no_hp VARCHAR(20) DEFAULT NULL,
    jenis_kelamin ENUM('L', 'P') DEFAULT NULL,
    umur INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 2: members (detail keanggotaan, relasi ke users)
-- Relasi: 1 member -> 1 user (ONE-TO-ONE)
-- ============================================================
CREATE TABLE IF NOT EXISTS members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    paket ENUM('1_bulan','3_bulan','6_bulan','1_tahun') NOT NULL DEFAULT '1_bulan',
    tgl_mulai DATE NOT NULL,
    tgl_selesai DATE NOT NULL,
    status ENUM('aktif','expired','pending') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 3: minuman (inventaris stok minuman)
-- ============================================================
CREATE TABLE IF NOT EXISTS minuman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    harga_jual DECIMAL(10,0) NOT NULL,
    harga_modal DECIMAL(10,0) NOT NULL,
    stok INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 4: transaksi_minuman (keluar/masuk stok)
-- Relasi: MANY-TO-ONE ke minuman
-- ============================================================
CREATE TABLE IF NOT EXISTS transaksi_minuman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    minuman_id INT NOT NULL,
    tipe ENUM('masuk','keluar') NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,0) NOT NULL,
    total DECIMAL(10,0) NOT NULL,
    keterangan VARCHAR(255) DEFAULT NULL,
    tgl_transaksi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (minuman_id) REFERENCES minuman(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 5: alat (peralatan gym + jadwal maintenance)
-- ============================================================
CREATE TABLE IF NOT EXISTS alat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(150) NOT NULL,
    kategori ENUM('Free Weight','Machine','All-in-One') NOT NULL,
    jumlah INT DEFAULT 1,
    status ENUM('tersedia','maintenance') DEFAULT 'tersedia',
    interval_hari INT DEFAULT 30 COMMENT 'interval maintenance dalam hari',
    tgl_maintenance_terakhir DATE DEFAULT NULL,
    tgl_maintenance_berikutnya DATE DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 6: kehadiran (daily check-in member)
-- Relasi: MANY-TO-ONE ke users
-- ============================================================
CREATE TABLE IF NOT EXISTS kehadiran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tgl_hadir DATE NOT NULL,
    jam_hadir TIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_checkin (user_id, tgl_hadir)
) ENGINE=InnoDB;

-- ============================================================
-- DATA AWAL (SEED)
-- ============================================================

-- Password: 'owner123' (bcrypt hashed) - untuk testing bisa pakai MD5 dulu
-- Di produksi wajib pakai password_hash()
INSERT INTO users (nama_lengkap, nama_panggilan, email, password, role, no_hp) VALUES
('Royce Wijaya', 'royce', 'royce@j17fitnes.com', '$2y$10$f/iGD/KaZoXgqBC/sDdMCOIG57wv7Yz1.PAEJNzaRPxbMYDZ.ekpS', 'owner', '08123456789'),
('Budi Santoso', 'budi', 'budi@gmail.com', '$2y$10$f/iGD/KaZoXgqBC/sDdMCOIG57wv7Yz1.PAEJNzaRPxbMYDZ.ekpS', 'member', '08234567890'),
('Siti Rahma', 'siti', 'siti@gmail.com', '$2y$10$f/iGD/KaZoXgqBC/sDdMCOIG57wv7Yz1.PAEJNzaRPxbMYDZ.ekpS', 'member', '08345678901'),
('Doni Pratama', 'doni', 'doni@gmail.com', '$2y$10$f/iGD/KaZoXgqBC/sDdMCOIG57wv7Yz1.PAEJNzaRPxbMYDZ.ekpS', 'member', '08456789012');

-- NOTE: Password default semua user adalah 'password' (Laravel default hash)
-- Untuk testing gunakan password: password

INSERT INTO members (user_id, paket, tgl_mulai, tgl_selesai, status) VALUES
(2, '1_bulan', CURDATE() - INTERVAL 28 DAY, CURDATE() + INTERVAL 3 DAY, 'aktif'),
(3, '3_bulan', CURDATE() - INTERVAL 10 DAY, CURDATE() + INTERVAL 80 DAY, 'aktif'),
(4, '1_bulan', CURDATE() - INTERVAL 35 DAY, CURDATE() - INTERVAL 5 DAY, 'expired');

INSERT INTO minuman (nama, harga_jual, harga_modal, stok) VALUES
('Air Mineral 600ml', 5000, 2500, 24),
('Pocari Sweat 500ml', 10000, 6500, 12),
('Teh Botol Sosro', 6000, 3500, 18),
('Mizone Apple', 7000, 4000, 15),
('Good Day Coffee', 8000, 5000, 10),
('Extra Joss Sachet', 5000, 2000, 30);

INSERT INTO alat (nama, kategori, jumlah, status, interval_hari, tgl_maintenance_terakhir, tgl_maintenance_berikutnya, catatan) VALUES
('Flat Barbell Bench Press Rack', 'Free Weight', 2, 'tersedia', 30, CURDATE() - INTERVAL 28 DAY, CURDATE() + INTERVAL 2 DAY, NULL),
('Incline Barbell Bench Press Rack', 'Free Weight', 1, 'tersedia', 30, CURDATE() - INTERVAL 15 DAY, CURDATE() + INTERVAL 15 DAY, NULL),
('Decline Barbell Bench Press Rack', 'Free Weight', 1, 'tersedia', 30, CURDATE() - INTERVAL 10 DAY, CURDATE() + INTERVAL 20 DAY, NULL),
('Decline Sit-Up Bench', 'Free Weight', 1, 'tersedia', 30, CURDATE() - INTERVAL 5 DAY, CURDATE() + INTERVAL 25 DAY, NULL),
('Dumbbell Set', 'Free Weight', 20, 'tersedia', 60, CURDATE() - INTERVAL 20 DAY, CURDATE() + INTERVAL 40 DAY, NULL),
('Barbell', 'Free Weight', 3, 'tersedia', 30, CURDATE() - INTERVAL 30 DAY, CURDATE(), 'Perlu pelumasan hari ini'),
('Rotary Torso Machine', 'Machine', 1, 'tersedia', 30, CURDATE() - INTERVAL 25 DAY, CURDATE() + INTERVAL 5 DAY, NULL),
('Multi-Station Leg Press & Dip Combo', 'Machine', 1, 'tersedia', 30, CURDATE() - INTERVAL 8 DAY, CURDATE() + INTERVAL 22 DAY, NULL),
('Seated Leg Extension & Leg Curl Combo', 'Machine', 1, 'tersedia', 30, CURDATE() - INTERVAL 12 DAY, CURDATE() + INTERVAL 18 DAY, NULL),
('Lat Pulldown', 'Machine', 1, 'maintenance', 30, CURDATE() - INTERVAL 30 DAY, CURDATE(), 'Kabel perlu diganti'),
('Seated Row Machine', 'Machine', 1, 'tersedia', 30, CURDATE() - INTERVAL 18 DAY, CURDATE() + INTERVAL 12 DAY, NULL),
('Pec Deck Fly', 'Machine', 1, 'tersedia', 30, CURDATE() - INTERVAL 22 DAY, CURDATE() + INTERVAL 8 DAY, NULL),
('Bike Machine', 'Machine', 3, 'tersedia', 30, CURDATE() - INTERVAL 3 DAY, CURDATE() + INTERVAL 27 DAY, NULL),
('All-in-One Functional Trainer & Smith Machine', 'All-in-One', 1, 'tersedia', 14, CURDATE() - INTERVAL 13 DAY, CURDATE() + INTERVAL 1 DAY, NULL);

-- Beberapa sample transaksi minuman
INSERT INTO transaksi_minuman (minuman_id, tipe, jumlah, harga_satuan, total, keterangan) VALUES
(1, 'masuk', 24, 2500, 60000, 'Restok awal'),
(2, 'masuk', 12, 6500, 78000, 'Restok awal'),
(3, 'masuk', 18, 3500, 63000, 'Restok awal'),
(1, 'keluar', 2, 5000, 10000, 'Penjualan'),
(2, 'keluar', 1, 10000, 10000, 'Penjualan'),
(3, 'keluar', 3, 6000, 18000, 'Penjualan');

-- Sample kehadiran
INSERT INTO kehadiran (user_id, tgl_hadir, jam_hadir) VALUES
(2, CURDATE() - INTERVAL 2 DAY, '07:30:00'),
(2, CURDATE() - INTERVAL 1 DAY, '08:15:00'),
(3, CURDATE() - INTERVAL 1 DAY, '17:00:00'),
(3, CURDATE(), '06:45:00');
