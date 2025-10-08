<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Kustomisasi</title>
    <link rel="icon" href="image/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root { --primary-color: #3498db; --secondary-color: #2c3e50; --success-color: #2ecc71; --danger-color: #e74c3c; --info-color: #9b59b6; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f9; display: flex; }
        #sidebar { width: 250px; background-color: var(--secondary-color); color: white; height: 100vh; position: fixed; display: flex; flex-direction: column; }
        #sidebar .logo { text-align: center; padding: 20px 0; }
        #sidebar .logo img { width: 100px; }
        #sidebar ul { list-style: none; flex-grow: 1; }
        #sidebar ul li a { color: white; text-decoration: none; padding: 15px 20px; display: block; transition: background-color 0.2s; }
        #sidebar ul li a:hover, #sidebar ul li a.active { background-color: #212f3d; }
        #content { margin-left: 250px; padding: 30px; width: calc(100% - 250px); }
        h1 { color: var(--secondary-color); margin-bottom: 20px; }
        .custom-container { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; }
        .btn-primary { background-color: var(--primary-color); }
        .btn-success { background-color: var(--success-color); }
        .btn-danger { background-color: var(--danger-color); font-size: 12px; padding: 5px 8px; }
        .btn-info { background-color: var(--info-color); font-size: 12px; padding: 5px 8px; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .type-card { border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; }
        .type-header { background: #f9fafb; padding: 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e0e0e0; }
        .type-header input { font-size: 18px; font-weight: bold; border: none; background: transparent; padding: 5px; width: 70%;}
        .type-options { padding: 15px; }
        .option-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px dashed #eee; gap: 10px; }
        .option-item:last-child { border-bottom: none; }
        .option-item input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .option-item input[type="text"] { flex-grow: 1; }
        .option-item input[type="number"] { width: 120px; }
        .modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #fff; margin: 5% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 700px; max-height: 80vh; display:flex; flex-direction:column; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 15px; margin-bottom: 15px; }
        .modal-body { overflow-y: auto; flex-grow: 1; }
        .product-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px; }
        .product-list label { display: block; padding: 10px; border-radius: 5px; border: 1px solid #eee; cursor: pointer; transition: background-color 0.2s, border-color 0.2s; }
        .product-list label:hover { background-color: #f5f5f5; }
        .product-list input:checked + span { font-weight: bold; color: var(--primary-color); }
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
            <li><a href="admin-custom.php" class="active"><i class="fas fa-cogs"></i> Kelola Kustomisasi</a></li>
            <li><a href="admin-pesan.php"><i class="fas fa-envelope"></i> Pesan Kontak</a></li>
            <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div id="content">
        <h1>Kelola Opsi Kustomisasi</h1>
        <div class="custom-container">
            <div class="header-actions">
                <span>Atur jenis, opsi, dan kaitkan opsi ke produk yang relevan.</span>
                <button class="btn btn-primary" onclick="addType()">+ Tambah Jenis Baru</button>
            </div>
            <div id="customList"></div>
             <button class="btn btn-success" style="width: 100%; padding: 15px; margin-top: 20px;" onclick="saveAll()">Simpan Semua Perubahan Teks & Harga</button>
        </div>
    </div>
    
    <div id="productLinkModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Kaitkan Produk ke Opsi</h2>
                <span onclick="closeModal()" style="font-size: 24px; cursor: pointer; padding: 5px;">&times;</span>
            </div>
            <div id="modalBody" class="modal-body">
                <p>Memuat produk...</p>
            </div>
            <div class="modal-footer" style="margin-top:20px; text-align:right;">
                <button class="btn btn-success" id="saveProductLinksBtn">Simpan Asosiasi Produk</button>
            </div>
        </div>
    </div>

<script>
let customData = [];

document.addEventListener('DOMContentLoaded', loadCustomizations);

async function loadCustomizations() {
    try {
        const response = await fetch('api_custom.php?action=get_all_customs');
        const data = await response.json();
        if(data.status === 'success') {
            customData = data.types.map(type => ({ ...type, options: type.options || [] }));
            render();
        } else {
            alert('Gagal memuat data kustomisasi: ' + data.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan saat memuat data.');
    }
}

function render() {
    const container = document.getElementById('customList');
    container.innerHTML = '';
    customData.forEach((type, typeIndex) => {
        container.innerHTML += `
            <div class="type-card" data-id="${type.id}">
                <div class="type-header">
                    <input type="text" value="${type.name}" onchange="updateTypeName(${typeIndex}, this.value)" placeholder="Nama Jenis Kustom">
                    <div>
                        <button class="btn btn-primary" style="margin-right:5px;" onclick="addOption(${typeIndex})">+ Tambah Opsi</button>
                        <button class="btn btn-danger" onclick="removeType(${typeIndex})">Hapus Jenis</button>
                    </div>
                </div>
                <div class="type-options">
                    ${type.options.map((opt, optIndex) => `
                        <div class="option-item" data-id="${opt.id}">
                            <input type="text" value="${opt.name}" placeholder="Nama Opsi" onchange="updateOption(${typeIndex}, ${optIndex}, 'name', this.value)">
                            <input type="number" value="${opt.price}" placeholder="Harga" onchange="updateOption(${typeIndex}, ${optIndex}, 'price', this.value)">
                            <div>
                                <button class="btn btn-info" onclick="openProductLinkModal(${opt.id}, '${opt.name}')" ${opt.id < 0 ? 'disabled title="Simpan dulu untuk bisa mengaitkan produk"' : ''}>Kaitkan Produk</button>
                                <button class="btn btn-danger" onclick="removeOption(${typeIndex}, ${optIndex})">×</button>
                            </div>
                        </div>
                    `).join('') || '<p style="padding:10px; color:#888;">Belum ada opsi untuk jenis ini.</p>'}
                </div>
            </div>`;
    });
}

function addType() { customData.push({ id: Date.now() * -1, name: '', options: [] }); render(); }
function removeType(typeIndex) { if(confirm('Anda yakin ingin menghapus jenis ini dan semua opsinya?')) { customData.splice(typeIndex, 1); render(); } }
function updateTypeName(typeIndex, newName) { customData[typeIndex].name = newName; }
function addOption(typeIndex) { customData[typeIndex].options.push({ id: Date.now() * -1, name: '', price: 0, type_id: customData[typeIndex].id }); render(); }
function removeOption(typeIndex, optIndex) { if(confirm('Hapus opsi ini?')) { customData[typeIndex].options.splice(optIndex, 1); render(); } }
function updateOption(typeIndex, optIndex, field, value) { customData[typeIndex].options[optIndex][field] = value; }

async function saveAll() {
    if (confirm('Simpan semua perubahan pada nama jenis, nama opsi, dan harga?')) {
        try {
            const response = await fetch('api_custom.php?action=save_customs', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ types: customData })
            });
            const result = await response.json();
            alert(result.message);
            if(result.status === 'success') {
                loadCustomizations();
            }
        } catch (error) { alert('Terjadi kesalahan saat menyimpan.'); }
    }
}

const modal = document.getElementById('productLinkModal');
const modalBody = document.getElementById('modalBody');
const modalTitle = document.getElementById('modalTitle');
const saveLinksBtn = document.getElementById('saveProductLinksBtn');

function closeModal() {
    modal.style.display = 'none';
}

async function openProductLinkModal(optionId, optionName) {
    if (!optionId || optionId < 0) {
        alert("Harap simpan opsi ini terlebih dahulu sebelum mengaitkan produk.");
        return;
    }
    
    modalTitle.textContent = `Kaitkan Produk ke Opsi: "${optionName}"`;
    modalBody.innerHTML = '<p>Memuat produk...</p>';
    modal.style.display = 'block';

    try {
        const response = await fetch(`api_custom.php?action=get_products_for_option&option_id=${optionId}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            let productHTML = '<form id="productLinkForm" class="product-list">';
            data.all_products.forEach(product => {
                // **PERBAIKAN DI SINI:** Memastikan perbandingan antara number dan number
                // Tipe data dari JSON seringkali sudah benar, namun untuk amannya kita konversi keduanya
                const isChecked = data.associated_ids.includes(Number(product.id));
                productHTML += `
                    <label>
                        <input type="checkbox" name="product_ids[]" value="${product.id}" ${isChecked ? 'checked' : ''}>
                        <span>${product.tipe}</span>
                    </label>
                `;
            });
            productHTML += '</form>';
            modalBody.innerHTML = productHTML;
            
            saveLinksBtn.onclick = () => saveProductLinks(optionId);
        } else {
            modalBody.innerHTML = `<p style="color:red;">${data.message}</p>`;
        }
    } catch (error) {
        modalBody.innerHTML = `<p style="color:red;">Gagal mengambil data produk.</p>`;
    }
}

async function saveProductLinks(optionId) {
    if (!confirm("Anda yakin ingin menyimpan perubahan asosiasi produk ini?\n\nProduk yang tidak dicentang akan dihapus kaitannya.")) {
        return;
    }

    const form = document.getElementById('productLinkForm');
    const selectedProductIds = Array.from(form.querySelectorAll('input:checked')).map(input => input.value);

    try {
        const response = await fetch('api_custom.php?action=save_products_for_option', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ option_id: optionId, product_ids: selectedProductIds })
        });
        const result = await response.json();
        alert(result.message);
        if (result.status === 'success') {
            closeModal();
        }
    } catch (error) {
        alert("Gagal menyimpan asosiasi produk.");
    }
}

window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}
</script>

</body>
</html>