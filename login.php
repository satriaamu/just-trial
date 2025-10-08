<?php
// Koneksi ke database
require_once 'config.php';
$conn = getMysqliConnection();

// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mengambil data pengguna berdasarkan username
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    // Pesan error akan disimpan di variabel untuk ditampilkan nanti
    $error_message = "";

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Set session atau cookie untuk pengguna yang berhasil login (opsional)
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Arahkan ke index setelah login berhasil
            header("Location: index");
            exit();
        } else {
            $error_message = "Password salah!";
        }
    } else {
        $error_message = "Username tidak ditemukan!";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Showcase Rumah Panggung Desa Mokobang</title>
    <link rel="icon" href="image/logo.png" type="image/x-icon">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

body {
    background-image: url('image/background.jpg');
    background-size: cover;
    background-position: center;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative; /* Menambahkan posisi relatif agar bisa menambahkan lapisan transparan */
}

body::before {
    content: ''; /* Membuat lapisan baru */
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6); /* Efek gelap transparan */
    z-index: -1; /* Menempatkan lapisan gelap di bawah semua elemen lainnya */
}
        /* Container for form */
        .background {
            background: rgba(0, 0, 0, 0.6); /* Semi-transparent background */
            padding: 40px;
            border-radius: 8px;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .form-container .logo {
            width: 150px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            padding: 12px;
            border: none;
            background-color: #2980b9;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #3498db;
        }

        p {
            font-size: 14px;
            color: #555;
            margin-top: 20px;
        }

        a {
            text-decoration: none;
            color: #2980b9;
        }

        a:hover {
            color: #3498db;
        }

        /* Error message styles */
        .error-message {
            color: red;
            margin-top: 10px;
        }

        /* Mobile Styles */
    @media (max-width: 768px) {
        body {
            padding: 15px;
            background-attachment: scroll;
        }
        
        .background {
            padding: 15px;
            width: 100%;
            max-width: 100%;
        }
        
        .form-container {
            padding: 20px;
        }
        
        .form-container .logo {
            width: 100px;
            margin-bottom: 15px;
        }
        
        h2 {
            font-size: 20px;
            margin-bottom: 15px;
        }
        
        input {
            padding: 12px;
            font-size: 15px;
        }
        
        button {
            padding: 13px;
            font-size: 15px;
        }
        
        p {
            font-size: 13px;
        }
    }
    </style>
</head>
<body>
    <div class="background">
        <div class="container">
            <div class="form-container">
                <img src="image/logo.png" alt="Logo" class="logo">
                <h2>Login</h2>
                <!-- Menampilkan pesan error jika ada -->
                <?php if (!empty($error_message)): ?>
                    <p class="error-message"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
                <p>Belum punya akun? <a href="register.php">Klik di sini untuk daftar</a></p>
            </div>
        </div>
    </div>
</body>
</html>
