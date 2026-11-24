<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: masuk.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->execute([$id]);
$produk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produk) {
    exit("Produk tidak ditemukan.");
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

    $nama_foto = $produk['foto'];
    $foto_baru = false;

    if (
        $nama === '' || $deskripsi === '' || $harga <= 0 ||
        $berat === '' || $bahan === '' ||
        $isi_paket === '' || $stok < 0
    ) {
        $pesan = "Mohon lengkapi semua data dengan benar.";
    } else {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $pesan = "Foto gagal diunggah.";
            } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                $pesan = "Ukuran foto maksimal 2 MB.";
            } else {
                $tmp = $_FILES['foto']['tmp_name'];
                $mime = mime_content_type($tmp);
                $jenis = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                if (!isset($jenis[$mime]) || getimagesize($tmp) === false) {
                    $pesan = "Foto harus JPG, PNG, atau WEBP.";
                } else {
                    $nama_foto = 'produk-' . bin2hex(random_bytes(5)) . '.' . $jenis[$mime];

                    if (move_uploaded_file($tmp, '../assets/images/' . $nama_foto)) {
                        $foto_baru = true;
                    } else {
                        $pesan = "Foto gagal disimpan.";
                    }
                }
            }
        }

        if ($pesan === '') {
            try {
                $stmt = $pdo->prepare("
                    UPDATE produk SET
                    nama_produk = ?, deskripsi = ?, harga = ?,
                    berat = ?, bahan = ?, isi_paket = ?,
                    stok = ?, foto = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $nama, $deskripsi, $harga, $berat,
                    $bahan, $isi_paket, $stok, $nama_foto, $id
                ]);

                if ($foto_baru && $produk['foto'] !== '') {
                    $foto_lama = '../assets/images/' . basename($produk['foto']);
                    if (is_file($foto_lama)) {
                        unlink($foto_lama);
                    }
                }

                header("Location: produk.php");
                exit;

            } catch (PDOException $e) {
                if ($foto_baru) {
                    unlink('../assets/images/' . $nama_foto);
                }
                $pesan = "Produk gagal diperbarui.";
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
    <title>Edit Produk - Amai Cookies</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff8f2;
            color: #805747;
            padding: 25px;
        }

        .container {
            max-width: 650px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 18px;
        }

        h1 {
            text-align: center;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #e5cfc3;
            border-radius: 9px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 90px;
        }

        button, .kembali {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 18px;
            border: none;
            border-radius: 20px;
            background: #a87562;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .pesan {
            background: #ffe6e6;
            padding: 12px;
            border-radius: 8px;
        }

        img {
            width: 130px;
            border-radius: 12px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
<div class="container">
    <h1>✏️ Edit Produk</h1>
    <p>Ubah informasi produk Amai Cookies di bawah ini.</p>

    <?php if ($pesan !== ''): ?>
        <p class="pesan"><?php echo htmlspecialchars($pesan); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Nama Produk</label>
        <input type="text" name="nama_produk"
            value="<?php echo htmlspecialchars($produk['nama_produk']); ?>" required>

        <label>Deskripsi</label>
        <textarea name="deskripsi" required><?php echo htmlspecialchars($produk['deskripsi']); ?></textarea>

        <label>Harga (Rp)</label>
        <input type="number" name="harga"
            value="<?php echo (int) $produk['harga']; ?>" min="1" required>

        <label>Berat</label>
        <input type="text" name="berat"
            value="<?php echo htmlspecialchars($produk['berat']); ?>" required>

        <label>Bahan</label>
        <textarea name="bahan" required><?php echo htmlspecialchars($produk['bahan']); ?></textarea>

        <label>Isi Paket</label>
        <input type="text" name="isi_paket"
            value="<?php echo htmlspecialchars($produk['isi_paket']); ?>" required>

        <label>Stok</label>
        <input type="number" name="stok"
            value="<?php echo (int) $produk['stok']; ?>" min="0" required>

        <label>Foto Produk Saat Ini</label>
        <img src="../assets/images/<?php echo htmlspecialchars($produk['foto']); ?>" alt="Foto produk">

        <label>Ganti Foto (Opsional)</label>
        <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">

        <button type="submit">Simpan Perubahan 💗</button>
        <a class="kembali" href="produk.php">Kembali</a>

    </form>
</div>
</body>
</html>