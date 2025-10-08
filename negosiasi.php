<?php
session_start();
// Memeriksa jika ada sesi user yang aktif
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$transaction_id = intval($_GET['transaction_id'] ?? 0);
if ($transaction_id === 0) {
    header('Location: history.php');
    exit();
}
$is_admin = isset($_SESSION['admin_id']);
if ($is_admin) {
    header('Location: admin-chat.php?transaction_id=' . $transaction_id);
    exit();
}
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Negosiasi</title>
    <link rel="icon" href="image/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Warna Bubble User (Pengirim) */
            --user-bg: #007bff;
            
            /* Warna Bubble Admin (Penerima) */
            --admin-bg: #F1F0F0;

            /* Warna Tombol */
            --success-color: #2ECC71;
            --danger-color: #E74C3C;
            --primary-color: #007bff;

            --system-bg: #f8f9fa;
            --border-color: #dee2e6;
            --text-dark: #343a40;
            --text-light: #6c757d;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f4f4; margin: 0; padding: 15px; }
        .chat-container { max-width: 1000px; margin: auto; background: white; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); display: flex; flex-direction: column; height: calc(100vh - 40px); overflow: hidden; }
        .product-header { padding: 15px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 15px; background-color: #2c3e50; color: white; border-radius: 8px 8px 0 0; flex-shrink: 0; }
        .product-header a { color: white; text-decoration: none; font-size: 20px; }
        .product-header img { width: 50px; height: 50px; border-radius: 5px; object-fit: cover; }
        .product-header h3 { margin: 0; font-size: 18px; }
        
        .main-chat-area { display: flex; flex-grow: 1; overflow: hidden; }
        .chat-log { flex: 3; overflow-y: auto; padding: 20px; border-right: 1px solid var(--border-color); }
        
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
        .message.sent .bubble { background-color: var(--user-bg); color: white; border-bottom-right-radius: 4px; }
        .message.received .bubble { background-color: var(--admin-bg); color: black; border-bottom-left-radius: 4px; }
        .message-time { font-size: 10px; color: #999; margin-top: 4px; padding: 0 5px; }
        .message.system { align-items: center; max-width: 100%; }
        .message.system .bubble { background-color: var(--system-bg); color: #6c757d; font-size: 12px; text-align: center; }
        
        /* --- PERUBAHAN DI SINI --- */
        .message.offer .bubble {
            background-color: #fffbe7;
            color: #333;
            max-width: 75%; /* Dikecilkan dari 100% menjadi 75% */
            width: auto; /* Agar lebar menyesuaikan kontennya */
            display: inline-block; /* Membuatnya tidak memenuhi satu baris penuh */
            text-align: left; /* Memastikan teks di dalamnya rata kiri */
            border: 1px solid #ffeccc;
        }
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
            outline: none; border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
        }
        .offer-controls .btn { border: none; font-weight: 500; cursor: pointer; color: white; }
        .btn-primary { background-color: var(--primary-color); }
        .btn-success { background-color: var(--success-color); }
        .btn-danger { background-color: var(--danger-color); }
        .status-info { padding-top:50px; text-align: center; }
        
        #custom-editor { padding: 12px; background: #fff; border-radius: 6px; }
        #custom-editor h6 { font-size: 14px; margin: 0 0 10px 0; color: #212529; font-weight: 500; }
        #custom-editor label { font-size: 13px; margin-right: 15px; display: inline-flex; align-items: center; }
        #negotiation_notes { resize: vertical; min-height: 80px; }
        
        .chat-input { display: flex; padding: 15px; border-top: 1px solid var(--border-color); background: #fff; flex-shrink: 0; }
        .chat-input input { flex-grow: 1; border: 1px solid var(--border-color); border-radius: 20px; padding: 10px 15px; }
        .chat-input button { background: #007bff; color: white; border: none; border-radius: 50%; width: 45px; height: 45px; margin-left: 10px; cursor: pointer; }

    </style>
</head>
<body>
    <div class="chat-container">
        <header class="product-header">
            <a href="history.php"><i class="fas fa-arrow-left"></i></a>
            <img id="productImage" src="image/logo.png" alt="Produk">
            <h3 id="productName">Memuat...</h3>
        </header>
        <div class="main-chat-area">
            <div class="chat-log" id="chatBox"></div>
            <div class="offer-section" id="offerControls"></div>
        </div>
        <div class="chat-input" id="chatInputSection">
            <input type="text" id="messageInput" placeholder="Ketik pesan...">
            <button id="sendMessageBtn"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

<script>
    const transactionId = <?php echo $transaction_id; ?>;
    let transactionDetails = {};
    let availableCustomOptions = [];
    const chatBox = document.getElementById('chatBox');
    const offerControls = document.getElementById('offerControls');
    const messageInput = document.getElementById('messageInput');
    const sendMessageBtn = document.getElementById('sendMessageBtn');

    function formatRupiah(angka) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka); }
    function formatNumberInput(input) { let value = input.value.replace(/[^0-9]/g, ''); input.dataset.realValue = value; input.value = value ? new Intl.NumberFormat('id-ID').format(value) : ''; }

    function renderMessages(messages) {
        chatBox.innerHTML = '';
        if (messages) {
            messages.forEach(msg => {
                const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const messageDiv = document.createElement('div');
                let bubbleHTML = '';
                let messageClass = '';

                if (msg.is_offer == 1) {
                    messageClass = `message offer ${msg.sender_type === 'user' ? 'sent' : 'received'}`;
                    const offerTitle = msg.sender_type === 'user' ? 'Anda mengajukan penawaran:' : 'Admin mengajukan penawaran:';
                    bubbleHTML = `<div class="bubble"><strong>${offerTitle}</strong>
                                <div class="offer-price">${formatRupiah(msg.offer_value)}</div>
                                <div class="offer-description">${msg.message.replace(/\n/g, '<br>')}</div></div>`;
                } else {
                    messageClass = `message ${msg.sender_type === 'user' ? 'sent' : (msg.sender_type === 'admin' ? 'received' : 'system')}`;
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
        if (details.status === 'negotiating') {
            const lastOfferByUser = details.last_offer_by === 'user';
            
            if (lastOfferByUser) {
                html = `<p class="status-info">Menunggu respon dari admin...</p>`;
            } else {
                const lastOfferByAdmin = details.last_offer_by === 'admin';
                const notes = details.negotiation_notes || '';
                let currentCustoms = JSON.parse(details.customization_details || '[]');
                const lastOfferPrice = details.last_offer_price || '';
                const formattedLastOffer = lastOfferPrice ? new Intl.NumberFormat('id-ID').format(lastOfferPrice) : '';
                
                html = `<div class="offer-controls">
                            <h6>Formulir Penawaran</h6>
                            <div id="custom-editor"></div>
                            <textarea id="negotiation_notes" placeholder="Tulis catatan...">${notes}</textarea>
                            <input type="text" id="offerPrice" oninput="formatNumberInput(this)" placeholder="Harga penawaran..." value="${formattedLastOffer}" data-real-value="${lastOfferPrice}">
                            <button class="btn btn-primary" onclick="submitOffer()">Ajukan/Koreksi Penawaran</button>`;

                if (lastOfferByAdmin) {
                    html += `<hr style="width:100%; border-color:#ccc; margin: 5px 0;">
                             <p>Admin menawarkan <strong>${formatRupiah(details.last_offer_price)}</strong></p>
                             <div style="display:flex; gap: 10px; width:100%;">
                                <button class="btn btn-success" onclick="performAction('user_accept_offer')" style="flex-grow:1;">✔️ Setujui</button>
                                <button class="btn btn-danger" onclick="performAction('user_reject_offer')" style="flex-grow:1;">✖️ Tolak</button>
                             </div>`;
                }
                html += `</div>`;
                offerControls.innerHTML = html; 
                renderCustomEditor(currentCustoms); 
                return; 
            }

        } else if (details.status === 'deal') {
            html = `<div class="offer-controls">
                        <h6>DEAL!</h6>
                        <p style="font-size:13px;">Silakan isi alamat pengiriman untuk perhitungan ongkos kirim.</p>
                        <textarea id="shippingAddress" placeholder="Isi alamat lengkap Anda di sini..."></textarea>
                        <button class="btn btn-success" onclick="submitAddress()">Kirim Alamat</button>
                    </div>`;
        } else {
            html = `<p class="status-info">Status: ${details.status.replace('_', ' ').toUpperCase()}</p>`;
        }

        if (details.status !== 'negotiating' && details.status !== 'deal') {
             document.getElementById('chatInputSection').style.display = 'none';
        } else {
             document.getElementById('chatInputSection').style.display = 'flex';
        }
        offerControls.innerHTML = html;
    }

    function renderCustomEditor(currentCustoms) {
        const editor = document.getElementById('custom-editor');
        if (!editor) return;

        editor.innerHTML = '<h6>Pilihan Kustom Anda:</h6>';
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

    async function fetchData() {
        let currentOfferPrice = null, currentOfferPriceReal = null, currentNotes = null;
        let currentCustomSelections = {};
        const offerInputEl = document.getElementById('offerPrice');
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
                const firstFetch = !transactionDetails.product_id;
                transactionDetails = data.details;

                if (firstFetch && transactionDetails.product_id) {
                    availableCustomOptions = [];
                    const customResp = await fetch(`api_custom.php?action=get_product_customs&product_id=${transactionDetails.product_id}`);
                    const customData = await customResp.json();
                    if (customData.status === 'success') availableCustomOptions = customData.customizations;
                }

                document.getElementById('productName').textContent = transactionDetails.product_name;
                const images = JSON.parse(transactionDetails.product_image || '[]');
                document.getElementById('productImage').src = images.length > 0 ? images[0] : 'image/logo.png';
                renderMessages(transactionDetails.chat_logs);
                renderOfferUI(transactionDetails);

                const newOfferInput = document.getElementById('offerPrice');
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

    function submitOffer() {
        const priceInput = document.getElementById('offerPrice');
        const notesInput = document.getElementById('negotiation_notes');
        const offer_price = priceInput.dataset.realValue;
        
        let selectedCustoms = [];
        document.querySelectorAll('#custom-editor input[type="radio"]:checked').forEach(radio => {
            if (radio.value !== "null") {
                selectedCustoms.push(JSON.parse(radio.value));
            }
        });

        if (!offer_price || offer_price <= 0) {
            alert('Masukkan harga penawaran yang valid.');
            return;
        }
        performAction('submit_offer', { 
            offer_price, 
            negotiation_notes: notesInput.value,
            customization_details: selectedCustoms
        });
    }

    async function performAction(action, data = {}) {
        try {
            const response = await fetch('api_transaction.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ action, transaction_id: transactionId, ...data }) });
            if (!response.ok) { const err = await response.json(); throw new Error(err.message); }
            await fetchData();
        } catch(error) { alert(`Terjadi Kesalahan: ${error.message}`); }
    }

    function submitAddress() { 
        const address = document.getElementById('shippingAddress').value.trim(); 
        if (!address) { alert('Alamat pengiriman tidak boleh kosong.'); return; } 
        performAction('submit_shipping_address', { address }); 
    }
    
    sendMessageBtn.addEventListener('click', () => { 
        const message = messageInput.value.trim(); 
        if (message) { 
            performAction('send_chat', { message }); 
            messageInput.value = ''; 
        } 
    });
    
    messageInput.addEventListener('keypress', (e) => { 
        if (e.key === 'Enter') sendMessageBtn.click(); 
    });
    
    fetchData();
    setInterval(fetchData, 8000);
</script>
</body>
</html>