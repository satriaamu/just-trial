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

// Handle form submissions with redirects
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit'])) {
        // Update message
        $stmt = $pdo->prepare("UPDATE pesan SET nama=?, email=?, pesan=? WHERE id=?");
        $stmt->execute([$_POST['nama'], $_POST['email'], $_POST['pesan'], $_POST['id']]);
        
        $_SESSION['success'] = "Pesan berhasil diperbarui";
        header("Location: admin-pesan");
        exit;
    }
    elseif (isset($_POST['delete'])) {
        // Delete message
        $stmt = $pdo->prepare("DELETE FROM pesan WHERE id=?");
        $stmt->execute([$_POST['id']]);
        
        $_SESSION['success'] = "Pesan berhasil dihapus";
        header("Location: admin-pesan"); 
        exit;
    }
}

// Get all messages (newest first)
$messages = $pdo->query("SELECT * FROM pesan ORDER BY id DESC")->fetchAll();
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
        
                .catalog-container {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .catalog-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px; /* Minimum width before scrolling */
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

/* Modal Edit */
.modal {
    display: none; /* Hide by default */
    position: fixed;
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    padding-top: 60px;
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
}

/* Modal Edit Styling */
.modal {
    display: none;
    position: fixed;
    z-index: 9999; /* di atas elemen lain */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow-y: auto;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background-color: #fff;
    margin: 80px auto;
    padding: 25px 30px;
    border-radius: 8px;
    max-width: 500px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    animation: slideDown 0.4s ease;
}

.modal-content h3 {
    margin-bottom: 20px;
    font-size: 22px;
    color: #2c3e50;
    text-align: center;
}

.modal-content label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
    color: #333;
}

.modal-content input[type="text"],
.modal-content input[type="email"],
.modal-content textarea {
    width: 100%;
    padding: 10px 12px;
    margin-top: 6px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 15px;
    resize: vertical;
}

.modal-content textarea {
    min-height: 100px;
}

.modal-content button[type="submit"] {
    margin-top: 20px;
    width: 100%;
    padding: 10px;
    background-color: #3498db;
    border: none;
    color: white;
    font-weight: bold;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.modal-content button[type="submit"]:hover {
    background-color: #2980b9;
}

.close-btn {
    float: right;
    font-size: 24px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
    transition: color 0.3s;
}

.close-btn:hover {
    color: #000;
}

/* Animasi */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }

}
.close-btn {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    float: right;
}

.close-btn:hover,
.close-btn:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Tombol Aksi */
.action-btns {
    display: flex;
    gap: 10px;
    justify-content: center;
}

/* Tombol Edit */
.edit-btn, .delete-btn {
    padding: 5px 10px;
    border-radius: 4px;
    border: none;
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
        <!-- Container for Beranda and totals -->
        <div class="announcement">
            <h2 style="text-align: center; margin-bottom: 20px;">Pesan</h2>
                <!-- Tabel Katalog -->
    <div class="catalog-container">
        <table class="catalog-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Pesan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
<tbody>
    <?php
    // Query untuk mengambil data pesan
    $stmt = $pdo->query("SELECT * FROM pesan");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td data-label="Nama">' . htmlspecialchars($row['nama']) . '</td>';
        echo '<td data-label="Email">' . htmlspecialchars($row['email']) . '</td>';
        echo '<td data-label="Pesan">' . htmlspecialchars($row['pesan']) . '</td>';
        echo '<td data-label="Aksi">
                <div class="action-btns">
                    <button class="edit-btn" onclick="openModal(' . $row['id'] . ', \'' . addslashes($row['nama']) . '\', \'' . addslashes($row['email']) . '\', \'' . addslashes($row['pesan']) . '\')"><i class="fas fa-edit"></i> Edit</button>
                    <form action="admin-pesan" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="' . $row['id'] . '">
                        <button class="delete-btn" name="delete"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
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
    
    <!-- Modal Edit -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">×</span>
        <h3>Edit Pesan</h3>
        <form action="admin-pesan" method="POST">
            <input type="hidden" name="id" id="editId">
            <label for="editNama">Nama</label>
            <input type="text" id="editNama" name="nama" required>
            <label for="editEmail">Email</label>
            <input type="email" id="editEmail" name="email" required>
            <label for="editPesan">Pesan</label>
            <textarea id="editPesan" name="pesan" required></textarea>
            <button type="submit" name="edit">Simpan</button>
        </form>
    </div>
</div>

</main>


        <!-- Footer -->
        <footer>
            <p style="color:white;">Copyright &copy; 2025 Showcase Rumah Panggung Desa Mokobang | All Rights Reserved.</p>
        </footer>
    </div>

    <!-- Script untuk toggle sidebar -->
    <script>
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
        });
        
        // Mendapatkan modal dan tombol
const modal = document.getElementById('editModal');
const closeBtn = document.querySelector('.close-btn');

// Fungsi untuk membuka modal dan mengisi data
function openModal(id, nama, email, pesan) {
    document.getElementById('editId').value = id;
    document.getElementById('editNama').value = nama;
    document.getElementById('editEmail').value = email;
    document.getElementById('editPesan').value = pesan;
    modal.style.display = "block";
}

// Fungsi untuk menutup modal
function closeModal() {
    modal.style.display = "none";
}

// Menutup modal jika klik di luar modal
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

    </script>

</body>
</html>