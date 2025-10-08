<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Admin</title>
    <link rel="icon" href="image/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        #sidebar { width: 250px; height: 100vh; background-color: #2c3e50; color: white; position: fixed; left: 0; top: 0; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; display: flex; }
        #sidebar { width: 250px; background-color: #2c3e50; color: white; height: 100vh; position: fixed; }
        #sidebar .logo { text-align: center; padding: 20px 0; }
        #sidebar .logo img { width: 100px; }
        #sidebar ul { list-style: none; padding: 0; }
        #sidebar ul li a { color: white; text-decoration: none; padding: 15px 20px; display: block; }
        #sidebar ul li a:hover { background-color: #212f3d; }
        #content { margin-left: 250px; padding: 20px; width: calc(100% - 250px); }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 700px; margin: auto; }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 20px; }
        .dashboard-cards { display: flex; flex-wrap: wrap; gap: 25px; justify-content: center; }
        .card { background: white; border-radius: 10px; padding: 25px; width: 100%; max-width: 320px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.08); text-decoration: none; color: #333; transition: transform 0.2s, box-shadow 0.2s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0,0,0,0.12); }
        .card i { font-size: 3rem; margin-bottom: 20px; color: #2c3e50; }
        #content { margin-left: 250px; transition: margin-left 0.3s ease; }
    </style>
</head>
<body>
    <div id="sidebar">
        <div class="logo"><img src="image/logo.png" alt="Logo"></div>
        <ul>
            <li><a href="admin-dashboard.php"><i class="fas fa-home"></i> Dasbor</a></li>
            <li><a href="admin-billing.php"><i class="fas fa-file-invoice-dollar"></i> Buat Billing</a></li>
            <li><a href="history.php"><i class="fas fa-comments-dollar"></i> Penawaran & Chat</a></li>
            <li><a href="admin-katalog.php"><i class="fas fa-clipboard-list"></i> Kelola Katalog</a></li>
            <li><a href="admin-custom.php"><i class="fas fa-cogs"></i> Kelola Kustomisasi</a></li>
            <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div id="content">
        <header> <h1>Dasbor Admin</h1> </header>
        <main class="main-content">
            <div class="content-container" style="padding: 40px;">
                <div class="dashboard-cards">
                    <a href="admin-billing.php" class="card">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <h3>Buat Billing Manual</h3>
                        <p>Kirim tagihan langsung ke pembeli terdaftar.</p>
                    </a>
                    <a href="history.php" class="card">
                        <i class="fas fa-comments-dollar"></i>
                        <h3>Cek Penawaran & Chat</h3>
                        <p>Lihat semua penawaran masuk dan kelola percakapan.</p>
                    </a>
                    <a href="admin-katalog.php" class="card">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>Kelola Katalog</h3>
                        <p>Tambah, perbarui, atau hapus item dari katalog produk.</p>
                    </a>
                    <a href="admin-custom.php" class="card">
                        <i class="fas fa-cogs"></i>
                        <h3>Kelola Kustomisasi</h3>
                        <p>Atur jenis dan opsi kustomisasi untuk produk.</p>
                    </a>
                     <a href="admin-pesan.php" class="card">
                        <i class="fas fa-envelope"></i>
                        <h3>Pesan Kontak</h3>
                        <p>Lihat dan balas pesan dari pengunjung website.</p>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>