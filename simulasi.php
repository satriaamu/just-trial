<?php
session_start();
require_once 'config.php';
$conn = getMysqliConnection();

$product_id = intval($_GET['product_id'] ?? 0);
if ($product_id === 0) {
    header("Location: katalog.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM katalog WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if(!$product) {
    header("Location: katalog.php");
    exit();
}
$images = json_decode($product['gambar'] ?? '[]', true);
$preview_image_url = !empty($images) ? htmlspecialchars(str_replace('\\', '/', $images[0])) : 'image/default-placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi Harga Custom - <?php echo htmlspecialchars($product['tipe']); ?></title>
    <link rel="icon" href="image/logo.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f4f4; color: #333; margin: 0; }
        .sim-container { display: flex; max-width: 1200px; margin: 30px auto; gap: 30px; flex-wrap: wrap; }
        .sim-product, .sim-options { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .sim-product { flex: 1; min-width: 300px; }
        .sim-options { flex: 2; min-width: 400px; }
        .sim-product img { width: 100%; border-radius: 8px; margin-bottom: 20px; }
        .custom-type-group { margin-bottom: 20px; }
        .custom-type-group h3 { border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; font-size: 18px; }
        .option-label { display: block; margin-bottom: 10px; cursor: pointer; background-color: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #dee2e6; }
        .option-label.selected { border-color: #007bff; background-color: #e7f3ff; }
        
        /* Preview Section */
        .preview-section { margin-top: 25px; padding: 15px; border-radius: 8px; background-color: #f8f9fa; border: 1px solid #e9ecef; }
        .preview-section h4 { margin: 0 0 10px 0; }
        #previewList { list-style: none; padding-left: 0; margin-bottom: 10px; font-size: 14px; }
        #previewList li { padding: 5px 0; }
        #previewNotes { font-size: 14px; color: #555; font-style: italic; }

        .notes-section textarea, .offer-section input { width: 100%; padding: 10px; margin-top: 8px; border-radius: 5px; border: 1px solid #ccc; }
        .total-price-display { font-size: 24px; font-weight: 500; color: #333; text-align: right; margin-top: 20px; }
        #startNegoBtn { width: 100%; margin-top: 20px; padding: 15px; font-size: 18px; background-color: #2980b9; color: white; border: none; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="sim-container">
        <div class="sim-product">
            <h2><?php echo htmlspecialchars($product['tipe']); ?></h2>
            <img src="<?php echo $preview_image_url; ?>" alt="<?php echo htmlspecialchars($product['tipe']); ?>">
            <p><strong>Harga Dasar:</strong> <span id="basePriceDisplay"></span></p>
            <span id="basePriceValue" style="display:none;"><?php echo $product['harga']; ?></span>
        </div>
        <div class="sim-options">
            <h2>Pilih Opsi Kustomisasi</h2>
            <div id="customizationOptionsContainer"><p>Memuat opsi...</p></div>
            
            <div class="notes-section">
                <label for="negotiation_notes"><strong>Catatan Tambahan (Opsional)</strong></label>
                <textarea id="negotiation_notes" placeholder="Contoh: Saya ingin jendela di sisi kanan diperbanyak..."></textarea>
            </div>
            
            <div class="preview-section">
                <h4>Preview Pilihan Anda</h4>
                <ul id="previewList"></ul>
                <p><strong>Catatan:</strong> <span id="previewNotes">Tidak ada catatan.</span></p>
            </div>

            <div class="total-price-display">
                Total Estimasi: <span id="totalPrice"></span>
            </div>

            <div class="offer-section">
                <label for="initial_offer_price"><strong>Harga Penawaran Awal Anda</strong></label>
                <input type="text" id="initial_offer_price" oninput="formatNumberInput(this)" placeholder="Masukkan harga yang Anda tawarkan">
            </div>

            <button id="startNegoBtn" onclick="startNegotiation()">Mulai Penawaran</button>
        </div>
    </div>

    <script>
        const productId = <?php echo $product_id; ?>;
        const basePrice = parseFloat(document.getElementById('basePriceValue').textContent);
        let selectedCustomizations = [];

        function formatRupiah(angka) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka); }
        function formatNumberInput(input) { let value = input.value.replace(/[^0-9]/g, ''); input.dataset.realValue = value; input.value = value ? new Intl.NumberFormat('id-ID').format(value) : ''; }

        document.addEventListener('DOMContentLoaded', async function() {
            document.getElementById('basePriceDisplay').textContent = formatRupiah(basePrice);
            document.getElementById('negotiation_notes').addEventListener('input', updatePreview);
            try {
                const response = await fetch(`api_custom.php?action=get_product_customs&product_id=${productId}`);
                const data = await response.json();
                if(data.status === 'success') {
                    renderOptions(data.customizations);
                    updateTotalPriceAndPreview();
                } else {
                    document.getElementById('customizationOptionsContainer').innerHTML = `<p style="color:red;">Gagal memuat opsi.</p>`;
                }
            } catch (error) {
                 document.getElementById('customizationOptionsContainer').innerHTML = '<p style="color:red;">Terjadi kesalahan jaringan.</p>';
            }
        });

        function renderOptions(customizations) {
            const container = document.getElementById('customizationOptionsContainer');
            container.innerHTML = '';
            if (customizations.length === 0) {
                container.innerHTML = '<p>Tidak ada opsi kustomisasi.</p>';
                return;
            }
            customizations.forEach(type => {
                let optionsHTML = `<div class="custom-type-group"><h3>${type.name}</h3>`;
                optionsHTML += `<label class="option-label selected"><input type="radio" name="type_${type.id}" value="null" data-name="Standar" data-price="0" checked> Standar</label>`;
                type.options.forEach(option => {
                    const optionData = JSON.stringify({ name: `${option.name}`, price: option.price }); // Disederhanakan
                    optionsHTML += `<label class="option-label">
                        <input type="radio" name="type_${type.id}" value='${optionData}'>
                        ${option.name} (+${formatRupiah(option.price)})
                    </label>`;
                });
                optionsHTML += `</div>`;
                container.innerHTML += optionsHTML;
            });
            
            container.addEventListener('change', (event) => {
                if(event.target.type === 'radio') {
                    event.target.closest('.custom-type-group').querySelectorAll('.option-label').forEach(label => label.classList.remove('selected'));
                    event.target.closest('.option-label').classList.add('selected');
                    updateTotalPriceAndPreview();
                }
            });
        }
        
        function updateTotalPriceAndPreview() {
            let customCost = 0;
            selectedCustomizations = [];
            document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                if (radio.value !== "null") {
                    const optionData = JSON.parse(radio.value);
                    customCost += parseFloat(optionData.price);
                    selectedCustomizations.push(optionData);
                }
            });
            const totalEstimasi = basePrice + customCost;
            document.getElementById('totalPrice').textContent = formatRupiah(totalEstimasi);
            
            // Set default offer price
            const offerInput = document.getElementById('initial_offer_price');
            offerInput.value = new Intl.NumberFormat('id-ID').format(totalEstimasi);
            offerInput.dataset.realValue = totalEstimasi;

            updatePreview();
        }

        function updatePreview() {
            const previewList = document.getElementById('previewList');
            const previewNotes = document.getElementById('previewNotes');
            const notes = document.getElementById('negotiation_notes').value;

            previewList.innerHTML = '';
            if (selectedCustomizations.length > 0) {
                selectedCustomizations.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = `• ${item.name}`;
                    previewList.appendChild(li);
                });
            } else {
                previewList.innerHTML = '<li>• Pilihan Standar</li>';
            }
            previewNotes.textContent = notes ? notes : 'Tidak ada catatan.';
        }

        async function startNegotiation() {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert("Anda harus login untuk memulai penawaran.");
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            const btn = document.getElementById('startNegoBtn');
            btn.disabled = true;
            btn.innerHTML = 'Memulai...';
            
            const notes = document.getElementById('negotiation_notes').value;
            const offerPriceInput = document.getElementById('initial_offer_price');
            const offerPrice = offerPriceInput.dataset.realValue;

            if (!offerPrice || offerPrice <= 0) {
                alert("Harap masukkan harga penawaran awal yang valid.");
                btn.disabled = false;
                btn.innerHTML = 'Mulai Penawaran';
                return;
            }

            try {
                const initResponse = await fetch('api_transaction.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'initiate', product_id: productId }) });
                const initData = await initResponse.json();
                if (initData.status !== 'success') throw new Error(initData.message);
                
                const offerPayload = {
                    action: 'submit_offer',
                    transaction_id: initData.transaction_id,
                    offer_price: offerPrice,
                    customization_details: selectedCustomizations,
                    negotiation_notes: notes
                };
                const offerResponse = await fetch('api_transaction.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(offerPayload) });
                const offerData = await offerResponse.json();
                if (offerData.status !== 'success') throw new Error(offerData.message);

                window.location.href = `negosiasi.php?transaction_id=${initData.transaction_id}`;

            } catch (error) {
                alert('Gagal memulai sesi: ' + error.message);
                btn.disabled = false;
                btn.innerHTML = 'Mulai Penawaran';
            }
        }
    </script>
</body>
</html>