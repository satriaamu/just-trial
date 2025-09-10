<?php
session_start();

// Memeriksa jika ada sesi admin ATAU sesi user yang aktif
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Mengambil ID transaksi dari URL
$transaction_id = intval($_GET['transaction_id'] ?? 0);
if ($transaction_id === 0) {
    // Jika tidak ada ID transaksi, arahkan ke halaman riwayat
    header('Location: history.php');
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
    <title>Ruang Negosiasi</title>
    <link rel="icon" href="image/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Roboto', sans-serif; background-color: #f4f4f4; }
        
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 90vh;
            overflow: hidden;
        }

        .product-header {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #2c3e50;
            color: white;
            border-radius: 8px 8px 0 0;
            flex-shrink: 0;
        }
        .product-header a {
            color: white;
            text-decoration: none;
            font-size: 20px;
            margin-right: 15px;
        }
        .product-header img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            object-fit: cover;
            border: 2px solid white;
        }
        .product-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .chat-box {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: #f9f9f9;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            max-width: 80%;
            flex-direction: column;
        }
        .message.sent {
            align-items: flex-end;
            margin-left: auto;
        }
        .message.received {
            align-items: flex-start;
            margin-right: auto;
        }
        .message .bubble {
            padding: 10px 15px;
            border-radius: 18px;
            line-height: 1.4;
            max-width: 100%;
        }
        .message.sent .bubble {
            background-color: #0A66C2;
            color: white;
            border-bottom-right-radius: 4px;
        }
        .message.received .bubble {
            background-color: #e5e5ea;
            color: black;
            border-bottom-left-radius: 4px;
        }
        .message-time {
            font-size: 10px;
            color: #888;
            margin-top: 4px;
            padding: 0 5px;
        }

        /* CSS UNTUK PESAN PENAWARAN */
        .message.offer-bubble {
            align-items: center;
            max-width: 100%;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .message.offer-bubble .bubble {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            width: 100%;
            text-align: center;
        }
        .offer-bubble h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
            font-weight: normal;
        }
        .offer-bubble p {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .offer-section {
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #ddd;
            text-align: center;
            flex-shrink: 0;
        }
        .offer-controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }
        .offer-controls button {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .offer-controls input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            max-width: 180px;
        }

        .btn-accept { background-color: #28a745; color: white; }
        .btn-reject { background-color: #dc3545; color: white; }
        .btn-offer { background-color: #007bff; color: white; }
        .status-info { padding: 15px; font-weight: bold; color: #555; }
        
        .chat-input {
            display: flex;
            padding: 15px;
            border-top: 1px solid #ddd;
            background: #fff;
            flex-shrink: 0;
        }
        .chat-input input {
            flex-grow: 1;
            border: 1px solid #ccc;
            border-radius: 20px;
            padding: 10px 15px;
            font-size: 16px;
        }
        .chat-input input:focus { outline: none; border-color: #0A66C2; }
        .chat-input button {
            background: #0A66C2; color: white; border: none; border-radius: 50%;
            width: 45px; height: 45px; margin-left: 10px; cursor: pointer;
            font-size: 18px; display: flex; align-items: center; justify-content: center;
            transition: background-color 0.2s;
        }
        .chat-input button:hover { background-color: #16437E; }
    </style>
</head>
<body>
    <div class="chat-container">
        <header class="product-header">
            <a href="history.php"><i class="fas fa-arrow-left"></i></a>
            <img id="productImage" src="image/logo.png" alt="Produk">
            <h3 id="productName">Memuat...</h3>
        </header>

        <div class="chat-box" id="chatBox">
            </div>
        
        <div class="offer-section" id="offerControls">
            </div>

        <div class="chat-input" id="chatInputSection">
            <input type="text" id="messageInput" placeholder="Ketik pesan...">
            <button id="sendMessageBtn"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <script>
        const transactionId = <?php echo $transaction_id; ?>;
        const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
        
        const chatBox = document.getElementById('chatBox');
        const messageInput = document.getElementById('messageInput');
        const sendMessageBtn = document.getElementById('sendMessageBtn');
        const offerControls = document.getElementById('offerControls');

        function formatRupiah(angka) {
            if (angka === null || isNaN(angka)) return '';
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
        }

        function renderChatMessages(messages) {
            let html = '';
            messages.forEach(msg => {
                const isSentByCurrentUser = (isAdmin && msg.sender_type === 'admin') || (!isAdmin && msg.sender_type === 'user');
                const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                if (msg.is_offer == 1) {
                    html += `
                        <div class="message offer-bubble">
                            <div class="bubble">
                                <h4>${msg.message}</h4>
                                <p>${formatRupiah(msg.offer_value)}</p>
                            </div>
                            <span class="message-time">${time}</span>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="message ${isSentByCurrentUser ? 'sent' : 'received'}">
                            <div class="bubble">${msg.message}</div>
                            <span class="message-time">${time}</span>
                        </div>
                    `;
                }
            });
            chatBox.innerHTML = html;
            chatBox.scrollTop = chatBox.scrollHeight;
        }
        
        async function fetchMessages() {
            try {
                const response = await fetch(`api_chat.php?transaction_id=${transactionId}`);
                const data = await response.json();
                if (data.status === 'success') {
                    renderChatMessages(data.messages);
                }
            } catch (error) {
                console.error("Gagal memuat pesan:", error);
            }
        }

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (message === '') return;
            messageInput.disabled = true;

            await fetch('api_chat.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ transaction_id: transactionId, message: message })
            });
            messageInput.value = '';
            messageInput.disabled = false;
            messageInput.focus();
            fetchMessages();
        }

        async function fetchTransactionDetails() {
            try {
                const response = await fetch(`api_transaction.php?action=get_details&transaction_id=${transactionId}`);
                const data = await response.json();
                if (data.status === 'success') {
                    const details = data.details;
                    document.getElementById('productName').textContent = details.product_name;
                    const images = JSON.parse(details.product_image || '[]');
                    document.getElementById('productImage').src = images.length > 0 ? images[0] : 'image/default.jpg';
                    updateOfferUI(details);
                } else {
                    offerControls.innerHTML = `<p class="status-info" style="color: red;">${data.message || 'Gagal memuat detail transaksi.'}</p>`;
                }
            } catch (error) {
                console.error("Gagal memuat detail transaksi:", error);
                offerControls.innerHTML = `<p class="status-info" style="color: red;">Terjadi kesalahan jaringan.</p>`;
            }
        }

        function updateOfferUI(details) {
            let html = '';
            const lastOfferByAdmin = details.last_offer_by === 'admin';
            const lastOfferByUser = details.last_offer_by === 'user';

            if (details.status === 'negotiating') {
                if (isAdmin) {
                    if (lastOfferByUser) {
                        html = `<div class="offer-controls">
                                    <button class="btn-accept" onclick="acceptOffer()">Terima Tawaran</button>
                                    <button class="btn-reject" onclick="rejectOffer()">Tolak</button>
                                    <input type="number" id="counterOfferPrice" placeholder="Ajukan harga baru">
                                    <button class="btn-offer" onclick="makeOffer('counterOfferPrice')">Tawar Balik</button>
                                </div>`;
                    } else {
                        html = `<p class="status-info">Menunggu respon dari customer...</p>`;
                    }
                } else { // Customer view
                    if (lastOfferByAdmin) {
                        html = `<div class="offer-controls">
                                    <button class="btn-accept" onclick="acceptOffer()">Terima Tawaran Admin</button>
                                    <button class="btn-reject" onclick="rejectOffer()">Tolak</button>
                                </div>`;
                    } else {
                        html = `<div class="offer-controls">
                                    <input type="number" id="offerPrice" placeholder="Masukkan harga penawaran">
                                    <button class="btn-offer" onclick="makeOffer('offerPrice')">Ajukan Penawaran</button>
                                </div>`;
                    }
                }
            } else if (details.status === 'deal' && !isAdmin) {
                html = `<p class="status-info" style="color: #28a745;">Deal! Silakan isi alamat untuk menghitung ongkir.</p>
                        <div class="offer-controls">
                            <input type="text" id="shipName" placeholder="Nama Penerima">
                            <input type="text" id="shipProvince" placeholder="Provinsi">
                            <input type="text" id="shipCity" placeholder="Kota/Kabupaten">
                            <button class="btn-offer" onclick="submitShipping()">Lanjut ke Pembayaran</button>
                        </div>`;
            } else {
                html = `<p class="status-info">Status Transaksi: ${details.status.replace('_', ' ').toUpperCase()}</p>`;
            }
            offerControls.innerHTML = html;

            const isTransactionActive = ['negotiating', 'deal'].includes(details.status);
            document.getElementById('chatInputSection').style.display = isTransactionActive ? 'flex' : 'none';
        }

        async function performAction(action, data = {}) {
            const body = { action, transaction_id: transactionId, ...data };
            try {
                const response = await fetch('api_transaction.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(body)
                });
                if (!response.ok) throw new Error('Network response was not ok');
                await response.json();
                fetchData(); // Refresh data after any action
            } catch (error) {
                console.error(`Error during ${action}:`, error);
                alert(`Gagal melakukan aksi: ${action}. Silakan coba lagi.`);
            }
        }

        function makeOffer(inputId) {
            const price = document.getElementById(inputId).value;
            if (!price || price <= 0) { alert('Masukkan nominal penawaran yang valid!'); return; }
            performAction('make_offer', { offer_price: price });
        }

        function acceptOffer() {
            if (!confirm('Apakah Anda yakin ingin menerima penawaran ini?')) return;
            performAction('accept_offer');
        }

        function rejectOffer() {
            if (!confirm('Apakah Anda yakin ingin menolak penawaran dan membatalkan transaksi ini?')) return;
            performAction('reject_offer');
        }

        async function submitShipping() {
            const name = document.getElementById('shipName').value;
            const province = document.getElementById('shipProvince').value;
            const city = document.getElementById('shipCity').value;
            if(!name || !province || !city) { alert('Semua field alamat wajib diisi'); return; }
            
            await performAction('submit_shipping', { name, province, city });
            window.location.href = `payment.php?transaction_id=${transactionId}`;
        }
        
        function fetchData() {
            fetchMessages();
            fetchTransactionDetails();
        }

        sendMessageBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });

        // Inisialisasi dan refresh otomatis
        fetchData();
        setInterval(fetchData, 5000); // Refresh data setiap 5 detik
    </script>
</body>
</html>