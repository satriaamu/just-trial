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
        /* CSS LENGKAP TETAP SAMA SEPERTI FILE ASLI */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        #sidebar { width: 250px; height: 100vh; background-color: #2c3e50; color: white; position: fixed; left: 0; top: 0; }
        /* ... Sisa CSS ... */
        .dashboard-cards { display: flex; flex-wrap: wrap; gap: 25px; }
        .card { background: white; border-radius: 10px; padding: 25px; width: 100%; max-width: 320px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.08); text-decoration: none; color: #333; }
        .card i { font-size: 3rem; margin-bottom: 20px; color: #2c3e50; }
        #content { margin-left: 250px; transition: margin-left 0.3s ease; }
    </style>
</head>
<body>
    <div id="sidebar">
        <ul>
            <li><a href="admin-dashboard.php"><i class="fas fa-home"></i> <span class="menu-text">Dasbor</span></a></li>
            <li><a href="admin-billing.php"><i class="fas fa-file-invoice-dollar"></i> <span class="menu-text">Buat Billing</span></a></li>
            <li><a href="history.php"><i class="fas fa-comments-dollar"></i> <span class="menu-text">Penawaran & Chat</span></a></li>
            <li><a href="admin-katalog.php"><i class="fas fa-clipboard-list"></i> <span class="menu-text">Kelola Katalog</span></a></li>
            <li><a href="admin-pesan.php"><i class="fas fa-envelope"></i> <span class="menu-text">Pesan Kontak</span></a></li>
            <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> <span class="menu-text">Logout</span></a></li>
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
                        <p>Kirim tagihan langsung ke pembeli terdaftar untuk transaksi di luar sistem negosiasi.</p>
                    </a>
                    <a href="history.php" class="card">
                        <i class="fas fa-comments-dollar"></i>
                        <h3>Cek Penawaran & Chat</h3>
                        <p>Lihat semua penawaran masuk dari calon pembeli dan kelola percakapan.</p>
                    </a>
                    <a href="admin-katalog.php" class="card">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>Kelola Katalog</h3>
                        <p>Tambah, perbarui, atau hapus item dari katalog produk rumah panggung.</p>
                    </a>
                     <a href="admin-pesan.php" class="card">
                        <i class="fas fa-envelope"></i>
                        <h3>Pesan Kontak</h3>
                        <p>Lihat dan balas pesan umum yang dikirim oleh pengunjung melalui halaman kontak.</p>
                    </a>
                </div>
            </div>
        </main>
    </div>
    </body>
</html>