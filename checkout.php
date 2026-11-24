<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once 'config/database.php';
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$items = [];
$total = 0;
$error = '';
$berhasil = false;
$kodePesanan = '';

// Ambil ulang produk dan harga dari database
foreach ($_SESSION['keranjang'] as $id => $jumlah) {
    $stmt = $pdo->prepare(
        "SELECT * FROM produk WHERE id = ? AND status = 'aktif'"
    );
    $stmt->execute([(int)$id]);
    $produk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produk) {
        continue;
    }

    $jumlah = (int)$jumlah;

    if ($jumlah < 1 || $jumlah > (int)$produk['stok']) {
        $error = 'Jumlah produk melebihi stok. Silakan periksa keranjang.';
        break;
    }

    $subtotal = (int)$produk['harga'] * $jumlah;
    $total += $subtotal;

    $items[] = [
        'produk' => $produk,
        'jumlah' => $jumlah,
        'subtotal' => $subtotal
    ];
}

// Proses pesanan ketika formulir dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $wa = trim($_POST['no_whatsapp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $kota = trim($_POST['kota'] ?? '');
    $metode = $_POST['metode_pembayaran'] ?? '';

    $metodeDiizinkan = ['Transfer Bank', 'QRIS', 'COD'];

    if (
        empty($items) ||
        $nama === '' ||
        $email === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $wa === '' ||
        $alamat === '' ||
        $kota === '' ||
        !in_array($metode, $metodeDiizinkan, true)
    ) {
        $error = 'Periksa kembali semua data pembelian.';
    } else {

        try {
            $pdo->beginTransaction();

            // Periksa stok kembali agar tidak melebihi persediaan
            foreach ($items as $item) {
                $stmt = $pdo->prepare(
                    "SELECT stok FROM produk WHERE id = ? FOR UPDATE"
                );
                $stmt->execute([$item['produk']['id']]);
                $stokSekarang = $stmt->fetchColumn();

                if ($stokSekarang === false ||
                    $item['jumlah'] > (int)$stokSekarang) {
                    throw new Exception('Stok produk tidak mencukupi.');
                }
            }

            // Simpan data pelanggan
            $stmt = $pdo->prepare(
                "INSERT INTO pelanggan
                (nama, email, no_whatsapp, alamat, kota)
                VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nama, $email, $wa, $alamat, $kota]);

            $pelangganId = $pdo->lastInsertId();

            // Membuat kode pesanan
            $kodePesanan = 'AMAI-' . date('Ymd') . '-' .
                           strtoupper(bin2hex(random_bytes(3)));

            // Simpan pesanan
            $stmt = $pdo->prepare(
                "INSERT INTO pesanan
                (kode_pesanan, pelanggan_id, total_harga,
                 metode_pembayaran, status_pembayaran, status_pesanan)
                VALUES (?, ?, ?, ?, 'Menunggu Pembayaran', 'Diproses')"
            );
            $stmt->execute([
                $kodePesanan,
                $pelangganId,
                $total,
                $metode
            ]);

            $pesananId = $pdo->lastInsertId();

            // Simpan rincian dan kurangi stok
            foreach ($items as $item) {
                $produkId = $item['produk']['id'];
                $jumlah = $item['jumlah'];
                $harga = $item['produk']['harga'];
                $subtotal = $item['subtotal'];

                $stmt = $pdo->prepare(
                    "INSERT INTO detail_pesanan
                    (pesanan_id, produk_id, jumlah, harga_satuan, subtotal)
                    VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $pesananId,
                    $produkId,
                    $jumlah,
                    $harga,
                    $subtotal
                ]);

                $stmt = $pdo->prepare(
                    "UPDATE produk SET stok = stok - ?
                     WHERE id = ? AND stok >= ?"
                );
                $stmt->execute([$jumlah, $produkId, $jumlah]);

                if ($stmt->rowCount() !== 1) {
                    throw new Exception('Gagal memperbarui stok.');
                }
            }

            $pdo->commit();

            $_SESSION['keranjang'] = [];
            $berhasil = true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'Pesanan belum berhasil dibuat. ' .
                     'Silakan periksa stok atau database.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Amai Cookies</title>
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

<section class="container checkout">

<?php if ($berhasil): ?>

    <div class="checkout-box">
        <h2>Pesanan Berhasil Dibuat! 🍪</h2>
        <p>Terima kasih sudah berbelanja di Amai Cookies.</p>

        <p>Kode pesanan:</p>
        <h3><?php echo htmlspecialchars($kodePesanan); ?></h3>

        <p>Total produk:
            <strong>Rp<?php echo number_format($total, 0, ',', '.'); ?></strong>
        </p>

        <p>Status pembayaran: Menunggu Pembayaran</p>
        <p>Metode pembayaran:
            <?php echo htmlspecialchars($metode); ?>
        </p>

        <p>Silakan hubungi admin Amai Cookies untuk konfirmasi
           pembayaran dan pengiriman.</p>

        <a class="detail-btn" href="index.php">Kembali ke Beranda</a>
    </div>

<?php elseif (empty($items)): ?>

    <div class="checkout-box">
        <h2>Keranjang masih kosong</h2>
        <p>Silakan pilih cookies terlebih dahulu.</p>
        <a class="detail-btn" href="index.php#produk">Pilih Produk</a>
    </div>

<?php else: ?>

    <h2>Checkout Amai Cookies</h2>
    <p>Lengkapi data berikut untuk memesan cookies favoritmu.</p>

    <?php if ($error): ?>
        <p class="pesan-keranjang">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <div class="checkout-box">
        <h3>Ringkasan Belanja</h3>

        <?php foreach ($items as $item): ?>
            <p>
                <?php echo htmlspecialchars($item['produk']['nama_produk']); ?>
                (<?php echo $item['jumlah']; ?> pcs) -
                Rp<?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
            </p>
        <?php endforeach; ?>

        <h3>
            Total: Rp<?php echo number_format($total, 0, ',', '.'); ?>
        </h3>
        <p>Ongkos kirim belum termasuk dalam total.</p>
    </div>

    <form method="POST" class="checkout-box">

        <h3>Data Pembeli</h3>

        <label>Nama Lengkap</label>
        <input type="text" name="nama" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Nomor WhatsApp</label>
        <input type="tel" name="no_whatsapp" required>

        <label>Alamat Lengkap</label>
        <textarea name="alamat" rows="4" required></textarea>

        <label>Kota</label>
        <input type="text" name="kota" required>

        <h3>Metode Pembayaran</h3>

        <select name="metode_pembayaran" required>
            <option value="">Pilih pembayaran</option>
            <option value="Transfer Bank">Transfer Bank</option>
            <option value="QRIS">QRIS</option>
            <option value="COD">COD</option>
        </select>

        <p class="catatan-pembayaran">
            Pembayaran belum diproses otomatis.
            Detail rekening atau QRIS resmi dan ketersediaan COD
            perlu dikonfirmasi oleh admin.
        </p>

        <button type="submit">Buat Pesanan</button>

    </form>

<?php endif; ?>

</section>

<footer>
    <h3>Amai Cookies</h3>
    <p>Lost in a World of Sweetness.</p>
    <p>&copy; 2026 Amai Cookies. All Rights Reserved.</p>
</footer>

</body>
</html>