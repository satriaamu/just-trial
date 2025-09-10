<?php
// Memulai sesi
session_start();

// Data koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mokobang";

// Membuat koneksi dengan PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Handle image uploads
                $uploadedImages = [];
                if (!empty($_FILES['gambar']['name'][0])) {
                    foreach ($_FILES['gambar']['tmp_name'] as $key => $tmp_name) {
                        $target_dir = "uploads/";
                        $target_file = $target_dir . basename($_FILES['gambar']['name'][$key]);
                        if (move_uploaded_file($tmp_name, $target_file)) {
                            $uploadedImages[] = $target_file;
                        }
                    }
                }
                $imagesJson = json_encode($uploadedImages);
                
                // Add new catalog item
                $stmt = $pdo->prepare("INSERT INTO katalog (tipe, harga, bahan_utama, struktur, konstruksi, rangka_atap, lantai_dinding, jumlah_kamar, teras_depan, ventilasi_jendela, pengerjaan, nomor_kontak, gambar, fitur_tambahan, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['tipe'],
                    $_POST['harga'],
                    $_POST['bahan_utama'],
                    $_POST['struktur'],
                    $_POST['konstruksi'],
                    $_POST['rangka_atap'],
                    $_POST['lantai_dinding'],
                    $_POST['jumlah_kamar'],
                    $_POST['teras_depan'],
                    $_POST['ventilasi_jendela'],
                    $_POST['pengerjaan'],
                    $_POST['nomor_kontak'],
                    $imagesJson,
                    $_POST['fitur_tambahan'],
                    $_POST['deskripsi']
                ]);
                $_SESSION['message'] = "Data berhasil ditambahkan";
                break;
                
            case 'edit':
                // Handle image uploads for edit
                $uploadedImages = [];
                $existingImages = isset($_POST['existing_images']) ? $_POST['existing_images'] : [];
                
                if (!empty($_FILES['gambar']['name'][0])) {
                    foreach ($_FILES['gambar']['tmp_name'] as $key => $tmp_name) {
                        $target_dir = "uploads/";
                        $target_file = $target_dir . basename($_FILES['gambar']['name'][$key]);
                        if (move_uploaded_file($tmp_name, $target_file)) {
                            $uploadedImages[] = $target_file;
                        }
                    }
                }
                
                $allImages = array_merge($existingImages, $uploadedImages);
                $imagesJson = json_encode($allImages);
                
                // Update catalog item
                $stmt = $pdo->prepare("UPDATE katalog SET tipe=?, harga=?, bahan_utama=?, struktur=?, konstruksi=?, rangka_atap=?, lantai_dinding=?, jumlah_kamar=?, teras_depan=?, ventilasi_jendela=?, pengerjaan=?, nomor_kontak=?, gambar=?, fitur_tambahan=?, deskripsi=? WHERE id=?");
                $stmt->execute([
                    $_POST['tipe'],
                    $_POST['harga'],
                    $_POST['bahan_utama'],
                    $_POST['struktur'],
                    $_POST['konstruksi'],
                    $_POST['rangka_atap'],
                    $_POST['lantai_dinding'],
                    $_POST['jumlah_kamar'],
                    $_POST['teras_depan'],
                    $_POST['ventilasi_jendela'],
                    $_POST['pengerjaan'],
                    $_POST['nomor_kontak'],
                    $imagesJson,
                    $_POST['fitur_tambahan'],
                    $_POST['deskripsi'],
                    $_POST['id']
                ]);
                $_SESSION['message'] = "Data berhasil diperbarui";
                break;
                
            case 'delete':
                // Delete catalog item
                $stmt = $pdo->prepare("DELETE FROM katalog WHERE id=?");
                $stmt->execute([$_POST['id']]);
                $_SESSION['message'] = "Data berhasil dihapus";
                break;
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Get data for editing
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM katalog WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($editData && $editData['gambar']) {
        $editData['gambar_array'] = json_decode($editData['gambar'], true);
    } else {
        $editData['gambar_array'] = [];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="icon" href="image/logo.png"> <!-- Favicon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Reset margin dan padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            height: 100%;
            overflow-x: hidden;
            position: relative;
        }

        /* Sidebar */
        #sidebar {
            width: 250px;
            height: 100%;
            background-color: #2c3e50; /* Hijau tua abu-abu */
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
            transition: all 0.3s ease;
            z-index: 100;
            padding-bottom: 30px;
            overflow-y: auto; /* Menambahkan scroll ketika konten lebih tinggi */
        }

        #sidebar.collapsed {
            width: 70px;
        }

        #sidebar.collapsed .logo img {
            width: 40px;
            margin-top: 10px;
        }

        #sidebar.collapsed .logo {
            padding: 0;
            margin-bottom: 20px;
        }

        #sidebar.collapsed .menu-text {
            display: none;
        }

        #sidebar.collapsed ul li {
            text-align: center;
            padding: 15px 10px;
        }

        #sidebar.collapsed ul li a i {
            margin-right: 0;
            font-size: 1.2rem;
        }

        #sidebar ul {
            list-style: none;
            padding: 0;
        }

        /* Logo di Sidebar */
        #sidebar .logo {
            text-align: center;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            position: relative;
        }

        #sidebar .logo img {
            width: 80%;
            max-width: 150px;
            transition: all 0.3s ease;
        }

        /* Tombol toggle di atas logo */
        #sidebar .toggle-container {
            display: flex;
            justify-content: flex-end;
            padding-right: 15px;
            margin-bottom: 10px;
        }

        #sidebar .toggle-btn {
            background-color: #273747;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        #sidebar .toggle-btn:hover {
            background-color: #212f3d;
        }

        #sidebar.collapsed .toggle-btn {
            margin: 0 auto;
        }

        #sidebar ul li {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #212f3d; /* Garis pemisah */
            transition: all 0.3s ease;
        }

        #sidebar ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 16px;
        }

        #sidebar ul li a i {
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        #sidebar ul li:hover {
            background-color: #212f3d; /* Warna ketika hover */
            color: #fff; /* Warna teks ketika hover */
        }

        /* Scrollbar di Sidebar */
        #sidebar::-webkit-scrollbar {
            width: 8px; /* Lebar scrollbar */
        }

        #sidebar::-webkit-scrollbar-track {
            background-color: #2c3e50; /* Warna track scrollbar */
        }

        #sidebar::-webkit-scrollbar-thumb {
            background-color: #212f3d; /* Warna thumb scrollbar */
            border-radius: 5px;
        }

        #sidebar::-webkit-scrollbar-thumb:hover {
            background-color: #2c3e50; /* Warna thumb scrollbar ketika hover */
        }

        /* Konten utama */
        #content {
            padding-left: 10px;
            padding-right: 10px;
            margin-left: 250px; /* Memberikan jarak agar konten tidak tertutup sidebar */
            overflow-x: hidden; /* Pastikan tidak ada scrolling horizontal */
            transition: margin-left 0.3s ease; /* Transisi saat sidebar berubah */
        }

        #content.collapsed {
            margin-left: 70px;
        }

        /* Header */
        header {
            background-color: #2c3e50; /* Hijau tua abu-abu */
            color: white;
            padding: 20px;
            text-align: center;
            position: fixed;
            width: calc(100% - 250px);
            top: 0;
            left: 250px;
            z-index: 2; /* Header berada di atas konten */
            transition: all 0.3s ease;
        }

        header.collapsed {
            width: calc(100% - 70px);
            left: 70px;
        }

        /* Footer */
        footer {
            background-color: #2c3e50; /* Hijau tua abu-abu */
            color: white;
            padding: 15px;
            text-align: center;
            position: fixed;
            width: calc(100% - 250px);
            bottom: 0;
            left: 250px;
            transition: all 0.3s ease;
        }

        footer.collapsed {
            width: calc(100% - 70px);
            left: 70px;
        }

        /* Pengumuman */
        .announcement {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        /* Container untuk konten utama */
        .content-container {
            width: 100%;
            max-width: 100%;
            padding: 90px 40px;
            margin: 0 auto;
            box-sizing: border-box;
            overflow-x: hidden; /* Menyembunyikan overflow horizontal */
        }
        
/* Container dan Tabel Utama */
.catalog-container {
    width: 100%;
    margin-bottom: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow-x: auto; /* Scroll horizontal */
    -webkit-overflow-scrolling: touch; /* Scroll halus di mobile */
}

.catalog-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1000px; /* Minimum width sebelum scroll */
}

