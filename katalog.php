<?php
session_start();
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "mokobang");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
// FIX: Mengatur charset koneksi untuk memperbaiki tampilan nama produk
$conn->set_charset("utf8mb4");

// Query untuk mengambil semua produk dari katalog
$sql = "SELECT * FROM katalog";
$result = $conn->query($sql);

$is_admin_logged_in = isset($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Rumah Panggung</title>
    <link rel="icon" href="image/logo.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/three@0.105.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/panolens@0.11.0/build/panolens.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background-color: #f4f4f4; color: #333; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 0 100px; background-color: #2c3e50; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        header .logo img { height: 100px; }
        header nav ul { list-style: none; display: flex; gap: 10px; }
        .button-18 { align-items: center; background-color: #0A66C2; border: 0; border-radius: 100px; color: #ffffff; cursor: pointer; display: inline-flex; font-size: 14px; font-weight: 600; justify-content: center; line-height: 20px; min-height: 40px; padding: 0 30px; text-decoration: none; transition: background-color 0.2s; }
        .button-18:hover { background-color: #16437E; }
        main { padding: 40px 20px; }
        main h1 { text-align: center; font-size: 32px; color: #2c3e50; margin-bottom: 40px; }
        .catalog-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto; }
        .catalog-item { background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: column; transition: transform 0.3s, box-shadow 0.3s; }
        .catalog-item:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        
        /* MODIFIKASI: Container untuk gambar preview */
        .preview-container { 
            width: 100%; 
            height: 250px; 
            background-size: cover; /* Memastikan gambar mengisi container */
            background-position: center center; /* Memusatkan gambar */
            background-repeat: no-repeat;
            cursor: pointer; 
            transition: opacity 0.3s ease;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden; /* Pastikan tidak ada scroll */
        }
        .preview-container::after {
            content: "\f065"; /* FontAwesome icon untuk "Expand" */
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            color: white;
            font-size: 3em;
            text-shadow: 0 0 10px rgba(0,0,0,0.7);
            position: absolute;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .preview-container:hover {
            opacity: 0.8;
        }
        .preview-container:hover::after {
            opacity: 1;
        }

        .item-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .item-content h2 { font-size: 22px; margin-bottom: 10px; color: #2c3e50; }
        .item-content p { font-size: 14px; color: #555; line-height: 1.6; margin-bottom: 5px; }
        .item-content strong { color: #333; }
        .item-footer { padding: 20px; background-color: #f8f9fa; border-top: 1px solid #ddd; display: flex; flex-direction: column; gap: 10px; }
        .custom-button { background-color: #007bff; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; width: 100%; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; }
        .custom-button:hover { background-color: #0056b3; }
        .custom-button:disabled { background-color: #6c757d; cursor: not-allowed; opacity: 0.7; }
        .whatsapp-button { background-color: #25D366; }
        .whatsapp-button:hover { background-color: #128C7E; }
        footer { display: flex; justify-content: space-between; align-items: flex-start; background-color: #333; color: white; padding: 40px 100px; margin-top: 40px; flex-wrap: wrap; }
        /* Tambahan CSS untuk Modal */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.8); /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 0;
            border: 1px solid #888;
            width: 90%;
            max-width: 900px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
            animation-name: animatetop;
            animation-duration: 0.4s;
            position: relative;
            height: 80vh; /* Tinggi modal */
        }
        @keyframes animatetop {
            from {top: -300px; opacity: 0}
            to {top: 0; opacity: 1}
        }
        .close-button {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 25px;
            font-size: 35px;
            font-weight: bold;
            z-index: 1001; /* Pastikan di atas viewer */
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        #panoramaViewer {
            width: 100%;
            height: 100%;
            background-color: black;
        }
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
        <div class="catalog-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php 
                        $images = json_decode($row['gambar'] ?? '[]', true); 
                        $preview_image_url = !empty($images) ? htmlspecialchars($images[0]) : 'image/default-placeholder.jpg'; // Gambar pertama sebagai preview
                        $kontak = $row['nomor_kontak'] ?? '6281234567890'; // Default jika kosong
                        $whatsapp_url = "https://wa.me/" . preg_replace('/[^0-9]/', '', $kontak) . "?text=" . urlencode("Halo, saya tertarik dengan produk Rumah Panggung tipe: " . $row['tipe']);
                    ?>
                    <div class="catalog-item">
                        <div class="preview-container" 
                             style="background-image: url('<?php echo $preview_image_url; ?>');"
                             data-images='<?php echo json_encode($images); ?>'
                             onclick="openPanoramaModal(this)">
                        </div>
                        
                        <div class="item-content">
                            <h2><?php echo htmlspecialchars($row['tipe']); ?></h2>
                            <p><strong>Harga Awal:</strong> <?php echo 'Rp ' . number_format($row['harga'], 0, ',', '.'); ?></p>
                            <p><strong>Kontak Penjual:</strong> <?php echo htmlspecialchars($kontak); ?></p>
                        </div>
                        <div class="item-footer">
                            <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="custom-button whatsapp-button">
                                <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
                            </a>
                            <?php if ($is_admin_logged_in): ?>
                                <button class="custom-button" disabled title="Admin tidak dapat memulai penawaran"> Mulai Penawaran </button>
                            <?php else: ?>
                                <button class="custom-button" onclick="startChat(this, <?php echo $row['id']; ?>)">
                                    <i class="fas fa-comments"></i> Mulai Penawaran Online
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>

    <div id="panoramaModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closePanoramaModal()">&times;</span>
            <div id="panoramaViewer"></div>
        </div>
    </div>
    
    <script>
        let currentViewer = null; // Variabel global untuk menyimpan instance Panolens Viewer

        function openPanoramaModal(element) {
            const images = JSON.parse(element.getAttribute('data-images'));
            const modal = document.getElementById('panoramaModal');
            const panoramaViewerContainer = document.getElementById('panoramaViewer');

            // Kosongkan container viewer sebelumnya jika ada
            panoramaViewerContainer.innerHTML = '';

            if (images && images.length > 0) {
                modal.style.display = 'flex'; // Tampilkan modal
                
                // Hapus viewer lama jika ada
                if (currentViewer) {
                    currentViewer.destroy(); // Penting untuk membuang instance lama
                    currentViewer = null;
                }

                // Inisialisasi Panolens di dalam modal
                currentViewer = new PANOLENS.Viewer({ 
                    container: panoramaViewerContainer, 
                    autoRotate: true, 
                    autoRotateSpeed: 0.3, 
                    controlBar: true // Tampilkan control bar di modal
                });
                const panorama = new PANOLENS.ImagePanorama(images[0]); // Hanya gambar pertama
                currentViewer.add(panorama);
            } else {
                alert('Gambar panorama tidak tersedia untuk produk ini.');
            }
        }

        function closePanoramaModal() {
            const modal = document.getElementById('panoramaModal');
            modal.style.display = 'none'; // Sembunyikan modal
            
            // Hentikan rotasi dan buang instance Panolens saat modal ditutup
            if (currentViewer) {
                currentViewer.dispose(); // Metode untuk membersihkan sumber daya Three.js
                currentViewer.destroy(); // Hapus dari DOM dan Three.js
                currentViewer = null;
            }
            document.getElementById('panoramaViewer').innerHTML = ''; // Pastikan bersih
        }

        // Tutup modal jika user klik di luar konten modal
        window.onclick = function(event) {
            const modal = document.getElementById('panoramaModal');
            if (event.target == modal) {
                closePanoramaModal();
            }
        }

        function startChat(button, productId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert("Anda harus login sebagai customer untuk memulai penawaran.");
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memulai...';
            fetch('api_transaction.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'initiate', product_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.transaction_id) {
                    window.location.href = `negosiasi.php?transaction_id=${data.transaction_id}`;
                } else {
                    alert('Gagal memulai sesi: ' + data.message);
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-comments"></i> Mulai Penawaran Online';
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan: ' + error);
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-comments"></i> Mulai Penawaran Online';
            });
        }
    </script>
</body>
</html>