<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "mokobang");
// FIX: Mengatur charset koneksi untuk memperbaiki tampilan nama produk
$conn->set_charset("utf8mb4");

// Ambil daftar user dan produk
$users_result = $conn->query("SELECT id, username FROM users ORDER BY username ASC");
$products_result = $conn->query("SELECT id, tipe FROM katalog ORDER BY tipe ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Billing Manual</title>
    <link rel="icon" href="image/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
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
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-submit { background-color: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; }
        .total-display { font-size: 1.2em; font-weight: bold; margin-top: 10px; text-align: right; }
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
            <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div id="content">
        <div class="container">
            <h1>Buat Billing Baru</h1>
            <form id="billingForm">
                <div class="form-group">
                    <label for="user_id">Pilih User (Pembeli)</label>
                    <select id="user_id" name="user_id" required>
                        <option value="">-- Pilih User --</option>
                        <?php while($row = $users_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['username']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="product_id">Pilih Produk</label>
                    <select id="product_id" name="product_id" required>
                         <option value="">-- Pilih Produk --</option>
                        <?php $products_result->data_seek(0); // Reset pointer result set ?>
                        <?php while($row = $products_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['tipe']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="deal_price">Jumlah Billing (Harga Deal)</label>
                    <input type="number" id="deal_price" name="deal_price" placeholder="Contoh: 50000000" required>
                </div>
                 <div class="form-group">
                    <label for="shipping_cost">Ongkos Kirim</label>
                    <input type="number" id="shipping_cost" name="shipping_cost" placeholder="Contoh: 1500000" required>
                </div>
                <div class="total-display">
                    Total Tagihan: <span id="total_bill_display">Rp 0</span>
                </div>
                <br>
                <button type="submit" class="btn-submit">Buat Billing</button>
            </form>
        </div>
    </div>
    <script>
        const dealPriceInput = document.getElementById('deal_price');
        const shippingCostInput = document.getElementById('shipping_cost');
        const totalBillDisplay = document.getElementById('total_bill_display');

        function calculateTotal() {
            const dealPrice = parseFloat(dealPriceInput.value) || 0;
            const shippingCost = parseFloat(shippingCostInput.value) || 0;
            const total = dealPrice + shippingCost;
            totalBillDisplay.textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(total);
        }

        dealPriceInput.addEventListener('input', calculateTotal);
        shippingCostInput.addEventListener('input', calculateTotal);

        document.getElementById('billingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = {
                action: 'create_billing',
                user_id: formData.get('user_id'),
                product_id: formData.get('product_id'),
                deal_price: formData.get('deal_price'),
                shipping_cost: formData.get('shipping_cost')
            };

            fetch('api_transaction.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.message || `Server merespons dengan status ${res.status}`) });
                }
                return res.json();
            })
            .then(result => {
                if(result.status === 'success') {
                    alert('Billing berhasil dibuat! Nomor Billing: ' + result.billing_number);
                    window.location.href = 'history.php';
                } else {
                    alert('Gagal membuat billing: ' + result.message);
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan: ' + err.message);
            });
        });
    </script>
</body>
</html>