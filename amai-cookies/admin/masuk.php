<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $query = $pdo->prepare(
        "SELECT * FROM admin WHERE username = ? LIMIT 1"
    );
    $query->execute([$username]);
    $admin = $query->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nama'] = $admin['nama'];

        header("Location: index.php");
        exit;
    } else {
        $pesan = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Amai Cookies</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff8f2;
            font-family: Arial, sans-serif;
            color: #654638;
            padding: 20px;
        }

        .login-box {
            width: 100%;
            max-width: 390px;
            background: white;
            padding: 35px 28px;
            border-radius: 22px;
            box-shadow: 0 8px 25px rgba(100, 65, 45, 0.10);
        }

        h2 {
            text-align: center;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #a58b7c;
            margin-bottom: 28px;
        }

        label {
            display: block;
            margin: 15px 0 7px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ead9ce;
            border-radius: 10px;
            font-size: 15px;
        }

        button {
            width: 100%;
            margin-top: 23px;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: #a87562;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .pesan {
            color: #c0392b;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>🍪 Amai Cookies</h2>
    <p class="subtitle">Selamat datang, Admin!</p>

    <?php if ($pesan): ?>
        <p class="pesan"><?= htmlspecialchars($pesan) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Masuk ke Dashboard</button>
    </form>
</div>

</body>
</html>