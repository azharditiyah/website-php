<?php
require_once 'config/database.php';

// Mengambil produk aktif dari database
$query = $pdo->query(
    "SELECT * FROM produk
     WHERE status = 'aktif'
     ORDER BY id ASC"
);

$produk = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Amai Cookies - Soft Cookies</title>

    <link rel="stylesheet"
          href="assets/css/style.css">
</head>

<body>

<header>
    <div class="logo">Amai Cookies.</div>

    <nav>
        <a href="index.php">Beranda</a>
        <a href="#produk">Produk</a>
        <a href="tentang.php">Tentang Kami</a>
        <a href="kontak.php">Kontak</a>
        <a href="kebijakan.php">Kebijakan</a>
        <a href="keranjang.php">keranjang 🛒</a>
    </nav>
</header>
<section class="hero">

    <div class="hero-content">

        <div class="hero-text">
            <span class="hero-label">HOMEMADE WITH LOVE ♡</span>

            <h1>Lost in a World of Sweetness</h1>

            <p>
                Temukan kebahagiaan dalam setiap gigitan.
                Nikmati kelembutan soft cookies dari Amai Cookies.
            </p>

            <a href="#produk">Belanja Sekarang ♡</a>
        </div>

        <div class="hero-image">
            <img
                src="assets/images/chocolate.jpg"
                alt="Soft Cookies Chocolate Amai Cookies"
            >
        </div>

    </div>

</section>

<section class="container" id="produk">

    <div class="section-title">
        <h2>Our Cookies</h2>

        <p>
            Pilih rasa favoritmu dan nikmati
            kelembutan setiap gigitan.
        </p>
    </div>

    <div class="products">

        <?php foreach ($produk as $item): ?>

            <div class="product-card">

                <img
                    src="assets/images/<?php echo htmlspecialchars($item['foto']); ?>"
                    alt="<?php echo htmlspecialchars($item['nama_produk']); ?>"
                    loading="lazy"
                >

                <div class="product-info">

                    <h3>
                        <?php echo htmlspecialchars($item['nama_produk']); ?>
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($item['deskripsi']); ?>
                    </p>

                    <div class="price">
                        Rp<?php echo number_format($item['harga'], 0, ',', '.'); ?>
                    </div>

                    <p>
                        Berat: <?php echo htmlspecialchars($item['berat']); ?>
                    </p>

                    <p>
                        Stok: <?php echo (int) $item['stok']; ?>
                    </p>

                    <a
                        class="detail-btn"
                        href="detail-produk.php?id=<?php echo (int) $item['id']; ?>"
                    >
                        Lihat Produk
                    </a>

                </div>

            </div>

        <?php endforeach; ?>

        <?php if (empty($produk)): ?>
            <p>Belum ada produk tersedia.</p>
        <?php endif; ?>

    </div>

</section>

<footer>
    <h3>Amai Cookies</h3>

    <p>A Little Sweetness, A Lot of Happiness.</p>

    <p>&copy; 2026 Amai Cookies. All Rights Reserved.</p>
</footer>

</body>
</html>