<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$produk_id = (int) ($_POST['produk_id'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$rating = (int) ($_POST['rating'] ?? 0);
$komentar = trim($_POST['komentar'] ?? '');

if ($produk_id <= 0 || $nama === '' || $rating < 1 || $rating > 5 || $komentar === '') {
    exit("Data ulasan tidak lengkap atau tidak valid.");
}

// Memeriksa produk
$cek = $pdo->prepare(
    "SELECT id FROM produk WHERE id = ? AND status = 'aktif'"
);
$cek->execute([$produk_id]);

if (!$cek->fetch()) {
    exit("Produk tidak ditemukan.");
}

// Menyimpan nama pelanggan
$stmt = $pdo->prepare(
    "INSERT INTO pelanggan (nama) VALUES (?)"
);
$stmt->execute([$nama]);

$pelanggan_id = $pdo->lastInsertId();

// Menyimpan ulasan
$stmt = $pdo->prepare("
    INSERT INTO ulasan (produk_id, pelanggan_id, rating, komentar)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([
    $produk_id,
    $pelanggan_id,
    $rating,
    $komentar
]);

header("Location: detail-produk.php?id=" . $produk_id);
exit;
?>