.catalog-table th {
    background-color: #2c3e50;
    color: white;
    padding: 12px 15px;
    text-align: left;
    position: sticky;
    top: 0;
}

.catalog-table td {
    padding: 10px 15px;
    border-bottom: 1px solid #e0e0e0;
    vertical-align: top;
}

.catalog-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.catalog-table tr:hover {
    background-color: #f1f1f1;
}

/* Tombol Aksi */
.action-btns {
    display: flex;
    gap: 5px;
}

.edit-btn, .delete-btn {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.edit-btn {
    background-color: #3498db;
    color: white;
}

.delete-btn {
    background-color: #e74c3c;
    color: white;
}
        
        /* Info box styling */
.info-box {
    margin-bottom: 20px;
    padding: 15px;
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.info-header {
    background-color: #2c3e50;
    color: white;
    padding: 10px;
    font-size: 20px;
    text-align: center;
}

.info-item {
    padding: 15px;
    background-color: #e0f7fa;
    font-weight: bold;
    color: #00796b;
    margin-bottom: 10px;
    text-align: center;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 700px;
    border-radius: 5px;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
    animation: modalopen 0.3s;
    
    /* Posisi tengah secara vertikal dan horizontal */
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    
    /* Scroll untuk konten panjang */
    max-height: 90vh;
    overflow-y: auto;
}

/* Scrollbar styling untuk modal */
.modal-content::-webkit-scrollbar {
    width: 8px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Animasi modal */
@keyframes modalopen {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, 
        .form-group textarea, 
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .form-actions {
            text-align: right;
            margin-top: 20px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
            margin-right: 10px;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

/* Add to your existing CSS */
.image-preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.image-preview-item {
    position: relative;
    border: 1px solid #ddd;
    padding: 5px;
    border-radius: 4px;
}

.image-preview-item img {
    display: block;
    margin-bottom: 5px;
}

.image-preview-item button {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: rgba(231, 76, 60, 0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

/* Tambahkan di bagian CSS */
#imagePreviewCarousel img {
    transition: opacity 0.3s ease;
}

#imagePreviewCarousel img.fade-out {
    opacity: 0;
}

#imagePreviewCarousel img.fade-in {
    opacity: 1;
}

/* Tombol navigasi gambar */
.image-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0,0,0,0.5);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 20px;
    cursor: pointer;
    z-index: 10;
}

.image-nav-btn:hover {
    background-color: rgba(0,0,0,0.7);
}

.image-nav-btn.prev {
    left: 15px;
}

.image-nav-btn.next {
    right: 15px;
}
       
/* Tampilan Mobile */
@media (max-width: 768px) {
        .modal-content {
        width: 95%;
        max-width: 95%;
        top: 20px;
        transform: translate(-50%, 0);
        max-height: 90vh;
    }
    
    @keyframes modalopen {
        from {
            opacity: 0;
            transform: translate(-50%, -20px);
        }
        to {
            opacity: 1;
            transform: translate(-50%, 0);
        }
    }
    .content-container {
        padding: 90px 15px; /* Padding lebih kecil di mobile */
    }
    
    .catalog-container {
        border: 1px solid #ddd;
        border-radius: 5px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .catalog-table {
        min-width: 1000px; /* Tetap pertahankan min-width yang cukup */
        display: table; /* Pastikan display tetap table */
    }
    
    .catalog-table th,
    .catalog-table td {
        padding: 8px 10px;
        font-size: 14px;
        white-space: nowrap; /* Mencegah text wrapping */
    }
    
    .action-btns {
        flex-direction: row;
        gap: 5px;
    }
    
    .button-container {
        text-align: center !important;
    }
}

/* Tampilan Mobile Sangat Kecil */
@media (max-width: 480px) {
    .catalog-table {
        min-width: 800px; /* Lebar minimum untuk konten */
    }
    
    .catalog-table th,
    .catalog-table td {
        padding: 6px 8px;
        font-size: 13px;
    }
    
    .edit-btn, .delete-btn {
        padding: 4px 8px;
        font-size: 12px;
    }
    
    .button-container {
        text-align: center !important;
    }
}
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div id="sidebar">
        <!-- Toggle button di atas logo -->
        <div class="toggle-container">
            <button class="toggle-btn" id="toggleBtn">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <!-- Logo -->
        <div class="logo">
            <img src="image/logo.png" alt="Logo">
        </div>

<ul>
    <li><a href="admin-dashboard.php"><i class="fas fa-home"></i> <span class="menu-text">Dasbor</span></a></li>
    <li><a href="history.php"><i class="fas fa-comments-dollar"></i> <span class="menu-text">Penawaran & Chat</span></a></li>
    <li><a href="admin-katalog.php"><i class="fas fa-clipboard-list"></i> <span class="menu-text">Kelola Katalog</span></a></li>
    <li><a href="admin-pesan.php"><i class="fas fa-envelope"></i> <span class="menu-text">Pesan Kontak</span></a></li>
    <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> <span class="menu-text">Logout</span></a></li>
</ul>
    </div>

    <!-- Konten -->
    <div id="content">
        <!-- Header -->
        <header>

        </header>

       <main class="main-content">
            <div class="content-container">
                <!-- Message display -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message success">
                        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Container for Beranda and totals -->
                <div class="announcement">
                    <h2 style="text-align: center; margin-bottom: 20px;">Kelola Katalog</h2>
                    <!-- Tombol Tambah Data -->
                    <div style="text-align: right; margin-bottom: 20px;" class="button-container">
    <button onclick="openModal()" style="padding: 10px 15px; background-color: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer;">
        <i class="fas fa-plus"></i> Tambah Data
    </button>
</div>

                    <!-- Tabel Katalog -->
                    <div class="catalog-container">
<table class="catalog-table">
    <thead>
        <tr>
            <th>Tipe</th>
            <th>Harga</th>
            <th>Bahan Utama</th>
            <th>Struktur</th>
            <th>Konstruksi</th>
            <th>Rangka Atap</th>
            <th>Lantai & Dinding</th>
            <th>Jumlah Kamar</th>
            <th>Teras Depan</th>
            <th>Ventilasi & Jendela</th>
            <th>Pengerjaan</th>
            <th>Nomor Kontak</th>
            <th>Gambar</th>
            <th>Fitur Tambahan</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Query untuk mengambil data katalog
        $stmt = $pdo->query("SELECT * FROM katalog ORDER BY created_at DESC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Decode gambar JSON jika ada
            $images = !empty($row['gambar']) ? json_decode($row['gambar'], true) : [];
            
            echo '<tr>';
            echo '<td data-label="Tipe">' . htmlspecialchars($row['tipe']) . '</td>';
            echo '<td data-label="Harga">Rp ' . number_format($row['harga'], 0, ',', '.') . '</td>';
            echo '<td data-label="Bahan Utama">' . htmlspecialchars($row['bahan_utama']) . '</td>';
            echo '<td data-label="Struktur">' . htmlspecialchars($row['struktur']) . '</td>';
            echo '<td data-label="Konstruksi">' . htmlspecialchars($row['konstruksi']) . '</td>';
            echo '<td data-label="Rangka Atap">' . htmlspecialchars($row['rangka_atap']) . '</td>';
            echo '<td data-label="Lantai & Dinding">' . htmlspecialchars($row['lantai_dinding']) . '</td>';
            echo '<td data-label="Jumlah Kamar">' . htmlspecialchars($row['jumlah_kamar']) . '</td>';
            echo '<td data-label="Teras Depan">' . htmlspecialchars($row['teras_depan']) . '</td>';
            echo '<td data-label="Ventilasi & Jendela">' . htmlspecialchars($row['ventilasi_jendela']) . '</td>';
            echo '<td data-label="Pengerjaan">' . htmlspecialchars($row['pengerjaan']) . '</td>';
            echo '<td data-label="Nomor Kontak">' . htmlspecialchars($row['nomor_kontak']) . '</td>';
            
// Modifikasi bagian kolom gambar di tabel menjadi seperti ini:
echo '<td data-label="Gambar">';
if (!empty($images)) {
    echo '<div style="display: flex; flex-wrap: wrap; gap: 5px;">';
    foreach ($images as $image) {
        echo '<img src="' . htmlspecialchars($image) . '" alt="Preview" style="max-width: 50px; max-height: 50px; object-fit: cover; border: 1px solid #ddd; border-radius: 3px; cursor: pointer;" onclick="previewImages(' . htmlspecialchars(json_encode($images)) . ', \'' . htmlspecialchars($image) . '\')">';
    }
    echo '</div>';
} else {
    echo '-';
}
echo '</td>';
            
            // Kolom Fitur Tambahan dengan ellipsis jika panjang
            echo '<td data-label="Fitur Tambahan" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' . htmlspecialchars($row['fitur_tambahan']) . '</td>';
            
            // Kolom Deskripsi dengan ellipsis jika panjang
            echo '<td data-label="Deskripsi" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' . htmlspecialchars($row['deskripsi']) . '</td>';
            
            // Kolom Aksi
            echo '<td data-label="Aksi">
                    <div class="action-btns">
                        <button onclick="editData('.$row['id'].')" class="edit-btn"><i class="fas fa-edit"></i></button>
                        <button onclick="confirmDelete('.$row['id'].')" class="delete-btn"><i class="fas fa-trash"></i></button>
                    </div>
                  </td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>
                    </div>
                </div>
            </div>
        </main>


        <!-- Footer -->
        <footer>
            <p style="color:white;">Copyright &copy; 2025 Showcase Rumah Panggung Desa Mokobang | All Rights Reserved.</p>
        </footer>
    </div>

    <!-- Modal Form -->
    <div id="formModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2><?= $editData ? 'Edit' : 'Tambah'; ?> Data Katalog</h2>
            <form id="katalogForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= $editData ? 'edit' : 'add'; ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= $editData['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="tipe">Tipe:</label>
                    <input type="text" id="tipe" name="tipe" value="<?= $editData ? htmlspecialchars($editData['tipe']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="harga">Harga:</label>
                    <input type="number" id="harga" name="harga" value="<?= $editData ? htmlspecialchars($editData['harga']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="bahan_utama">Bahan Utama:</label>
                    <input type="text" id="bahan_utama" name="bahan_utama" value="<?= $editData ? htmlspecialchars($editData['bahan_utama']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="struktur">Struktur:</label>
                    <input type="text" id="struktur" name="struktur" value="<?= $editData ? htmlspecialchars($editData['struktur']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="konstruksi">Konstruksi:</label>
                    <input type="text" id="konstruksi" name="konstruksi" value="<?= $editData ? htmlspecialchars($editData['konstruksi']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="rangka_atap">Rangka Atap:</label>
                    <input type="text" id="rangka_atap" name="rangka_atap" value="<?= $editData ? htmlspecialchars($editData['rangka_atap']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="lantai_dinding">Lantai & Dinding:</label>
                    <input type="text" id="lantai_dinding" name="lantai_dinding" value="<?= $editData ? htmlspecialchars($editData['lantai_dinding']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="jumlah_kamar">Jumlah Kamar:</label>
                    <input type="text" id="jumlah_kamar" name="jumlah_kamar" value="<?= $editData ? htmlspecialchars($editData['jumlah_kamar']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="teras_depan">Teras Depan:</label>
                    <input type="text" id="teras_depan" name="teras_depan" value="<?= $editData ? htmlspecialchars($editData['teras_depan']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="ventilasi_jendela">Ventilasi & Jendela:</label>
                    <input type="text" id="ventilasi_jendela" name="ventilasi_jendela" value="<?= $editData ? htmlspecialchars($editData['ventilasi_jendela']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="pengerjaan">Pengerjaan:</label>
                    <input type="text" id="pengerjaan" name="pengerjaan" value="<?= $editData ? htmlspecialchars($editData['pengerjaan']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nomor_kontak">Nomor Kontak:</label>
                    <input type="text" id="nomor_kontak" name="nomor_kontak" value="<?= $editData ? htmlspecialchars($editData['nomor_kontak']) : ''; ?>" rekataquired>
                </div>
                
                            <div class="form-group">
                <label for="gambar">Gambar:</label>
                <input type="file" id="gambar" name="gambar[]" multiple accept="image/*">
                
                <?php if ($editData && !empty($editData['gambar_array'])): ?>
                    <div class="existing-images">
                        <p>Gambar saat ini:</p>
                        <div class="image-preview-container">
                            <?php foreach ($editData['gambar_array'] as $index => $image): ?>
                                <div class="image-preview-item">
                                    <img src="<?= htmlspecialchars($image) ?>" alt="Gambar <?= $index + 1 ?>" style="max-width: 100px; max-height: 100px;">
                                    <input type="hidden" name="existing_images[]" value="<?= htmlspecialchars($image) ?>">
                                    <button type="button" onclick="removeImage(this)" class="btn btn-danger" style="padding: 2px 5px; font-size: 12px;">Hapus</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div id="newImagePreview" class="image-preview-container"></div>
            </div>
                
                <div class="form-group">
                    <label for="fitur_tambahan">Fitur Tambahan:</label>
                    <textarea id="fitur_tambahan" name="fitur_tambahan"><?= $editData ? htmlspecialchars($editData['fitur_tambahan']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" required><?= $editData ? htmlspecialchars($editData['deskripsi']) : ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="width: 400px;">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2>Konfirmasi Hapus</h2>
            <p>Apakah Anda yakin ingin menghapus data ini?</p>
            <form id="deleteForm" method="post">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tambahkan ini di bagian modal yang sudah ada -->
<!-- Image Preview Modal -->
<div id="imagePreviewModal" class="modal">
    <div class="modal-content" style="max-width: 90%; width: auto;">
        <span class="close" onclick="closeImagePreviewModal()">&times;</span>
        <h2>Preview Gambar</h2>
        <div id="imagePreviewCarousel" style="text-align: center;">
            <!-- Gambar akan dimuat di sini -->
        </div>
        <div style="text-align: center; margin-top: 15px;">
            <button onclick="prevImage()" class="btn btn-secondary" style="margin-right: 10px;"><i class="fas fa-chevron-left"></i> Sebelumnya</button>
            <span id="imageCounter">1/1</span>
            <button onclick="nextImage()" class="btn btn-secondary" style="margin-left: 10px;">Selanjutnya <i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

    <!-- Script untuk toggle sidebar -->
<script>
    // Deklarasi variabel modal
    const formModal = document.getElementById('formModal');
    const deleteModal = document.getElementById('deleteModal');
    
    // Modal functions
    function openModal() {
        formModal.style.display = 'block';
    }
    
    function closeModal() {
        formModal.style.display = 'none';
        // Hapus parameter edit dari URL tanpa reload
        if (window.location.search.includes('edit=')) {
            const url = new URL(window.location);
            url.searchParams.delete('edit');
            window.history.pushState({}, '', url);
        }
    }
    
    function editData(id) {
        window.location.href = 'admin-katalog.php?edit=' + id;
    }
    
    function confirmDelete(id) {
        document.getElementById('deleteId').value = id;
        deleteModal.style.display = 'block';
    }
    
    function closeDeleteModal() {
        deleteModal.style.display = 'none';
    }
    
    // Menutup modal jika klik di luar modal
    window.onclick = function(event) {
        if (event.target == formModal) {
            closeModal();
        }
        if (event.target == deleteModal) {
            closeDeleteModal();
        }
    }
    
    // Add to your existing JavaScript
document.getElementById('gambar').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('newImagePreview');
    previewContainer.innerHTML = '';
    
    if (this.files) {
        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const previewItem = document.createElement('div');
                previewItem.className = 'image-preview-item';
                previewItem.innerHTML = `
                    <img src="${event.target.result}" alt="Preview" style="max-width: 100px; max-height: 100px;">
                    <button type="button" onclick="removeNewImage(this)" class="btn btn-danger" style="padding: 2px 5px; font-size: 12px;">Hapus</button>
                `;
                previewContainer.appendChild(previewItem);
            }
            reader.readAsDataURL(file);
        });
    }
});

