<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: masuk.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM produk ORDER BY id DESC");
$produk = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Amai Cookies</title>

    <style>
        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #fff8f2;
            color: #654638;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            color: #805747;
        }

        .tombol {
            display: inline-block;
            padding: 12px 18px;
            margin: 10px 5px 20px 0;
            background: #a87562;
            color: white;
            text-decoration: none;
            border-radius: 10px;
        }

        .produk {
            background: white;
            padding: 18px;
            margin-bottom: 15px;
            border-radius: 15px;
            border: 1px solid #f2ded3;
        }

        .produk img {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 12px;
        }

        .produk p {
            line-height: 1.6;
        }

        .kosong {
            background: white;
            padding: 20px;
            border-radius: 12px;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>🍪 Kelola Produk Amai Cookies</h1>
    <p>Daftar produk yang tersedia di toko kamu.</p>

    <a class="tombol" href="index.php">← Kembali ke Dashboard</a>

    <a class="tombol" href="tambah-produk.php">+ Tambah Produk</a>

    <?php if (count($produk) > 0): ?>

        <?php foreach ($produk as $item): ?>

            <div class="produk">

                <img
                    src="../assets/images/<?php echo htmlspecialchars($item['foto']); ?>"
                    alt="<?php echo htmlspecialchars($item['nama_produk']); ?>"
                >

                <h3>
                    <?php echo htmlspecialchars($item['nama_produk']); ?>
                </h3>

                <p>
                    Harga: Rp<?php echo number_format($item['harga'], 0, ',', '.'); ?>
                </p>

                <p>
                    Stok: <?php echo (int) $item['stok']; ?>
                </p>

                <p>
                    Status: <?php echo htmlspecialchars($item['status']); ?>
                </p>
                <a class="tombol"
   href="edit-produk.php?id=<?php echo (int) $item['id']; ?>">
   ✏️ Edit Produk
</a>
<form action="hapus-produk.php" method="POST"
      onsubmit="return confirm('Yakin ingin menonaktifkan produk ini?');">

    <input type="hidden" name="id"
           value="<?php echo (int) $item['id']; ?>">

    <button type="submit"
            style="background:#d98b8b; color:white; border:none;
            padding:10px 15px; border-radius:20px; cursor:pointer;">
        🗑️ Hapus Produk
    </button>

</form>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="kosong">
            Belum ada produk.
        </div>

    <?php endif; ?>

</div>

</body>
</html>