<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: masuk.php");
    exit;
}

// Mengambil semua pesanan dan data pelanggan
$stmt = $pdo->query("
    SELECT pesanan.*, 
           pelanggan.nama,
           pelanggan.no_whatsapp,
           pelanggan.alamat,
           pelanggan.kota
    FROM pesanan
    JOIN pelanggan ON pesanan.pelanggan_id = pelanggan.id
    ORDER BY pesanan.dibuat_pada DESC
");

$pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Amai Cookies</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff8f2;
            color: #805747;
            padding: 20px;
        }

        .container {
            max-width: 950px;
            margin: auto;
        }

        h1 {
            text-align: center;
        }

        .pesanan {
            background: white;
            padding: 22px;
            margin: 20px 0;
            border-radius: 16px;
            border: 1px solid #f2ded3;
            box-shadow: 0 4px 12px rgba(150,105,85,0.08);
        }

        .pesanan h2 {
            color: #a87562;
            font-size: 19px;
        }

        .pesanan p {
            line-height: 1.6;
        }

        .tombol {
            display: inline-block;
            background: #a87562;
            color: white;
            padding: 11px 17px;
            border-radius: 20px;
            text-decoration: none;
            margin: 10px 0;
        }

        .kosong {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
        }

        hr {
            border: 0;
            border-top: 1px solid #f2ded3;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>📦 Pesanan Amai Cookies</h1>
    <p style="text-align:center;">
        Kelola pesanan pelanggan toko Amai Cookies 💗
    </p>

    <a href="index.php" class="tombol">
        ← Kembali ke Dashboard
    </a>

    <?php if (count($pesanan) > 0): ?>

        <?php foreach ($pesanan as $item): ?>

            <div class="pesanan">

                <h2>
                    Pesanan #<?php echo htmlspecialchars($item['kode_pesanan']); ?>
                </h2>

                <p>
                    <strong>Nama Pelanggan:</strong>
                    <?php echo htmlspecialchars($item['nama']); ?>
                </p>

                <p>
                    <strong>WhatsApp:</strong>
                    <?php echo htmlspecialchars($item['no_whatsapp'] ?? '-'); ?>
                </p>

                <p>
                    <strong>Alamat:</strong>
                    <?php echo htmlspecialchars($item['alamat'] ?? '-'); ?>,
                    <?php echo htmlspecialchars($item['kota'] ?? '-'); ?>
                </p>

                <hr>

                <strong>Detail Produk:</strong>

                <?php
                $stmt_detail = $pdo->prepare("
                    SELECT detail_pesanan.*, produk.nama_produk
                    FROM detail_pesanan
                    JOIN produk ON detail_pesanan.produk_id = produk.id
                    WHERE detail_pesanan.pesanan_id = ?
                ");

                $stmt_detail->execute([$item['id']]);
                $detail = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php foreach ($detail as $produk): ?>
                    <p>
                        <?php echo htmlspecialchars($produk['nama_produk']); ?>
                        × <?php echo (int) $produk['jumlah']; ?>
                        = Rp<?php echo number_format($produk['subtotal'], 0, ',', '.'); ?>
                    </p>
                <?php endforeach; ?>

                <hr>

                <p>
                    <strong>Total Pembayaran:</strong>
                    Rp<?php echo number_format($item['total_harga'], 0, ',', '.'); ?>
                </p>

                <p>
                    <strong>Metode Pembayaran:</strong>
                    <?php echo htmlspecialchars($item['metode_pembayaran']); ?>
                </p>

                <p>
                    <strong>Status Pembayaran:</strong>
                    <?php echo htmlspecialchars($item['status_pembayaran']); ?>
                </p>

                <p>
    <strong>Status Pesanan:</strong>
    <?php echo htmlspecialchars($item['status_pesanan']); ?>
</p>

<form action="ubah-status.php" method="POST">

    <input type="hidden" name="id"
           value="<?php echo (int) $item['id']; ?>">

    <label><strong>Ubah Status Pesanan:</strong></label>

    <select name="status_pesanan" required
            style="padding:10px; border-radius:10px; margin-top:8px;">

        <option value="di proses"
            <?php echo $item['status_pesanan'] === 'di proses' ? 'selected' : ''; ?>>
            Di Proses
        </option>

        <option value="dikirim"
            <?php echo $item['status_pesanan'] === 'dikirim' ? 'selected' : ''; ?>>
            Dikirim
        </option>

        <option value="selesai"
            <?php echo $item['status_pesanan'] === 'selesai' ? 'selected' : ''; ?>>
            Selesai
        </option>

        <option value="dibatalkan"
            <?php echo $item['status_pesanan'] === 'dibatalkan' ? 'selected' : ''; ?>>
            Dibatalkan
        </option>

    </select>

    <button type="submit"
            style="padding:10px 15px; background:#a87562;
            color:white; border:none; border-radius:15px; cursor:pointer;">
        Simpan Status 💗
    </button>

</form>

                <p>
                    <strong>Tanggal Pesanan:</strong>
                    <?php echo htmlspecialchars($item['dibuat_pada']); ?>
                </p>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="kosong">
            <h3>Belum Ada Pesanan 🍪</h3>
            <p>Pesanan pelanggan akan muncul di halaman ini.</p>
        </div>

    <?php endif; ?>

</div>

</body>
</html>