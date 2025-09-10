<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$conn = new mysqli("localhost", "root", "", "mokobang");
$user_id = $_SESSION['user_id'];
$conn->set_charset("utf8mb4");

// Ambil semua transaksi/billing untuk user ini
$sql = "SELECT t.*, k.tipe as product_name 
        FROM transactions t 
        JOIN katalog k ON t.product_id = k.id 
        WHERE t.user_id = ? 
        ORDER BY t.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pembelian Saya</title>
    <link rel="icon" href="image/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f4f4f4; color: #333; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 0 100px; background-color: #2c3e50; }
        header .logo img { height: 100px; }
        header nav ul { list-style: none; display: flex; gap: 10px; }
        .button-18 { align-items: center; background-color: #0A66C2; border: 0; border-radius: 100px; color: #ffffff; cursor: pointer; display: inline-flex; font-size: 14px; font-weight: 600; justify-content: center; min-height: 40px; padding: 0 30px; text-decoration: none; }
        main { padding: 40px 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 25px; }
        .billing-item { border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; overflow: hidden; }
        .billing-header { background-color: #f8f9fa; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .billing-header strong { font-size: 1.1em; }
        .billing-body { padding: 20px; }
        .status { padding: 5px 12px; border-radius: 15px; color: white; font-size: 12px; text-transform: capitalize; }
        .status-awaiting_payment { background-color: #ffc107; color: #333; }
        .status-paid { background-color: #28a745; }
        .status-expired, .status-cancelled { background-color: #dc3545; }
        .copy-btn { background: none; border: none; cursor: pointer; color: #007bff; }
        .action-buttons { margin-top: 15px; display: flex; gap: 10px; }
        .btn-print { background-color: #17a2b8; }
    </style>
</head>
<body>
    <header>
        <div class="logo"><a href="index.php"><img src="image/logo-showcase.png" alt="Logo"></a></div>
        <nav>
            <ul>
                <li><a href="katalog.php" class="button-18">Lanjut Belanja</a></li>
                <li><a href="logout.php" class="button-18">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
            <h1>Daftar Pembelian & Tagihan Saya</h1>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="billing-item">
                        <div class="billing-header">
                            <div>
                                <strong>Produk: <?php echo htmlspecialchars($row['product_name']); ?></strong><br>
                                <small>Tanggal: <?php echo date('d M Y', strtotime($row['created_at'])); ?></small>
                            </div>
                            <span class="status status-<?php echo $row['status']; ?>"><?php echo str_replace('_', ' ', $row['status']); ?></span>
                        </div>
                        <div class="billing-body">
                            <p><strong>Nomor Billing:</strong> <?php echo htmlspecialchars($row['billing_number']); ?> 
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo $row['billing_number']; ?>')"><i class="fas fa-copy"></i> Salin</button>
                            </p>
                            <p><strong>Total Tagihan:</strong> <?php echo 'Rp ' . number_format($row['total_bill'], 0, ',', '.'); ?></p>
                            
                            <div class="action-buttons">
                                <?php if($row['status'] == 'awaiting_payment'): ?>
                                    <p><strong>Batas Pembayaran:</strong> <span class="countdown" data-deadline="<?php echo $row['payment_deadline']; ?>"></span></p>
                                    <a href="payment.php?transaction_id=<?php echo $row['id']; ?>" class="button-18">Lihat Detail & Bayar</a>
                                <?php elseif($row['status'] == 'paid'): ?>
                                    

<a href="cetak-bukti.php?id=<?php echo $row['id']; ?>" class="button-18 btn-print" target="_blank">
                                        <i class="fas fa-print"></i> Cetak Bukti
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center;">Anda belum memiliki riwayat pembelian atau tagihan.</p>
            <?php endif; ?>
        </div>
    </main>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => alert('Nomor billing disalin!'));
        }
        document.querySelectorAll('.countdown').forEach(elem => {
            const deadline = new Date(elem.dataset.deadline).getTime();
            const interval = setInterval(() => {
                const now = new Date().getTime();
                const distance = deadline - now;
                if (distance < 0) {
                    clearInterval(interval);
                    elem.textContent = "Waktu Habis";
                    return;
                }
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                elem.textContent = `${days} hari ${hours} jam ${minutes} menit lagi`;
            }, 1000);
        });
    </script>
</body>
</html>