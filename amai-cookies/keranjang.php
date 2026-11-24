<?php
// Session harus dimulai sebelum HTML ditampilkan
session_start();

require_once 'config/database.php';

// Membuat keranjang kosong jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Pesan pemberitahuan
$pesan = '';

// PROSES TAMBAH PRODUK
if (isset($_POST['tambah'])) {

    $id = (int) ($_POST['produk_id'] ?? 0);
    $jumlah = (int) ($_POST['jumlah'] ?? 1);

    if ($jumlah < 1) {
        $jumlah = 1;
    }

    $stmt = $pdo->prepare(
        "SELECT id, stok FROM produk
         WHERE id = ? AND status = 'aktif'"
    );
    $stmt->execute([$id]);
    $produk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produk) {
        $_SESSION['pesan_keranjang'] = 'Produk tidak ditemukan.';
    } elseif ($produk['stok'] < 1) {
        $_SESSION['pesan_keranjang'] = 'Stok produk sedang habis.';
    } else {
        $jumlah_lama = $_SESSION['keranjang'][$id] ?? 0;
        $jumlah_baru = $jumlah_lama + $jumlah;

        if ($jumlah_baru > $produk['stok']) {
            $_SESSION['pesan_keranjang'] =
                'Jumlah melebihi stok yang tersedia.';
        } else {
            $_SESSION['keranjang'][$id] = $jumlah_baru;
            $_SESSION['pesan_keranjang'] =
                'Produk berhasil ditambahkan ke keranjang!';
        }
    }

    header('Location: keranjang.php');
    exit;
}

// PROSES HAPUS PRODUK
if (isset($_POST['hapus'])) {
    $id = (int) ($_POST['produk_id'] ?? 0);

    unset($_SESSION['keranjang'][$id]);

    $_SESSION['pesan_keranjang'] =
        'Produk berhasil dihapus dari keranjang.';

    header('Location: keranjang.php');
    exit;
}

// PROSES PERBARUI JUMLAH
if (isset($_POST['perbarui'])) {

    if (isset($_POST['jumlah']) && is_array($_POST['jumlah'])) {

        foreach ($_POST['jumlah'] as $id => $jumlah) {

            $id = (int) $id;
            $jumlah = (int) $jumlah;

            $stmt = $pdo->prepare(
                "SELECT stok FROM produk
                 WHERE id = ? AND status = 'aktif'"
            );
            $stmt->execute([$id]);
            $produk = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$produk || $jumlah < 1) {
                unset($_SESSION['keranjang'][$id]);
            } elseif ($jumlah > $produk['stok']) {
                $_SESSION['keranjang'][$id] =
                    (int) $produk['stok'];
            } else {
                $_SESSION['keranjang'][$id] = $jumlah;
            }
        }
    }

    $_SESSION['pesan_keranjang'] =
        'Keranjang berhasil diperbarui.';

    header('Location: keranjang.php');
    exit;
}

// Mengambil pesan pemberitahuan
if (isset($_SESSION['pesan_keranjang'])) {
    $pesan = $_SESSION['pesan_keranjang'];
    unset($_SESSION['pesan_keranjang']);
}

// Mengambil detail produk dalam keranjang
$items = [];
$total = 0;

foreach ($_SESSION['keranjang'] as $id => $jumlah) {

    $stmt = $pdo->prepare(
        "SELECT * FROM produk
         WHERE id = ? AND status = 'aktif'"
    );
    $stmt->execute([(int) $id]);

    $produk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produk || $produk['stok'] < 1) {
        unset($_SESSION['keranjang'][$id]);
        continue;
    }

    $jumlah = min((int) $jumlah, (int) $produk['stok']);

    $_SESSION['keranjang'][$id] = $jumlah;

    $subtotal = $produk['harga'] * $jumlah;
    $total += $subtotal;

    $items[] = [
        'produk' => $produk,
        'jumlah' => $jumlah,
        'subtotal' => $subtotal
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Amai Cookies</title>
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
    <div class="section-title">
        <h2>Keranjang Belanja</h2>
        <p>Periksa cookies pilihanmu sebelum melanjutkan.</p>
    </div>

    <?php if ($pesan): ?>
        <p class="pesan-keranjang">
            <?php echo htmlspecialchars($pesan); ?>
        </p>
    <?php endif; ?>

    <?php if (empty($items)): ?>

        <div class="keranjang-kosong">
            <h3>Keranjangmu masih kosong 🍪</h3>
            <p>Yuk, pilih cookies favoritmu terlebih dahulu!</p>
            <a class="detail-btn" href="index.php#produk">
                Mulai Belanja
            </a>
        </div>

    <?php else: ?>

        <form method="POST" action="keranjang.php">

            <div class="tabel-keranjang">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php $p = $item['produk']; ?>

                            <tr>
                                <td>
                                    <img
                                        src="assets/images/<?php echo htmlspecialchars($p['foto']); ?>"
                                        alt="<?php echo htmlspecialchars($p['nama_produk']); ?>"
                                        width="65"
                                    >
                                    <strong>
                                        <?php echo htmlspecialchars($p['nama_produk']); ?>
                                    </strong>
                                </td>

                                <td>
                                    Rp<?php echo number_format($p['harga'], 0, ',', '.'); ?>
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="jumlah[<?php echo (int) $p['id']; ?>]"
                                        value="<?php echo $item['jumlah']; ?>"
                                        min="1"
                                        max="<?php echo (int) $p['stok']; ?>"
                                        required
                                    >
                                </td>

                                <td>
                                    Rp<?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                                </td>

                                <td>
                                    <button
                                        type="submit"
                                        name="hapus"
                                        value="1"
                                        formaction="keranjang.php"
                                        formmethod="POST"
                                        onclick="this.form.querySelector('[name=produk_id]').value='<?php echo (int) $p['id']; ?>';"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <input type="hidden" name="produk_id" value="">

            <button type="submit" name="perbarui">
                Perbarui Keranjang
            </button>

        </form>

        <div class="total-keranjang">
            <h3>
                Total Belanja:
                Rp<?php echo number_format($total, 0, ',', '.'); ?>
            </h3>

            <p>Ongkos kirim akan dihitung pada tahap pemesanan.</p>

            <a class="detail-btn" href="index.php#produk">
                Lanjut Belanja
            </a>
            <a class="detail-btn" href="checkout.php">
    Lanjut ke Checkout
</a>
        </div>

    <?php endif; ?>

</section>

<footer>
    <h3>Amai Cookies</h3>
    <p>Lost in a World of Sweetness.</p>
    <p>&copy; 2026 Amai Cookies. All Rights Reserved.</p>
</footer>

</body>
</html>