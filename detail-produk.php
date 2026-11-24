<?php
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare(
    "SELECT * FROM produk WHERE id = ? AND status = 'aktif'"
);
$stmt->execute([$id]);

$produk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produk) {
    exit("Maaf, produk tidak ditemukan.");
}

// Mengambil ulasan untuk produk ini
$stmt_ulasan = $pdo->prepare("
    SELECT ulasan.*, pelanggan.nama
    FROM ulasan
    JOIN pelanggan ON ulasan.pelanggan_id = pelanggan.id
    WHERE ulasan.produk_id = ?
    ORDER BY ulasan.dibuat_pada DESC
");
$stmt_ulasan->execute([$id]);
$ulasan = $stmt_ulasan->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - Amai Cookies</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header>
    <div class="logo">Amai Cookies.</div>
    <nav>
        <a href="index.php">Home</a>
        <a href="index.php#produk">Produk</a>
        <a href="keranjang.php">Keranjang</a>
    </nav>
</header>

<section class="container">

    <div class="detail-produk">

        <div>
            <img
                src="assets/images/<?php echo htmlspecialchars($produk['foto']); ?>"
                alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>"
                class="gambar-detail"
            >
        </div>

        <div class="info-detail">

            <h1><?php echo htmlspecialchars($produk['nama_produk']); ?></h1>

            <h2 class="price">
                Rp<?php echo number_format($produk['harga'], 0, ',', '.'); ?>
            </h2>

            <p><?php echo htmlspecialchars($produk['deskripsi']); ?></p>

            <p><strong>Berat:</strong>
                <?php echo htmlspecialchars($produk['berat']); ?>
            </p>

            <p><strong>Bahan:</strong>
                <?php echo htmlspecialchars($produk['bahan']); ?>
            </p>

            <p><strong>Isi paket:</strong>
                <?php echo htmlspecialchars($produk['isi_paket']); ?>
            </p>

            <p><strong>Stok:</strong>
                <?php echo (int) $produk['stok']; ?>
            </p>

            <?php if ($produk['stok'] > 0): ?>

                <form action="keranjang.php" method="POST">

                    <input type="hidden" name="produk_id"
                           value="<?php echo (int) $produk['id']; ?>">

                    <label for="jumlah">Jumlah pembelian:</label>

                    <input
                        type="number"
                        id="jumlah"
                        name="jumlah"
                        value="1"
                        min="1"
                        max="<?php echo (int) $produk['stok']; ?>"
                        required
                    >

                    <button type="submit" name="tambah">
                        Tambahkan ke Keranjang
                    </button>

                </form>

            <?php else: ?>

                <p>Maaf, produk sedang habis.</p>

            <?php endif; ?>

            <p><a href="index.php">← Kembali ke katalog</a></p>

        </div>
    </div>
<!-- Bagian Ulasan Pelanggan -->
<div class="bagian-ulasan">

    <h2>Ulasan Pelanggan 💗</h2>

    <?php if (count($ulasan) > 0): ?>

        <?php foreach ($ulasan as $item): ?>

            <div class="kartu-ulasan">

                <h3>
                    <?php echo htmlspecialchars($item['nama']); ?>
                </h3>

                <p class="bintang">
                    <?php echo str_repeat("⭐", (int) $item['rating']); ?>
                </p>

                <p>
                    <?php echo htmlspecialchars($item['komentar']); ?>
                </p>

                <small>
                    <?php echo date('d-m-Y', strtotime($item['dibuat_pada'])); ?>
                </small>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p>Belum ada ulasan untuk produk ini. Jadilah pelanggan pertama yang memberikan ulasan! 💕</p>

    <?php endif; ?>
<h3>Bagikan Pengalamanmu! 💗</h3>

<form action="tambah-ulasan.php" method="POST" class="form-ulasan">

    <input
        type="hidden"
        name="produk_id"
        value="<?php echo (int) $produk['id']; ?>"
    >

    <label>Nama kamu:</label>
    <input
        type="text"
        name="nama"
        placeholder="Masukkan nama kamu"
        required
    >

    <label>Rating:</label>
    <select name="rating" required>
        <option value="">Pilih rating</option>
        <option value="5">⭐⭐⭐⭐⭐ (Sangat suka)</option>
        <option value="4">⭐⭐⭐⭐ (Suka)</option>
        <option value="3">⭐⭐⭐ (Cukup suka)</option>
        <option value="2">⭐⭐ (Kurang suka)</option>
        <option value="1">⭐ (Tidak suka)</option>
    </select>

    <label>Ulasan:</label>
    <textarea
        name="komentar"
        placeholder="Ceritakan pengalamanmu mencoba cookies kami..."
        required
    ></textarea>

    <button type="submit">Kirim Ulasan 💌</button>

</form>
</div>
</section>

<footer>
    <h3>Amai Cookies</h3>
    <p>Lost in a World of Sweetness.</p>
    <p>&copy; 2026 Amai Cookies. All Rights Reserved.</p>
</footer>

</body>
</html>