function removeImage(button) {
    const item = button.closest('.image-preview-item');
    item.remove();
}

function removeNewImage(button) {
    const item = button.closest('.image-preview-item');
    item.remove();
    
    // Get the file input
    const fileInput = document.getElementById('gambar');
    const files = Array.from(fileInput.files);
    const itemIndex = Array.from(item.parentNode.children).indexOf(item);
    
    // Remove the corresponding file
    files.splice(itemIndex, 1);
    
    // Create a new DataTransfer object and set the files
    const dataTransfer = new DataTransfer();
    files.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
}
    
    // Event listener untuk DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleBtn');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const header = document.querySelector('header');
        const footer = document.querySelector('footer');
        const icon = toggleBtn.querySelector('i');
    
        // Cek jika layar mobile dan inisialisasi state collapsed
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            content.classList.add('collapsed');
            header.classList.add('collapsed');
            footer.classList.add('collapsed');
            icon.classList.remove('fa-chevron-left');
            icon.classList.add('fa-chevron-right');
        }
    
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('collapsed');
            header.classList.toggle('collapsed');
            footer.classList.toggle('collapsed');
            
            // Ganti ikon tombol toggle
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            }
        });
        
        // Buka modal edit jika ada parameter edit
        if (window.location.search.includes('edit=')) {
            openModal();
        }
    });
    
    // Tambahkan fungsi-fungsi ini di bagian script
