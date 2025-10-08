<?php
session_start();

// Koneksi ke database
$servername = "db.fr-pari1.bengt.wasmernet.com";
$username = "67cf073f7d048000d4a691b28792";
$password = "068e67cf-073f-7e33-8000-c7299acc4133";
$dbname = "mokobang";
$port = 10272;

// Membuat koneksi dengan port
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Showcase Rumah Panggung Desa Mokobang</title>

    <link rel="icon" href="image/logo.png" type="image/x-icon">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

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
    padding: 0px;
    background-color: #f4f4f4; /* Background untuk main content */
}

/* Profile Section */
#profil {
    text-align: center;
    padding: 20px;
}

#profil .container {
    max-width: 800px; /* Maksimal lebar kontainer */
    margin: 0 auto; /* Membuat kontainer berada di tengah */
    padding: 20px;
    background-color: #ffffff; /* Latar belakang putih untuk kontainer */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Memberikan efek bayangan */
    border-radius: 10px; /* Sudut kontainer yang melengkung */
}

#profil img {
    width: 300px; /* Ukuran gambar */
    height: auto;
    border-radius: 8px; /* Sudut gambar sedikit melengkung */
    margin-bottom: 20px; /* Memberikan jarak antara gambar dan teks */
}

h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #2c3e50; /* Warna teks judul */
}

p {
    font-size: 16px;
    margin-bottom: 15px;
    text-align: justify;
    color: #555; /* Warna teks untuk paragraf */
    line-height: 1.6; /* Jarak antar baris untuk kenyamanan membaca */
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

/* Section Styles */
section {
    margin-bottom: 40px;
    padding: 0;
    text-align: center;
}

/* Section 1: Profil */
#profil {
    background-image: url('image/background.jpg');
    background-size: cover;
    background-position: center;
    color: white;
    position: relative;
    padding: 100px 80px; /* Padding for better spacing */
    margin: 0px;
}

#profil::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.8); /* Semi-transparent overlay */
    z-index: 1; /* Make sure the overlay is on top of the background */
}

#profil h2,
#profil p,
#profil a {
    color: white !important; /* Memastikan semua teks berwarna putih */
    position: relative;
    z-index: 2;
}

#profil h2 {
    font-size: 32px;
    margin-bottom: 20px;
}

#profil p {
    font-size: 16px;
    margin-bottom: 20px;
}

/* Section 2: Penjelasan Produk */
#produk {
    background-color: #f9f9f9;
    padding: 60px 0;
}

#produk h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #2c3e50;
}

#produk p {
    font-size: 16px;
    margin-bottom: 20px;
    color: #555;
}

/* Section 3: Kontak */
#kontak {
    background-color: #fff;
    padding: 60px 0;
}

#kontak h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #2c3e50;
}

#kontak p {
    font-size: 16px;
    margin-bottom: 20px;
    color: #555;
}

