<?php
session_start();

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mokobang";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Inisialisasi variabel notifikasi
$notifikasi = null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_contact'])) {
    $nama = $_POST['name'];
    $email = $_POST['email'];
    $pesan = $_POST['message'];

    // Insert pesan ke dalam database
    $sql = "INSERT INTO pesan (nama, email, pesan) VALUES ('$nama', '$email', '$pesan')";
    if ($conn->query($sql)) {
        // Set session untuk notifikasi
        $_SESSION['notifikasi'] = [
            'tipe' => 'sukses',
            'pesan' => 'Pesan Anda telah berhasil dikirim!'
        ];
        
        // Redirect ke halaman yang sama untuk menghindari resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $notifikasi = [
            'tipe' => 'gagal',
            'pesan' => 'Terjadi kesalahan, pesan gagal dikirim.'
        ];
    }
}

// Ambil notifikasi dari session jika ada
if (isset($_SESSION['notifikasi'])) {
    $notifikasi = $_SESSION['notifikasi'];
    unset($_SESSION['notifikasi']); // Hapus notifikasi setelah ditampilkan
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Showcase Rumah Panggung Desa Mokobang</title>

    <!-- Favicon -->
    <link rel="icon" href="image/logo.png" type="image/x-icon">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts for button text -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: #f4f4f4;
            color: #333;
            font-size: 16px;
        }

        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0px 100px;
            background-color: #2c3e50;
        }

        header .logo img {
            height: 100px; /* Meningkatkan ukuran logo */
        }

        header nav ul {
            list-style: none;
            display: flex;
            gap: 10px;
        }

        header nav ul li {
            padding: 0px 6px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Button Style for Header (button-18) */
        .button-18 {
            align-items: center;
            background-color: #0A66C2;
            border: 0;
            border-radius: 100px;
            box-sizing: border-box;
            color: #ffffff;
            cursor: pointer;
            display: inline-flex;
            font-family: -apple-system, system-ui, system-ui, "Segoe UI", Roboto, "Helvetica Neue", "Fira Sans", Ubuntu, Oxygen, "Oxygen Sans", Cantarell, "Droid Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Lucida Grande", Helvetica, Arial, sans-serif;
            font-size: 14px;
            font-weight: 600;
            justify-content: center;
            line-height: 20px;
            min-height: 40px;
            padding-left: 30px;
            padding-right: 30px;
            text-align: center;
            touch-action: manipulation;
            transition: background-color 0.167s cubic-bezier(0.4, 0, 0.2, 1) 0s, box-shadow 0.167s cubic-bezier(0.4, 0, 0.2, 1) 0s, color 0.167s cubic-bezier(0.4, 0, 0.2, 1) 0s;
            user-select: none;
            -webkit-user-select: none;
            text-decoration: none; /* Menghilangkan underline pada tautan di header */
            vertical-align: middle;
        }

        .button-18:hover,
        .button-18:focus { 
            background-color: #16437E;
            color: #ffffff;
        }

        .button-18:active {
            background: #09223b;
            color: rgb(255, 255, 255, .7);
        }

        .button-18:disabled { 
            cursor: not-allowed;
            background: rgba(0, 0, 0, .08);
            color: rgba(0, 0, 0, .3);
        }

        /* Cart Icon (Font Awesome) */
        header nav ul li.cart {
            display: flex;
            align-items: center;
            text-decoration: none; /* Menghilangkan underline pada tautan di header */
        }
        
        .cart-icon {
            background: none; /* Hilangkan background putih pada ikon */
            padding: 0; /* Menghilangkan padding */
            font-size: 25px;
        }

        /* Burger Menu */
        .burger-menu {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
        }

        .burger-menu span {
            width: 25px;
            height: 3px;
            background-color: white;
        }

        /* Main Content */
main {
    padding: 20px;
    background-color: #f4f4f4;
}

/* Kontak Section */
#kontak {
    text-align: center;
    padding: 10px 0;
}

#kontak .container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

#kontak h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #2c3e50;
}

#kontak p {
    font-size: 16px;
    margin-bottom: 30px;
    color: #555;
    line-height: 1.6;
}

.form-group {
    margin-bottom: 20px;
    text-align: left;
}

.form-group label {
    font-size: 14px;
    color: #333;
    margin-bottom: 8px;
    display: block;
}

.form-group input, 
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

.form-group textarea {
    resize: vertical;
}

button {
    padding: 12px;
    border: none;
    background-color: #2980b9;
    color: white;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
}

button:hover {
    background-color: #3498db;
}

        .catalog-item {
            margin-bottom: 30px;
        }

        .catalog-item button {
            background-color: #2980b9;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .catalog-item button:hover {
            background-color: #3498db;
        }

        /* Footer */
footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background-color: #333;
    color: white;
    padding: 20px 100px; /* Padding untuk spasi */
    margin-top: 40px;
    flex-wrap: wrap; /* Memungkinkan elemen untuk membungkus pada layar kecil */
}