let currentImages = [];
let currentImageIndex = 0;

function previewImages(images, initialImage) {
    console.log('Images:', images);
    console.log('Initial Image:', initialImage);
    currentImages = images;
    currentImageIndex = currentImages.indexOf(initialImage);
    console.log('Current Image Index:', currentImageIndex);
    updateImagePreview();
    document.getElementById('imagePreviewModal').style.display = 'block';
}

function updateImagePreview() {
    const carousel = document.getElementById('imagePreviewCarousel');
    const counter = document.getElementById('imageCounter');
    
    carousel.innerHTML = `<img src="${currentImages[currentImageIndex]}" style="max-height: 70vh; max-width: 100%; object-fit: contain;">`;
    counter.textContent = `${currentImageIndex + 1}/${currentImages.length}`;
}

function nextImage() {
    if (currentImageIndex < currentImages.length - 1) {
        currentImageIndex++;
        updateImagePreview();
    }
}

function prevImage() {
    if (currentImageIndex > 0) {
        currentImageIndex--;
        updateImagePreview();
    }
}

function closeImagePreviewModal() {
    document.getElementById('imagePreviewModal').style.display = 'none';
}

// Tambahkan event listener untuk menutup modal ketika klik di luar
window.onclick = function(event) {
    if (event.target == formModal) {
        closeModal();
    }
    if (event.target == deleteModal) {
        closeDeleteModal();
    }
    if (event.target == document.getElementById('imagePreviewModal')) {
        closeImagePreviewModal();
    }
}
</script>

</body>
</html>