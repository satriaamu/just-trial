<?php
session_start();
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$transaction_id = intval($_GET['transaction_id'] ?? 0);
if ($transaction_id === 0) {
    header('Location: history.php');
    exit();
}
$is_admin = isset($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Negosiasi</title>
    <link rel="icon" href="image/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f4f4f4; margin: 0; }
        .container { display: flex; max-width: 1200px; margin: 30px auto; gap: 30px; }
        .product-panel, .negotiation-panel { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .product-panel { flex: 1; }
        .negotiation-panel { flex: 2; }
        h2 { color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .product-image { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 15px; }
        .status-box { padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .status-box h3 { margin-top: 0; margin-bottom: 10px; }
        .status-negotiating { background-color: #fff3cd; border: 1px solid #ffeeba; }
        .status-deal { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .status-payment { background-color: #d1ecf1; border: 1px solid #bee5eb; }
        .offer-price { font-size: 24px; font-weight: bold; color: #007bff; }
        .controls { margin-top: 15px; display:flex; gap:10px; flex-wrap:wrap; justify-content:center; align-items: center; }
        .controls input { padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .controls button { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; }
        .btn-accept { background-color: #28a745; }
        .btn-offer { background-color: #007bff; }
        .chat-log { height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        .log-item { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #eee; font-size: 14px; }
        .log-item small { color: #888; display:block; margin-bottom: 4px; }
        .log-item strong { text-transform: capitalize; }
        .chat-input { display: flex; gap: 10px; }
        .chat-input input { flex-grow: 1; }

        @media (max-width: 900px) {
            .container { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="product-panel" id="productPanel"><h2>Memuat Produk...</h2></div>
        <div class="negotiation-panel">
            <h2>Status & Aksi Negosiasi</h2>
            <div id="statusBox" class="status-box"><p>Memuat status...</p></div>
            <h2>Diskusi & Janji Temu</h2>
            <div id="chatLog" class="chat-log"><p>Memuat diskusi...</p></div>
            <div class="chat-input">
                <input type="text" id="chatInput" placeholder="Ketik pesan atau atur janji temu...">
                <button id="sendChatBtn" class="btn-offer">Kirim</button>
            </div>
        </div>
    </div>

    <script>
        const transactionId = new URLSearchParams(window.location.search).get('transaction_id');
        const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;

        function formatRupiah(angka) {
            if (angka === null || isNaN(angka)) return '';
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
        }

        async function updateUI(details) {
            // Update Panel Produk
            const productPanel = document.getElementById('productPanel');
            const images = JSON.parse(details.product_image || '[]');
            productPanel.innerHTML = `
                <h2>Detail Produk</h2>
                <img src="${images.length > 0 ? images[0] : 'image/logo.png'}" class="product-image">
                <h3>${details.product_name}</h3>
                <p><strong>Harga Awal:</strong> ${formatRupiah(details.harga_awal)}</p>
            `;
            
            // Update Status Box & Controls
            const statusBox = document.getElementById('statusBox');
            let statusHTML = '';
            const canAdminAct = isAdmin && details.last_offer_by === 'user';
            const canUserAct = !isAdmin && (details.last_offer_by === 'admin' || details.last_offer_by === null);

            if (details.status === 'negotiating') {
                statusBox.className = 'status-box status-negotiating';
                statusHTML = `<h3>Sedang Negosiasi</h3>`;
                if (details.last_offer_price) {
                    statusHTML += `<p>Tawaran terakhir dari <strong>${details.last_offer_by}</strong>: <span class="offer-price">${formatRupiah(details.last_offer_price)}</span></p>`;
                } else {
                    statusHTML += `<p>Belum ada penawaran. Customer, silakan ajukan harga pertama Anda.</p>`;
                }

                if (canAdminAct) {
                    statusHTML += `<div class="controls">
                        <button class="btn-accept" onclick="acceptOffer()">Setujui Harga Ini</button>
                        <input type="number" id="offerInput" placeholder="Ajukan harga lain">
                        <button class="btn-offer" onclick="submitOffer()">Koreksi Harga</button>
                    </div>`;
                }
                if (canUserAct) {
                     statusHTML += `<div class="controls">
                        ${details.last_offer_by === 'admin' ? '<button class="btn-accept" onclick="acceptOffer()">Setujui Harga Admin</button>' : ''}
                        <input type="number" id="offerInput" placeholder="Ajukan harga Anda">
                        <button class="btn-offer" onclick="submitOffer()">Ajukan Tawaran</button>
                    </div>`;
                }
            
            } else if (details.status === 'deal') {
                statusBox.className = 'status-box status-deal';
                statusHTML = `<h3>Kesepakatan Tercapai!</h3><p>Harga deal: <span class="offer-price">${formatRupiah(details.deal_price)}</span></p>${!isAdmin ? `<p>Silakan isi alamat pengiriman di bawah untuk menghitung ongkir.</p><div class="controls"><input id="shipName" placeholder="Nama Penerima"><input id="shipCity" placeholder="Kota Tujuan"><button onclick="submitShipping()">Hitung Ongkir & Buat Tagihan</button></div>` : '<p>Menunggu customer mengisi alamat.</p>'}`;
            
            } else if (details.status === 'awaiting_payment') {
                statusBox.className = 'status-box status-payment';
                statusHTML = `<h3>Menunggu Pembayaran</h3><p>Total Tagihan: <span class="offer-price">${formatRupiah(details.total_bill)}</span></p><p>Nomor VA: <strong>${details.virtual_account}</strong></p><a href="payment.php?transaction_id=${transactionId}"><button class="btn-offer">Lihat Halaman Pembayaran</button></a>`;
            } else {
                statusBox.className = 'status-box';
                statusHTML = `<h3>Transaksi Selesai</h3><p>Status: ${details.status.toUpperCase()}</p>`;
            }
            statusBox.innerHTML = statusHTML;
        }

        async function updateChatLog() {
            const response = await fetch(`api_transaction.php?action=get_chat_logs&transaction_id=${transactionId}`);
            const data = await response.json();
            const chatLog = document.getElementById('chatLog');
            chatLog.innerHTML = '';
            if (data.status === 'success') {
                data.logs.forEach(log => {
                    const time = new Date(log.created_at).toLocaleString('id-ID');
                    chatLog.innerHTML += `<div class="log-item"><small>${time}</small><strong>${log.sender_type}:</strong> ${log.message}</div>`;
                });
                chatLog.scrollTop = chatLog.scrollHeight;
            }
        }

        async function performAction(action, data = {}) {
            await fetch('api_transaction.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ action, transaction_id: transactionId, ...data }) });
            fetchData();
        }

        function submitOffer() { const price = document.getElementById('offerInput').value; if (!price) return; performAction('submit_offer', { offer_price: price }); }
        function acceptOffer() { if (!confirm('Setujui harga ini?')) return; performAction('accept_offer'); }
        async function submitShipping() { 
            const name = document.getElementById('shipName').value; 
            const city = document.getElementById('shipCity').value; 
            if(!name || !city) { 
                alert('Nama dan Kota Tujuan wajib diisi.');
                return; 
            }
            performAction('submit_shipping', { name, city }); 
        }

        async function sendChatMessage() {
            const input = document.getElementById('chatInput');
            if (!input.value.trim()) return;
            await performAction('send_chat', { message: input.value });
            input.value = '';
        }
        
        async function fetchData() {
            const response = await fetch(`api_transaction.php?action=get_details&transaction_id=${transactionId}`);
            const data = await response.json();
            if (data.status === 'success') {
                updateUI(data.details);
                updateChatLog();
            }
        }

        document.getElementById('sendChatBtn').addEventListener('click', sendChatMessage);
        
        fetchData();
        setInterval(fetchData, 5000); // Refresh data setiap 5 detik
    </script>
</body>
</html>