<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit();
}
$active_transaction_id = intval($_GET['transaction_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Chat Admin</title>
    <link rel="icon" href="image/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Roboto', sans-serif; background-color: #f4f4f4; }
        .dashboard-container { display: flex; height: 100vh; }
        .chat-list-panel { width: 350px; background: #fff; border-right: 1px solid #ddd; display: flex; flex-direction: column; }
        .chat-list-header { padding: 20px; background-color: #2c3e50; color: white; flex-shrink: 0; display: flex; align-items: center; gap: 15px; }
        .chat-list-header a { color: white; text-decoration: none; font-size: 18px; }
        .chat-list { overflow-y: auto; flex-grow: 1; }
        .chat-item { display: flex; align-items: center; padding: 15px; cursor: pointer; border-bottom: 1px solid #eee; transition: background-color 0.2s; }
        .chat-item:hover { background-color: #f5f5f5; }
        .chat-item.active { background-color: #e9f5ff; border-right: 3px solid #007bff; }
        .chat-item img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px; }
        .chat-details { overflow: hidden; }
        .chat-details .name { font-weight: bold; }
        .chat-details .product { font-size: 14px; color: #555; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
        .unread-indicator { margin-left: auto; width: 12px; height: 12px; background-color: #007bff; border-radius: 50%; }
        .chat-panel { flex-grow: 1; display: flex; flex-direction: column; background-color: #f9f9f9; }
        .no-chat-selected { display: flex; align-items: center; justify-content: center; height: 100%; color: #aaa; text-align: center; }
        .chat-container { display: flex; flex-direction: column; max-width: 100%; height: 100%; margin: 0; box-shadow: none; border-radius: 0; }
        .product-header, .offer-section, .chat-input { flex-shrink: 0; }
        .chat-box { flex-grow: 1; overflow-y: auto; padding: 20px; }
        .product-header { padding: 15px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 15px; background-color: #2c3e50; color: white; }
        .product-header img { width: 50px; height: 50px; border-radius: 5px; object-fit: cover; border: 2px solid white; }
        .message { margin-bottom: 15px; display: flex; max-width: 80%; flex-direction: column; }
        .message.sent { align-items: flex-end; margin-left: auto; }
        .message.received { align-items: flex-start; margin-right: auto; }
        .message .bubble { padding: 10px 15px; border-radius: 18px; line-height: 1.4; max-width: 100%; }
        .message.sent .bubble { background-color: #0A66C2; color: white; }
        .message.received .bubble { background-color: #e5e5ea; color: black; }
        .message-time { font-size: 10px; color: #888; margin-top: 4px; padding: 0 5px; }
        .message.offer-bubble .bubble { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; width:100%; text-align:center; padding: 15px; }
        .offer-bubble h4 { margin: 0 0 8px 0; font-size: 14px; font-weight: normal; }
        .offer-bubble p { margin: 0; font-size: 20px; font-weight: bold; }
        .offer-section { padding: 15px; background: #f0f0f0; border-top: 1px solid #ddd; text-align: center; }
        .offer-controls { display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap; }
        .offer-controls button { padding: 10px 20px; font-size: 14px; font-weight: bold; border: none; border-radius: 5px; cursor: pointer; }
        .offer-controls input { padding: 10px; border: 1px solid #ccc; border-radius: 5px; max-width: 180px; }
        .btn-accept { background-color: #28a745; color: white; }
        .btn-reject { background-color: #dc3545; color: white; }
        .btn-offer { background-color: #007bff; color: white; }
        .status-info { padding: 15px; font-weight: bold; color: #555; }
        .chat-input { display: flex; padding: 15px; border-top: 1px solid #ddd; background: #fff; }
        .chat-input input { flex-grow: 1; border: 1px solid #ccc; border-radius: 20px; padding: 10px 15px; font-size:16px; }
        .chat-input button { background: #0A66C2; color: white; border: none; border-radius: 50%; width: 45px; height: 45px; margin-left: 10px; cursor: pointer; font-size:18px; display:flex; align-items:center; justify-content:center; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="chat-list-panel">
            <div class="chat-list-header">
                <a href="admin-dashboard.php"><i class="fas fa-arrow-left"></i></a>
                <h2>Percakapan</h2>
            </div>
            <div class="chat-list" id="chatList">
                <p style="text-align: center; padding: 20px; color: #888;">Memuat percakapan...</p>
            </div>
        </div>
        <div class="chat-panel" id="chatPanel">
            <div class="chat-container" id="chatContainer" style="display: <?php echo $active_transaction_id > 0 ? 'flex' : 'none'; ?>;">
                <header class="product-header">
                    <img id="productImage" src="image/logo.png" alt="Produk">
                    <h3 id="productName">Memuat...</h3>
                </header>
                <div class="chat-box" id="chatBox"></div>
                <div class="offer-section" id="offerControls"></div>
                <div class="chat-input" id="chatInputSection">
                    <input type="text" id="messageInput" placeholder="Ketik balasan sebagai admin...">
                    <button id="sendMessageBtn"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
            <div class="no-chat-selected" id="noChatSelected" style="display: <?php echo $active_transaction_id > 0 ? 'none' : 'flex'; ?>;">
                <div>
                    <i class="fas fa-comments fa-4x"></i>
                    <p style="margin-top: 20px;">Pilih percakapan dari daftar di sebelah kiri.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const activeTransactionId = <?php echo $active_transaction_id; ?>;
        
        async function loadChatList() {
            try {
                const response = await fetch('api_transaction.php?action=get_chat_list');
                const data = await response.json();
                const chatListDiv = document.getElementById('chatList');
                chatListDiv.innerHTML = '';
                if (data.status === 'success') {
                    if (data.list.length === 0) {
                        chatListDiv.innerHTML = '<p style="text-align: center; padding: 20px; color: #888;">Tidak ada percakapan aktif.</p>';
                        return;
                    }
                    data.list.forEach(chat => {
                        const item = document.createElement('div');
                        item.className = 'chat-item';
                        if (chat.transaction_id == activeTransactionId) {
                            item.classList.add('active');
                        }
                        item.onclick = () => { window.location.href = `admin_chat.php?transaction_id=${chat.transaction_id}`; };
                        item.innerHTML = `
                            <img src="${chat.product_image || 'image/logo.png'}" alt="produk">
                            <div class="chat-details">
                                <div class="name">${chat.customer_name}</div>
                                <div class="product">${chat.product_name}</div>
                            </div>
                            ${chat.has_unread > 0 ? '<div class="unread-indicator"></div>' : ''}
                        `;
                        chatListDiv.appendChild(item);
                    });
                }
            } catch (error) { console.error("Gagal memuat daftar chat:", error); }
        }

        if (activeTransactionId > 0) {
            const isAdmin = true;
            const chatBox = document.getElementById('chatBox');
            const messageInput = document.getElementById('messageInput');
            const sendMessageBtn = document.getElementById('sendMessageBtn');
            const offerControls = document.getElementById('offerControls');
            
            // Salin semua fungsi JS dari chat.php
            function formatRupiah(angka) { if (angka === null || isNaN(angka)) return ''; return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka); }
            function renderChatMessages(messages) {
                let html = '';
                messages.forEach(msg => {
                    const isSentByCurrentUser = (isAdmin && msg.sender_type === 'admin') || (!isAdmin && msg.sender_type === 'user');
                    const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    if (msg.is_offer == 1) {
                        html += `<div class="message offer-bubble"><div class="bubble"><h4>${msg.message}</h4><p>${formatRupiah(msg.offer_value)}</p></div><span class="message-time">${time}</span></div>`;
                    } else {
                        html += `<div class="message ${isSentByCurrentUser ? 'sent' : 'received'}"><div class="bubble">${msg.message}</div><span class="message-time">${time}</span></div>`;
                    }
                });
                chatBox.innerHTML = html;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
            async function fetchMessages() {
                const response = await fetch(`api_chat.php?transaction_id=${activeTransactionId}`);
                const data = await response.json();
                if (data.status === 'success') { renderChatMessages(data.messages); }
            }
            async function sendMessage() {
                const message = messageInput.value.trim();
                if (message === '') return;
                messageInput.disabled = true;
                await fetch('api_chat.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ transaction_id: activeTransactionId, message: message }) });
                messageInput.value = ''; messageInput.disabled = false; messageInput.focus();
                fetchMessages();
            }
            async function fetchTransactionDetails() {
                const response = await fetch(`api_transaction.php?action=get_details&transaction_id=${activeTransactionId}`);
                const data = await response.json();
                if (data.status === 'success') {
                    const details = data.details;
                    document.getElementById('productName').textContent = details.product_name;
                    const images = JSON.parse(details.product_image || '[]');
                    document.getElementById('productImage').src = images.length > 0 ? images[0] : 'image/default.jpg';
                    updateOfferUI(details);
                }
            }
            function updateOfferUI(details) { /* Salin fungsi lengkap dari chat.php */ 
                let html = '';
                const lastOfferByUser = details.last_offer_by === 'user';
                if (details.status === 'negotiating') {
                    if (lastOfferByUser) {
                        html = `<div class="offer-controls"><button class="btn-accept" onclick="acceptOffer()">Terima</button><button class="btn-reject" onclick="rejectOffer()">Tolak</button><input type="number" id="counterOfferPrice" placeholder="Tawar Balik"><button class="btn-offer" onclick="makeOffer('counterOfferPrice')">Tawar</button></div>`;
                    } else {
                        html = `<p class="status-info">Menunggu respon customer...</p>`;
                    }
                } else {
                    html = `<p class="status-info">Status: ${details.status.replace('_', ' ').toUpperCase()}</p>`;
                }
                offerControls.innerHTML = html;
                const isTransactionActive = ['negotiating', 'deal'].includes(details.status);
                document.getElementById('chatInputSection').style.display = isTransactionActive ? 'flex' : 'none';
            }
            async function performAction(action, data = {}) {
                const body = { action, transaction_id: activeTransactionId, ...data };
                await fetch('api_transaction.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(body) });
                fetchData();
            }
            function makeOffer(inputId) { const price = document.getElementById(inputId).value; if (!price || price <= 0) return; performAction('make_offer', { offer_price: price }); }
            function acceptOffer() { if (!confirm('Terima penawaran ini?')) return; performAction('accept_offer'); }
            function rejectOffer() { if (!confirm('Tolak dan batalkan transaksi?')) return; performAction('reject_offer'); }
            function fetchData() { fetchMessages(); fetchTransactionDetails(); }
            sendMessageBtn.addEventListener('click', sendMessage);
            messageInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
            fetchData();
            setInterval(fetchData, 5000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadChatList();
            setInterval(loadChatList, 10000);
        });
    </script>
</body>
</html>