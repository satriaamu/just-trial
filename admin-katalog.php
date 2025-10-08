<?php
// Memulai sesi
session_start();

// Redirect jika bukan admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Data koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mokobang";

// Membuat koneksi dengan PDO untuk keamanan dan kemudahan
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Handle semua operasi CRUD (Create, Read, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
        case 'edit':
            // Logika upload dan path gambar
            $uploadedImages = [];
            if (!empty($_FILES['gambar']['name'][0])) {
                foreach ($_FILES['gambar']['tmp_name'] as $key => $tmp_name) {
                    $target_dir = "uploads/";
                    $file_name = time() . '_' . basename($_FILES['gambar']['name'][$key]);
                    $target_file = $target_dir . $file_name;
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $uploadedImages[] = str_replace('\\', '/', $target_file);
                    }
                }
            }
            
            if ($_POST['action'] == 'add') {
                $imagesJson = json_encode($uploadedImages);
                $stmt = $pdo->prepare(
                    "INSERT INTO katalog (tipe, harga, bahan_utama, struktur, konstruksi, rangka_atap, lantai_dinding, jumlah_kamar, teras_depan, ventilasi_jendela, pengerjaan, nomor_kontak, gambar, fitur_tambahan, deskripsi, stock) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $_POST['tipe'], $_POST['harga'], $_POST['bahan_utama'], $_POST['struktur'],
                    $_POST['konstruksi'], $_POST['rangka_atap'], $_POST['lantai_dinding'],
                    $_POST['jumlah_kamar'], $_POST['teras_depan'], $_POST['ventilasi_jendela'],
                    $_POST['pengerjaan'], $_POST['nomor_kontak'], $imagesJson,
                    $_POST['fitur_tambahan'], $_POST['deskripsi'], $_POST['stock']
                ]);
                $_SESSION['message'] = "Data berhasil ditambahkan";
            } else {
                // Menggabungkan gambar lama dan baru saat edit
                $existingImages = isset($_POST['existing_images']) ? $_POST['existing_images'] : [];
                $allImages = array_merge($existingImages, $uploadedImages);
                $imagesJson = json_encode($allImages);

                $stmt = $pdo->prepare(
                    "UPDATE katalog SET tipe=?, harga=?, bahan_utama=?, struktur=?, konstruksi=?, rangka_atap=?, lantai_dinding=?, jumlah_kamar=?, teras_depan=?, ventilasi_jendela=?, pengerjaan=?, nomor_kontak=?, gambar=?, fitur_tambahan=?, deskripsi=?, stock=? WHERE id=?"
                );
                $stmt->execute([
                    $_POST['tipe'], $_POST['harga'], $_POST['bahan_utama'], $_POST['struktur'],
                    $_POST['konstruksi'], $_POST['rangka_atap'], $_POST['lantai_dinding'],
                    $_POST['jumlah_kamar'], $_POST['teras_depan'], $_POST['ventilasi_jendela'],
                    $_POST['pengerjaan'], $_POST['nomor_kontak'], $imagesJson,
                    $_POST['fitur_tambahan'], $_POST['deskripsi'], $_POST['stock'],
                    $_POST['id']
                ]);
                $_SESSION['message'] = "Data berhasil diperbarui";
            }
            break;
            
        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM katalog WHERE id=?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['message'] = "Data berhasil dihapus";
            break;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Ambil data untuk form edit jika ada parameter 'edit' di URL
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM katalog WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Katalog</title>
    <link rel="icon" href="image/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f4f4; }
        #sidebar { width: 250px; background-color: #2c3e50; color: white; height: 100vh; position: fixed; }
        #sidebar .logo { text-align: center; padding: 20px 0; }
        #sidebar .logo img { width: 100px; }
        #sidebar ul { list-style: none; padding: 0; }
        #sidebar ul li a { color: white; text-decoration: none; padding: 15px 20px; display: block; }
        #sidebar ul li a:hover, #sidebar ul li a.active { background-color: #212f3d; }
        #content { margin-left: 250px; padding: 20px; width: calc(100% - 250px); }
        .announcement { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .catalog-container { overflow-x: auto; }
        .catalog-table { width: 100%; border-collapse: collapse; min-width: 1200px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: middle; }
        th { background-color: #2c3e50; color: white; position: sticky; top: 0; }
        .action-btns { display: flex; gap: 8px; }
        .btn { padding: 8px 12px; border: none; border-radius: 4px; color: white; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .edit-btn { background-color: #3498db; }
        .delete-btn { background-color: #e74c3c; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 3% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .full-width { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .image-preview-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .image-preview-item { position: relative; }
        .image-preview-item img { max-width: 100px; max-height: 100px; border-radius: 4px; border: 1px solid #ddd; }
        .remove-image-btn { position: absolute; top: -5px; right: -5px; background: #e74c3c; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 12px; line-height: 20px; text-align: center; }
    </style>
</head>
<body>
    <div id="sidebar">
        <div class="logo"><img src="image/logo.png" alt="Logo"></div>
        <ul>
            <li><a href="admin-dashboard.php"><i class="fas fa-home"></i> Dasbor</a></li>
            <li><a href="admin-billing.php"><i class="fas fa-file-invoice-dollar"></i> Buat Billing</a></li>
            <li><a href="history.php"><i class="fas fa-comments-dollar"></i> Penawaran & Chat</a></li>
            <li><a href="admin-katalog.php" class="active"><i class="fas fa-clipboard-list"></i> Kelola Katalog</a></li>
            <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div id="content">
        <header><h1>Kelola Katalog Produk</h1></header>
        <main class="main-content">
            <div class="announcement">
                <div style="text-align: right; margin-bottom: 20px;">
                    <button onclick="openModal()" class="btn" style="background-color: #27ae60;"><i class="fas fa-plus"></i> Tambah Data Baru</button>
                </div>
                <div class="catalog-container">
                    <table class="catalog-table">
                        <thead>
                            <tr>
                                <th>Tipe</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Bahan Utama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT id, tipe, harga, stock, bahan_utama FROM katalog ORDER BY created_at DESC");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['tipe']); ?></td>
                                    <td><?php echo 'Rp ' . number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['stock']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['bahan_utama']); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="?edit=<?php echo $row['id']; ?>" class="btn edit-btn"><i class="fas fa-edit"></i> Edit</a>
                                            <form method="POST" onsubmit="return confirm('Anda yakin ingin menghapus data ini?');" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="btn delete-btn"><i class="fas fa-trash"></i> Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="formModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()" style="float:right; cursor:pointer; font-size: 24px;">&times;</span>
            <h2><?php echo $editData ? 'Edit' : 'Tambah'; ?> Data Katalog</h2>
            <form id="katalogForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editData ? 'edit' : 'add'; ?>">
                <?php if ($editData): ?><input type="hidden" name="id" value="<?php echo $editData['id']; ?>"><?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group"><label>Tipe:</label><input type="text" name="tipe" value="<?php echo htmlspecialchars($editData['tipe'] ?? ''); ?>" required></div>
                    <div class="form-group"><label>Harga:</label><input type="number" name="harga" value="<?php echo htmlspecialchars($editData['harga'] ?? ''); ?>" required></div>
                    <div class="form-group"><label>Bahan Utama:</label><input type="text" name="bahan_utama" value="<?php echo htmlspecialchars($editData['bahan_utama'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Struktur:</label><input type="text" name="struktur" value="<?php echo htmlspecialchars($editData['struktur'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Konstruksi:</label><input type="text" name="konstruksi" value="<?php echo htmlspecialchars($editData['konstruksi'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Rangka Atap:</label><input type="text" name="rangka_atap" value="<?php echo htmlspecialchars($editData['rangka_atap'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Lantai & Dinding:</label><input type="text" name="lantai_dinding" value="<?php echo htmlspecialchars($editData['lantai_dinding'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Jumlah Kamar:</label><input type="text" name="jumlah_kamar" value="<?php echo htmlspecialchars($editData['jumlah_kamar'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Teras Depan:</label><input type="text" name="teras_depan" value="<?php echo htmlspecialchars($editData['teras_depan'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Ventilasi & Jendela:</label><input type="text" name="ventilasi_jendela" value="<?php echo htmlspecialchars($editData['ventilasi_jendela'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Estimasi Pengerjaan:</label><input type="text" name="pengerjaan" value="<?php echo htmlspecialchars($editData['pengerjaan'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Nomor Kontak:</label><input type="text" name="nomor_kontak" value="<?php echo htmlspecialchars($editData['nomor_kontak'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Jumlah Stok:</label><input type="number" name="stock" value="<?php echo htmlspecialchars($editData['stock'] ?? '1'); ?>" required min="0"></div>
                </div>
                
                <div class="form-group full-width"><label>Gambar (pilih beberapa untuk tur 360°, gambar pertama akan jadi preview):</label><input type="file" name="gambar[]" multiple accept="image/*"></div>
                <?php if ($editData && !empty($editData['gambar'])): ?>
                    <div class="form-group full-width">
                        <label>Gambar Saat Ini:</label>
                        <div class="image-preview-container">
                            <?php foreach(json_decode($editData['gambar']) as $image): ?>
                                <div class="image-preview-item">
                                    <img src="<?php echo htmlspecialchars(str_replace('\\', '/', $image)); ?>" alt="Preview">
                                    <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($image); ?>">
                                    <button type="button" class="remove-image-btn" onclick="this.parentElement.remove()">×</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group full-width"><label>Fitur Tambahan (pisahkan dengan baris baru):</label><textarea name="fitur_tambahan" rows="4"><?php echo htmlspecialchars($editData['fitur_tambahan'] ?? ''); ?></textarea></div>
                <div class="form-group full-width"><label>Deskripsi:</label><textarea name="deskripsi" rows="4"><?php echo htmlspecialchars($editData['deskripsi'] ?? ''); ?></textarea></div>
                
                <div style="text-align:right; margin-top:20px;">
                    <button type="button" onclick="closeModal()" class="btn" style="background-color:#95a5a6;">Batal</button>
                    <button type="submit" class="btn" style="background-color:#2980b9;">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const formModal = document.getElementById('formModal');
        
        function openModal() {
            // Reset form for new entry
            document.getElementById('katalogForm').reset();
            document.querySelector('input[name="action"]').value = 'add';
            const id_input = document.querySelector('input[name="id"]');
            if (id_input) {
                id_input.remove();
            }
            document.querySelector('.image-preview-container')?.remove();
            formModal.style.display = 'block'; 
        }

        function closeModal() {
            formModal.style.display = 'none';
            // Clear the "?edit=X" from URL
            window.history.pushState({}, '', window.location.pathname);
        }
        
        <?php if (isset($_GET['edit'])): ?>
            // If the page is loaded with an "edit" parameter, show the modal
            formModal.style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>