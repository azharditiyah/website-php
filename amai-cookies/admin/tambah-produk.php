<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: masuk.php");
    exit;
}

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama_produk'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = (int) ($_POST['harga'] ?? 0);
    $berat = trim($_POST['berat'] ?? '');
    $bahan = trim($_POST['bahan'] ?? '');
    $isi_paket = trim($_POST['isi_paket'] ?? '');
    $stok = (int) ($_POST['stok'] ?? 0);

    if (
        $nama === '' || $deskripsi === '' ||
        $harga <= 0 || $berat === '' ||
        $bahan === '' || $isi_paket === '' ||
        $stok < 0
    ) {
        $pesan = "Mohon lengkapi semua data dengan benar.";
    } elseif (
        !isset($_FILES['foto']) ||
        $_FILES['foto']['error'] !== UPLOAD_ERR_OK
    ) {
        $pesan = "Foto produk wajib dipilih.";
    } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
        $pesan = "Ukuran foto maksimal 2 MB.";
    } else {

        $tmp = $_FILES['foto']['tmp_name'];
        $mime = mime_content_type($tmp);

        $jenis_foto = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($jenis_foto[$mime]) || getimagesize($tmp) === false) {
            $pesan = "Foto harus berformat JPG, PNG, atau WEBP.";
        } else {

            $folder = '../assets/images/';

            $nama_foto = 'produk-' . bin2hex(random_bytes(5))
                . '.' . $jenis_foto[$mime];

            if (move_uploaded_file($tmp, $folder . $nama_foto)) {

                $slug = strtolower(trim($nama));
                $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
                $slug = trim($slug, '-') . '-' . bin2hex(random_bytes(3));

                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO produk
                        (nama_produk, slug, deskripsi, harga, berat,
                        bahan, isi_paket, stok, foto, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')
                    ");

                    $stmt->execute([
                        $nama, $slug, $deskripsi, $harga,
                        $berat, $bahan, $isi_paket, $stok, $nama_foto
                    ]);

                    header("Location: produk.php");
                    exit;

                } catch (PDOException $e) {
                    unlink($folder . $nama_foto);
                    $pesan = "Produk gagal disimpan. Periksa kembali data produk.";
                }

            } else {
                $pesan = "Foto gagal diunggah.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Amai Cookies</title>

    <style>
        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #fff8f2;
            color: #654638;
        }

        .container {
            max-width: 650px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
        }

        h2 {
            color: #805747;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 17px;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ead9ce;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 14px;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button, .kembali {
            display: inline-block;
            margin-top: 22px;
            padding: 13px 20px;
            border: none;
            border-radius: 10px;
            background: #a87562;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .pesan {
            color: #c0392b;
            background: #fff0e8;
            padding: 12px;
            border-radius: 10px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>🍪 Tambah Produk Baru</h2>
    <p>Masukkan informasi produk Amai Cookies.</p>

    <?php if ($pesan): ?>
        <p class="pesan">
            <?php echo htmlspecialchars($pesan); ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Nama Produk</label>
        <input type="text" name="nama_produk" required>

        <label>Deskripsi Produk</label>
        <textarea name="deskripsi" required></textarea>

        <label>Harga (Rp)</label>
        <input type="number" name="harga" min="1" required>

        <label>Berat</label>
        <input type="text" name="berat" placeholder="Contoh: 80 gram" required>

        <label>Bahan</label>
        <textarea name="bahan" required></textarea>

        <label>Isi Paket</label>
        <input type="text" name="isi_paket" placeholder="Contoh: 1 pcs cookies" required>

        <label>Stok Produk</label>
        <input type="number" name="stok" min="0" required>

        <label>Foto Produk</label>
        <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" required>

        <button type="submit">Simpan Produk 💗</button>

    </form>

    <a href="produk.php" class="kembali">← Kembali</a>

</div>

</body>
</html>