/* Custom Button */
.custom-button {
    display: inline-block;
    background-color: #0A66C2;
    color: white;
    padding: 15px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 100px;
    text-decoration: none;
    text-align: center;
    transition: background-color 0.3s ease;
    z-index: 2; /* Ensure the button is above the overlay */
    position: relative;
    margin-top: 15px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.custom-button:hover {
    background-color: #16437E;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.custom-button:active {
    background-color: #09223b;
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Layout for Section 2 and Section 3 */
.section-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0 auto;
    padding: 0 100px;
    max-width: 1200px;
    gap: 40px; /* Jarak antara gambar dan deskripsi */
}

/* Section Image */
.section-image {
    max-width: 45%;
    height: auto;
    border-radius: 10px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    border: 2px solid #000; /* Border hitam pada gambar */
}

.section-image:hover {
    transform: scale(1.02);
}

/* Section Description */
.section-description {
    flex: 1;
    padding: 20px;
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
    
    /* Section Content Mobile Styles */
    .section-content {
        flex-direction: column;
        padding: 0 20px;
    }
    
    .section-description {
        margin-left: 0;
        margin-top: 20px;
        order: 2;
        text-align: center;
    }
    
    .section-image {
        order: 1;
        max-width: 100%;
        margin-bottom: 20px;
    }
    
    #kontak .section-content {
        flex-direction: column;
    }
    
    /* Improve button appearance on mobile */
    .custom-button {
        padding: 12px 25px;
        font-size: 15px;
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

}

@media (max-width: 480px) {
    .section-content {
        padding: 0 10px;
    }
    
    .section-description {
        padding: 10px;
    }
    
    .custom-button {
        width: 100%;
    }
}
    </style>
</head>
<body>

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
                <li><a href="daftar-pembelian.php" class="button-18"><i class="fas fa-receipt"></i>&nbsp; Daftar Pembelian</a></li>
                <li><a href="logout.php" class="button-18"><i class="fas fa-sign-out-alt"></i>&nbsp; Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="button-18"><i class="fas fa-sign-in-alt"></i>&nbsp; Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
                    <div class="burger-menu">
        <span></span>
        <span></span>
        <span></span>
    </div>
</header>

    <main>
    <section id="profil">
        <h2>Profil Rumah Panggung Desa Mokobang</h2>
        <p>Desa Mokobang terkenal dengan rumah panggung tradisional yang memiliki ciri khas dan keunikan tersendiri. Rumah panggung di desa ini dibuat dengan bahan-bahan alami yang ramah lingkungan dan memiliki desain yang kental dengan budaya lokal.</p>
        <a href="profil.php" class="custom-button">Lihat Profil Lebih Lanjut</a>
    </section>

    <section id="produk">
        <div class="section-content">
            <img src="image/gambar5.png" alt="Penjelasan Produk" class="section-image">
            <div class="section-description">
                <h2>Penjelasan Produk</h2>
                <p>Jelajahi berbagai model rumah panggung kami yang memiliki berbagai kelebihan, seperti desain ramah lingkungan dan sesuai dengan budaya lokal. Kami juga menyediakan model 360° yang dapat dilihat secara interaktif.</p>
                <a href="katalog.php" class="custom-button">Lihat Katalog</a>
            </div>
        </div>
    </section>

    <section id="kontak">
        <div class="section-content">
            <div class="section-description">
                <h2>Kontak Kami</h2>
                <p>Jika Anda memiliki pertanyaan atau ingin bekerja sama, jangan ragu untuk menghubungi kami melalui halaman kontak. Kami akan segera merespons pesan Anda.</p>
                <a href="kontak.php" class="custom-button">Hubungi Kami</a>
            </div>
            <img src="image/gambar4.png" alt="Kontak Kami" class="section-image">
        </div>
    </section>
</main>

<footer>
    <div class="footer-left">
        <img src="image/logo.png" alt="Logo Showcase Rumah Panggung Mokobang">
        <div class="profile-desc">
            <p style="color:white; text-align: justify;">Showcase Rumah Panggung Desa Mokobang adalah tempat terbaik untuk menemukan desain rumah panggung tradisional dengan kualitas terbaik. Kami menawarkan berbagai model rumah panggung yang terbuat dari bahan-bahan alami dan ramah lingkungan, memberikan nuansa yang harmonis dengan alam sekitar dan budaya lokal. Setiap rumah dirancang dengan penuh perhatian dan detail, menciptakan kenyamanan yang tak tertandingi.</p>
        </div>
    </div>

    <div class="footer-right">
        <div class="contact-info">
            <h3>Kontak</h3>
            <p>Telepon: 0812-3456-7890</p>
            <p>Email: mokobangrumahpanggung@gmail.com</p>
            <p>Alamat: Desa Mokobang, Kecamatan Modoinding, Kabupaten Minahasa Selatan, Sulawesi Utara</p>
            <p>Jam Layanan: Senin – Sabtu (08.00 – 17.00 WITA)</p>
        </div>

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

    <div class="copyright">
        <p>Copyright &copy; 2025 Showcase Rumah Panggung Desa Mokobang | All Rights Reserved.</p>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const burgerMenu = document.querySelector('.burger-menu');
        const navLinks = document.querySelector('header nav ul');
        
        // Improved burger menu functionality
        burgerMenu.addEventListener('click', function() {
            this.classList.toggle('active');
            navLinks.classList.toggle('active');
            
            // Close menu when clicking on a link
            const navItems = navLinks.querySelectorAll('li a');
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    burgerMenu.classList.remove('active');
                    navLinks.classList.remove('active');
                });
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInside = burgerMenu.contains(event.target) || navLinks.contains(event.target);
            if (!isClickInside && navLinks.classList.contains('active')) {
                burgerMenu.classList.remove('active');
                navLinks.classList.remove('active');
            }
        });
    });
</script>

</body>
</html>