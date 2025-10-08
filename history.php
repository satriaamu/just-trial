<?php
session_start();
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$is_admin = isset($_SESSION['admin_id']);
$user_id = $_SESSION['user_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_admin ? 'Daftar Transaksi' : 'Riwayat Transaksi'; ?></title>
    <link rel="icon" href="image/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Roboto', sans-serif; background-color: #f4f4f4; color: #333; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 0 100px; background-color: #2c3e50; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        header .logo img { height: 100px; }
        header nav ul { list-style: none; display: flex; gap: 10px; }
        .button-18 { align-items: center; background-color: #0A66C2; border: 0; border-radius: 100px; color: #ffffff; cursor: pointer; display: inline-flex; font-size: 14px; font-weight: 600; justify-content: center; line-height: 20px; min-height: 40px; padding: 0 30px; text-decoration: none; }
        main { padding: 40px 20px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 10px; color: #2c3e50; }
        p.subtitle { text-align: center; margin-bottom: 25px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #ddd; padding: 15px; text-align: left; vertical-align: middle; }
        th { background-color: #f8f9fa; color: #333; font-weight: 600; }
        tr:hover { background-color: #f1f1f1; }
        td a { color: #007bff; text-decoration: none; font-weight: bold; }
        .status { padding: 6px 12px; border-radius: 15px; color: white; font-size: 12px; text-transform: capitalize; text-align: center; display: inline-block; }
        .status-negotiating { background-color: #ffc107; color: #333; }
        .status-deal, .status-awaiting_payment { background-color: #17a2b8; }
        .status-paid, .status-completed { background-color: #28a745; }
        .status-cancelled { background-color: #dc3545; }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="<?php echo $is_admin ? 'admin-dashboard.php' : 'index.php'; ?>">
                <img src="image/logo-showcase.png" alt="Logo">
            </a>
        </div>
        <nav>
            <ul>
                <?php if($is_admin): ?>
                    <li><a href="admin-dashboard.php" class="button-18"><i class="fas fa-arrow-left"></i>&nbsp; Kembali ke Dasbor</a></li>
                <?php else: ?>
                    <li><a href="katalog.php" class="button-18"><i class="fas fa-store"></i>&nbsp; Lanjut Belanja</a></li>
                    <li><a href="logout.php" class="button-18"><i class="fas fa-sign-out-alt"></i>&nbsp; Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
            <h1><?php echo $is_admin ? 'Daftar Transaksi' : 'Riwayat Transaksi Anda'; ?></h1>
            <p class="subtitle"><?php echo $is_admin ? 'Klik "Buka Chat" untuk melihat riwayat percakapan dengan pengguna.' : 'Di sini Anda dapat melihat status semua penawaran dan pembelian Anda.'; ?></p>
            <table>
                <thead>
                    <tr>
                        <?php if($is_admin) echo '<th>Username Pembeli</th>'; ?>
                        <th>Produk</th>
                        <th>Total Tagihan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody"></tbody>
            </table>
        </div>
    </main>
    <script>
        const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
        function formatRupiah(angka) {
            if (angka === null || isNaN(angka)) return '-';
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
        }
        async function fetchHistory() {
            const response = await fetch('api_transaction.php?action=get_history');
            const data = await response.json();
            const tableBody = document.getElementById('historyTableBody');
            tableBody.innerHTML = ''; 
            if (data.status === 'success' && data.history.length > 0) {
                data.history.forEach(trx => {
                    const row = document.createElement('tr');
                    let adminColumn = isAdmin ? `<td>${trx.username}</td>` : '';
                    
                    // --- PERUBAHAN DI SINI: Tautan untuk admin diubah agar sesuai dengan alur chat admin ---
                    let linkHref = isAdmin 
                        ? `admin-chat.php?transaction_id=${trx.id}` 
                        : `negosiasi.php?transaction_id=${trx.id}`;
                    
                    row.innerHTML = `
                        ${adminColumn}
                        <td>${trx.product_name}</td>
                        <td>${formatRupiah(trx.total_bill)}</td>
                        <td><span class="status status-${trx.status}">${trx.status.replace('_', ' ')}</span></td>
                        <td><a href="${linkHref}">Buka Chat</a></td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Belum ada riwayat transaksi.</td></tr>`;
            }
        }
        document.addEventListener('DOMContentLoaded', fetchHistory);
    </script>
</body>
</html>