<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: masuk.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: produk.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    exit("ID produk tidak valid.");
}

// Menonaktifkan produk agar riwayat pesanan dan ulasan tetap aman
$stmt = $pdo->prepare("
    UPDATE produk
    SET status = 'nonaktif'
    WHERE id = ?
");
$stmt->execute([$id]);

header("Location: produk.php");
exit;
?>