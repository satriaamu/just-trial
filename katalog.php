<?php
session_start();
// Koneksi ke database
require_once 'config.php';
$conn = getMysqliConnection();

// Query untuk mengambil semua produk dari katalog
$sql = "SELECT * FROM katalog";
$result = $conn->query($sql);

$is_admin_logged_in = isset($_SESSION['admin_id']);

// Mengambil opsi unik untuk filter dari database secara dinamis
$bahan_utama_options = [];
$konstruksi_options = [];
$kamar_options = [];

if ($result->num_rows > 0) {
    // Memundurkan pointer hasil query ke awal
    $result->data_seek(0);
    while($row = $result->fetch_assoc()) {
        if (!empty($row['bahan_utama']) && !in_array($row['bahan_utama'], $bahan_utama_options)) {
            $bahan_utama_options[] = $row['bahan_utama'];
        }
        if (!empty($row['konstruksi']) && !in_array($row['konstruksi'], $konstruksi_options)) {
            $konstruksi_options[] = $row['konstruksi'];
        }
        preg_match('/(\d+)/', $row['jumlah_kamar'], $matches);
        if (isset($matches[1])) {
            $kamar = (int)$matches[1];
            if ($kamar > 0 && !in_array($kamar, $kamar_options)) {
                $kamar_options[] = $kamar;
            }
        }
    }
}
sort($kamar_options);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Rumah Panggung 360°</title>
    <link rel="icon" href="image/logo.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/three@0.105.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/panolens@0.11.0/build/panolens.min.js"></script>
    <style>
        /* General Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background-color: #f4f4f4; color: #333; }
        
        /* Header */
        header { display: flex; justify-content: space-between; align-items: center; padding: 0 100px; background-color: #2c3e50; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        header .logo img { height: 100px; }
        header nav ul { list-style: none; display: flex; gap: 10px; }
        .button-18 { align-items: center; background-color: #0A66C2; border: 0; border-radius: 100px; color: #ffffff; cursor: pointer; display: inline-flex; font-size: 14px; font-weight: 600; justify-content: center; line-height: 20px; min-height: 40px; padding: 0 30px; text-decoration: none; }
        
        /* Main Content */
        main { padding: 40px 20px; }
        main h1 { text-align: center; font-size: 32px; color: #2c3e50; margin-bottom: 20px; }
        
        /* Filter Container */
        .filter-container { max-width: 1200px; margin: 0 auto 40px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: grid; grid-template-columns: 1fr; gap: 20px; align-items: center; }
        .filter-group { position: relative; display: flex; flex-direction: column; }
        .filter-group label { font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #555; }
        .filter-group input { padding: 12px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px; }
        
        /* Catalog Grid */
        .catalog-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto; align-items: start; }
        .catalog-item { background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: column; transition: transform 0.3s, box-shadow 0.3s; }
        .catalog-item:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        
        .preview-container { width: 100%; height: 250px; background-size: cover; background-position: center center; cursor: pointer; position: relative; display: flex; justify-content: center; align-items: center; overflow: hidden; background-color: #eee; }
        .preview-container::after { content: "Lihat Tur 360°"; position: absolute; color: white; background-color: rgba(0,0,0,0.5); padding: 10px 20px; border-radius: 5px; opacity: 0; transition: opacity 0.3s; }
        .preview-container:hover::after { opacity: 1; }
        
        .item-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .item-content h2 { font-size: 22px; margin-bottom: 10px; color: #2c3e50; }
        
        .details-section { text-align: left; font-size: 14px; color: #555; line-height: 1.6; margin-bottom: 15px; }
        .details-toggle { max-height: 120px; overflow: hidden; transition: max-height 0.5s ease-in-out; }
        .details-toggle.expanded { max-height: 1000px; }
        .toggle-details-btn { color: #007bff; cursor: pointer; font-weight: bold; display: inline-block; margin-top: 10px; }
        
        .item-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #ddd;
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: auto;
        }
        .item-footer.has-multiple-actions {
             grid-template-columns: 1fr 1fr;
        }
        .custom-button {
            padding: 12px; font-size: 14px; border-radius: 8px; cursor: pointer; font-weight: bold;
            display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;
            border: 2px solid transparent; transition: all 0.2s ease-in-out; text-align: center;
        }
        .btn-primary { background-color: #007bff; color: white; border-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; color: white; border-color: #6c757d; }
        .btn-secondary:hover { background-color: #5a6268; }
        .btn-whatsapp { background-color: #25D366; color: white; border-color: #25D366; }
        .btn-whatsapp:hover { background-color: #128C7E; }
        .custom-button:disabled { background-color: #6c757d; color: white; cursor: not-allowed; opacity: 0.7; }
        .btn-full-width { grid-column: 1 / -1; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: hidden; background-color: rgba(0,0,0,0.9); }
        .modal-content { background-color: #111; width: 100%; height: 100%; position: relative; }
        .close-button { color: white; position: absolute; top: 15px; right: 35px; font-size: 40px; font-weight: bold; z-index: 1001; cursor: pointer; }
        #panoramaViewer { width: 100%; height: 100%; }
        
        .stock-status { font-weight: bold; }
        .stock-status.available { color: #28a745; }
        .stock-status.out-of-stock { color: #e74c3c; }
        #no-results { text-align: center; padding: 50px; font-size: 18px; color: #777; display: none; }
    </style>
</head>
<body>
    <header>
        <div class="logo"> <a href="index.php"><img src="image/logo-showcase.png" alt="Logo"></a> </div>
        <nav>
            <ul>
                <li><a href="index.php" class="button-18"><i class="fas fa-home"></i>&nbsp; Beranda</a></li>
                <li><a href="profil.php" class="button-18"><i class="fas fa-user"></i>&nbsp; Profil</a></li>
                <li><a href="katalog.php" class="button-18"><i class="fas fa-cogs"></i>&nbsp; Katalog</a></li>
                <li><a href="daftar-pembelian.php" class="button-18"><i class="fas fa-receipt"></i>&nbsp; Pembelian Saya</a></li>
                <?php if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])): ?>
                    <li><a href="logout.php" class="button-18"><i class="fas fa-sign-out-alt"></i>&nbsp; Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="button-18"><i class="fas fa-sign-in-alt"></i>&nbsp; Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Katalog Rumah Panggung</h1>

        <div class="filter-container">
            <div class="filter-group">
                <label for="searchInput"><i class="fas fa-search"></i> Cari Produk</label>
                <input type="text" id="searchInput" placeholder="Ketik tipe, bahan, atau deskripsi produk...">
            </div>
        </div>
        <div class="catalog-container">
            <?php if ($result->num_rows > 0): ?>
                <?php $result->data_seek(0); ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php 
                        $images_raw = json_decode($row['gambar'] ?? '[]', true);
                        $images = [];
                        if (is_array($images_raw)) { foreach ($images_raw as $img) { $images[] = str_replace('\\', '/', $img); } }
                        $preview_image_url = !empty($images) ? htmlspecialchars($images[0]) : 'image/default-placeholder.jpg';
                        $kontak = $row['nomor_kontak'] ?? '6281234567890';
                        $whatsapp_url = "https://wa.me/" . preg_replace('/[^0-9]/', '', $kontak) . "?text=" . urlencode("Halo, saya tertarik dengan produk Rumah Panggung tipe: " . $row['tipe']);
                        $stock = $row['stock'] ?? 0;
                    ?>
                    <div class="catalog-item">
                        <div class="preview-container" style="background-image: url('<?php echo $preview_image_url; ?>');" data-images='<?php echo htmlspecialchars(json_encode($images)); ?>' onclick="openPanoramaModal(this)"></div>
                        <div class="item-content">
                            <h2><?php echo htmlspecialchars($row['tipe']); ?></h2>
                            <div class="details-section">
                                <div class="details-toggle">
                                    <?php
                                    $details_to_show = [
                                        'Harga Awal' => 'Rp ' . number_format($row['harga'], 0, ',', '.'),
                                        'Bahan Utama' => $row['bahan_utama'],
                                        'Struktur' => $row['struktur'],
                                        'Konstruksi' => $row['konstruksi'],
                                        'Rangka Atap' => $row['rangka_atap'],
                                        'Lantai & Dinding' => $row['lantai_dinding'],
                                        'Jumlah Kamar' => $row['jumlah_kamar'],
                                        'Teras Depan' => $row['teras_depan'],
                                        'Ventilasi & Jendela' => $row['ventilasi_jendela'],
                                        'Estimasi Pengerjaan' => $row['pengerjaan'],
                                        'Deskripsi' => nl2br(htmlspecialchars($row['deskripsi'])),
                                        'Fitur Tambahan' => nl2br(htmlspecialchars($row['fitur_tambahan']))
                                    ];
                                    ?>
                                    <?php if ($stock > 0): ?>
                                        <p class="stock-status available">Stok Tersedia: <?php echo $stock; ?> unit</p>
                                    <?php else: ?>
                                        <p class="stock-status out-of-stock">Stok Habis</p>
                                    <?php endif; ?>
                                    
                                    <?php foreach ($details_to_show as $label => $value): ?>
                                        <?php if (!empty(trim(strip_tags((string)$value)))): ?>
                                            <p><strong><?php echo $label; ?>:</strong> <?php echo $value; ?></p>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <span class="toggle-details-btn">selengkapnya</span>
                            </div>
                            <p><strong>Kontak Penjual:</strong> <?php echo htmlspecialchars($kontak); ?></p>
                        </div>
                        <div class="item-footer has-multiple-actions">
                            <?php if ($stock > 0 && !$is_admin_logged_in): ?>
                                <a href="simulasi.php?product_id=<?php echo $row['id']; ?>" class="custom-button btn-primary btn-full-width"><i class="fas fa-calculator"></i> Simulasi & Penawaran Kustom</a>
                                <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="custom-button btn-whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                                <button class="custom-button btn-secondary" onclick="startChat(this, <?php echo $row['id']; ?>)"><i class="fas fa-comments"></i> Penawaran Standar</button>
                            <?php elseif ($stock <= 0): ?>
                                <button class="custom-button btn-full-width" disabled>Stok Habis</button>
                            <?php else: ?>
                                <button class="custom-button btn-full-width" disabled>Aksi tidak tersedia untuk Admin</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
        
        <div id="no-results">
            <p>Produk yang Anda cari tidak ditemukan.</p>
        </div>
        </main>
    <div id="panoramaModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closePanoramaModal()">&times;</span>
            <div id="panoramaViewer"></div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logika untuk tombol "selengkapnya"
            document.querySelectorAll('.toggle-details-btn').forEach(btn => {
                const content = btn.previousElementSibling;
                if (content.scrollHeight <= content.clientHeight) {
                    btn.style.display = 'none';
                }
                btn.addEventListener('click', function() {
                    content.classList.toggle('expanded');
                    this.textContent = content.classList.contains('expanded') ? 'Lihat Ringkas' : 'Selengkapnya';
                });
            });

            // **START: Logika Pencarian**
            const searchInput = document.getElementById('searchInput');
            const catalogItems = document.querySelectorAll('.catalog-item');
            const noResultsDiv = document.getElementById('no-results');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleItemsCount = 0;

                catalogItems.forEach(item => {
                    const itemText = item.textContent.toLowerCase();
                    
                    if (itemText.includes(searchTerm)) {
                        item.style.display = 'flex'; // Tampilkan item jika cocok
                        visibleItemsCount++;
                    } else {
                        item.style.display = 'none'; // Sembunyikan item jika tidak cocok
                    }
                });
                
                // Tampilkan atau sembunyikan pesan "tidak ada hasil"
                if (visibleItemsCount === 0) {
                    noResultsDiv.style.display = 'block';
                } else {
                    noResultsDiv.style.display = 'none';
                }
            });
            // **END: Logika Pencarian**
        });

        let currentViewer = null;
        function openPanoramaModal(element) {
            const images = JSON.parse(element.getAttribute('data-images'));
            if (!images || images.length < 1) { alert('Gambar panorama tidak tersedia.'); return; }
            const modal = document.getElementById('panoramaModal');
            const viewerContainer = document.getElementById('panoramaViewer');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (currentViewer) { currentViewer.destroy(); }
            viewerContainer.innerHTML = '';
            currentViewer = new PANOLENS.Viewer({ container: viewerContainer, autoRotate: true, autoRotateSpeed: 0.3, controlBar: true });
            const panoramas = images.map(src => new PANOLENS.ImagePanorama(src));
            for (let i = 0; i < panoramas.length; i++) {
                panoramas[i].link(panoramas[(i + 1) % panoramas.length], new THREE.Vector3(4000, -1000, -2000));
            }
            panoramas.forEach(p => currentViewer.add(p));
            currentViewer.setPanorama(panoramas[0]);
        }
        function closePanoramaModal() {
            const modal = document.getElementById('panoramaModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            if (currentViewer) { currentViewer.destroy(); currentViewer = null; }
            document.getElementById('panoramaViewer').innerHTML = '';
        }
        
        function startChat(button, productId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert("Anda harus login untuk memulai penawaran.");
                window.location.href = 'login.php';
                return;
            <?php endif; ?>
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memulai...';
            // Penawaran standar mengirimkan detail kustomisasi kosong
            fetch('api_transaction.php', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify({ action: 'initiate', product_id: productId, customization_details: '{}' }) 
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.transaction_id) {
                    window.location.href = `negosiasi.php?transaction_id=${data.transaction_id}`;
                } else {
                    alert('Gagal memulai sesi: ' + (data.message || 'Error'));
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-comments"></i> Penawaran Standar';
                }
            });
        }
    </script>
</body>
</html>