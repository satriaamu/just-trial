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
    background-color: #f4f4f4; /* Background untuk main content */
}

/* Profile Section */
#profil {
    text-align: center;
    padding: 20px;
}

#profil .container {
    max-width: 90%; /* Maksimal lebar kontainer */
    margin: 0 auto; /* Membuat kontainer berada di tengah */
    padding: 20px;
    background-color: #ffffff; /* Latar belakang putih untuk kontainer */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Memberikan efek bayangan */
    border-radius: 10px; /* Sudut kontainer yang melengkung */
}

#profil img {
    width: 70%; /* Ukuran gambar */
    height: auto;
    border-radius: 8px; /* Sudut gambar sedikit melengkung */
    margin-bottom: 20px; /* Memberikan jarak antara gambar dan teks */
    border: 2px solid black;
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

    /* Two Column Layout */
    .main-container {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        padding: 20px;
    }
    
    .description-column {
        flex: 1;
        min-width: 300px;
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .gallery-column {
        flex: 1;
        min-width: 300px;
    }
    
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
    
.gallery-item {
    cursor: pointer;
    transition: transform 0.3s;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border: 2px solid black;
    width: 100%; /* Set width to 80% */
    margin: 0 auto; /* Center the item horizontally */
}

    .gallery-item:hover {
        transform: scale(1.03);
    }
    
    .gallery-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        display: block;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        overflow: auto;
    }
    
    .modal-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }
    
    .modal-img {
        max-width: 90%;
        max-height: 80vh;
        object-fit: contain;
        border: 2px solid black;
    }
    
    .close {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 35px;
        font-weight: bold;
        cursor: pointer;
    }

    .nav-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        font-size: 50px;
        cursor: pointer;
        padding: 20px;
        user-select: none;
        z-index: 2;
        background-color: rgba(0,0,0,0.5);
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s;
    }
    
    .nav-arrow:hover {
        background-color: rgba(0,0,0,0.8);
    }
    
    .prev {
        left: 30px;
    }
    
    .next {
        right: 30px;
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

    main {
        padding: 20px 15px;
    }
    
    .main-container {
        padding: 0;
        gap: 20px;
    }
    
    .description-column, 
    .gallery-column {
        padding: 15px;
    }
    
    #profil img {
        width: 100%;
        max-width: 300px;
    }
    
    .gallery-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .gallery-item img {
        height: 150px;
    }
    
        .nav-arrow {
        font-size: 25px;
        width: 35px;
        height: 35px;
        padding: 5px;
    }
    
    .prev {
        left: 5px;
    }
    
    .next {
        right: 5px;
    }
    
    .modal-img {
        max-width: 95%;
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
    <div class="main-container">
        <!-- Left Column - Description -->
        <div class="description-column">
            <h2 style="text-align: center;">Profil Rumah Panggung Desa Mokobang</h2>
            <p>Desa Mokobang terletak di Kecamatan Modoinding, Kabupaten Minahasa Selatan, Sulawesi Utara. Berada di kawasan dataran tinggi dengan udara sejuk dan lingkungan asri, desa ini dikenal sebagai sentra kerajinan rumah panggung tradisional Minahasa. Dengan masyarakat yang mayoritas bekerja di sektor pertanian dan kerajinan, Desa Mokobang terus melestarikan nilai budaya melalui produksi rumah panggung yang kokoh, estetis, dan bernilai historis tinggi. Setiap rumah panggung dibuat dengan teknik tradisional yang telah turun-temurun, menggunakan bahan-bahan alami berkualitas dari hutan sekitar desa.</p>
        
        <!-- Right Column - Gallery -->
        <div class="gallery-column">
            <div class="gallery-grid">
                <div class="gallery-item" onclick="openModal('image/mokobang.jpg')">
                    <img src="image/mokobang.jpg" alt="Rumah Panggung 1">
                </div>
                <div class="gallery-item" onclick="openModal('image/mokobang1.jpg')">
                    <img src="image/mokobang1.jpg" alt="Rumah Panggung 2">
                </div>
                <div class="gallery-item" onclick="openModal('image/mokobang2.jpg')">
                    <img src="image/mokobang2.jpg" alt="Rumah Panggung 3">
                </div>
                <div class="gallery-item" onclick="openModal('image/mokobang3.jpg')">
                    <img src="image/mokobang3.jpg" alt="Rumah Panggung 4">
                </div>
                <div class="gallery-item" onclick="openModal('image/mokobang4.jpg')">
                    <img src="image/mokobang4.jpg" alt="Rumah Panggung 5">
                </div>
                <div class="gallery-item" onclick="openModal('image/mokobang5.jpg')">
                    <img src="image/mokobang5.jpg" alt="Rumah Panggung 6">
                </div>
            </div>
        </div>
    </div>
    </div>
    
    <!-- Modal -->
    <div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <div class="modal-content">
        <span class="nav-arrow prev" onclick="navigateModal(-1)">&#10094;</span>
        <img id="modalImage" class="modal-img">
        <span class="nav-arrow next" onclick="navigateModal(1)">&#10095;</span>
    </div>
</div>
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
    
    // Array of all image sources
    const galleryImages = [
        'image/mokobang.jpg',
        'image/mokobang1.jpg',
        'image/mokobang2.jpg',
        'image/mokobang3.jpg',
        'image/mokobang4.jpg',
        'image/mokobang5.jpg'
    ];
    
    let currentImageIndex = 0;
    
    function openModal(imgSrc) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        currentImageIndex = galleryImages.indexOf(imgSrc);
        modal.style.display = "block";
        modalImg.src = imgSrc;
        document.body.style.overflow = "hidden"; // Prevent scrolling
    }
    
    function closeModal() {
        document.getElementById('imageModal').style.display = "none";
        document.body.style.overflow = "auto"; // Re-enable scrolling
    }
    
    function navigateModal(direction) {
        currentImageIndex += direction;
        
        // Wrap around if at beginning or end
        if (currentImageIndex >= galleryImages.length) {
            currentImageIndex = 0;
        } else if (currentImageIndex < 0) {
            currentImageIndex = galleryImages.length - 1;
        }
        
        document.getElementById('modalImage').src = galleryImages[currentImageIndex];
    }
    
    // Close modal when clicking outside the image
    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target == modal) {
            closeModal();
        }
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', function(event) {
        const modal = document.getElementById('imageModal');
        if (modal.style.display === "block") {
            if (event.key === 'ArrowLeft') {
                navigateModal(-1);
            } else if (event.key === 'ArrowRight') {
                navigateModal(1);
            } else if (event.key === 'Escape') {
                closeModal();
            }
        }
    });
</script>

</body>
</html>