/* Footer Left Section */
footer .footer-left {
    display: flex;
    flex-direction: column;
    align-items: center;
}

footer .footer-left img {
    width: 150px; /* Lebar tetap untuk logo */
    height: auto;
}

footer .footer-left .profile-desc {
    margin-top: 15px;
    font-size: 14px;
    text-align: justify;
    color: white;
    max-width: 350px; /* Lebar maksimum untuk deskripsi */
}

/* Footer Right Section */
footer .footer-right {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap; /* Memungkinkan konten membungkus pada layar kecil */
}

footer .contact-info,
footer .quick-links {
    margin-bottom: 20px; /* Spasi antar section */
    margin-right: 20px; /* Memberikan jarak antar kolom di kanan */
}

/* Optional: Adjust column widths on large screens */
footer .footer-left,
footer .footer-right {
    width: 45%;  /* Atur lebar kolom kiri dan kanan secara proporsional */
}

footer h3 {
    margin-bottom: 15px;
    font-size: 18px;
    color: yellow;
}

footer .contact-info p,
footer .quick-links ul {
    margin: 5px 0;
    color: white;
}

footer .quick-links ul {
    list-style-type: none;
    padding-left: 0;
}

footer .quick-links ul li {
    margin-bottom: 8px;
}

footer .quick-links ul li a {
    text-decoration: none;
    color: white;
    font-size: 16px;
}

footer .quick-links ul li a:hover {
    color: #FFD700; /* Hover effect */
}

/* Copyright Section */
footer .copyright {
    width: 100%;
    text-align: center;
    font-size: 14px;
    color: white;
    margin-top: 20px;
}

footer .copyright p {
    color:white; 
    text-align: center;
}

/* Menambahkan garis pemisah di sebelah kanan ikon keranjang */
header nav ul li.cart {
    display: flex;
    align-items: center;
    text-decoration: none; /* Menghilangkan underline pada tautan di header */
    border-right: 2px solid white; /* Garis pemisah di sebelah kanan */
    padding-right: 25px; /* Memberikan sedikit ruang antara ikon dan garis pemisah */
}

/* Style untuk notifikasi */
.notifikasi {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
}

.notifikasi-sukses {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.notifikasi-gagal {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.notifikasi ul {
    margin: 5px 0;
    padding-left: 20px;
    text-align: left;
}

        /* Mobile Styles */
    @media (max-width: 768px) {
        header .logo {
        display: flex;
        justify-content: center; /* Menyusun logo secara horizontal di tengah */
        width: 100%; /* Pastikan logo memiliki lebar penuh */
    }
    
    header nav ul li.cart {
        border-right: none; /* Menghapus garis pemisah */
        padding-right: 0; /* Menghilangkan ruang ekstra */
    }

    header .logo img {
        height: 100px; /* Menyesuaikan ukuran logo */
        width: auto; /* Menjaga rasio aspek gambar */
    }
        
            header {
        flex-wrap: wrap;
        padding: 15px 20px;
    }

    nav {
        width: 100%;
    }

    header nav ul {
        flex-direction: column;
        background-color: #2c3e50;
        width: 100%;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-in-out;
    }

    header nav ul.active {
        max-height: 500px; /* Set this higher if you have more menu items */
    }

    .burger-menu {
        display: flex;
        justify-content: center; /* Menempatkan burger menu di tengah secara horizontal */
        align-items: center; /* Vertikal center */
        flex-grow: 1; /* Memungkinkan burger menu untuk mengambil ruang dan mendorong elemen lainnya */
        margin-top: 20px;
    }
        
    footer {
        flex-direction: column;  /* Stacks the content vertically */
        align-items: center;  /* Centers the content */
        text-align: center;  /* Centers the text */
        padding: 20px 15px;  /* Reduced padding */
    }

    footer .footer-left,
    footer .footer-right {
        width: 100%;  /* Full width for both sections on mobile */
        margin-bottom: 20px;  /* Adds space between sections */
        align-items: center;  /* Centers content in each section */
    }

    footer .footer-left .profile-desc {
        text-align: center;  /* Centers the description */
        max-width: 100%;  /* Remove max-width constraint */
    }

    footer .contact-info,
    footer .quick-links {
        width: 100%;  /* Full width for contact and quick links on mobile */
        text-align: center;  /* Centers the contact and links */
    }
    
    footer .contact-info p {
        width: 100%;  /* Full width for contact and quick links on mobile */
        text-align: center;  /* Centers the contact and links */
    }

    footer .quick-links ul li {
        margin-bottom: 12px;  /* Adds more space between items */
    }

    footer .copyright {
        font-size: 12px;  /* Adjusts font size for mobile */
    }
    
    .burger-menu.active span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}
.burger-menu.active span:nth-child(2) {
    opacity: 0;
}
.burger-menu.active span:nth-child(3) {
    transform: rotate(-45deg) translate(5px, -5px);
}
.burger-menu span {
    transition: all 0.3s ease;
}

#kontak .container {
        width: 90%;
    }

    #kontak h2 {
        font-size: 24px;
    }

    .form-group input, 
    .form-group textarea {
        font-size: 14px;
    }

}
    </style>
