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
    <title>Keranjang Belanja - Showcase Rumah Panggung Desa Mokobang</title>

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
            height: 100px;
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
            text-decoration: none;
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

        /* Cart Icon */
        header nav ul li.cart {
            display: flex;
            align-items: center;
            text-decoration: none;
            border-right: 2px solid white;
            padding-right: 25px;
        }
        
        .cart-icon {
            background: none;
            padding: 0;
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
            transition: all 0.3s ease;
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

        /* Main Content */
        main {
            padding: 40px;
            min-height: 60vh;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .cart-title {
            font-size: 28px;
            margin-bottom: 30px;
            color: #2c3e50;
            text-align: center;
        }

        .cart-items {
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .cart-item-image {
            flex: 0 0 200px;
            height: 150px;
            overflow: hidden;
            border-radius: 8px;
            border: 2px solid black;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex: 1;
            min-width: 250px;
        }

        .cart-item-title {
            font-size: 20px;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .cart-item-price {
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }

        .cart-item-description {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }

        .cart-item-contact {
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #0A66C2;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity {
            font-size: 16px;
            min-width: 30px;
            text-align: center;
        }

        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .cart-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .summary-title {
            font-size: 20px;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .summary-total {
            font-weight: bold;
            font-size: 18px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .checkout-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }

        .checkout-btn {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.3s;
            text-decoration: none;
        }

        .checkout-btn:hover {
            background: #27ae60;
        }

        .whatsapp-btn {
            background: #25D366;
        }

        .whatsapp-btn:hover {
            background: #128C7E;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 0;
        }

        .empty-cart i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            font-size: 24px;
            color: #555;
            margin-bottom: 15px;
        }

        .empty-cart p {
            color: #777;
            margin-bottom: 20px;
        }

        .back-to-catalog {
            background: #0A66C2;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        /* Footer */
        footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            background-color: #333;
            color: white;
            padding: 20px 100px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        /* Footer Left Section */
        footer .footer-left {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        footer .footer-left img {
            width: 150px;
            height: auto;
        }

        footer .footer-left .profile-desc {
            margin-top: 15px;
            font-size: 14px;
            text-align: justify;
            color: white;
            max-width: 350px;
        }

        /* Footer Right Section */
        footer .footer-right {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        footer .contact-info,
        footer .quick-links {
            margin-bottom: 20px;
            margin-right: 20px;
        }

        footer .footer-left,
        footer .footer-right {
            width: 45%;
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
            color: #FFD700;
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
            color: white; 
            text-align: center;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            /* Header */
            header {
                flex-wrap: wrap;
                padding: 15px 20px;
            }

            header .logo {
                display: flex;
                justify-content: center;
                width: 100%;
            }

            header .logo img {
                height: 100px;
                width: auto;
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
                max-height: 500px;
            }

            header nav ul li.cart {
                border-right: none;
                padding-right: 0;
            }

            .burger-menu {
                display: flex;
                justify-content: center;
                align-items: center;
                flex-grow: 1;
                margin-top: 20px;
            }

            /* Main Content */
            main {
                padding: 20px;
            }

            .cart-container {
                padding: 15px;
            }

            .cart-item {
                flex-direction: column;
            }

            .cart-item-image {
                flex: 0 0 auto;
                width: 100%;
                height: 200px;
            }

            /* Footer */
            footer {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 20px 15px;
            }

            footer .footer-left,
            footer .footer-right {
                width: 100%;
                margin-bottom: 20px;
                align-items: center;
            }

            footer .footer-left .profile-desc {
                text-align: center;
                max-width: 100%;
            }

            footer .contact-info,
            footer .quick-links {
                width: 100%;
                text-align: center;
            }
            
            footer .contact-info p {
                width: 100%;
                text-align: center;
            }

            footer .quick-links ul li {
                margin-bottom: 12px;
            }

            footer .copyright {
                font-size: 12px;
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
    <div class="cart-container">
        <h1 class="cart-title">Keranjang Belanja</h1>
        
        <div class="cart-items" id="cartItems">
            <!-- Items will be loaded here by JavaScript -->
        </div>
        
        <div class="cart-summary" id="cartSummary">
            <!-- Summary will be loaded here by JavaScript -->
        </div>
        
        <div class="checkout-buttons" id="checkoutButtons">
            <!-- Checkout buttons will be loaded here by JavaScript -->
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
// =============================================
// CART FUNCTIONALITY
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize burger menu
    const burgerMenu = document.querySelector('.burger-menu');
    const navLinks = document.querySelector('header nav ul');

    if (burgerMenu && navLinks) {
        burgerMenu.addEventListener('click', () => {
            burgerMenu.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
    }

    // Load cart items
    loadCart();
});

function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartItemsContainer = document.getElementById('cartItems');
    const cartSummaryContainer = document.getElementById('cartSummary');
    const checkoutButtonsContainer = document.getElementById('checkoutButtons');
    
    if (cart.length === 0) {
        cartItemsContainer.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Keranjang Anda Kosong</h3>
                <p>Silakan tambahkan produk dari katalog kami</p>
                <a href="katalog.php" class="back-to-catalog">
                    Kembali ke Katalog
                </a>
            </div>
        `;
        cartSummaryContainer.innerHTML = '';
        checkoutButtonsContainer.innerHTML = '';
        return;
    }
    
    // Group items by contact number
    const itemsByContact = {};
    cart.forEach(item => {
        if (!itemsByContact[item.contact]) {
            itemsByContact[item.contact] = [];
        }
        itemsByContact[item.contact].push(item);
    });
    
    // Render cart items
    cartItemsContainer.innerHTML = '';
    cart.forEach((item, index) => {
        cartItemsContainer.innerHTML += `
            <div class="cart-item" data-id="${item.id}">
                <div class="cart-item-image">
                    <img src="${item.image}" alt="${item.title}">
                </div>
                <div class="cart-item-details">
                    <h3 class="cart-item-title">${item.title}</h3>
                    <div class="cart-item-price">${item.price}</div>
                    <div class="cart-item-contact">
                        <strong>Kontak Penjual:</strong> ${item.contact}
                    </div>
                    <div class="cart-item-actions">
                        <div class="quantity-control">
                            <button class="quantity-btn minus-btn" data-index="${index}">-</button>
                            <span class="quantity">${item.quantity}</span>
                            <button class="quantity-btn plus-btn" data-index="${index}">+</button>
                        </div>
                        <button class="remove-btn" data-index="${index}">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    // Render summary
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartSummaryContainer.innerHTML = `
        <h3 class="summary-title">Ringkasan Belanja</h3>
        <div class="summary-item">
            <span>Total Item:</span>
            <span>${totalItems}</span>
        </div>
        <div class="summary-total">
            <span>Total:</span>
            <span>${totalItems} Produk</span>
        </div>
    `;
    
// Di fungsi loadCart() di keranjang.php
checkoutButtonsContainer.innerHTML = '';
Object.keys(itemsByContact).forEach(contact => {
    // Bersihkan nomor dari karakter non-digit
    let whatsappNumber = contact.replace(/[^0-9]/g, '');
    
    // Format nomor untuk WhatsApp
    if (whatsappNumber.startsWith('0')) {
        whatsappNumber = '62' + whatsappNumber.substring(1);
    } else if (whatsappNumber.startsWith('8')) {
        whatsappNumber = '62' + whatsappNumber;
    } else if (whatsappNumber.startsWith('+62')) {
        whatsappNumber = whatsappNumber.substring(1);
    }
    
    const itemsForContact = itemsByContact[contact];
    const totalItemsForContact = itemsForContact.reduce((sum, item) => sum + item.quantity, 0);
    
    // Dapatkan semua tipe rumah yang dipesan dari penjual ini
    const houseTypes = [...new Set(itemsForContact.map(item => item.title))].join(', ');
    
    // Prepare WhatsApp message
    let message = `Halo, saya ingin memesan rumah dari Showcase Rumah Panggung Desa Mokobang:\n\n`;
    itemsForContact.forEach(item => {
        message += `- Tipe: ${item.title} (${item.quantity}x)\n`;
        message += `  Harga: ${item.price}\n\n`;
    });
    
    message += `Total: ${totalItemsForContact} produk\n`;
    message += `\nMohon info lebih lanjut tentang proses pemesanan. Terima kasih.`;
    const encodedMessage = encodeURIComponent(message);
    
    checkoutButtonsContainer.innerHTML += `
        <a href="https://wa.me/${whatsappNumber}?text=${encodedMessage}" target="_blank" class="checkout-btn whatsapp-btn">
            <i class="fab fa-whatsapp"></i> Pesan ${houseTypes} via WhatsApp (${contact})
        </a>
    `;
});
    
    // Add event listeners for quantity controls
    document.querySelectorAll('.minus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            updateQuantity(parseInt(this.getAttribute('data-index')), -1);
        });
    });
    
    document.querySelectorAll('.plus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            updateQuantity(parseInt(this.getAttribute('data-index')), 1);
        });
    });
    
    // Add event listeners for remove buttons
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            removeItem(parseInt(this.getAttribute('data-index')));
        });
    });
}

function updateQuantity(index, change) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if (index >= 0 && index < cart.length) {
        cart[index].quantity += change;
        
        // Remove item if quantity reaches 0
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
    }
}

function removeItem(index) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if (index >= 0 && index < cart.length) {
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
    }
}
</script>

</body>
</html>