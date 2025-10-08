<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <link rel="icon" href="image/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f4f4f4; text-align: center; padding-top: 50px; }
        .payment-container { max-width: 500px; margin: auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .payment-header { background-color: #2c3e50; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .payment-body { padding: 30px; }
        .payment-body p { margin: 10px 0; color: #555; }
        .payment-body strong { color: #333; }
        .total-bill { font-size: 28px; font-weight: bold; color: #dc3545; margin: 15px 0; }
        .va-box { background: #f8f9fa; border: 1px dashed #ccc; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .va-box p { margin: 5px 0; font-size: 14px; }
        .va-number { font-size: 24px; font-weight: bold; letter-spacing: 2px; color: #000; }
        .copy-btn { background: #007bff; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        .countdown-box { margin-top: 20px; padding: 10px; background-color: #fff3cd; border-radius: 5px; }
        #countdown { color: #856404; font-weight: bold; font-size: 18px; }
        .locked-info { margin-top: 20px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header"><h2>Selesaikan Pembayaran Anda</h2></div>
        <div class="payment-body" id="paymentBox">
            <p><strong>Nomor Invoice:</strong> <span id="billingNumber">-</span></p>
            <p>Harga Deal: <strong id="dealPrice">-</strong></p>
            <p>Ongkos Kirim: <strong id="shippingCost">-</strong></p>
            <hr style="margin: 15px 0;">
            <p>Total Tagihan:</p>
            <div class="total-bill" id="totalBill">-</div>
            <div class="va-box">
                <p>Silakan transfer ke Virtual Account berikut:</p>
                <div class="va-number" id="vaNumber">-</div>
                <button class="copy-btn" onclick="copyVA()">Salin Nomor</button>
            </div>
            <div class="countdown-box"><p>Batas waktu pembayaran:</p><div id="countdown">-</div></div>
            <p class="locked-info">Anda tidak dapat kembali ke halaman sebelumnya hingga transaksi selesai atau dibatalkan.</p>
        </div>
    </div>
    <script>
        const transactionId = new URLSearchParams(window.location.search).get('transaction_id');
        function formatRupiah(angka) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka); }

        async function fetchPaymentDetails() {
            const response = await fetch(`api_transaction.php?action=get_details&transaction_id=${transactionId}`);
            const data = await response.json();
            if (data.status === 'success' && data.details.status === 'awaiting_payment') {
                const details = data.details;
                document.getElementById('billingNumber').textContent = details.billing_number;
                document.getElementById('dealPrice').textContent = formatRupiah(details.deal_price);
                document.getElementById('shippingCost').textContent = formatRupiah(details.shipping_cost);
                document.getElementById('totalBill').textContent = formatRupiah(details.total_bill);
                document.getElementById('vaNumber').textContent = details.virtual_account;
                
                const deadline = new Date(details.payment_deadline).getTime();
                const countdownElem = document.getElementById('countdown');
                const interval = setInterval(() => {
                    const distance = deadline - new Date().getTime();
                    if (distance < 0) { clearInterval(interval); countdownElem.textContent = "WAKTU PEMBAYARAN HABIS"; return; }
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    countdownElem.textContent = `${days} hari ${hours} jam ${minutes} menit`;
                }, 1000);
            } else {
                 document.getElementById('paymentBox').innerHTML = '<h2>Tagihan tidak valid atau sudah dibayar.</h2><a href="history.php">Kembali ke Riwayat</a>';
            }
        }
        function copyVA() { navigator.clipboard.writeText(document.getElementById('vaNumber').textContent).then(() => alert('Nomor Virtual Account disalin!')); }
        history.pushState(null, null, location.href);
        window.onpopstate = () => history.go(1);
        fetchPaymentDetails();
    </script>
</body>
</html>