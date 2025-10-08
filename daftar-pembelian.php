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
    <title><?php echo $is_admin ? 'Daftar Transaksi' : 'Riwayat Pembelian'; ?></title>
    <link rel="icon" href="image/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f9; color: #333; }
        
        /* --- START: CSS untuk Header --- */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0px 100px;
            background-color: #2c3e50;
        }
        header .logo img { height: 100px; }
        header nav ul { list-style: none; display: flex; gap: 10px; }
        header nav ul li { padding: 0px 6px; display: flex; justify-content: center; align-items: center; }
        .button-18 { align-items: center; background-color: #0A66C2; border: 0; border-radius: 100px; color: #ffffff; cursor: pointer; display: inline-flex; font-size: 14px; font-weight: 600; justify-content: center; line-height: 20px; min-height: 40px; padding: 0 30px; text-decoration: none; }
        .burger-menu { display: none; flex-direction: column; gap: 5px; cursor: pointer; }
        .burger-menu span { width: 25px; height: 3px; background-color: white; transition: all 0.3s ease; }
        /* --- END: CSS untuk Header --- */

        main { padding: 30px 15px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h1 { margin-bottom: 20px; color: #2c3e50; font-weight: 600; }
        
        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            align-items: end;
        }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-size: 13px; margin-bottom: 5px; color: #555; font-weight: 500; }
        .filter-group input, .filter-group select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
        }
        .filter-group input[type="text"] { width: 100%; }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; vertical-align: middle; }
        th { background-color: #f8f9fa; color: #444; font-weight: 600; font-size: 14px; }
        tr { border-bottom: 1px solid #f0f0f0; }
        tr:last-child { border-bottom: none; }
        td { font-size: 14px; }
        
        .status { padding: 5px 12px; border-radius: 15px; color: white; font-size: 11px; text-transform: capitalize; text-align: center; display: inline-block; font-weight: 500; }
        .status-negotiating { background-color: #ffc107; color: #333; }
        .status-deal, .status-awaiting_payment { background-color: #17a2b8; }
        .status-paid, .status-completed { background-color: #28a745; }
        .status-cancelled { background-color: #dc3545; }

        .btn-action {
            padding: 8px 12px;
            font-size: 13px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            cursor: pointer;
        }
        .btn-detail { background-color: #007bff; }
        .btn-print { background-color: #6c757d; }
        .no-results { text-align: center; padding: 40px; color: #777; }
        
        @media (max-width: 768px) {
            header { flex-wrap: wrap; padding: 15px 20px; }
            header .logo { width: 100%; text-align: center; margin-bottom: 10px; }
            nav { width: 100%; }
            header nav ul { flex-direction: column; background-color: #2c3e50; width: 100%; max-height: 0; overflow: hidden; transition: max-height 0.3s ease-in-out; }
            header nav ul.active { max-height: 500px; }
            .burger-menu { display: flex; margin: 0 auto; }
            .burger-menu.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
            .burger-menu.active span:nth-child(2) { opacity: 0; }
            .burger-menu.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }
        }
    </style>
</head>
<body>

    <header>
        <div class="logo">
            <a href="<?php echo $is_admin ? 'admin-dashboard.php' : 'index.php'; ?>">
                <img src="image/logo-showcase.png" alt="Showcase Logo">
            </a>
        </div>
        <nav>
            <ul>
                <?php if ($is_admin): ?>
                    <li><a href="admin-dashboard.php" class="button-18"><i class="fas fa-arrow-left"></i>&nbsp; Kembali ke Dasbor</a></li>
                <?php else: ?>
                    <li><a href="index.php" class="button-18"><i class="fas fa-home"></i>&nbsp; Beranda</a></li>
                    <li><a href="katalog.php" class="button-18"><i class="fas fa-store"></i>&nbsp; Lanjut Belanja</a></li>
                    <li><a href="logout.php" class="button-18"><i class="fas fa-sign-out-alt"></i>&nbsp; Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="burger-menu">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>

    <main>
        <div class="container">
            <h1><?php echo $is_admin ? 'Daftar Transaksi' : 'Riwayat Pembelian Anda'; ?></h1>
            
            <div class="filters">
                <div class="filter-group" style="grid-column: 1 / -1; @media (min-width: 768px) { grid-column: span 2; }">
                    <label for="searchInput">Cari (Produk, No. Transaksi, Status)</label>
                    <input type="text" id="searchInput" placeholder="Ketik untuk mencari...">
                </div>
                <div class="filter-group">
                    <label for="sortOrder">Urutkan Berdasarkan</label>
                    <select id="sortOrder">
                        <option value="newest">Tanggal Terbaru</option>
                        <option value="oldest">Tanggal Terlama</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="startDate">Dari Tanggal</label>
                    <input type="date" id="startDate">
                </div>
                <div class="filter-group">
                    <label for="endDate">Hingga Tanggal</label>
                    <input type="date" id="endDate">
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <?php if($is_admin) echo '<th>Username</th>'; ?>
                            <th>No. Transaksi</th>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody"></tbody>
                </table>
            </div>
            <div id="no-results" class="no-results" style="display: none;">
                <p>Tidak ada data yang cocok dengan filter Anda.</p>
            </div>
        </div>
    </main>
    
    <script>
        const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
        let allTransactions = [];

        // --- Helper Functions ---
        const formatRupiah = (angka) => {
            if (angka === null || isNaN(angka)) return '-';
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
        };

        const formatDate = (dateString) => {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        };

        // --- Render Function ---
        const renderTable = (transactions) => {
            const tableBody = document.getElementById('historyTableBody');
            const noResultsDiv = document.getElementById('no-results');
            tableBody.innerHTML = '';

            if (transactions.length === 0) {
                noResultsDiv.style.display = 'block';
                return;
            }
            noResultsDiv.style.display = 'none';

            transactions.forEach(trx => {
                const row = document.createElement('tr');
                let adminColumn = isAdmin ? `<td>${trx.username || '-'}</td>` : '';
                
                let actionButton;
                const detailUrl = isAdmin ? `admin-chat.php?transaction_id=${trx.id}` : `negosiasi.php?transaction_id=${trx.id}`;
                
                if (trx.status === 'paid' || trx.status === 'completed') {
                    actionButton = `<a href="cetak-bukti.php?id=${trx.id}" target="_blank" class="btn-action btn-print"><i class="fas fa-print"></i> Bukti</a>`;
                } else if (trx.status === 'awaiting_payment') {
                    actionButton = `<a href="payment.php?transaction_id=${trx.id}" class="btn-action btn-detail"><i class="fas fa-money-bill"></i> Bayar</a>`;
                } else {
                    actionButton = `<a href="${detailUrl}" class="btn-action btn-detail"><i class="fas fa-eye"></i> Detail</a>`;
                }

                row.innerHTML = `
                    ${adminColumn}
                    <td>${trx.billing_number || `TRX-${trx.id}`}</td>
                    <td>${formatDate(trx.created_at)}</td>
                    <td>${trx.product_name}</td>
                    <td>${formatRupiah(trx.total_bill)}</td>
                    <td><span class="status status-${trx.status}">${(trx.status || 'N/A').replace('_', ' ')}</span></td>
                    <td>${actionButton}</td>
                `;
                tableBody.appendChild(row);
            });
        };

        // --- Filter and Sort Logic ---
        const applyFilters = () => {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const sortOrder = document.getElementById('sortOrder').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            let filtered = allTransactions.filter(trx => {
                const trxDate = trx.created_at.split(' ')[0];
                const isAfterStartDate = !startDate || trxDate >= startDate;
                const isBeforeEndDate = !endDate || trxDate <= endDate;
                
                const searchMatch = 
                    (trx.product_name && trx.product_name.toLowerCase().includes(searchTerm)) ||
                    (trx.billing_number && trx.billing_number.toLowerCase().includes(searchTerm)) ||
                    (trx.status && trx.status.replace('_', ' ').toLowerCase().includes(searchTerm)) ||
                    (isAdmin && trx.username && trx.username.toLowerCase().includes(searchTerm));
                
                return isAfterStartDate && isBeforeEndDate && searchMatch;
            });

            filtered.sort((a, b) => {
                const dateA = new Date(a.created_at);
                const dateB = new Date(b.created_at);
                return sortOrder === 'newest' ? dateB - dateA : dateA - dateB;
            });

            renderTable(filtered);
        };
        
        // --- Initial Fetch ---
        async function fetchHistory() {
            try {
                const response = await fetch('api_transaction.php?action=get_history');
                const data = await response.json();
                if (data.status === 'success') {
                    allTransactions = data.history;
                    applyFilters(); // Initial render
                } else {
                    document.getElementById('historyTableBody').innerHTML = `<tr><td colspan="7" class="no-results">Gagal memuat data: ${data.message}</td></tr>`;
                }
            } catch (error) {
                document.getElementById('historyTableBody').innerHTML = `<tr><td colspan="7" class="no-results">Terjadi kesalahan jaringan.</td></tr>`;
            }
        }

        // --- Event Listeners ---
        document.addEventListener('DOMContentLoaded', () => {
            fetchHistory();
            document.getElementById('searchInput').addEventListener('input', applyFilters);
            document.getElementById('sortOrder').addEventListener('change', applyFilters);
            document.getElementById('startDate').addEventListener('change', applyFilters);
            document.getElementById('endDate').addEventListener('change', applyFilters);

            // Burger menu logic
            const burgerMenu = document.querySelector('.burger-menu');
            const navLinks = document.querySelector('header nav ul');
            burgerMenu.addEventListener('click', () => {
                burgerMenu.classList.toggle('active');
                navLinks.classList.toggle('active');
            });
        });
    </script>
</body>
</html>