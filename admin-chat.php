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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Warna Bubble Admin (Pengirim) */
            --admin-bg: #007bff; 
            
            /* Warna Bubble User (Penerima) */
            --user-bg: #F1F0F0; 

            /* Warna Tombol Setuju */
            --success-color: #2ECC71; 
            
            /* Warna Tombol Tolak */
            --danger-color: #E74C3C; 

            --system-bg: #f8f9fa;
            --border-color: #dee2e6; 
            --text-dark: #343a40; 
            --text-light: #6c757d;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f4f4; display: flex; height: 100vh; }
        
        .dashboard-container {
            display: flex;
            width: 100%;
            height: 100%;
        }

        .chat-list-panel {
            width: 350px;
            background: white;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .chat-list-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .chat-list-header a { color: var(--text-dark); text-decoration: none; font-size: 20px; }
        .chat-list-header h2 { font-size: 18px; font-weight: 600; }
        .chat-list { overflow-y: auto; flex-grow: 1; }
        .chat-item { display: flex; padding: 15px 20px; cursor: pointer; border-bottom: 1px solid var(--border-color); align-items: center;}
        .chat-item.active { background-color: #e7f3ff; }
        .chat-item img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; margin-right: 15px; }
        .chat-details .name { font-weight: 600; }
        .chat-details .product { font-size: 13px; color: var(--text-light); }
        .unread-indicator { margin-left: auto; width: 10px; height: 10px; background-color: var(--admin-bg); border-radius: 50%; flex-shrink: 0; }
        
        .chat-panel {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .no-chat-selected {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            flex-direction: column;
            color: #bdc3c7;
            text-align: center;
        }
        .no-chat-selected i { font-size: 4rem; margin-bottom: 1rem; }

        .chat-view {
            display: none;
            flex-direction: column;
            height: 100%;
            width: 100%;
        }
        .chat-view.active {
            display: flex;
        }
        
        .product-header {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #2c3e50;
            color: white;
            flex-shrink: 0;
        }
        .product-header img { width: 50px; height: 50px; border-radius: 5px; object-fit: cover; }
        .product-header h3 { margin: 0; font-size: 18px; }

        .main-chat-area {
            display: flex;
            flex-grow: 1;
            overflow: hidden;
        }

        .chat-log {
            flex: 3;
            overflow-y: auto;
            padding: 20px;
            border-right: 1px solid var(--border-color);
        }
        .message { display: flex; flex-direction: column; margin-bottom: 15px; }
        .message.sent { align-items: flex-end; }
        .message.received { align-items: flex-start; }
        .message .bubble { 
            max-width: 75%; 
            padding: 10px 15px; 
            border-radius: 18px; 
            line-height: 1.5; 
            white-space: pre-wrap; 
            box-shadow: 0 1px 2px rgba(0,0,0,0.08); 
        }
        .message.sent .bubble { background-color: var(--admin-bg); color: white; border-bottom-right-radius: 4px; }
        .message.received .bubble { background-color: var(--user-bg); color: black; border-bottom-left-radius: 4px; }
        .message-time { font-size: 10px; color: #999; margin-top: 4px; padding: 0 5px; }
        .message.system { align-items: center; max-width: 100%; }
        .message.system .bubble { background-color: var(--system-bg); color: #6c757d; font-size: 12px; text-align: center; }
        .message.offer .bubble { background-color: #fffbe7; color: #333; width: 100%; max-width: 100%; border: 1px solid #ffeccc; }
        .offer-price { font-size: 20px; font-weight: bold; color: #856404; }
        .offer-description { font-size: 13px; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #ccc; }

        .offer-section {
            flex: 2;
            padding: 20px;
            background: #f8f9fa;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .offer-controls { display: flex; flex-direction: column; gap: 12px; }
        .offer-controls .btn, .offer-controls input, .offer-controls textarea {
            padding: 10px 12px; font-size: 13px; width: 100%; border-radius: 6px;
            border: 1px solid var(--border-color); transition: all 0.2s;
        }
        .offer-controls input:focus, .offer-controls textarea:focus {
            outline: none; border-color: var(--admin-bg);
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
        }
        .offer-controls .btn { border: none; font-weight: 500; cursor: pointer; color: white; }
        .btn-accept { background-color: var(--success-color); }
        .btn-reject { background-color: var(--danger-color); }
        .btn-offer { background-color: var(--admin-bg); }
        .status-info { padding-top:50px; text-align: center; }

        #custom-editor { padding: 12px; background: #fff; border-radius: 6px; margin-bottom: 10px; }
        #custom-editor h6 { font-size: 14px; margin: 0 0 10px 0; color: #212529; font-weight: 500; }
        #custom-editor label { font-size: 13px; margin-right: 15px; display: inline-flex; align-items: center; }
        #negotiation_notes { resize: vertical; min-height: 80px; }

        .chat-input { display: flex; padding: 15px; border-top: 1px solid var(--border-color); background: #fff; flex-shrink: 0; }
        .chat-input input { flex-grow: 1; border: 1px solid var(--border-color); border-radius: 20px; padding: 10px 15px; }
        .chat-input button { background: #007bff; color: white; border: none; border-radius: 50%; width: 45px; height: 45px; margin-left: 10px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="chat-list-panel">
            <div class="chat-list-header">
                <a href="admin-dashboard.php" title="Kembali ke Dasbor"><i class="fas fa-arrow-left"></i></a>
                <h2>Percakapan</h2>
            </div>
            <div class="chat-list" id="chatList"></div>
        </div>
        <div class="chat-panel" id="chatPanel">
             <div class="chat-view" id="chatView">
                <header class="product-header">
                    <img id="productImage" src="image/logo.png" alt="Produk">
                    <div>
                        <h3 id="productName">Memuat...</h3>
                        <small id="customerName"></small>
                    </div>
                </header>
                <div class="main-chat-area">
                    <div class="chat-log" id="chatBox"></div>
                    <div class="offer-section" id="offerControls"></div>
                </div>
                <div class="chat-input" id="chatInputSection">
                    <input type="text" id="messageInput" placeholder="Ketik balasan...">
                    <button id="sendMessageBtn"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
            <div class="no-chat-selected" id="noChatSelected">
                <i class="fas fa-comments"></i>
                <p>Pilih percakapan untuk ditampilkan</p>
            </div>
        </div>
    </div>

<script>
    let currentTransactionId = <?php echo $active_transaction_id; ?>;
    let transactionDetails = {};
    let availableCustomOptions = [];
    let activeInterval = null;

    const chatView = document.getElementById('chatView');
    const noChatSelected = document.getElementById('noChatSelected');
    const productImage = document.getElementById('productImage');
    const productName = document.getElementById('productName');
    const customerName = document.getElementById('customerName');
    const chatBox = document.getElementById('chatBox');
    const offerControls = document.getElementById('offerControls');
    const messageInput = document.getElementById('messageInput');
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const chatListDiv = document.getElementById('chatList');

    function formatRupiah(angka) {
        if (angka === null || isNaN(angka)) return '';
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }
    
    function formatNumberInput(input) {
        let value = input.value.replace(/[^0-9]/g, '');
        input.dataset.realValue = value;
        input.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
    }

    function renderMessages(messages) {
        chatBox.innerHTML = '';
        if (messages) {
            messages.forEach(msg => {
                const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const messageDiv = document.createElement('div');
                let bubbleHTML = '';
                let messageClass = '';

                if (msg.is_offer == 1) {
                    messageClass = `message offer ${msg.sender_type === 'admin' ? 'sent' : 'received'}`;
                    const offerTitle = msg.sender_type === 'admin' ? 'Anda mengajukan penawaran:' : 'User mengajukan penawaran:';
                    bubbleHTML = `<div class="bubble"><strong>${offerTitle}</strong>
                                <div class="offer-price">${formatRupiah(msg.offer_value)}</div>
                                <div class="offer-description">${msg.message.replace(/\n/g, '<br>')}</div></div>`;
                } else {
                    messageClass = `message ${msg.sender_type === 'admin' ? 'sent' : (msg.sender_type === 'user' ? 'received' : 'system')}`;
                    bubbleHTML = `<div class="bubble">${msg.message.includes('cetak-bukti.php') ? `<span class="pdf-link">Pembayaran berhasil! <a href="${msg.message}" target="_blank">Unduh Bukti Pembayaran</a></span>` : msg.message.replace(/\n/g, '<br>')}</div>`;
                }

                messageDiv.className = messageClass;
                messageDiv.innerHTML = `${bubbleHTML}<span class="message-time">${time}</span>`;
                chatBox.appendChild(messageDiv);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }

    function renderOfferUI(details) {
        let html = '';
        if (details.status === 'awaiting_shipping_cost') {
            html = `<div class="offer-controls">
                        <h6>Input Ongkos Kirim</h6>
                        <p>User menunggu input ongkir untuk alamat:</p>
                        <textarea readonly style="min-height: 80px;">${details.shipping_address}</textarea>
                        <input type="text" id="shippingCostInput" oninput="formatNumberInput(this)" placeholder="Masukkan ongkos kirim">
                        <button class="btn btn-accept" onclick="submitShippingCost()">Buat Tagihan</button>
                    </div>`;
        } else if (details.status === 'negotiating') {
            if (details.last_offer_by === 'user') {
                 const notesDefault = details.negotiation_notes || '';
                 let currentCustomsDefault = JSON.parse(details.customization_details || '[]');
                 
                 // **PERBAIKAN:** Menambahkan value dan data-real-value ke input koreksi harga
                 const lastOfferPrice = details.last_offer_price || '';
                 const formattedLastOffer = lastOfferPrice ? new Intl.NumberFormat('id-ID').format(lastOfferPrice) : '';

                 html = `<div class="offer-controls">
                            <h6>Tawaran dari User</h6>
                            <p>User menawarkan <strong>${formatRupiah(details.last_offer_price)}</strong></p>
                            <div style="display:flex; gap: 10px; width:100%;">
                               <button class="btn btn-accept" onclick="performAction('admin_accept_offer')" style="flex-grow:1;">✔️ Setujui</button>
                               <button class="btn btn-reject" onclick="performAction('admin_reject_offer')" style="flex-grow:1;">✖️ Tolak</button>
                            </div>
                            <hr style="width:100%; margin:15px 0;">
                            <h6>Koreksi Penawaran</h6>
                            <div id="custom-editor"></div>
                            <textarea id="negotiation_notes" placeholder="Tulis catatan koreksi (opsional)...">${notesDefault}</textarea>
                            <input type="text" id="adminOfferInput" oninput="formatNumberInput(this)" placeholder="Koreksi harga..." value="${formattedLastOffer}" data-real-value="${lastOfferPrice}">
                            <button class="btn btn-offer" onclick="submitOffer()">✏️ Koreksi & Tawar Balik</button>
                         </div>`;
                 offerControls.innerHTML = html;
                 renderCustomEditor(currentCustomsDefault);
                 return; 
            } else {
                 html = `<p class="status-info">Menunggu respon dari customer...</p>`;
            }
        } else {
            html = `<p class="status-info">Status: ${details.status.replace('_', ' ').toUpperCase()}</p>`;
        }
        
        if (details.status !== 'negotiating' && details.status !== 'awaiting_shipping_cost') {
             document.getElementById('chatInputSection').style.display = 'none';
        } else {
             document.getElementById('chatInputSection').style.display = 'flex';
        }

        offerControls.innerHTML = html;
    }
    
    function renderCustomEditor(currentCustoms) {
        const editor = document.getElementById('custom-editor');
        if (!editor) return;
        editor.innerHTML = '<h6>Koreksi Pilihan Custom:</h6>';
        if (availableCustomOptions.length === 0) {
            editor.innerHTML += '<p style="font-size:12px; color:#888;">Tidak ada opsi kustomisasi.</p>';
            return;
        }
        availableCustomOptions.forEach(type => {
            let optionsHTML = `<div class="custom-type-group" style="margin-bottom:8px;"><strong style="font-size:13px;">${type.name}:</strong><br>`;
            const currentChoice = currentCustoms.find(c => type.options.some(opt => opt.name === c.name));
            let isStandardChecked = !currentChoice;
            optionsHTML += `<label><input type="radio" name="type_${type.id}" value="null" ${isStandardChecked ? 'checked' : ''}> Standar</label>`;
            type.options.forEach(option => {
                const optionData = JSON.stringify({ name: option.name, price: option.price });
                let isChecked = currentChoice && currentChoice.name === option.name;
                optionsHTML += `<label><input type="radio" name="type_${type.id}" value='${optionData}' ${isChecked ? 'checked' : ''}> ${option.name}</label>`;
            });
            optionsHTML += `</div>`;
            editor.innerHTML += optionsHTML;
        });
    }

    async function fetchData(transactionId) {
        let currentOfferPrice = null, currentOfferPriceReal = null, currentNotes = null;
        let currentCustomSelections = {};

        const offerInputEl = document.getElementById('adminOfferInput');
        if (offerInputEl) {
            currentOfferPrice = offerInputEl.value;
            currentOfferPriceReal = offerInputEl.dataset.realValue;
        }
        const notesInputEl = document.getElementById('negotiation_notes');
        if (notesInputEl) {
            currentNotes = notesInputEl.value;
        }
        document.querySelectorAll('#custom-editor input[type="radio"]:checked').forEach(radio => {
            currentCustomSelections[radio.name] = radio.value;
        });

        try {
            const response = await fetch(`api_transaction.php?action=get_details&transaction_id=${transactionId}`);
            const data = await response.json();
            if (data.status === 'success') {
                const firstFetch = !transactionDetails.product_id || transactionDetails.product_id !== data.details.product_id;
                transactionDetails = data.details;

                if (firstFetch) {
                    availableCustomOptions = [];
                    const customResp = await fetch(`api_custom.php?action=get_product_customs&product_id=${transactionDetails.product_id}`);
                    const customData = await customResp.json();
                    if (customData.status === 'success') availableCustomOptions = customData.customizations;
                }

                productName.textContent = transactionDetails.product_name;
                customerName.textContent = 'Pembeli: ' + transactionDetails.customer_name;
                const images = JSON.parse(transactionDetails.product_image || '[]')[0] || 'image/logo.png';
                productImage.src = images;
                
                renderMessages(transactionDetails.chat_logs);
                renderOfferUI(transactionDetails);

                const newOfferInput = document.getElementById('adminOfferInput');
                // **PERBAIKAN:** Hanya isi kembali nilai jika ada nilai yang sedang diketik, jika tidak biarkan default dari renderOfferUI
                if (newOfferInput && currentOfferPrice !== null) {
                    newOfferInput.value = currentOfferPrice;
                    newOfferInput.dataset.realValue = currentOfferPriceReal;
                }
                const newNotesInput = document.getElementById('negotiation_notes');
                if (newNotesInput && currentNotes !== null) {
                    newNotesInput.value = currentNotes;
                }
                if (Object.keys(currentCustomSelections).length > 0) {
                    for (const name in currentCustomSelections) {
                        const value = currentCustomSelections[name];
                        const radioToSelect = document.querySelector(`#custom-editor input[name="${name}"][value='${CSS.escape(value)}']`);
                        if (radioToSelect) {
                            radioToSelect.checked = true;
                        }
                    }
                }
            }
        } catch (error) { console.error("Gagal memuat data:", error); }
    }

    async function performAction(action, data = {}) {
        try {
            const response = await fetch('api_transaction.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action, transaction_id: currentTransactionId, ...data })
            });
            if (!response.ok) {
                const err = await response.json();
                throw new Error(err.message || 'Gagal melakukan aksi');
            }
            await fetchData(currentTransactionId);
            await loadChatList();
        } catch(error) { alert(`Terjadi Kesalahan: ${error.message}`); }
    }

    function submitOffer() {
        const priceInput = document.getElementById('adminOfferInput');
        const offer_price = priceInput.dataset.realValue;
        if (!offer_price || offer_price <= 0) { alert('Masukkan harga penawaran yang valid.'); return; }
        
        const notes = document.getElementById('negotiation_notes').value;
        let selectedCustoms = [];
        document.querySelectorAll('#custom-editor input[type="radio"]:checked').forEach(radio => {
            if (radio.value !== "null") {
                selectedCustoms.push(JSON.parse(radio.value));
            }
        });

        performAction('submit_offer', { 
            offer_price,
            negotiation_notes: notes,
            customization_details: selectedCustoms
        });
    }

    function submitShippingCost() {
        const costInput = document.getElementById('shippingCostInput');
        const shipping_cost = costInput.dataset.realValue;
        if (!shipping_cost || shipping_cost < 0) { alert('Masukkan ongkos kirim yang valid.'); return; }
        performAction('submit_shipping_cost', { shipping_cost });
    }
    
    function sendChatMessage() {
        const message = messageInput.value.trim();
        if (message) {
            performAction('send_chat', { message });
            messageInput.value = '';
        }
    }

    async function loadChatList() {
        try {
            const response = await fetch('api_transaction.php?action=get_chat_list');
            const data = await response.json();
            chatListDiv.innerHTML = '';
            if (data.status === 'success' && data.list.length > 0) {
                data.list.forEach(chat => {
                    const item = document.createElement('div');
                    item.className = 'chat-item';
                    if (chat.transaction_id == currentTransactionId) item.classList.add('active');
                    item.dataset.transactionId = chat.transaction_id;
                    item.onclick = () => selectChat(chat.transaction_id, item);
                    const images = JSON.parse(chat.product_image || '[]');
                    item.innerHTML = `<img src="${images.length > 0 ? images[0] : 'image/logo.png'}" alt="produk">
                        <div class="chat-details">
                            <div class="name">${chat.customer_name}</div>
                            <div class="product">${chat.product_name}</div>
                        </div>
                        ${chat.unread_count > 0 ? '<div class="unread-indicator"></div>' : ''}`;
                    chatListDiv.appendChild(item);
                });
            } else {
                chatListDiv.innerHTML = '<p style="text-align: center; padding: 20px; color: #888;">Tidak ada percakapan aktif.</p>';
            }
        } catch(error) { console.error("Gagal memuat daftar chat", error); }
    }

    function selectChat(transactionId, element) {
        if (activeInterval) clearInterval(activeInterval);
        currentTransactionId = transactionId;
        
        const url = new URL(window.location);
        url.searchParams.set('transaction_id', transactionId);
        window.history.pushState({}, '', url);

        chatView.classList.add('active');
        noChatSelected.style.display = 'none';
        
        document.querySelectorAll('.chat-item').forEach(item => item.classList.remove('active'));
        if(element) element.classList.add('active');
        const indicator = element ? element.querySelector('.unread-indicator') : null;
        if(indicator) indicator.remove();
        
        fetchData(transactionId);
        activeInterval = setInterval(() => fetchData(transactionId), 8000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadChatList();
        if (currentTransactionId > 0) {
            setTimeout(() => {
                const activeElement = document.querySelector(`.chat-item[data-transaction-id='${currentTransactionId}']`);
                selectChat(currentTransactionId, activeElement);
            }, 500);
        } else {
            noChatSelected.style.display = 'flex';
            chatView.classList.remove('active');
        }

        sendMessageBtn.addEventListener('click', sendChatMessage);
        messageInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendChatMessage(); });
        setInterval(loadChatList, 15000);
    });
</script>
</body>
</html>