</head>
<body>

    <!-- Header -->
<header>
    <div class="logo">
        <img src="image/logo-showcase.png" alt="Showcase Logo">
    </div>
    <nav>
        <ul>
            <li class="cart">
                <a href="keranjang.php">
                    <i class="fas fa-shopping-cart cart-icon" style="color: white;"></i>
                </a> 
            </li>
            <li><a href="index.php" class="button-18"><i class="fas fa-home"></i>&nbsp; Beranda</a></li>
            <li><a href="profil.php" class="button-18"><i class="fas fa-user"></i>&nbsp; Profil</a></li>
            <li><a href="katalog.php" class="button-18"><i class="fas fa-cogs"></i>&nbsp; Katalog 360°</a></li>
            <li><a href="kontak.php" class="button-18"><i class="fas fa-phone"></i>&nbsp; Kontak</a></li>

            <?php if (isset($_SESSION['username'])): ?>
                <!-- If user is logged in -->
                <li><a href="logout.php" class="button-18"><i class="fas fa-sign-out-alt"></i>&nbsp; Logout</a></li>
            <?php else: ?>
                <!-- If user is not logged in -->
                <li><a href="login.php" class="button-18"><i class="fas fa-sign-in-alt"></i>&nbsp; Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Burger Menu for small screens -->
    <div class="burger-menu">
        <span></span>
        <span></span>
        <span></span>
    </div>
</header>

    <!-- Main Content -->
    <main>
        <!-- Section 1: Kontak -->
        <section id="kontak">
            <div class="container">
                <h2>Kontak Kami</h2>
                <p>Jika Anda memiliki pertanyaan atau ingin bekerja sama dengan kami, silakan hubungi kami melalui formulir di bawah ini. Kami akan segera merespons pesan Anda.</p>
                
<?php if ($notifikasi): ?>
    <div class="notifikasi notifikasi-<?= $notifikasi['tipe'] ?>">
        <?php if (is_array($notifikasi['pesan'])): ?>
            <ul>
                <?php foreach ($notifikasi['pesan'] as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <?= $notifikasi['pesan'] ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
                
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required placeholder="Masukkan Nama Lengkap" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="Masukkan Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="message">Pesan</label>
                        <textarea id="message" name="message" rows="4" required placeholder="Tulis pesan Anda..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    <button type="submit" name="submit_contact" class="button-18">Kirim Pesan</button>
                </form>
            </div>
        </section>
    </main>


<!-- Footer Section -->
<footer>
    <!-- Kolom Kiri: Logo dan Deskripsi -->
    <div class="footer-left">
        <img src="image/logo.png" alt="Logo Showcase Rumah Panggung Mokobang">
        <div class="profile-desc">
            <p style="color:white; text-align: justify;">Showcase Rumah Panggung Desa Mokobang adalah tempat terbaik untuk menemukan desain rumah panggung tradisional dengan kualitas terbaik. Kami menawarkan berbagai model rumah panggung yang terbuat dari bahan-bahan alami dan ramah lingkungan, memberikan nuansa yang harmonis dengan alam sekitar dan budaya lokal. Setiap rumah dirancang dengan penuh perhatian dan detail, menciptakan kenyamanan yang tak tertandingi.</p>
        </div>
    </div>

    <!-- Kolom Kanan: Kontak dan Quick Links -->
    <div class="footer-right">
        <!-- Kontak Info -->
        <div class="contact-info">
            <h3>Kontak</h3>
            <p>Telepon: 0812-3456-7890</p>
            <p>Email: mokobangrumahpanggung@gmail.com</p>
            <p>Alamat: Desa Mokobang, Kecamatan Modoinding, Kabupaten Minahasa Selatan, Sulawesi Utara</p>
            <p>Jam Layanan: Senin – Sabtu (08.00 – 17.00 WITA)</p>
        </div>

        <!-- Quick Links -->
        <div class="quick-links">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i>&nbsp; Beranda</a></li>
                <li><a href="profil.php"><i class="fas fa-user"></i>&nbsp; Profil</a></li>
                <li><a href="katalog.php"><i class="fas fa-cogs"></i>&nbsp; Katalog 360°</a></li>
                <li><a href="kontak.php"><i class="fas fa-phone"></i>&nbsp; Kontak</a></li>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i>&nbsp; Login</a></li>
            </ul>
        </div>
    </div>

    <!-- Copyright -->
    <div class="copyright">
        <p>Copyright &copy; 2025 Showcase Rumah Panggung Desa Mokobang | All Rights Reserved.</p>
    </div>
</footer>

<script>
    const burgerMenu = document.querySelector('.burger-menu');
    const navLinks = document.querySelector('header nav ul');

    burgerMenu.addEventListener('click', () => {
        burgerMenu.classList.toggle('active');
        navLinks.classList.toggle('active');
    });
</script>

</body>
</html>
