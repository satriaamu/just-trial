<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mokobang";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Proses registrasi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Encrypt password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into database
    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";

    if ($conn->query($sql) === TRUE) {
        echo "Registration successful!";
        header("Location: login"); // Redirect to login page
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
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
    <!-- Favicon -->
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
                <h2>Register</h2>
                <form method="POST" action="">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Daftar</button>
                </form>
                <p>Sudah punya akun? <a href="login.php">Klik di sini</a></p>
            </div>
        </div>
    </div>
</body>
</html>
