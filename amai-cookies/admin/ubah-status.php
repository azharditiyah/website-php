<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: masuk.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pesanan.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$status = $_POST['status_pesanan'] ?? '';

$status_valid = [
    'di proses',
    'dikirim',
    'selesai',
    'dibatalkan'
];

if ($id <= 0 || !in_array($status, $status_valid, true)) {
    exit("Data status tidak valid.");
}

$stmt = $pdo->prepare("
    UPDATE pesanan
    SET status_pesanan = ?
    WHERE id = ?
");

$stmt->execute([$status, $id]);

header("Location: pesanan.php");
exit;
?>