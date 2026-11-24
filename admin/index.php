<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: masuk.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - Amai Cookies</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff8f2;
            color: #654638;
        }

        header {
            background: #a87562;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .container {
            padding: 30px;
            text-align: center;
        }

        .kartu {
            background: white;
            padding: 25px;
            margin: 20px auto;
            max-width: 500px;
            border-radius: 18px;
        }

        a {
            display: block;
            background: #a87562;
            color: white;
            padding: 13px;
            margin: 12px auto;
            max-width: 250px;
            border-radius: 10px;
            text-decoration: none;
        }
    </style>
</head>

<body>

<header>
    <h1>🍪 Amai Cookies</h1>
    <p>Dashboard Admin</p>
</header>

<div class="container">

    <div class="kartu">

        <h2>Selamat Datang, Admin! 💗</h2>

        <p>
            Halo,
            <?php echo htmlspecialchars($_SESSION['admin_nama']); ?>!
        </p>

        <p>Selamat mengelola toko Amai Cookies.</p>
<a href="produk.php">Kelola Produk 🍪</a>
<a href="pesanan.php">Kelola Pesanan 📦</a>
        <a href="../index.php">Lihat Website Toko</a>
<a href="logout.php"
   onclick="return confirm('Yakin ingin keluar dari akun admin?');">
   🚪 Logout
</a>
    </div>

</div>

</body>
</html>