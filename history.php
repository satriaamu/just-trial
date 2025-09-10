<?php
session_start();
// Memeriksa jika ada sesi admin ATAU sesi user yang aktif
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    // Jika tidak ada sesi sama sekali, arahkan ke halaman login utama
    header('Location: login.php');
    exit();
}
// Tentukan peran pengguna berdasarkan sesi yang ada
$is_admin = isset($_SESSION['admin_id']);
$user_id = $_SESSION['user_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_admin ? 'Daftar Penawaran Masuk' : 'Riwayat Transaksi'; ?></title>
    <link rel="icon" href="image/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 100px;
            background-color: #2c3e50;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        header .logo img {
            height: 100px;
        }
        header nav ul {
            list-style: none;
            display: flex;
            gap: 10px;
        }
        .button-18 {
            align-items: center;
            background-color: #0A66C2;
            border: 0;
            border-radius: 100px;
            color: #ffffff;
            cursor: pointer;
            display: inline-flex;
            font-size: 14px;
            font-weight: 600;
            justify-content: center;
            line-height: 20px;
            min-height: 40px;
            padding: 0 30px;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .button-18:hover {
            background-color: #16437E;
        }
        main {
            padding: 40px 20px;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        p.subtitle {
            text-align: center;
            margin-bottom: 25px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border-bottom: 1px solid #ddd;
            padding: 15px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        td a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.2s;
        }
        td a:hover {
            color: #0056b3;
        }
        .status {
            padding: 6px 12px;
            border-radius: 15px;
            color: white;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
            text-align: center;
            min-width: 120px;
            display: inline-block;
        }
        .status-negotiating { background-color: #ffc107; color: #333; }
        .status-deal, .status-awaiting_payment { background-color: #17a2b8; }
        .status-paid, .status-completed { background-color: #28a745; }
        .status-cancelled { background-color: #dc3545; }

        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
                flex-direction: column;
            }
            main {
                padding: 20px 10px;
            }
            th, td {
                padding: 10px;
                font-size: 14px;
            }
        }
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
            <h1><?php echo $is_admin ? 'Daftar Penawaran Masuk' : 'Riwayat Transaksi Anda'; ?></h1>
            <p class="subtitle"><?php echo $is_admin ? 'Klik "Buka Chat" untuk merespons penawaran dari customer.' : 'Di sini Anda dapat melihat status semua penawaran dan pembelian Anda.'; ?></p>
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
                <tbody id="historyTableBody">
                    <tr>
                        <td colspan="<?php echo $is_admin ? '5' : '4'; ?>" style="text-align:center; padding: 20px;">
                            <i class="fas fa-spinner fa-spin"></i> Memuat data...
                        </td>
                    </tr>
                </tbody>
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
            try {
                const response = await fetch('api_transaction.php?action=get_history');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                const tableBody = document.getElementById('historyTableBody');
                tableBody.innerHTML = ''; // Kosongkan tabel sebelum diisi

                if (data.status === 'success' && data.history.length > 0) {
                    data.history.forEach(trx => {
                        const row = document.createElement('tr');
                        
                        let adminColumn = isAdmin ? `<td>${trx.username}</td>` : '';
                        let linkHref = `negosiasi.php?transaction_id=${trx.id}`;
                        
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
                    const colSpan = isAdmin ? 5 : 4;
                    tableBody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding: 20px;">Belum ada riwayat transaksi.</td></tr>`;
                }
            } catch (error) {
                console.error("Gagal mengambil riwayat:", error);
                const tableBody = document.getElementById('historyTableBody');
                const colSpan = isAdmin ? 5 : 4;
                tableBody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding: 20px; color: red;">Gagal memuat data. Silakan coba lagi nanti.</td></tr>`;
            }
        }

        // Panggil fungsi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', fetchHistory);
    </script>
</body>
</html>